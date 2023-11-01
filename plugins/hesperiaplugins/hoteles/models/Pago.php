<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;
use Input;
use HesperiaPlugins\Stripe\Models\Payment;
/**
 * Model
 */
class Pago extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Validation
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'hesperiaplugins_hoteles_pagos';

    protected $fillable = [
        'referencia',
        'pagable_type',
        'tipo_pago_id',
        'pagable_id',
        'archivo',
        'comprobante_e',
    ];
    public $attachOne = [
      'archivo'=> 'System\Models\File'
    ];

    public $belongsTo = [
     'tipoPago' => ['HesperiaPlugins\Hoteles\Models\TipoPago', 'key' => 'tipo_pago_id'],
     //'reservacion' => ['HesperiaPlugins\Hoteles\Models\Reservacion', 'key' => 'codigo']
     //VARIABLE - RUTA DEL MODELO - KEY--> CLAVE-FORANEA EN MI TABLA
    ];


    public function scopeIsReservacion($query)
    {
        return $query->where('clase', '=', 'reservacion');
    }

    public $morphTo = [
        'pagable' => []
    ];

    public function getDetallePago(){
   
      $detalle = [];
      
      if($this->tipo_pago_id === 3 && !empty($this->referencia)){
        $reg = Payment::select("response")->where("id", $this->referencia)->first();

        $card = $reg->response["payment_method_details"]["card"];
        $detalle["brand"] = $card["brand"];
        $detalle["exp_date"] = $card["exp_month"]."/".$card["exp_year"];
        $detalle["card_number"] = "************".$card["last4"];
        $detalle["owner"] = $reg->response["metadata"]["tarjetahabiente"];
      }
      return $detalle;
    }

    

    /*public function getPagableTypeOptions(){
      $data = Input::get();
      var_dump($data);
      return ['HesperiaPlugins\Hoteles\Models\Reservacion' => 'Reservación'];
    }*/

}
