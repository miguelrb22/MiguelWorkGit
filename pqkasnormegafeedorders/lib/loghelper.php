<?php

/**
 * Oct8ne
 *
 * @author      Oct8ne
 * @version     1.0.0
 */

class LogHelper
{

    /**
     * Escribe en el log
     * @param $type
     * @param $message
     * @return bool|int
     */

    public static function Log($type, $message){


        $date = date('Y-m');

        $filename = _PS_MODULE_DIR_."/pqkasnormegafeedorders/log/".$date.".txt";

        $date = date('Y-m-d h:i:s');
        $result = file_put_contents($filename,"{$date} {$type}: {$message}" ."\r\n", FILE_APPEND);

        return $result;
    }


    public static function LogException($ex)
    {
        self::Log('Exception', $ex->getFile().':'.$ex->getLine().' -> '.$ex->getMessage());
    }
}
