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

    /** @var string préfix à ajouter au titre de la déclinaison */
    public $prefix;

    /** @var string suffix à ajouter au titre de la déclinaison */
    public $suffix;

    /** @var string terme à remplacer dans le titre de la déclinaison */
    public $replace_from;

    /** @var string terme de remplacement */
    public $replace_to;

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
            'replace_from' => ['type' => self::TYPE_STRING, 'lang' => true],
            'replace_to' => ['type' => self::TYPE_STRING, 'lang' => true],
            'quantity' => ['type' => self::TYPE_INT],
            'price_multiplicator' => ['type' => self::TYPE_FLOAT],
        ],
    ];

    /** Instancie cette classe à l'objet Prestashop associé
     *
     * @param int id de l'object associé
     * @param bool force la création d'une ligne en base de donnée
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

    /** Retourne si un objet Prestashop est assoicé et est actif
     *
     * @param int id de l'object associé
     *
     * @return boolean
     */
    public static function getIsActiveByIdObject($idObject)
    {
        return (bool) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new \DbQuery)
                ->select("count(*)")
                ->from(static::$definition['table'])
                ->where("id_object={$idObject}")
                ->build()
        );
    }
}
