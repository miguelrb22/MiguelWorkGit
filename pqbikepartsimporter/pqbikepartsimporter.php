<?php

class PqBikePartsImporter extends Module
{
    const PQ_KEY_CK = 'BKP_KEY';
    const PQ_PASS_CK = 'BKP_PASS';
    
    const PQ_BKP_COMMISSION_CK = 'BKP_COMMISSION';
    const PQ_BKP_ADDITIONAL_TIME_CK = 'BKP_ADDITIONAL_TIME';
    const PQ_BKP_DEFAULT_STATUS_CK = 'BKP_DEFAULT_STATUS';

    public function __construct()
    {
        $this->name = 'pqbikepartsimporter';
        $this->tab = 'administration';
        $this->version = 1.0;
        $this->author = 'Prestaquality';
        $this->need_instance = 1;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('PQ - PqBikePartsImporter');
        $this->description = $this->l('Bike parts importer');
    }

    public function getContent()
    {
        $logged = $this->postProcess();
        
        return $this->renderMainSettingsForm($logged);
        
    }

    public function postProcess()
    {
        $logged = false;
        
        if (!empty(Tools::getValue('submitBKPLogin'))) {
            
            $user = strval(Tools::getValue('BKP_KEY'));
            if (!$user || empty($user) || !Validate::isGenericName($user))
            {
                $this->context->controller->errors[]=($this->l('Invalid user value.'));
            }
            else
            {
                 Configuration::updateValue(self::PQ_KEY_CK, $user);
                $this->context->controller->confirmations[]= ($this->l('User updated.'));
            }
            
            $pass = strval(Tools::getValue('BKP_PASS'));
            if (!$pass || empty($pass) || !Validate::isGenericName($pass))
            {
                $this->context->controller->errors[]=($this->l('Invalid password value.'));
            }
            else
            {
                Configuration::updateValue(self::PQ_PASS_CK, $pass);
                $this->context->controller->confirmations[]= ($this->l('Password updated.'));
            }
            
        }
        
        if (!empty(Tools::getValue('saveBKPCategories'))) {
           
            $comission = strval(Tools::getValue('BKP_COMMISSION'));
            if (!$comission || empty($comission) || !Validate::isGenericName($comission))
            {
                $this->context->controller->errors[]=($this->l('Invalid comission value.'));
            }
            else
            {
                Configuration::updateValue(self::PQ_BKP_COMMISSION_CK, $comission);
                $this->context->controller->confirmations[]= ($this->l('Comission updated.'));
            }
            
            $additional_time = strval(Tools::getValue('BKP_ADDITIONAL_TIME'));
            if (!$additional_time || empty($additional_time) || !Validate::isGenericName($additional_time))
            {
                $this->context->controller->errors[]=($this->l('Invalid additional time value.'));
            }
            else
            {
                Configuration::updateValue(self::PQ_BKP_ADDITIONAL_TIME_CK, $additional_time);
                $this->context->controller->confirmations[]= ($this->l('Aditional time updated.'));
            }
            
            $default_status = strval(Tools::getValue('BKP_DEFAULT_STATUS'));
            if($default_status == 'yes')
            {
                $default_status = 'true';
            }
            else
            {
                $default_status = 'false';
            }
            if (!$default_status || empty($default_status) || !Validate::isGenericName($default_status))
            {
                $this->context->controller->errors[]=($this->l('Invalid default status value.'));
            }
            else
            {
                Configuration::updateValue(self::PQ_BKP_DEFAULT_STATUS_CK, $default_status);
                $this->context->controller->confirmations[]= ($this->l('Default status updated.'));
            }
            
        }
        
        $user = Configuration::get(self::PQ_KEY_CK);
        $pass = Configuration::get(self::PQ_PASS_CK);
        
        if($this->loggin($user, $pass))
        {
            $logged = true;
        }
        
        return $logged;
        
    }
    
    public function loggin($user, $pass)
    {
        return true;
    }

    public function renderMainSettingsForm($logged = false)
    {
        //Esquleto del formulario
        $form_schema = array();
        
        //Configuración General
        $form_schema[] = $this->getMainSettingsFormSchema();

	    //Compilamos el formulario
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;

        $helper->submit_action = 'submitBKPLogin';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getMainSettingsFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        
        $this->context->smarty->assign(array(
                'pq_user' => self::PQ_KEY_CK,
                'pq_pass' => self::PQ_PASS_CK
             )); 
        
        if($logged)
        {
            return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
        }
        else
        {
            return $this->display(__FILE__, 'views/templates/admin/loggin.tpl');
        }
    }

    protected function getMainSettingsFormValues()
    {       
        $arrayToReturn =  array(
            self::PQ_KEY_CK => Configuration::get(self::PQ_KEY_CK),
            self::PQ_PASS_CK => Configuration::get(self::PQ_PASS_CK),
            self::PQ_BKP_COMMISSION_CK => Configuration::get(self::PQ_BKP_COMMISSION_CK),                
            self::PQ_BKP_ADDITIONAL_TIME_CK => Configuration::get(self::PQ_BKP_ADDITIONAL_TIME_CK),
            self::PQ_BKP_DEFAULT_STATUS_CK => Configuration::get(self::PQ_BKP_DEFAULT_STATUS_CK),
        );
                   
        return $arrayToReturn;
    }

    //formulario mostrado cuando NO se ha hecho loggin
    private function getMainSettingsFormSchema()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'name' => self::PQ_KEY_CK,
                        'required' => true,
                        'label' => $this->l('User'),
                        'class' => 'col-lg-2'
                    ),
                    array(
                        'type' => 'password',
                        'name' => self::PQ_KEY_PASS,
                        'required' => true,
                        'label' => $this->l('Password'),
                        'class' => 'col-lg-2'
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }
    
    //formulario mostrado cuando SI se ha hecho loggin
    private function getLoggedSettingsFormSchema()
    {
       
        /*
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                    'tabs' => array(
                        'test1' => $this->l('TAB 1'),
                        'test2' => $this->l('TAB 2'),
                        'test3' => $this->l('TAB 3')
                    )
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'name' => self::PQ_BKP_COMMISSION_CK,
                        'tab' => 'test1',
                        'required' => true,
                        'label' => $this->l('Comisión'),
                        'class' => 'col-lg-2'
                    ),
                    array(
                        'type' => 'text',
                        'name' => self::PQ_BKP_ADDITIONAL_TIME_CK,
                        'tab' => 'test2',
                        'required' => true,
                        'label' => $this->l('Tiempo de entrega adicional'),
                        'class' => 'col-lg-2'
                    ),
                    array(
                        'type' => 'text',
                        'name' => self::PQ_BKP_DEFAULT_STATUS_CK,
                        'tab' => 'test3',
                        'required' => true,
                        'label' => $this->l('Deshabilitado'),
                        'class' => 'col-lg-3'
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );*/
    }


    public function install()
    {
        if (parent::install() == false) {
            return false;
        } else {
            include(dirname(__FILE__) . '/sql/install.php');
        }
        return true;
    }

    public function uninstall()
    {
        if (parent::uninstall() == false) {
            return false;
        } else {
            include(dirname(__FILE__) . '/sql/uninstall.php');
        }
        return true;
    }

}
