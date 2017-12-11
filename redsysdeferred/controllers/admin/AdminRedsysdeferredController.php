<?php

/**
 * Created by PhpStorm.
 * User: migue
 * Date: 18/01/2017
 * Time: 13:42
 */
class AdminRedsysdeferredController extends ModuleAdminController
{

    public function __construct()
    {
        $this->bootstrap = false;
        $this->display = 'view';
        parent::__construct();
        $this->meta_title = $this->l('Url generator');
        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $base_link = 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/redsysdeferred/payment.php';
        } else {
            $base_link = $this->context->link->getModuleLink('redsysdeferred', 'payment');
        }
        //Tab Module Configuration
        $this->context->smarty->assign(array(
            'currencies'  => Currency::getCurrencies(),
            'base_link'   => $base_link
        ));
    }

    public function setMedia()
    {
        $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/md5.min.js');
        return parent::setMedia();
    }

}