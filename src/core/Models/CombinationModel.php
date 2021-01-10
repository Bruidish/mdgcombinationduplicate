<?php
/**
 * @author Michel Dumont <michel.dumont.io>
 * @version 1.0.0 - 2021-01-08
 * @copyright 2021
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.7
 */

namespace mdg\combinationduplicate\core\Models;

if (!defined('_CAN_LOAD_FILES_')) {
    exit;
}

class CombinationModel extends \mdg\combinationduplicate\Models\ObjectModel
{
    /** @var int id de l'objet Prestashop associé (id_product_attribute) */
    public $id_object;

    /** @var int id du produit de référence */
    public $id_product;

    /** @var int id de la déclinaison générée lors de la duplication (id_product_attribute) */
    public $id_main_combination;

    /** @var int quantité à destocker sur la déclinaison initiale */
    public $quantity;

    public static $definition = [
        'table' => 'mdgcombinationduplicate_combination',
        'primary' => 'id_association',
        'multilang' => false,
        'multi_shop' => false,
        'fields' => [
            'id_object' => ['type' => self::TYPE_INT],
            'id_product' => ['type' => self::TYPE_INT],
            'id_main_combination' => ['type' => self::TYPE_INT],
            'quantity' => ['type' => self::TYPE_INT],
        ],
    ];

    /**
     * Instancie cette classe à l'objet Prestashop associé
     * Créait une nouvelle entrée si elle n'existe pas
     *
     * @param int id de l'object associé
     * @param bool Créait une nouvelle entrée si elle n'existe pas
     *
     * @return self
     */
    public static function getInstanceByIdObject($idObject, $force = true)
    {
        $id = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue("SELECT " . static::$definition['primary'] . " FROM " . _DB_PREFIX_ . static::$definition['table'] . " WHERE id_object={$idObject}");

        if (!$id && $force) {
            $that = new self();
            $that->id_object = $idObject;
            $that->add();
            $id = $that->id;
        }

        return new self($id);
    }

    /**
     * Instancie cette classe à l'objet Prestashop associé
     *
     * @param int id de l'object associé
     *
     * @return self|false
     */
    public static function getExistsInstanceByIdObject($idObject)
    {
        $output = static::getInstanceByIdObject($idObject, false);
        return $output->id ? $output : false;
    }
}
