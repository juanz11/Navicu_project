<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;
use Db;

use Lovata\Toolbox\Traits\Helpers\TraitCached;

/**
 * Model
 */
class Hotel extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use TraitCached;
    /*
     * Validation
     */
    public $rules = [
      'nombre' => 'required',
      'descripcion' => 'required',
      'slug' => 'required',
      'informacion' => 'required',
      'emails_notificacion.*.nombre' => 'required',
      'emails_notificacion.*.email' => 'required|email'
    ];

    public $customMessages = [
      'emails_notificacion.*.nombre.required' => 'El campo nombre es obligatorio',
      'emails_notificacion.*.email.required'  => 'El campo email es obligatorio',
      'emails_notificacion.*.email.email'  => 'El campo email tiene un formato incorrecto',
    ];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    protected $jsonable = ['telefonos','emails', 'emails_notificacion','paquete_fechas'];

    public $cached = [
      'id',
      'nombre',
      'descripcion',
      'direccion',
      'slug',
      'codigo_postal',
      'informacion',
      'banner',
      'foto_inicio',
      'galeria',
    ];

    public $indexable = [
      'id',
      'nombre',
      'descripcion',
      'direccion',
      'slug',
      'codigo_postal',
      'informacion',
      'minimo_noches',
      'noches_antelacion',
      'orden',
      'estrellas'
    ];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'hesperiaplugins_hoteles_hotel';


    /*Relaciones*/

   public $attachOne = [
     'banner'=> 'System\Models\File',
     'foto_inicio'=>'System\Models\File'
   ];

   public $attachMany = [
     'galeria'=> 'System\Models\File'
   ];

   public $belongsToMany =[
     'regimenes' => [
       'HesperiaPlugins\Hoteles\Models\Regimen',
       'table' => 'hesperiaplugins_hoteles_hotel_regimen',
       'pivot' => ['defecto']
     ],

     'tipo_pagos' => [
      'HesperiaPlugins\Hoteles\Models\TipoPago',
      'table' => 'hesperiaplugins_hoteles_hotel_tipo_pago'
     ],
     
     'atributos' => [
      'HesperiaPlugins\Hoteles\Models\Atributo',
      'table' => 'hesperiaplugins_hoteles_hotel_atributo',
      'conditions' => 'tipo_atributo_id = 2',
      'pivot' => ['orden'],
     ]
   ];

   public $hasMany = [
      'habitaciones' => ['HesperiaPlugins\Hoteles\Models\Habitacion', 'key' => 'hotel_id'],
      'servicios' => ['HesperiaPlugins\Hoteles\Models\Servicio', 'key' => 'hotel_id'],
      'restaurantes' =>['HesperiaPlugins\Restaurant\Models\Restaurant', 'key'=>'hotel_id'],
      'novedades' =>['HesperiaPlugins\Hoteles\Models\Novedad', 'key'=>'hotel_id'],
      'impuestos' =>['HesperiaPlugins\Hoteles\Models\Impuesto', 'key'=>'hotel_id'],
      'datosBancarios' =>['HesperiaPlugins\Hoteles\Models\DatosBancarios', 'key'=>'hotel_id']
    ];

    public function getImpuestos($moneda){
      $impuestos =  Impuesto::where('moneda_id', $moneda)->where('hotel_id', $this->id)->get();
      return $impuestos;
    }


    public function getRegimenPorDefecto(){
      $selected = null;
      
      foreach ($this->regimenes as $regimen) {
        if ($regimen->pivot->defecto == 1) {
            $selected =  $regimen;
        }
      }
      
      //trace_log($this->nombre."-".$selected->nombre);
      return $selected;
    }
    
}
