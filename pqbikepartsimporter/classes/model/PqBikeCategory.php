<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PqBikeCategory
 *
 * @author dani
 */
class PqBikeCategory
{
    public $key;
    public $count;
    public $desc;
    public $products;
    public $features;

    public function __contruct($key = null, $count = null, $desc = null)
    {
        $this->key = $key;
        $this->count = $count;
        $this->desc = $desc;
    }
}