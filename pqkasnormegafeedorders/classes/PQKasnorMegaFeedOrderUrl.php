<?php

/**
 * Created by PhpStorm.
 * User: migue
 * Date: 14/11/2017
 * Time: 12:39
 */
class PQKasnorMegaFeedOrderUrl extends ObjectModel
{


    public $id_pqkasnormegafeedorders_url;
    public $id_order;
    public $url;

    public static $definition = array(

        'table' => 'pqkasnormegafeedorders_url',
        'primary' => 'id_pqkasnormegafeedorders_url',
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'required' => true),
            'url' => array('type' => self::TYPE_STRING,'required' => true )
        )
    );
}