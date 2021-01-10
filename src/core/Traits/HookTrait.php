<?php
/**
 * @author:  Michel Dumont <michel.dumont.io>
 * @version: 1.0.0 - 2018-03-30
 * @license: http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package: prestashop 1.6 - 1.7
 */

namespace mdg\combinationduplicate\core\Traits;

use mdg\combinationduplicate\Forms\ProductForm;
use mdg\combinationduplicate\Models\CombinationModel;
use mdg\combinationduplicate\Models\ProductModel;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

if (!defined('_PS_VERSION_')) {
    exit;
}

trait HookTrait
{
    /** Charge les médias en front
     * @since PS 1.7
     *
     * @inheritdoc
     */
    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet("module-{$this->name}-front-css", "modules/{$this->name}/views/css/{$this->name}-front.css");
    }

    /**
     * Si une commande est payée à la validation
     *
     * @inheritdoc
     */
    public function hookActionValidateOrder($params)
    {
        $this->stockDownMainCombinations($params['order']->id);
    }

    /**
     * Surcouche la template des déclinaisons pour affihcer les lots dans un second espace
     * @inheritdoc
     */
    public function hookDisplayOverrideTemplate(array $params)
    {
        if ($params['controller']->php_self == 'product') {
            $idProduct = \Tools::getValue('id_product');
            $productSettings = ProductModel::getExistsInstanceByIdObject($idProduct);

            // Si le produit possède une régle de déclinaisons dupliquées
            if ($productSettings && $productSettings->active) {
                $combinationsDuplicatedIdsAttributes = [];
                $sqlCombinationsDuplicatedIdsAttributes = \Db::getInstance()->executes(
                    (new \DbQuery)
                        ->select("id_attribute")
                        ->from("product_attribute_combination")
                        ->where("id_product_attribute IN(" .
                            (new \DbQuery)
                                ->select("id_object")
                                ->from(CombinationModel::$definition['table'])
                                ->where("id_product = {$idProduct}")
                                ->build()
                            . ")")
                        ->build()
                );
                if ($sqlCombinationsDuplicatedIdsAttributes) {
                    foreach ($sqlCombinationsDuplicatedIdsAttributes as $rowCombinationsDuplicatedIdsAttributes) {
                        $combinationsDuplicatedIdsAttributes[] = $rowCombinationsDuplicatedIdsAttributes['id_attribute'];
                    }

                    $this->context->smarty->assign([
                        "combinationDuplicatedSettings" => $productSettings,
                        "combinationsDuplicatedIdsAttributes" => $combinationsDuplicatedIdsAttributes,
                    ]);

                    switch ($params['template_file']) {
                        case 'catalog/product':
                            return "module:{$this->name}/views/templates/override/product.tpl";
                        case 'catalog/_partials/product-variants':
                            return "module:{$this->name}/views/templates/override/product-variants.tpl";
                    }
                }
            }
        }
    }

    #region BO PRODUCT
    /**
     * Génère le formulaire dans la fiche produit
     *
     * @inheritdoc
     */
    public function hookDisplayAdminProductsOptionsStepBottom(array $params)
    {
        $productId = (int) $params['id_product'];
        $kernel = SymfonyContainer::getInstance();

        $twig = $kernel->get('twig');
        $formFactory = $kernel->get('form.factory');
        $legacyContext = $this->get('prestashop.adapter.legacy.context');

        $productForm = new ProductForm($productId, $legacyContext);

        return $twig->render(_PS_MODULE_DIR_ . "{$this->name}/views/templates/admin/product-combination-bottom.html.twig", [
            'form' => $productForm->buildForm($formFactory)->createView(),
        ]);
    }

    /**
     * CRUD sur les données du module après adinistration du produit
     *
     * @inheritdoc
     */
    public function hookActionObjectProductAddAfter(array $params)
    {
        $output = true;

        $productId = (int) $params['object']->id;
        $productForm = new ProductForm($productId);

        $output &= $productForm->processForm(\Tools::getValue($this->name));
        if ($productForm->object->active) {
            $output &= $this->duplicateCombinationsByProduct($productId, $productForm->object->prefix, $productForm->object->suffix, $productForm->object->replace_from, $productForm->object->replace_to, $productForm->object->quantity, $productForm->object->price_multiplicator);
            $output &= $this->stockFullDuplicatedCombinations($productId);
        }

        return $output;
    }
    public function hookActionObjectProductUpdateAfter($params)
    {
        return $this->hookActionObjectProductAddAfter($params);
    }
    public function hookActionObjectProductDeleteAfter($params)
    {
        (ProductModel::getInstanceByIdObject($params['object']->id))->delete();
    }
    #endregion BO PRODUCT

    #region BO Combination
    /**
     * CRUD sur les données du module après adinistration d'une déclinaison
     *
     * @inheritdoc
     */
    public function hookActionObjectCombinationDeleteAfter($params)
    {
        (CombinationModel::getInstanceByIdObject($params['object']->id))->delete();
    }
    #endregion BO Combination

}
