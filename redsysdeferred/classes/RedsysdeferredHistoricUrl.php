<?php

/**
 * Created by PhpStorm.
 * User: migue
 * Date: 19/01/2017
 * Time: 11:53
 */
class RedsysdeferredHistoricUrl extends ObjectModel
{

    public $url;
    public $date_upd;
    public $paid;
    public $id_order;
    public $isdrop;


    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'redsysdeferred_historic_url',
        'primary' => 'id_historic',
        'fields' => array(
            'url' => array('type' => self::TYPE_STRING),
            'date_upd' => array('type' => self::TYPE_DATE),
            'paid' => array('type' => self::TYPE_BOOL),
            'id_order' => array('type' => self::TYPE_INT),
            'isdrop' => array('type' => self::TYPE_BOOL),
        )
    );

    /**
     * @param $url
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    private function getByUrl($url)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from(self::$definition['table'], 'ht')
            ->where('ht.url = ' . '"' . $url . '"');

        //Sacamos el resultado
        return db::getInstance()->getRow($query);
    }


    /**
     * Inserta una nueva linea de historico de urls en la base de datos
     * @param $url
     */
    public function Insert($url)
    {

        $data = $this->getByUrl($url);

        $aux = explode("&z=", $url);
        $aux = $aux[1];
        $aux = explode("-", $aux);

        if (isset($aux[1]))
            $aux = $aux[1];
        else $aux = null;

        if (empty($data)) {

            $historic = new RedsysdeferredHistoricUrl();
            $historic->url = $url;
            $historic->date_upd = date('Y-m-d H:i:s');
            $historic->paid = false;

            if (isset($aux) && !empty($aux)) {

                $historic->id_order = (int)$aux;
                $historic->isdrop = true;
            }
            $historic->save();

        } else {

            $id = ($data["id_historic"]);
            $historic = new RedsysdeferredHistoricUrl($id);
            $historic->date_upd = date('Y-m-d H:i:s');

            $historic->save();

        }

        return $historic->id;

    }

    /**
     * Establecer una url como pagada
     * @param $id
     */

    public function setPaidOut($id)
    {

        $historic = new RedsysdeferredHistoricUrl($id);

        if (!empty($historic)) {
            $historic->date_upd = date('Y-m-d H:i:s');
            $historic->paid = true;
            $historic->save();
        }

        if($historic->isdrop){

            $order = new Order($historic->id_order);
            $order->setCurrentState(2);
        }

    }

}