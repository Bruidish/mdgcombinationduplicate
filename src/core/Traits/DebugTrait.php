<?php
/**
 * @author:  Michel Dumont <michel.dumont.io>
 * @version: 1.0.0 - 2018-03-30
 * @license: http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package: prestashop 1.6 - 1.7
 */

namespace mdg\combinationduplicate\core\Traits;

if (!defined('_PS_VERSION_')) {
    exit;
}

trait DebugTrait
{
    protected function getLogger()
    {
        $logger = new \FileLogger();
        $logger->setFilename(_PS_ROOT_DIR_ . "/var/logs/{$this->name}_debug.log");
        return $logger;
    }

    protected function logDebug($message)
    {
        $this->getLogger()->logDebug("{$message}");
    }

    protected function logError($message)
    {
        $this->getLogger()->logError("{$message}");
    }
}
