<?php

require_once(_PS_MODULE_DIR_ . 'pqbikepartsimporter/classes/BkpCategory.php');
require_once(_PS_MODULE_DIR_ . 'pqbikepartsimporter/classes/BkpFeature.php');
require_once(_PS_MODULE_DIR_ . 'pqbikepartsimporter/classes/BkpFeatureValue.php');

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

        Configuration::deleteByName(self::PQ_KEY_CK);
        Configuration::deleteByName(self::PQ_PASS_CK);

        return true;
    }

    public function getContent()
    {
        $this->postProcess();
        $logged = $this->isLoggedIn();

        Context::getContext()->controller->addJs(_MODULE_DIR_.$this->module->name.'/views/js/pqbikepartsimporter.js');

        if ($logged) {

            $prestashop_categories = Category::getAllCategoriesName();

            $prestashop_categories[] = array("id_category" => 0, "name" => $this->l('No sincronizar'));


            $all = BkpCategory::getAll();

            $data = BkpCategory::getCategoryFeatureValueData($all[0]['id']);


            $features = Feature::getFeatures($this->context->language->id);

            $taxes = TaxCore::getTaxes($this->context->language->id, true);

            $this->context->smarty->assign(array(
                'pq_bike_form1' => $this->renderGeneralSettingsForm(),
                'pq_bkp_feature_value_date' => $data,
                'pq_bkp_features' => $features,
                'taxes' => $taxes,
                'bkp_categories' => $all,
                'prestashop_categories' => $prestashop_categories,
                'pq_bike_form3' => $this->renderGeneralSettingsForm(),
                'bkpsubmiturl' => $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'bkp_cron_categories' => $this->context->link->getModuleLink('pqbikepartsimporter', 'cron', array("redirect" => true, "action" => "categories")),
                'bkp_cron_charasteristics' => $this->context->link->getModuleLink('pqbikepartsimporter', 'cron', array("redirect" => true, "action" => "charasteristics")),
                'bkp_cron_categories_nr' => $this->context->link->getModuleLink('pqbikepartsimporter', 'cron', array("redirect" => false, "action" => "categories")),
                'bkp_cron_charasteristics_nr' => $this->context->link->getModuleLink('pqbikepartsimporter', 'cron', array("redirect" => false, "action" => "charasteristics")),
            ));

            $this->context->smarty->assign(array(
                'characteristics_layout' => $this->display(__FILE__, 'views/templates/admin/tabs/characteristics_content.tpl')
            ));

            return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');

        } else {
            return $this->renderLoginForm();
        }
    }

    public function postProcess()
    {


        //dump($_REQUEST);
        //die();
        if (Tools::isSubmit('submitBKPLogin')) {

            $key = Tools::getValue(self::PQ_KEY_CK);
            $pass = Tools::getValue(self::PQ_PASS_CK);

            $this->linkUp($key, $pass);

        } else if (Tools::isSubmit('submitBKPLogout')) {

            $this->logout();

        } else if (Tools::isSubmit('submitBKPGeneralSettings')) {

            $comision = Tools::getValue(self::PQ_BKP_COMMISSION_CK);
            $tiempo_adicional = Tools::getValue(self::PQ_BKP_ADDITIONAL_TIME_CK);
            $desabilitado = Tools::getValue(self::PQ_BKP_DEFAULT_STATUS_CK);


            if (empty($comision) || empty($tiempo_adicional)) {

                $this->context->controller->errors[] = ($this->l('All inputs are required'));
                return;
            }

            if (!is_numeric($comision) || !is_numeric($tiempo_adicional) || !is_numeric($desabilitado)) {

                $this->context->controller->errors[] = ($this->l('All inputs must be integer'));
                return;
            }

            Configuration::updateValue(self::PQ_BKP_COMMISSION_CK, $comision);
            Configuration::updateValue(self::PQ_BKP_ADDITIONAL_TIME_CK, $tiempo_adicional);
            Configuration::updateValue(self::PQ_BKP_DEFAULT_STATUS_CK, $desabilitado);

            $this->context->controller->confirmations[] = ($this->l('Settings updated succesfull'));

        } else if (Tools::isSubmit('submitBKPCategoryAsociation')) {


            $categories = array_filter($_REQUEST, function ($aux) {

                if (strpos($aux, "bkpcategory_") !== false) return true;

                return false;
            }, ARRAY_FILTER_USE_KEY);

            foreach ($categories as $key => $value) {

                $bkpid = explode("_", $key)[1];
                $bkpcategory = new BkpCategory($bkpid);
                $bkpcategory->id_category = $value;
                $bkpcategory->id_tax_rule = Tools::getValue('bkp_category_tax_' . explode("_", $key)[1], 0);
                $bkpcategory->save();

            }

            $this->context->controller->confirmations[] = ($this->l('Associations configured successfully'));

        } else if (Tools::isSubmit('submitBKPCaracteristicsAsociation')) {


            $feature_types = array_filter($_REQUEST, function ($aux) {

                if (strpos($aux, "type_feature_") !== false) return true;

                return false;
            }, ARRAY_FILTER_USE_KEY);


            foreach ($feature_types as $key => $value) {

                $keydata = explode("_", $key);
                $feature = new BkpFeature((int)$keydata[2]);
                $feature->type = $value;
                $feature->save();

                if ($value == 1) {

                    $categories_features_types = array_filter($_REQUEST, function ($aux) use ($feature) {

                        if (strpos($aux, "feature_value_for_cat_{$feature->id}") !== false) return true;

                        return false;
                    }, ARRAY_FILTER_USE_KEY);

                    foreach ($categories_features_types as $key2 => $value2) {

                        $cfdata = explode("_", $key2);

                        BkpFeatureValue::setDataFeatureValue($cfdata[4], $cfdata[5], $value, $value2);

                    }
                } else if ($value == 2) {

                    $char_features_types = array_filter($_REQUEST, function ($aux) use ($feature) {

                        if (strpos($aux, "feature_value_for_char_{$feature->id}") !== false) return true;

                        return false;
                    }, ARRAY_FILTER_USE_KEY);

                    foreach ($char_features_types as $key2 => $value2) {

                        $cfdata = explode("_", $key2);

                        BkpFeatureValue::setDataFeatureValue($cfdata[4], $cfdata[5], $value, $value2);

                    }
                }
            }

            $this->context->controller->confirmations[] = ($this->l('Associations configured successfully'));

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

        if (isset($key) && isset($pass) && !empty($key) && !empty($pass)) {

            return true;
        }

        return false;
    }


    public function renderLoginForm()
    {

        $form_schema = array();

        $form_schema[] = $this->getLoginFormSchema();


        //Compilamos el formulario
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;

        $helper->submit_action = 'submitBKPLogin';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getLoginFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $this->context->smarty->assign(array(
            'pq_user' => self::PQ_KEY_CK,
            'pq_pass' => self::PQ_PASS_CK
        ));


        return $helper->generateForm($form_schema);
    }

    public function renderGeneralSettingsForm($logged = false)
    {
        $form_schema = array();
        $form_schema[] = $this->getGeneralSettingsFormSchema();

        //Compilamos el formulario
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;

        $helper->submit_action = 'submitBKPGeneralSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getGeneralSettingsFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm($form_schema);
    }


    protected function getGeneralSettingsFormValues()
    {
        $arrayToReturn = array(
            self::PQ_BKP_COMMISSION_CK => Configuration::get(self::PQ_BKP_COMMISSION_CK),
            self::PQ_BKP_ADDITIONAL_TIME_CK => Configuration::get(self::PQ_BKP_ADDITIONAL_TIME_CK),
            self::PQ_BKP_DEFAULT_STATUS_CK => Configuration::get(self::PQ_BKP_DEFAULT_STATUS_CK),
        );

        return $arrayToReturn;
    }

    protected function getLoginFormValues()
    {
        $arrayToReturn = array(

            self::PQ_KEY_CK => Configuration::get(self::PQ_KEY_CK),
            self::PQ_PASS_CK => Configuration::get(self::PQ_PASS_CK),
        );

        return $arrayToReturn;
    }

    //formulario mostrado cuando NO se ha hecho loggin
    private function getLoginFormSchema()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Login'),
                    'icon' => 'icon-lock',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'name' => self::PQ_KEY_CK,
                        'required' => true,
                        'prefix' => '<i class="icon-user"></i>',
                        'label' => $this->l('Key'),
                        'class' => 'col-lg-3'
                    ),
                    array(
                        'type' => 'password',
                        'name' => self::PQ_PASS_CK,
                        'required' => true,
                        'label' => $this->l('Password'),
                        'class' => 'col-lg-3'
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    //formulario mostrado cuando SI se ha hecho loggin
    private function getGeneralSettingsFormSchema()
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
                    'title' => $this->l('Initial Configuration'),
                    'icon' => 'icon-gear',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'name' => self::PQ_BKP_COMMISSION_CK,
                        'suffix' => "<i class='icon-money'></i>",
                        'required' => true,
                        'label' => $this->l('Commission'),
                        'class' => 'col-lg-3 numeric',
                        'desc' => 'In %'
                    ),
                    array(
                        'type' => 'text',
                        'name' => self::PQ_BKP_ADDITIONAL_TIME_CK,
                        'suffix' => '<i class="icon-road"></i>',
                        'required' => true,
                        'label' => $this->l('Additional delivery time'),
                        'class' => 'col-lg-3 numeric',
                        'desc' => 'In days'
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Disabled?'),
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


    public function linkUp($key, $pass)
    {

        // key 81603093
        //pass 4d4s4mr3

        $url = BikePartsWebServiceClient::buildURLForAllProducts($key, $pass, 1);
        $response = BikePartsWebServiceClient::requestXML($url)['response_info'];

        if ($response == 'successful') {

            ConfigurationCore::updateValue(self::PQ_KEY_CK, $key);
            ConfigurationCore::updateValue(self::PQ_PASS_CK, $pass);

            $this->context->controller->confirmations[] = ($this->l('Login successfully'));


        } else if ($response == 'login failed') {

            $this->context->controller->errors[] = ($this->l('Login failed. Key or password wrong'));

        } else {

            $this->context->controller->errors[] = ($this->l('Unknown Error'));
        }

    }

    public function logout()
    {
        ConfigurationCore::updateValue(self::PQ_KEY_CK, null);
        ConfigurationCore::updateValue(self::PQ_PASS_CK, null);

        $this->context->controller->confirmations[] = ($this->l('Logout successfully'));
    }

    public function requires()
    {
        require_once(dirname(__FILE__) . '/lib/bikepartswebserviceclient.php');
    }

}
