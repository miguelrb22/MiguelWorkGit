<?php
/**
 *
 * @author    Prestaquality.com
 * @copyright 2014 - 2017 Prestaquality
 * @license   Commercial license see license.txt
 * @category  Prestashop
 * @category  Module
 * Support by mail  : info@prestaquality.com
 */

if (!defined('_PS_VERSION_')) {
    exit;
}


$pq_lib_path = dirname(__FILE__).'/lib/PqKasnormegafeedOrdersLib.php';
if (!file_exists($pq_lib_path)) {
    exit;
}
require_once $pq_lib_path;

class pqkasnormegafeedorders extends Module
{
    public function __construct()
    {

        $this->name = 'pqkasnormegafeedorders';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Prestaquality';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Kasnor Mega Feed Orders');
        $this->description = $this->l('Module to send the orders at Kasnor');

        $this->confirmUninstall = $this->l('Are you sure?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
         try {

            $correct = parent::install();

            return $correct;

         } catch (Exception $ex)
         {
              PqKasnormegafeedOrdersLib::logException($ex);
         }

    }

    public function uninstall()
    {

        return parent::uninstall();
    }

    

}