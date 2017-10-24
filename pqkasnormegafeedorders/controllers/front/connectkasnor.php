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

        parent::init();
    }


    public function postProcess(){

        ddd($this->data);
    }
    
}