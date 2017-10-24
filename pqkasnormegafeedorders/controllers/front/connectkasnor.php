<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of connectkasnor
 *
 * @author dani
 */
class PqkasnormegafeedordersConnectkasnorModuleFrontController extends ModuleFrontController
{

    protected $data = array();

    public function __construct(){

        parent::__construct();

    }


    public function init(){

        $data = Tools::getValue("data");

        $data = json_decode($data,true);

        $this->data = $data;

        if(_PS_VERSION_ > "1.7.0") {

            $this->setTemplate('module:pqkasnormegafeedorders/views/templates/front/result.tpl');
        }

        parent::init();
    }


    public function postProcess(){


        $email = $this->data["email"];
        $address = $this->data["address"];
        $products = $this->data["products"];

        if(isset($email) && !empty($email) && isset($address) && !empty($address) && isset($products) && !empty($products)){

            $customer = $this->getCustomerByEmail($email);

            if(Validate::isLoadedObject($customer)){

               dump($customer);
           }

        }
    }

    /**
     * Devuelve el usuario que pertenece el email y si esta en el grupo de kasnor
     * @param $email
     * @return array|false|mysqli_result|null|PDOStatement|resource
     */
    public function getCustomerByEmail($email)
    {

        $sql = 'SELECT *
                FROM `'._DB_PREFIX_.'customer`
                WHERE `email` = \''.pSQL($email).'\'
                AND `id_default_group` = '.pSQL(Configuration::get("KASNORMEGAFEEDORDER_USER_GROUP")).'            
                ';

        $user = Db::getInstance()->executeS($sql);

        if(isset($user) && !empty($user)){

            return new Customer($user[0]['id_customer']);
        }
        return null;
    }


    /**
     * Devuelve una direccion por su alias
     * @param $alias
     * @return array|false|mysqli_result|null|PDOStatement|resource
     */
    public function getAdressByAlias($alias)
    {

        $sql = 'SELECT *
                FROM `'._DB_PREFIX_.'address`
                WHERE `alias` = \''.pSQL($alias).'\'
                ';

        $address = Db::getInstance()->executeS($sql);

        if(isset($address) && !empty($address)){

            return new Address($address[0]['address']);
        }
        return null;
    }
    
}