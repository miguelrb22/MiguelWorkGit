<?php

/**
 * Created by PhpStorm.
 * User: migue
 * Date: 19/01/2017
 * Time: 13:31
 */
class AdminRedsysdeferredHistoricController extends ModuleAdminController
{

    public function __construct() {

       $module = Module::getInstanceByName('redsysdeferred'); //Cargamos temporalmente esta instancia porque aun no se ha cargado el del padre y necesitamos el modulo
        require_once ($module->loadClass()."/classes/RedsysdeferredHistoricUrl.php");
        $this->bootstrap = true;
        $this->table = 'redsysdeferred_historic_url';
        $this->className = 'RedsysdeferredHistoricUrl';
        $this->lang = false;
        $this->explicitSelect = true;
        $this->context = Context::getContext();
        $this->identifier = 'id_historic';
        $this->list_no_link = true;


        $this->fields_list = array(
            'id_historic' => array(
                'title' => $this->l('Id'),
                'type' => 'text',
                'order_by' => true,
            ),
            'url' => array(
                'title' => $this->l('Url'),
                'type' => 'text',
            ),
            'date_upd' => array(
                'title' => $this->l('Date updated'),
                'type' => 'text',
                'align' => 'text-right',
                'order_by' => true
            ),
            'paid' => array(
                'title' => $this->l('Paid'),
                'type' => 'text',
                'name' => 'paided',
                'align' => 'text-right pagado',
                'order_by' => true
            )
        );

        parent::__construct();
    }

    public function initToolbar()
    {
        switch ($this->display)
        {
            case 'add':
            case 'edit':
                // Default save button - action dynamically handled in javascript
                $this->toolbar_btn['save'] = array(
                    'href' => '#',
                    'desc' => $this->l('Save')
                );
                $back = Tools::safeOutput(Tools::getValue('back', ''));
                if (empty($back))
                    $back = self::$currentIndex.'&token='.$this->token;
                if (!Validate::isCleanHtml($back))
                    die(Tools::displayError());
                if (!$this->lite_display)
                    $this->toolbar_btn['cancel'] = array(
                        'href' => $back,
                        'desc' => $this->l('Cancel')
                    );
                break;
            case 'view':
                // Default cancel button - like old back link
                $back = Tools::safeOutput(Tools::getValue('back', ''));
                if (empty($back))
                    $back = self::$currentIndex.'&token='.$this->token;
                if (!Validate::isCleanHtml($back))
                    die(Tools::displayError());
                if (!$this->lite_display)
                    $this->toolbar_btn['back'] = array(
                        'href' => $back,
                        'desc' => $this->l('Back to list')
                    );
                break;
            case 'options':
                $this->toolbar_btn['save'] = array(
                    'href' => '#',
                    'desc' => $this->l('Save')
                );
                break;
            default: // list
                /*$this->toolbar_btn['new'] = array(
                    'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
                    'desc' => $this->l('Add new')
                );*/
                if ($this->allow_export)
                    $this->toolbar_btn['export'] = array(
                        'href' => self::$currentIndex.'&export'.$this->table.'&token='.$this->token,
                        'desc' => $this->l('Export')
                    );
        }
        $this->addToolBarModulesListButton();
    }

    public function setMedia()
    {
        $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/paid.js');
        return parent::setMedia();
    }

}