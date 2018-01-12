<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PqBikeProduct
 *
 * @author dani
 */
class PqBikeProduct
{
    public $number;
    public $unitprice;
    public $recommendedretailprice;
    public $description1;
    public $description2;
    public $availablestatus;
    public $availablestatusdesc;
    public $supplieritemnumber;
    public $tax;
    public $ean;
    public $manufacturerean;
    public $customstariffnumber;
    public $supplier;
    public $categorykey;
    public $expecteddeliverydate;

    /**
     * son las características realcionadas con el producto
     * @var type Array
     */
    public $features;
    public $infourl;
    public $pictureurl;

    public function __contruct($number = null )
    {
        $this->number = $number;

    }
}