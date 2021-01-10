<?php
/**
 * @author Michel Dumont <michel.dumont.io>
 * @version 1.0.0 - 2021-01-09
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.7
 */

namespace mdg\combinationduplicate\core\Traits;

use mdg\combinationduplicate\Models\CombinationModel;

if (!defined('_PS_VERSION_')) {
    exit;
}

trait ProcessTrait
{
    /**
     * Force le stock des déclinaisons dupliquées
     *
     * @param int id_produit
     *
     * @return boolean
     */
    public function stockFullDuplicatedCombinations($idProduct)
    {
        $output = true;

        $dataCombinations = \Db::getInstance()->executes(
            (new \DbQuery())
                ->select("cm.id_object")
                ->from(CombinationModel::$definition['table'], "cm")
                ->where("cm.id_product = {$idProduct}")
        );
        if ($dataCombinations) {
            foreach ($dataCombinations as $rowCombination) {
                $output &= \StockAvailable::setQuantity($idProduct, $rowCombination['id_object'], 9999);
            }
        }

        return $output;
    }

    /**
     * Déstocker les déclinaisons préncipales et re stock les déclinaisons dupliquées
     *
     * @param int id de la commande passée
     *
     * @return void
     */
    public function stockDownMainCombinations($idOrder)
    {
        $orderProducts = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new \DbQuery)
                ->select("product_attribute_id, product_quantity")
                ->from("order_detail")
                ->where("id_order={$idOrder}")
                ->build()
        );

        if ($orderProducts) {
            foreach ($orderProducts as $orderProduct) {
                $combination = CombinationModel::getExistsInstanceByIdObject($orderProduct['product_attribute_id']);
                if ($combination) {
                    \StockAvailable::setQuantity($combination->id_product, $combination->id_object, 9999);
                    \StockAvailable::updateQuantity($combination->id_product, $combination->id_main_combination, -1 * $orderProduct['product_quantity'] * $combination->quantity);
                }
            }
        }
    }

    /**
     * Dupplique les déclinaisons d'un produit
     *
     * @param int id_produit
     * @param string prefixe à ajouter aux valeurs des déclinaisons
     * @param string sufixe à ajouter aux valeurs des déclinaisons
     * @param string terme à remplacer
     * @param string terme de remplacement
     * @param int quantité du lot
     * @param float coeffichient à apliquer au prix
     *
     * @return boolean
     */
    public function duplicateCombinationsByProduct($idProduct, $prefix, $suffix, $replace_from, $replace_to, $quantity, $priceMultiplicator)
    {
        $output = true;

        $sqlCombinations = new \DbQuery();
        $sqlCombinations->select("pa.id_product_attribute");
        $sqlCombinations->from("product_attribute", "pa");
        $sqlCombinations->where("pa.id_product = {$idProduct}");
        $sqlCombinations->where("pa.id_product_attribute NOT IN(" .
            (new \DbQuery)
                ->select("id_object")
                ->from(CombinationModel::$definition['table'], "cm")
                ->where("cm.id_product = {$idProduct}")
                ->build()
            . ")");
        $sqlCombinations->where("pa.id_product_attribute NOT IN(" .
            (new \DbQuery)
                ->select("id_main_combination")
                ->from(CombinationModel::$definition['table'], "cm")
                ->where("cm.id_product = {$idProduct}")
                ->build()
            . ")");

        $dataCombinations = \Db::getInstance()->executes($sqlCombinations);
        if ($dataCombinations) {
            foreach ($dataCombinations as $rowCombination) {
                $output &= $this->_dupplicateCombination($idProduct, $rowCombination['id_product_attribute'], $prefix, $suffix, $replace_from, $replace_to, $quantity, $priceMultiplicator);
            }
        }

        return $output;
    }

    /**
     * Dupplique une déclinaison
     *
     * @param int id du produit
     * @param int id de la déclinaison à dupliquer
     * @param string prefixe à ajouter aux valeurs des déclinaisons
     * @param string sufixe à ajouter aux valeurs des déclinaisons
     * @param string terme à remplacer
     * @param string terme de remplacement
     * @param int quantité du lot
     * @param float coeffichient à apliquer au prix
     *
     * @return boolean
     */
    private function _dupplicateCombination($idProduct, $idProductAttribute, $prefix = null, $suffix = null, $replace_from = null, $replace_to = null, $quantity = null, $priceMultiplicator = null)
    {
        $output = true;

        if ($prefix || $suffix) {
            $isMultiLangActivated = (bool) \Language::isMultiLanguageActivated();
            $idLangDefault = \Configuration::get('PS_LANG_DEFAULT');
            $dataProductAttribute = \Db::getInstance()->getRow(
                (new \DbQuery)
                    ->select("*")
                    ->from("product_attribute")
                    ->where("id_product_attribute = {$idProductAttribute}")
                    ->build()
            );
            $dataAttributes = \Db::getInstance()->executes(
                (new \DbQuery)
                    ->select("a.*, al.*")
                    ->from("product_attribute_combination", "pac")
                    ->innerJoin("attribute", "a", "a.id_attribute=pac.id_attribute")
                    ->innerJoin("attribute_lang", "al", "al.id_attribute=pac.id_attribute AND al.id_lang={$idLangDefault}")
                    ->where("pac.id_product_attribute = {$idProductAttribute}")
                    ->build()
            );

            if ($dataAttributes) {
                $attributesClonedList = [];
                foreach ($dataAttributes as $rowAttribute) {
                    $rowAttribute['name'] = str_replace($replace_from, $replace_to, $rowAttribute['name']);

                    // Vérifie si l'attribut existe dans la langue par défaut avec préfixes et suffixes
                    $idAttributeCloned = \Db::getInstance()->getValue(
                        (new \DbQuery)
                            ->select("al.id_attribute")
                            ->from("attribute_lang", "al")
                            ->where("al.id_lang={$idLangDefault}")
                            ->where("al.name='{$prefix[$idLangDefault]}{$rowAttribute['name']}{$suffix[$idLangDefault]}'")
                            ->build()
                    );

                    // Créait l'attribut s'il n'existe pas
                    if (!$idAttributeCloned) {
                        $attribute = new \Attribute();
                        $attribute->id_attribute_group = $rowAttribute['id_attribute_group'];
                        if ($isMultiLangActivated) {
                            $dataAttributeNames = \Db::getInstance()->executes(
                                (new \DbQuery)
                                    ->select("al.name, al.id_lang")
                                    ->from("attribute_lang", "al")
                                    ->where("al.id_attribute='{$rowAttribute['id_attribute']}'")
                                    ->build()
                            );
                            foreach ($dataAttributeNames as $rowAttributeName) {
                                $attribute->name[$rowAttributeName['id_lang']] = "{$prefix[$idLangDefault]}{$rowAttributeName['name']}{$suffix[$idLangDefault]}";
                            }
                        } else {
                            $attribute->name[$idLangDefault] = "{$prefix[$idLangDefault]}{$rowAttribute['name']}{$suffix[$idLangDefault]}";
                        }
                        $attribute->add();
                        $idAttributeCloned = $attribute->id;
                    }

                    $attributesClonedList[] = $idAttributeCloned;
                }

                // Créait la combinaison associé au produit
                $product = new \Product($idProduct);
                $priceMultiplicator = str_replace(',', '.', $priceMultiplicator);
                $idProductAttributeCloned = (int) $product->addCombinationEntity(
                    $dataProductAttribute['wholesale_price'] * $quantity,
                    (($product->price + $dataProductAttribute['price']) * $priceMultiplicator) - $product->price,
                    (($product->weight + $dataProductAttribute['weight']) * $quantity) - $product->weight,
                    (($product->unit_price + $dataProductAttribute['unit_price_impact']) * $priceMultiplicator) - $product->unit_price,
                    null,
                    null, // quantity DEPRECATED
                    null,
                    $dataProductAttribute['reference'],
                    null,
                    $dataProductAttribute['ean13'],
                    null
                );

                $output &= (new \Combination($idProductAttributeCloned))->setAttributes($attributesClonedList);

                // Associe la déclinaison au module
                $output &= \Db::getInstance()->insert(CombinationModel::$definition['table'], [
                    'id_object' => $idProductAttributeCloned,
                    'id_product' => $idProduct,
                    'id_main_combination' => $idProductAttribute,
                    'quantity' => $quantity,
                ]);
            }
        }

        return $output;
    }

}
