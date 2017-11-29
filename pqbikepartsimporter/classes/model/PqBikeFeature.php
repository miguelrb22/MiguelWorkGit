<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PqBikeFeature
 *
 * @author dani
 */
class PqBikeFeature
{
    public $featurekey;
    public $featurekeydesc;
    public $featurevalue;

    public function __contruct($featurekey = null, $featurekeydesc = null, $featurevaule = null )
    {
        $this->featurekey = $featurekey;
        $this->featurekeydesc = $featurekeydesc;
        $this->featurevalue = $featurevalue;

    }
}