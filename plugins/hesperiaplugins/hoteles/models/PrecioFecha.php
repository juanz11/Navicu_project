<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;
use Db;
/**
 * Model
 */
class PrecioFecha extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Validation
     */
    public $rules = [
    ];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'hesperiaplugins_hoteles_precios_fechas';

    public $belongsTo = [
      'fecha' => ['HesperiaPlugins\Hoteles\Models\Fecha', 'key' => 'fecha_id'],
      'moneda' => ['HesperiaPlugins\Hoteles\Models\Moneda', 'key' => 'moneda_id'],
      'regimen' => ['HesperiaPlugins\Hoteles\Models\Regimen',
    'conditions' => "status = 1"],
    ];

    public $fillable =["regimen_id", "fecha_id", "moneda_id", "precio", "ocupacion"]; 

    public function getOcupacionOptions(){
      $hab_seleccionada = Db::table('hesperiaplugins_hoteles_habitaciones')->where('id', '=', $this->calendario->habitacion_id)->lists('capacidad');
      //var_dump($hab_seleccionada);
      $opciones = array();
      $ocupaciones = [
        "1-0", "1-1", "1-2", "1-3", "2-0", "2-1", "2-2", "3-0", "3-1",
        "4-0"];
       if (isset($hab_seleccionada[0])) {
         foreach ($ocupaciones as  $value) {
           $aux = explode("-", $value);
           if ($aux[0]+$aux[1] <= $hab_seleccionada[0]) {
             $opciones["$aux[0]-$aux[1]"] = "$aux[0] Adultos - $aux[1] Niños";
           }
         }
       }
        return $opciones;
    }
}
