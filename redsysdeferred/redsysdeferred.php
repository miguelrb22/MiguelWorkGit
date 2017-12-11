<?php
/**
* Redsys Deferred - Get payments without orders.
*
* This product is licensed for one customer to use in one installation. Site developer has the
* right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitues a violation of the user agreement.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade module to newer
* versions in the future.
*
*  @author    idnovate.com <info@idnovate.com>
*  @copyright 2015 PrestaShop SA
*  @license   See above
*/

if (!defined('_PS_VERSION_')) {
    exit;
}


if (!class_exists("RedsysAPIR")) {
    require_once(dirname(__FILE__).'/apiRedsys/apiRedsysFinal.php');
}

class RedsysDeferred extends PaymentModule
{
    protected $_postErrors = array();
    protected $_success = false;

    public function __construct()
    {
        $this->name = 'redsysdeferred';
        $this->tab = 'payments_gateways';
        $this->version = '1.1.9';

        $this->author = 'idnovate';
        $this->module_key = 'fe43db648dcf79b56d9c8e25701dc44d';
        $this->displayName = $this->l('Redsys Deferred - Get payments without orders');
        $this->description = $this->l('Multicommerce, multiterminal and multicurrency support for credit card payments with REDSYS platform (SERVIRED / SERMEPA). You can receive payments without a related order.');

        parent::__construct();

        /* Backward compatibility */
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
        }
    }

    public function install()
    {
        // Valores por defecto al instalar el módulo
        if (!parent::install()
            || !$this->installTab()
            || !$this->installTab2()
            || !Configuration::updateValue('REDSYS_DEF_URLTPV', '1')
            || !Configuration::updateValue('REDSYS_DEF_URLTPV2', '1')
            || !Configuration::updateValue('REDSYS_DEF_URLTPV3', '1')
            || !Configuration::updateValue('REDSYS_DEF_TIPOFIRMA', 0)
            || !Configuration::updateValue('REDSYS_DEF_TIPOFIRMA2', 0)
            || !Configuration::updateValue('REDSYS_DEF_TIPOFIRMA3', 0)
            || !Configuration::updateValue('REDSYS_DEF_NOTIFICACION', 1)
            || !Configuration::updateValue('REDSYS_DEF_SSL', 0)
            || !Configuration::updateValue('REDSYS_DEF_IDIOMAS_ESTADO', 0)
            || !Configuration::updateValue('REDSYS_DEF_MAIL_CUSTOMER', 0)
            || !Configuration::updateValue('REDSYS_DEF_CLAVE', '')
            || !Configuration::updateValue('REDSYS_DEF_CLAVE2', '')
            || !Configuration::updateValue('REDSYS_DEF_CLAVE3', '')
            || !Configuration::updateValue('REDSYS_DEF_NOMBRE', '')
            || !Configuration::updateValue('REDSYS_DEF_NOMBRE2', '')
            || !Configuration::updateValue('REDSYS_DEF_NOMBRE3', '')
            || !Configuration::updateValue('REDSYS_DEF_CODIGO', '')
            || !Configuration::updateValue('REDSYS_DEF_CODIGO2', '')
            || !Configuration::updateValue('REDSYS_DEF_CODIGO3', '')
            || !Configuration::updateValue('REDSYS_DEF_TERMINAL', '')
            || !Configuration::updateValue('REDSYS_DEF_TERMINAL2', '')
            || !Configuration::updateValue('REDSYS_DEF_TERMINAL3', '')
            || !Configuration::updateValue('REDSYS_DEF_MONEDA', '')
            || !Configuration::updateValue('REDSYS_DEF_MONEDA2', '')
            || !Configuration::updateValue('REDSYS_DEF_MONEDA3', '')
            || !Configuration::updateValue('REDSYS_DEF_TRANS', '')
            || !Configuration::updateValue('REDSYS_DEF_TRANS2', '')
            || !Configuration::updateValue('REDSYS_DEF_TRANS3', '')
            || !Configuration::updateValue('REDSYS_DEF_INDICE', '')
            || !$this->registerHook('header')) {
            return false;
        }

        include(dirname(__FILE__).'/sql/install.php');

        return true;
    }

    public function uninstall()
    {
        /* Valores a quitar si desinstalamos el módulo */
        if (!parent::uninstall()
            || !$this->uninstallTab()
            || !$this->uninstallTab2()
            || !Configuration::deleteByName('REDSYS_DEF_URLTPV')
            || !Configuration::deleteByName('REDSYS_DEF_URLTPV2')
            || !Configuration::deleteByName('REDSYS_DEF_URLTPV3')
            || !Configuration::deleteByName('REDSYS_DEF_CLAVE')
            || !Configuration::deleteByName('REDSYS_DEF_CLAVE2')
            || !Configuration::deleteByName('REDSYS_DEF_CLAVE3')
            || !Configuration::deleteByName('REDSYS_DEF_NOMBRE')
            || !Configuration::deleteByName('REDSYS_DEF_NOMBRE2')
            || !Configuration::deleteByName('REDSYS_DEF_NOMBRE3')
            || !Configuration::deleteByName('REDSYS_DEF_CODIGO')
            || !Configuration::deleteByName('REDSYS_DEF_CODIGO2')
            || !Configuration::deleteByName('REDSYS_DEF_CODIGO3')
            || !Configuration::deleteByName('REDSYS_DEF_TERMINAL')
            || !Configuration::deleteByName('REDSYS_DEF_TERMINAL2')
            || !Configuration::deleteByName('REDSYS_DEF_TERMINAL3')
            || !Configuration::deleteByName('REDSYS_DEF_TIPOFIRMA')
            || !Configuration::deleteByName('REDSYS_DEF_TIPOFIRMA2')
            || !Configuration::deleteByName('REDSYS_DEF_TIPOFIRMA3')
            || !Configuration::deleteByName('REDSYS_DEF_MONEDA')
            || !Configuration::deleteByName('REDSYS_DEF_MONEDA2')
            || !Configuration::deleteByName('REDSYS_DEF_MONEDA3')
            || !Configuration::deleteByName('REDSYS_DEF_TRANS')
            || !Configuration::deleteByName('REDSYS_DEF_TRANS2')
            || !Configuration::deleteByName('REDSYS_DEF_TRANS3')
            || !Configuration::deleteByName('REDSYS_DEF_NOTIFICACION')
            || !Configuration::deleteByName('REDSYS_DEF_SSL')
            || !Configuration::deleteByName('REDSYS_DEF_IDIOMAS_ESTADO')
            || !Configuration::deleteByName('REDSYS_DEF_MAIL_CUSTOMER')
            || !Configuration::deleteByName('REDSYS_DEF_INDICE')) {
            return false;
        }

        include(dirname(__FILE__).'/sql/uninstall.php');

        return true;
    }

    public function installTab()
{
    $tab = new Tab();
    $tab->active = 1;
    $tab->class_name = 'AdminRedsysdeferred';
    $tab->name = array();
    foreach (Language::getLanguages(true) as $lang) {
        $tab->name[$lang['id_lang']] = 'Redsys url generator';
    }


    $tab->id_parent = (int)Tab::getIdFromClassName('AdminOrders');


    $tab->module = $this->name;
    return $tab->add();
}

    public function uninstallTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminRedsysdeferred');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        } else {
            return false;
        }
    }

    /**
     * Instala la pestaña de la tabla de historicos
     * @return mixed
     */
    public function installTab2()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminRedsysdeferredHistoric';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Redsys url historic';
        }

        $tab->id_parent = (int)Tab::getIdFromClassName('AdminOrders');

        $tab->module = $this->name;
        return $tab->add();
    }

    /**
     * Desinstala la pestaña de la tabla de historicos
     * @return bool
     */
    public function uninstallTab2()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminRedsysdeferredHistoric');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        } else {
            return false;
        }
    }

    protected function _postValidation()
    {
        //Tab Module Configuration
        if (Tools::isSubmit('saveConfig')) {
            //If any of the fields for commerce is not empty
            $commerce = ' 1 - ';
            if (Tools::getValue('moneda')
                || Tools::getValue('clave')
                || Tools::getValue('nombre')
                || Tools::getValue('codigo')
                || Tools::getValue('terminal')) {
                if (!Tools::getValue('moneda')) {
                    $this->_postErrors[] = $this->l('Commerce configuration').$commerce.$this->l('Currency is required');
                }
                if (!Tools::getValue('clave')) {
                    $this->_postErrors[] = $this->l('Commerce configuration').$commerce.$this->l('Secret Key encryption is required');
                }
                if (!Tools::getValue('nombre')) {
                    $this->_postErrors[] = $this->l('Commerce configuration').$commerce.$this->l('Commerce name is required');
                }
                if (!Tools::getValue('codigo')) {
                    $this->_postErrors[] = $this->l('Commerce configuration').$commerce.$this->l('Commerce number (FUC) is required');
                }
                if (!Tools::getValue('terminal')) {
                    $this->_postErrors[] = $this->l('Commerce configuration').$commerce.$this->l('Terminal number is required');
                }
            }

            $commerce = ' 2 - ';
            if (Tools::getValue('moneda2')
                || Tools::getValue('clave2')
                || Tools::getValue('nombre2')
                || Tools::getValue('codigo2')
                || Tools::getValue('terminal2')) {
                if (!Tools::getValue('moneda2')) {
                    $this->_postErrors[] = $this->l('Commerce configuration').$commerce.$this->l('Currency is required');
                }
                if (!Tools::getValue('clave2')) {
                    $this->_postErrors[] = $this->l('Commerce configuration').$commerce.$this->l('Secret Key encryption is required');
                }
                if (!Tools::getValue('nombre2')) {
                    $this->_postErrors[] = $this->l('Commerce configuration').$commerce.$this->l('Commerce name is required');
                }
                if (!Tools::getValue('codigo2')) {
                    $this->_postErrors[] = $this->l('Commerce configuration').$commerce.$this->l('Commerce number (FUC) is required');
                }
                if (!Tools::getValue('terminal2')) {
                    $this->_postErrors[] = $this->l('Commerce configuration').$commerce.$this->l('Terminal number is required');
                }
            }

            $commerce = ' 3 - ';
            if (Tools::getValue('moneda3')
                || Tools::getValue('clave3')
                || Tools::getValue('nombre3')
                || Tools::getValue('codigo3')
                || Tools::getValue('terminal3')) {
                if (!Tools::getValue('moneda3')) {
                    $this->_postErrors[] = $this->l('Commerce configuration').$commerce.$this->l('Currency is required');
                }
                if (!Tools::getValue('clave3')) {
                    $this->_postErrors[] = $this->l('Commerce configuration').$commerce.$this->l('Secret Key encryption is required');
                }
                if (!Tools::getValue('nombre3')) {
                    $this->_postErrors[] = $this->l('Commerce configuration').$commerce.$this->l('Commerce name is required');
                }
                if (!Tools::getValue('codigo3')) {
                    $this->_postErrors[] = $this->l('Commerce configuration').$commerce.$this->l('Commerce number (FUC) is required');
                }
                if (!Tools::getValue('terminal3')) {
                    $this->_postErrors[] = $this->l('Commerce configuration').$commerce.$this->l('Terminal number is required');
                }
            }

            //2 terminals cannot have the same currency
            if ((Tools::getValue('moneda') && Tools::getValue('moneda2')) || (Tools::getValue('moneda') && Tools::getValue('moneda3')) || (Tools::getValue('moneda2') && Tools::getValue('moneda3'))) {
                if ((Tools::getValue('moneda') == Tools::getValue('moneda2')) || (Tools::getValue('moneda') == Tools::getValue('moneda3')) || (Tools::getValue('moneda2') == Tools::getValue('moneda3'))) {
                    $this->_postErrors[] = $this->l('You can not define same currency for more than one commerce.');
                }
            }
        }
        //Tab Payment
        /*elseif (isset($_POST['addPayment']))
        {
            if (!Tools::getValue('payment_amount')) {
                $this->_postErrors[] = $this->l('You have to define the payment amount.');
            }

        }*/

        if (!count($this->_postErrors)) {
            $this->_success = true;
        }
    }

    protected function _postProcess()
    {
        // Actualizar la configuración en la tabla de configuración (Configuration)
        if (Tools::isSubmit('saveConfig')) {
            Configuration::updateValue('REDSYS_DEF_URLTPV', Tools::getValue('urltpv'));
            Configuration::updateValue('REDSYS_DEF_URLTPV2', Tools::getValue('urltpv2'));
            Configuration::updateValue('REDSYS_DEF_URLTPV3', Tools::getValue('urltpv3'));
            Configuration::updateValue('REDSYS_DEF_CLAVE', Tools::getValue('clave'));
            Configuration::updateValue('REDSYS_DEF_CLAVE2', Tools::getValue('clave2'));
            Configuration::updateValue('REDSYS_DEF_CLAVE3', Tools::getValue('clave3'));
            Configuration::updateValue('REDSYS_DEF_NOMBRE', Tools::getValue('nombre'));
            Configuration::updateValue('REDSYS_DEF_NOMBRE2', Tools::getValue('nombre2'));
            Configuration::updateValue('REDSYS_DEF_NOMBRE3', Tools::getValue('nombre3'));
            Configuration::updateValue('REDSYS_DEF_CODIGO', Tools::getValue('codigo'));
            Configuration::updateValue('REDSYS_DEF_CODIGO2', Tools::getValue('codigo2'));
            Configuration::updateValue('REDSYS_DEF_CODIGO3', Tools::getValue('codigo3'));
            Configuration::updateValue('REDSYS_DEF_TERMINAL', Tools::getValue('terminal'));
            Configuration::updateValue('REDSYS_DEF_TERMINAL2', Tools::getValue('terminal2'));
            Configuration::updateValue('REDSYS_DEF_TERMINAL3', Tools::getValue('terminal3'));
            Configuration::updateValue('REDSYS_DEF_TIPOFIRMA', Tools::getValue('tipofirma'));
            Configuration::updateValue('REDSYS_DEF_TIPOFIRMA2', Tools::getValue('tipofirma2'));
            Configuration::updateValue('REDSYS_DEF_TIPOFIRMA3', Tools::getValue('tipofirma3'));
            Configuration::updateValue('REDSYS_DEF_MONEDA', Tools::getValue('moneda'));
            Configuration::updateValue('REDSYS_DEF_MONEDA2', Tools::getValue('moneda2'));
            Configuration::updateValue('REDSYS_DEF_MONEDA3', Tools::getValue('moneda3'));

            // parámetros de la personalización
            Configuration::updateValue('REDSYS_DEF_NOTIFICACION', Tools::getValue('notificacion'));
            Configuration::updateValue('REDSYS_DEF_SSL', Tools::getValue('ssl'));
            Configuration::updateValue('REDSYS_DEF_IDIOMAS_ESTADO', Tools::getValue('idiomas_estado'));
            Configuration::updateValue('REDSYS_DEF_MAIL_CUSTOMER', Tools::getValue('mail_customer'));
        }
    }

    protected function _displayForm()
    {
        $config = Configuration::getMultiple(
            array(
                'REDSYS_DEF_INDICE',
                'REDSYS_DEF_URLTPV',
                'REDSYS_DEF_URLTPV2',
                'REDSYS_DEF_URLTPV3',
                'REDSYS_DEF_CLAVE',
                'REDSYS_DEF_CLAVE2',
                'REDSYS_DEF_CLAVE3',
                'REDSYS_DEF_NOMBRE',
                'REDSYS_DEF_NOMBRE2',
                'REDSYS_DEF_NOMBRE3',
                'REDSYS_DEF_CODIGO',
                'REDSYS_DEF_CODIGO2',
                'REDSYS_DEF_CODIGO3',
                'REDSYS_DEF_TERMINAL',
                'REDSYS_DEF_TERMINAL2',
                'REDSYS_DEF_TERMINAL3',
                'REDSYS_DEF_TIPOFIRMA',
                'REDSYS_DEF_TIPOFIRMA2',
                'REDSYS_DEF_TIPOFIRMA3',
                'REDSYS_DEF_MONEDA',
                'REDSYS_DEF_MONEDA2',
                'REDSYS_DEF_MONEDA3',
                'REDSYS_DEF_TRANS',
                'REDSYS_DEF_TRANS2',
                'REDSYS_DEF_TRANS3',
                'REDSYS_DEF_NOTIFICACION',
                'REDSYS_DEF_SSL',
                'REDSYS_DEF_IDIOMAS_ESTADO',
                'REDSYS_DEF_MAIL_CUSTOMER'
            )
        );

        //Tab Module Configuration
        $this->context->smarty->assign(array(
            'currencies'        => Currency::getCurrencies(),
            'nombre'            => $config['REDSYS_DEF_NOMBRE'],
            'nombre2'           => $config['REDSYS_DEF_NOMBRE2'],
            'nombre3'           => $config['REDSYS_DEF_NOMBRE3'],
            'clave'             => $config['REDSYS_DEF_CLAVE'],
            'clave2'            => $config['REDSYS_DEF_CLAVE2'],
            'clave3'            => $config['REDSYS_DEF_CLAVE3'],
            'moneda'            => $config['REDSYS_DEF_MONEDA'],
            'moneda2'           => $config['REDSYS_DEF_MONEDA2'],
            'moneda3'           => $config['REDSYS_DEF_MONEDA3'],
            'codigo'            => $config['REDSYS_DEF_CODIGO'],
            'codigo2'           => $config['REDSYS_DEF_CODIGO2'],
            'codigo3'           => $config['REDSYS_DEF_CODIGO3'],
            'terminal'          => $config['REDSYS_DEF_TERMINAL'],
            'terminal2'         => $config['REDSYS_DEF_TERMINAL2'],
            'terminal3'         => $config['REDSYS_DEF_TERMINAL3'],
            'ssl'               => $config['REDSYS_DEF_SSL'],
            'idiomas_estado'    => $config['REDSYS_DEF_IDIOMAS_ESTADO'],
            'mail_customer'     => $config['REDSYS_DEF_MAIL_CUSTOMER'],
            'entorno'           => $config['REDSYS_DEF_URLTPV'],
            'entorno2'          => $config['REDSYS_DEF_URLTPV2'],
            'entorno3'          => $config['REDSYS_DEF_URLTPV3'],
            'tipofirma'         => $config['REDSYS_DEF_TIPOFIRMA'],
            'tipofirma2'        => $config['REDSYS_DEF_TIPOFIRMA2'],
            'tipofirma3'        => $config['REDSYS_DEF_TIPOFIRMA3'],
            'notificacion'      => $config['REDSYS_DEF_NOTIFICACION'],
        ));

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $base_link = 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/redsysdeferred/payment.php';
        } else {
            $base_link = $this->context->link->getModuleLink('redsysdeferred', 'payment');
        }

        //General vars
        $this->context->smarty->assign(array(
            'displayName'       => $this->displayName,
            'errors'            => $this->_postErrors,
            'success'           => $this->_success,
            'rdPath'            => $this->_path,
            'base_link'         => $base_link,
            'currencies'        => Currency::getCurrencies(),
        ));

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            return $this->display(__FILE__, 'views/templates/hook/admin_form.tpl');
        } else {
            return $this->display(__FILE__, 'admin_form.tpl');
        }
    }
    public function getContent()
    {
        // Recoger datos
        if (!empty($_POST)) {
            if (Tools::isSubmit('saveConfig')) {
                $this->_postValidation();
                $this->_postProcess();
            }
        }

        return $this->_displayForm();
    }

    public function hookHeader()
    {
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            Tools::addCSS($this->_path.'views/css/redsys_deferred_14.css', 'all');
        } else {
            $this->context->controller->addCSS($this->_path.'/views/css/redsys_deferred.css', 'all');
        }
    }

    public function redirectTPV($id_order_aux = 0)
    {
        $trans = 0;

        // Array config con los datos de configuración
        $config = Configuration::getMultiple(
            array(
                'REDSYS_DEF_INDICE',
                'REDSYS_DEF_URLTPV',
                'REDSYS_DEF_URLTPV2',
                'REDSYS_DEF_URLTPV3',
                'REDSYS_DEF_CLAVE',
                'REDSYS_DEF_CLAVE2',
                'REDSYS_DEF_CLAVE3',
                'REDSYS_DEF_NOMBRE',
                'REDSYS_DEF_NOMBRE2',
                'REDSYS_DEF_NOMBRE3',
                'REDSYS_DEF_CODIGO',
                'REDSYS_DEF_CODIGO2',
                'REDSYS_DEF_CODIGO3',
                'REDSYS_DEF_TERMINAL',
                'REDSYS_DEF_TERMINAL2',
                'REDSYS_DEF_TERMINAL3',
                'REDSYS_DEF_TIPOFIRMA',
                'REDSYS_DEF_TIPOFIRMA2',
                'REDSYS_DEF_TIPOFIRMA3',
                'REDSYS_DEF_MONEDA',
                'REDSYS_DEF_MONEDA2',
                'REDSYS_DEF_MONEDA3',
                'REDSYS_DEF_TRANS',
                'REDSYS_DEF_TRANS2',
                'REDSYS_DEF_TRANS3',
                'REDSYS_DEF_SSL',
                'REDSYS_DEF_IDIOMAS_ESTADO',
                'REDSYS_DEF_DEF_PAYMENT_AM'
            )
        );

        //Set 2 decimals for amount
        $amount = (float)str_replace('.', '', number_format(Tools::getValue('amount'), 2, '.', ''));

        // switch case para analizar la moneda y asignar los valores del comercio correspondientes
        $currency_iso = new Currency(Tools::getValue('currency_payment'));
        $currency_iso = $currency_iso->iso_code_num;

        $errors = array();
        switch ($currency_iso)
        {
            case $config['REDSYS_DEF_MONEDA']:
                switch ($config['REDSYS_DEF_URLTPV'])
                {
                    case '1':
                        $urltpv_comercio = 'https://sis.redsys.es/sis/realizarPago/utf-8';
                        break;
                    case '2':
                        $urltpv_comercio = 'https://sis-t.redsys.es:25443/sis/realizarPago/utf-8';
                        break;
                }
                $codigo = $config['REDSYS_DEF_CODIGO'];
                $clave = $config['REDSYS_DEF_CLAVE'];
                $nombre_comercio = $config['REDSYS_DEF_NOMBRE'];
                $terminal = $config['REDSYS_DEF_TERMINAL'];
                $tipofirma_comercio = $config['REDSYS_DEF_TIPOFIRMA'];
                break;
            case $config['REDSYS_DEF_MONEDA2']:
                switch ($config['REDSYS_DEF_URLTPV2'])
                {
                    case '1':
                        $urltpv_comercio = 'https://sis.redsys.es/sis/realizarPago/utf-8';
                        break;
                    case '2':
                        $urltpv_comercio = 'https://sis-t.redsys.es:25443/sis/realizarPago/utf-8';
                        break;
                }
                $codigo = $config['REDSYS_DEF_CODIGO2'];
                $clave = $config['REDSYS_DEF_CLAVE2'];
                $nombre_comercio = $config['REDSYS_DEF_NOMBRE2'];
                $terminal = $config['REDSYS_DEF_TERMINAL2'];
                $tipofirma_comercio = $config['REDSYS_DEF_TIPOFIRMA2'];
                break;
            case $config['REDSYS_DEF_MONEDA3']:
                switch ($config['REDSYS_DEF_URLTPV3'])
                {
                    case '1':
                        $urltpv_comercio = 'https://sis.redsys.es/sis/realizarPago/utf-8';
                        break;
                    case '2':
                        $urltpv_comercio = 'https://sis-t.redsys.es:25443/sis/realizarPago/utf-8';
                        break;
                }
                $codigo = $config['REDSYS_DEF_CODIGO3'];
                $clave = $config['REDSYS_DEF_CLAVE3'];
                $nombre_comercio = $config['REDSYS_DEF_NOMBRE3'];
                $terminal = $config['REDSYS_DEF_TERMINAL3'];
                $tipofirma_comercio = $config['REDSYS_DEF_TIPOFIRMA3'];
                break;

            default:
                $errors[] = $this->module->l('Currency is not valid', 'payment');
                $this->context->assign(array(
                    '_errors'   => $errors,
                ));
                return $this->setTemplate('payment.tpl');
        }


        $order_id = str_pad ($id_order_aux, 4, "0", STR_PAD_LEFT);

        $ssl = $config['REDSYS_DEF_SSL'];

        if ($ssl) {
            $urltienda = 'https://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/redsysdeferred/response.php';
        } else {
            $urltienda = 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/redsysdeferred/response.php';
        }

        //Activación de los idiomas del TPV
        $idiomas_estado = $config['REDSYS_DEF_IDIOMAS_ESTADO'];
        if ($idiomas_estado) {
            $lang = new Language((int)$this->context->cookie->id_lang);
            $idioma_web = $lang->iso_code;
            switch ($idioma_web) {
                case 'es':
                    $idioma_tpv = '001';
                    break;
                case 'en':
                    $idioma_tpv = '002';
                    break;
                case 'ca':
                    $idioma_tpv = '003';
                    break;
                case 'fr':
                    $idioma_tpv = '004';
                    break;
                case 'de':
                    $idioma_tpv = '005';
                    break;
                case 'nl':
                    $idioma_tpv = '006';
                    break;
                case 'it':
                    $idioma_tpv = '007';
                    break;
                case 'sv':
                    $idioma_tpv = '008';
                    break;
                case 'pt':
                    $idioma_tpv = '009';
                    break;
                case 'pl':
                    $idioma_tpv = '011';
                    break;
                case 'gl':
                    $idioma_tpv = '012';
                    break;
                case 'eu':
                    $idioma_tpv = '013';
                    break;
                default:
                    $idioma_tpv = '002';
            }
        } else {
            //Default POS lang
            $idioma_tpv = '0';
        }

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $url_ok = 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/redsysdeferred/pago_ok.php';
            $url_ko = 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/redsysdeferred/pago_ko.php';
        } else {
            $url_ok = Context::getContext()->link->getModuleLink('redsysdeferred', 'paymentok');
            $url_ko = Context::getContext()->link->getModuleLink('redsysdeferred', 'paymentko');
        }

        $description = Tools::substr((Tools::getValue('description')) ? Tools::getValue('description') : $this->module->l('Payment from', 'payment').' '.Configuration::get('PS_SHOP_NAME'), 0, 124);
        $description = preg_replace("/[^A-Za-z0-9 ]/", '', $description);
        $name = preg_replace("/[^A-Za-z0-9 ]/", '', Tools::getValue('name'));

        $miObj = new RedsysAPIR;
        $miObj->setParameter("DS_MERCHANT_AMOUNT", $amount);
        $miObj->setParameter("DS_MERCHANT_ORDER", (string)$order_id);
        $miObj->setParameter("DS_MERCHANT_MERCHANTCODE", $codigo);
        $miObj->setParameter("DS_MERCHANT_CURRENCY", $currency_iso);
        $miObj->setParameter("DS_MERCHANT_TRANSACTIONTYPE", $trans);
        $miObj->setParameter("DS_MERCHANT_TERMINAL", $terminal);
        $miObj->setParameter("DS_MERCHANT_MERCHANTURL", $urltienda);
        $miObj->setParameter("DS_MERCHANT_URLOK", $url_ok);
        $miObj->setParameter("DS_MERCHANT_URLKO", $url_ko);
        $miObj->setParameter("Ds_Merchant_ConsumerLanguage", $idioma_tpv);
        $miObj->setParameter("Ds_Merchant_ProductDescription", $description);
        $miObj->setParameter("Ds_Merchant_MerchantData", Tools::getValue('mail_address').'~'.$name.'~'.$description);
        $miObj->setParameter("Ds_Merchant_MerchantName", $nombre_comercio);
        $miObj->setParameter("Ds_Merchant_Module", $this->name);
        $miObj->setParameter("Ds_Merchant_PayMethods", "T");

        //Datos de configuración
        $version = "HMAC_SHA256_V1";
        $paramsBase64 = $miObj->createMerchantParameters();
        $signatureMac = $miObj->createMerchantSignature($clave);

        //We don't use a template to avoid reload effect of screen in browser
        $html = '<form action="'.$urltpv_comercio.'" name="redsysForm" id="redsysForm" method="post" accept-charset="ISO-8859-1" >
            <input type="hidden" name="Ds_SignatureVersion" value="'.htmlspecialchars($version).'" />
            <input type="hidden" name="Ds_MerchantParameters" value="'.htmlspecialchars($paramsBase64).'" />
            <input type="hidden" name="Ds_Signature" value="'.htmlspecialchars($signatureMac).'" />

        </form>
        <script>
            document.getElementById("redsysForm").submit();
        </script>';

        echo $html;
        die;
    }

    /**
     * Carga la clase solicitada
     * @param type $class_name Nombre de la clase
     * @throws Exception Si el archivo no existe
     */
    public function loadClass()
    {
        //Componemos el path
        return ($this->getLocalPath());

    }
}
