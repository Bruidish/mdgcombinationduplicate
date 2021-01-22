<?php
/**
 * @author Michel Dumont <michel.dumont.io>
 * @version 1.0.0 - 2021-01-21
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.6 - 1.7
 */

namespace mdg\combinationduplicate\core\Traits;

if (!defined('_PS_VERSION_')) {
    exit;
}

trait SmartyTrait
{
    /**
     * Retourne un tableau associatif après l'avoir filtré
     *
     * @param array tableau à filtrer
     * @param string clé du tableau qui sert de filtre
     *
     * @return array
     */
    public static function sortByKey($array, $key = 'name')
    {
        uasort($array, function ($a, $b) use ($key) {
            return strcmp($a[$key], $b[$key]);
        });

        return $array;
    }
}
