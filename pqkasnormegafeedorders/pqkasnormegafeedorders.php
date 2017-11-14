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


$pq_lib_path = dirname(__FILE__) . '/lib/PqKasnormegafeedOrdersLib.php';
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

            include(dirname(__FILE__) . '/sql/install.php');

            $correct = parent::install();

            return $correct;

        } catch (Exception $ex) {
            PqKasnormegafeedOrdersLib::logException($ex);
        }

    }

    public function uninstall()
    {

        include(dirname(__FILE__) . '/sql/uninstall.php');


        return parent::uninstall();
    }


    /**
     * Load the configuration form
     */
    public function getContent()
    {

        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitPqKasnorMegafeedOrdersModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = "";

        return $output . $this->renderForm();
    }


    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitPqKasnorMegafeedOrdersModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }


    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {


        $user_groups = Group::getGroups($this->context->language->id);

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(

                    array(
                        'col' => 4,
                        'type' => 'select',
                        'name' => 'KASNORMEGAFEEDORDER_USER_GROUP',
                        'desc' => $this->l('Default Group for Kasnor Clients'),
                        'label' => $this->l('Kasnor Client Group'),
                        'options' => array(
                            'query' => $user_groups,                           // $options contains the data itself.
                            'id' => 'id_group',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
                            'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
                        )
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'KASNORMEGAFEEDORDER_USER_GROUP' => Configuration::get('KASNORMEGAFEEDORDER_USER_GROUP', '')

        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }


}