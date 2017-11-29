<?php

class PqBikepartsImporter extends Module
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
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->requires();

        $this->displayName = $this->l('PQ - PqBikePartsImporter');
        $this->description = $this->l('Bike parts importer');
    }

    public function getContent()
    {

        $this->postProcess();
        $logged = $this->isLoggedIn();
        Context::getContext()->controller->addJs(__PS_BASE_URI__ . 'modules/pqbikepartsimporter/views/js/pqbikepartsimporter.js');
        return $this->renderMainSettingsForm($logged);
        
    }

    public function postProcess()
    {

;
        if (Tools::isSubmit('submitBKPLogin')) {
            
            $user = Tools::getValue('BKP_KEY');
            $pass = Tools::getValue('BKP_PASS');

            $this->linkUp($user, $pass);
            
        } else if(Tools::isSubmit('submitBKPLogout')){

            $this->logout();
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

    }

    /**
     * Comprueba si se ha iniciaco sesión o no
     * @return bool
     */

    public function isLoggedIn()
    {
        $key = Configuration::get(self::PQ_KEY_CK);
        $pass = Configuration::get(self::PQ_PASS_CK);

        if(isset($key) && isset($pass) && !empty($key) && !empty($pass)){

            return true;
        }

        return false;
    }



    public function renderMainSettingsForm($logged = false)
    {
        //Esquleto del formulario
        $form_schema = array();
        //Configuración General
        if($logged){
            $form_schema1[] = $this->getLoggedSettingsFormSchema();
            $form_schema2[] = $this->getLoggedCategoriesFormSchema();
            $form_schema3[] = $this->getLoggedCharacteristicsFormSchema();
        }else{
            $form_schema[] = $this->getMainSettingsFormSchema();

        }



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
                'pq_pass' => self::PQ_PASS_CK,
                'pq_bike_form' => $helper->generateForm($form_schema),
             ));

        if($logged)
        {
            $this->context->smarty->assign(array(
                'pq_bike_form1' => $helper->generateForm($form_schema1),
                'pq_bike_form2' => $helper->generateForm($form_schema2),
                'pq_bike_form3' => $helper->generateForm($form_schema3)
            ));

            return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
        }
        else
        {
            return $helper->generateForm($form_schema);
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
                         'suffix' => '<i class="icon-key"></i>',
                        'label' => $this->l('User'),
                        'class' => 'col-lg-2'
                    ),
                    array(
                        'type' => 'password',
                        'name' => self::PQ_PASS_CK,
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

        $options = array(
            array(
                'id_option' => 0,       // The value of the 'value' attribute of the <option> tag.
                'name' => 'No'    // The value of the text content of the  <option> tag.
            ),
            array(
                'id_option' => 1,
                'name' => 'Yes'
            ),
        );

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'name' => self::PQ_BKP_COMMISSION_CK,

                        'required' => true,
                        'label' => $this->l('Comisión'),
                        'class' => 'col-lg-2'
                    ),
                    array(
                        'type' => 'text',
                        'name' => self::PQ_BKP_ADDITIONAL_TIME_CK,
                        'required' => true,
                        'label' => $this->l('Tiempo de entrega adicional'),
                        'class' => 'col-lg-2'
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Deshabilitado'),
                        'name' => self::PQ_BKP_DEFAULT_STATUS_CK,
                        'required' => true,
                        'is_bool' => true,
                        'class' => 'col-lg-3',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),

                ),


                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }


    private function getLoggedCharacteristicsFormSchema()
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
                        'name' => self::PQ_BKP_COMMISSION_CK,
                        'required' => true,
                        'label' => $this->l('Comisión'),
                        'class' => 'col-lg-2'
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }


    private function getLoggedCategoriesFormSchema()
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
                        'name' => self::PQ_BKP_COMMISSION_CK,
                        'required' => true,
                        'label' => $this->l('Comisión'),
                        'class' => 'col-lg-2'
                    ),
                    array(
                        'type' => 'text',
                        'name' => self::PQ_BKP_ADDITIONAL_TIME_CK,
                        'required' => true,
                        'label' => $this->l('Tiempo de entrega adicional'),
                        'class' => 'col-lg-2'
                    ),
                ),

                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }


    public function linkUp($key,$pass){

        // key 81603093
        //pass 4d4s4mr3

        $url = BikePartsWebServiceClient::buildURLForAllProducts($key,$pass,1);
        $response = BikePartsWebServiceClient::requestXML($url)['response_info'];

        if($response == 'successful'){

            ConfigurationCore::updateValue(self::PQ_KEY_CK, $key);
            ConfigurationCore::updateValue(self::PQ_PASS_CK, $pass);

            $this->context->controller->confirmations[]= ($this->l('Login successful'));


        } else  if($response == 'login failed'){

            $this->context->controller->errors[]=($this->l('Login failed. Key or password wrong'));

        } else {

            $this->context->controller->errors[]=($this->l('Unknown Error'));
        }

    }

    public function logout(){

        ConfigurationCore::updateValue(self::PQ_KEY_CK, null);
        ConfigurationCore::updateValue(self::PQ_PASS_CK, null);

        $this->context->controller->confirmations[]= ($this->l('Logout successful'));


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

    public function requires(){

        require_once (dirname(__FILE__).'/lib/bikepartswebserviceclient.php');


    }

}
