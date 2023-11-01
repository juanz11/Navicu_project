<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;
use Carbon\Carbon;
use DB;
/**
 * Model
 */
class Paquete extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'hesperiaplugins_hoteles_paquete';

    /* relaciones*/

    public $belongsTo = [
        'hotel' => ['HesperiaPlugins\Hoteles\Models\Hotel', 'key' => 'hotel_id']
    ];

    public $hasMany = [
      'reservaciones' => ['HesperiaPlugins\Hoteles\Models\Reservacion', 'key' => 'paquete_id'],
      'reservaciones_count' => ['HesperiaPlugins\Hoteles\Models\Reservacion', 'key' => 'paquete_id']
    ];

    public $belongsToMany =[
     'upsellings' => [
       'HesperiaPlugins\Hoteles\Models\Upselling',
       'table' => 'hesperiaplugins_hoteles_paquete_upselling',
       'pivot' => ['obligatorio'],
       //'order'      => 'obligatorio desc', mejor ordeno por el otro lado
     ],
     'habitaciones' => [
       'HesperiaPlugins\Hoteles\Models\Habitacion',
       'table' => 'hesperiaplugins_hoteles_paquete_habitacion',
       'pivot' => ['regimen_id']
     ]
   ];

   public $attachOne = [
     'banner'=> 'System\Models\File',
   ];

   public function cambioFormatoFecha($fecha, $format){
     $date = new Carbon($fecha);
     return $date->format($format);
   }

  public function scopeIsActivo($query){
    return $query->where('ind_activo', '=', 1);
  }

  public function getMonedaOptions($value, $formData){
    $moneda = Db::table('hesperiaplugins_hoteles_moneda as m')
    ->lists('m.moneda', 'm.id');
    return $moneda;
  }

  public function getSumUpgrades($dates){
    $total = 0;
    $reservas = $this->reservaciones()->between($dates)->moneda($dates)->confirmadas()->upgrades()->get();
    
    foreach ($reservas as $key => $reserva) {

      foreach ($reserva->upgrades as $key => $upg) {
        $total= $upg->precio + $total;
      }
      
    }
    return $total;
  }

  public function scopeListPaquetes($query,$options){

    if ($options['ind_destacado'] == 1) {
      $query->where('ind_destacado',1);
    }
      
    $parts = explode(' ', $options['sortOrder']);
    list($campo, $direccion) = $parts;
    $query->orderBy($campo, $direccion);
    
    return $query;
  }


  public function pivotListRegimen($fieldName, $value, $formData){
   
    $regimenes = array();

    foreach ($this->hotel->regimenes as $regimen) {
      $regimenes[$regimen->id] = $regimen->nombre;
    } 

    

    return $regimenes;
  }

  //NUEVOO

  public function scopeActive($query){
    return $query->where('ind_activo', '=', 1);
  }

}