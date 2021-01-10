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

class ProductModel extends \mdg\combinationduplicate\Models\ObjectModel
{
    /** @var int id de l'objet Prestashop associé */
    public $id_object;

    /** @var bool Le fonctionnel est actif/inactif sur le produit */
    public $active;

    /** @var string terme du nom de la déclinaison à remplacer */
    public $prefix;

    /** @var string terme de remplacement du nom de la déclinaison */
    public $suffix;

    /** @var int nombre d'unité pour chaque lot */
    public $quantity;

    /** @var float coefficient multiplicateur à appliquer au prix */
    public $price_multiplicator;

    public static $definition = [
        'table' => 'mdgcombinationduplicate_product',
        'primary' => 'id_association',
        'multilang' => true,
        'multi_shop' => false,
        'fields' => [
            'id_object' => ['type' => self::TYPE_INT],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'prefix' => ['type' => self::TYPE_STRING, 'lang' => true],
            'suffix' => ['type' => self::TYPE_STRING, 'lang' => true],
            'quantity' => ['type' => self::TYPE_INT],
            'price_multiplicator' => ['type' => self::TYPE_FLOAT],
        ],
    ];

    /** Instancie cette classe à l'objet Prestashop associé
     *
     * @param int id de l'object associé
     *
     * @return self
     */
    public static function getInstanceByIdObject($idObject)
    {
        $id = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue("SELECT " . static::$definition['primary'] . " FROM " . _DB_PREFIX_ . static::$definition['table'] . " WHERE id_object={$idObject}");

        if (!$id) {
            $that = new self();
            $that->id_object = $idObject;
            $that->add();
            $id = $that->id;
        }

        return new self($id);
    }
}
