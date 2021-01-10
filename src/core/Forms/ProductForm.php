<?php
/**
 * Gère le formulaire du model Product
 *
 * @author Michel Dumont <michel.dumont.io>
 * @version 1.0.0 - 2021-01-08
 * @copyright 2021
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.7
 */

namespace mdg\combinationduplicate\core\Forms;

use mdg\combinationduplicate\Models\ProductModel;
use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\TranslateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProductForm extends \mdg\combinationduplicate\Forms\ObjectForm
{
    /**
     * @inheritdoc
     */
    public function __construct($object = null, $legacyContext = null)
    {
        parent::__construct($object, $legacyContext);
        parent::constructFormBuilder(__FILE__, ProductModel::class, $object);
    }

    /** Retourne le formulaire pour la page produit */
    public function buildForm($builder)
    {
        return $builder
            ->createNamedBuilder($this->module->name, FormType::class, $this->object)
            ->add('active', SwitchType::class, [
                'label' => $this->module->l('Active duplication', $this->form_name),
            ])
            ->add('prefix', TranslateType::class, [
                'type' => TextType::class,
                'locales' => $this->locales,
                'label' => $this->module->l('Add a prefix', $this->form_name),
            ])
            ->add('suffix', TranslateType::class, [
                'type' => TextType::class,
                'locales' => $this->locales,
                'label' => $this->module->l('Add a suffix', $this->form_name),
            ])
            ->add('replace_from', TranslateType::class, [
                'type' => TextType::class,
                'locales' => $this->locales,
                'label' => $this->module->l('Replace part of the name', $this->form_name),
            ])
            ->add('replace_to', TranslateType::class, [
                'type' => TextType::class,
                'locales' => $this->locales,
                'label' => $this->module->l('With this', $this->form_name),
            ])
            ->add('quantity', IntegerType::class, [
                'label' => $this->module->l('Quantity by package', $this->form_name),
            ])
            ->add('price_multiplicator', NumberType::class, [
                'label' => $this->module->l('Price multiplicator', $this->form_name),
            ])
            ->getForm();
    }

    /** Traite l'enregistrement du formulaire de la page produit
     *
     * @param array datas à enregistrer
     *
     * @return bool
     */
    public function processForm($formData)
    {
        $output = true;

        // Enregistrement des données
        foreach ($this->object::$definition['fields'] as $fieldName => $fieldParams) {
            $this->object->{$fieldName} = (isset($formData[$fieldName]) ? $formData[$fieldName] : $this->object->{$fieldName});
        }

        $output &= $this->object->save();

        return $output;
    }
}
