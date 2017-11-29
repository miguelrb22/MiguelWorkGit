<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PqBikeFeaturevalue
 *
 * @author dani
 */
class PqBikeFeaturevalue
{
    public $valuekey;
    public $valuedesc;

    public function __contruct($valuekey = null, $valuedesc = null)
    {
        $this->valuekey = $valuekey;
        $this->valuedesc = $valuedesc;

    }
}