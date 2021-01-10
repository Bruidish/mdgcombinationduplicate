<?php
/**
 * @author:  Michel Dumont <michel.dumont.io>
 * @version 1.0.0 - 2021-01-08
 * @copyright 2021
 * @license: http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package: prestashop 1.7
 */

require_once __DIR__ . '/vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class mdgcombinationduplicate extends \Module
{
    use mdg\combinationduplicate\Traits\DebugTrait;
    use mdg\combinationduplicate\Traits\HookTrait;
    use mdg\combinationduplicate\Traits\ProcessTrait;

    public function __construct()
    {
        $this->name = 'mdgcombinationduplicate';
        $this->tab = 'administration';
        $this->version = '0.0.1';
        $this->author = 'Michel Dumont';
        $this->need_instance = 0;
        $this->bootstrap = 1;
        $this->ps_versions_compliancy = ['min' => '1.7.7.0', 'max' => _PS_VERSION_];
        $this->ps_versions_dir = version_compare(_PS_VERSION_, '1.7', '<') ? 'v16' : 'v17';

        foreach (glob(_PS_MODULE_DIR_ . "{$this->name}/controllers/front/*.php") as $file) {
            if ($file !== 'index.php') {
                $this->controllers[] = basename($file, '.php');
            }
        }

        parent::__construct();

        $this->displayName = $this->l('(mdg) Duplication of combinations');
        $this->description = $this->l('Duplicate your variations with different packaging.');
    }

    #region INSTALL
    /**
     * @inheritdoc
     */
    public function install()
    {
        if (parent::install()) {
            return (new \mdg\combinationduplicate\Controllers\InstallerController)->install();
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function uninstall()
    {
        if (parent::uninstall()) {
            return (new \mdg\combinationduplicate\Controllers\InstallerController)->uninstall();
        }

        return false;
    }
    #endregion
}
