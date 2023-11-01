<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;
use Db;
use Redirect;
use Response;
use Route;
use Url;
use Flash;
use Carbon\Carbon;
use \HesperiaPlugins\Hoteles\Models\Habitacion;
use Input;
/**
 * Model
 */
class Fecha extends Model
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
    public $table = 'hesperiaplugins_hoteles_fechas';

    /*relaciones*/

    public $hasMany = [
        'precios'  => ['HesperiaPlugins\Hoteles\Models\PrecioFecha', 'key' => 'fecha_id']
    ];
    public $belongsTo = [
        'habitacion' =>['HesperiaPlugins\Hoteles\Models\Habitacion', 'key' => 'habitacion_id']
    ];

    public $fillable =["habitacion_id", "fecha"]; 

    /*fin relaciones*/


    public function getRegimenOptions($value, $formData){
     
      $habitacion = Habitacion::find($this->habitacion_id);
      $regimenes = Db::table('hesperiaplugins_hoteles_regimen as a')
      ->join("hesperiaplugins_hoteles_hotel_regimen as b", "a.id", "=", "b.regimen_id")
      ->where('b.hotel_id', '=', $habitacion->hotel->id)->lists('a.nombre', 'a.id');
      return $regimenes;
    }

    public function getMonedaOptions(){
      $moneda = Db::table('hesperiaplugins_hoteles_moneda as a')->where("defecto", 1)->lists('a.moneda', 'a.id');

      return $moneda;
    }

    public function getOcupacionOptions(){
      $hab_seleccionada = Db::table('hesperiaplugins_hoteles_habitaciones')->where('id', '=', $this->habitacion_id)->lists('capacidad');
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

    public function buscarDisponibilidad($data){
      //var_dump($data);
      $begin = new \DateTime($data["checkin"]);
      $end = new \DateTime($data["checkout"]);
      $moneda = $data["moneda"];
      //$end->sub(new \DateInterval('P1D'));

      $interval = new \DateInterval('P1D');

      $daterange = new \DatePeriod($begin, $interval, $end);

      $first=null;

      foreach($daterange as $date){
        $next = Db::table('hesperiaplugins_hoteles_fechas as a')
        //->select("a.disponible", "c.nombre", "c.id")
        ->select("c.id", "a.fecha", "a.disponible")
        ->join("hesperiaplugins_hoteles_precios_fechas as b", "a.id", "=", "b.fecha_id" )
        ->join("hesperiaplugins_hoteles_moneda as d", "d.id", "=", "b.moneda_id" )
        ->join("hesperiaplugins_hoteles_habitaciones as c", "c.id", "=", "a.habitacion_id")
        //->orderBy("c.id")
        ->where('fecha', "=", $date->format("Y-m-d"))
        ->where('d.id', "=", $data["moneda"])
        ->where('c.status', "=", 1)
       // ->where('b.precio', ">", 0)
        ->where('c.hotel_id', $data["hotel"])
        ->where('a.disponible', ">", "0"); //nuevo para la otra comparacion
        if(isset($data["habitacion"]) && $data["habitacion"]!=""){
          $next->where("habitacion_id", "=", $data["habitacion"]);
        }
        if(isset($data["ocupacion"]) && $data["ocupacion"]!=""){
          $first->where("b.ocupacion", "=", $data["ocupacion"]);
        }
        if(isset($data["regimen"]) && $data["regimen"]!=""){
          $first->where("b.regimen_id", "=", $data["regimen"]);
        }
        if ($first==null)
          $first = $next;
        else
          $first= $first->union($next);
        //echo $date->format("Y-m-d") . "<br>";
      }
      if ($begin == $end) {
        $first = Db::table('hesperiaplugins_hoteles_fechas as a')
        //->select("a.disponible", "c.nombre", "c.id")
        ->select("c.id", "a.fecha", "a.disponible")
        ->join("hesperiaplugins_hoteles_precios_fechas as b", "a.id", "=", "b.fecha_id" )
        ->join("hesperiaplugins_hoteles_moneda as d", "d.id", "=", "b.moneda_id" )
        ->join("hesperiaplugins_hoteles_habitaciones as c", "c.id", "=", "a.habitacion_id")
        //->orderBy("c.id")
        ->where('fecha', "=", $begin->format("Y-m-d"))
        ->where('d.id', "=", $data["moneda"])
        ->where('c.status', "=", 1)
        //->where('b.precio', ">", 0)
        ->where('c.hotel_id', $data["hotel"])
        ->where('a.disponible', ">", "0");

        if(isset($data["habitacion"]) && $data["habitacion"]!=""){
          $first->where("habitacion_id", "=", $data["habitacion"]);
        }
        if(isset($data["ocupacion"]) && $data["ocupacion"]!=""){
          $first->where("b.ocupacion", "=", $data["ocupacion"]);
        }
        if(isset($data["regimen"]) && $data["regimen"]!=""){
          $first->where("b.regimen_id", "=", $data["regimen"]);
        }
      }
      $list = $first->orderBy("id")->get();

      /*
      recorremos las fechas para saber si las habitaciones estan disponibles
      en TODAS, asi poderlas devolver
      */
      $array_habs = array();
      $date1 = new Carbon($begin->format("Y-m-d"));
      $date2 = new Carbon($end->format("Y-m-d"));
      $interval = new \DateInterval('P1D');
      $daterange = new \DatePeriod($date1, $interval, $date2);
      //echo "$num_noches";
      $habs_borradas = array();
      $array_fechas_hab = array();
      $hab_ciclo = null;
      $i=0;
      $len = count($list);
      //4 VUELTAS - vuelta 1-->
      foreach ($list as $item => $value) {
        if (($hab_ciclo == null || $hab_ciclo == $value->id))  { //SI ES LA PRIMERA VUELTA
          $hab_ciclo = $value->id;
          //GUARDO LAS FECHAS EN UN ARRAY PARA COMPARARLAS AL FINAL
          array_push($array_fechas_hab, $value->fecha);
          if ($i == $len - 1) { //SI ES LA ULTIMA FECHA del arreglo
            $flag_hab = true;
            foreach($daterange as $date){
              if (!in_array($date->format("Y-m-d"), $array_fechas_hab)) {
                $flag_hab = false;
              }
            }
            if ($flag_hab) {
              array_push($array_habs, $hab_ciclo);
            }
          }
        }else{
          $flag_hab = true;
          foreach($daterange as $date){

            if (!in_array($date->format("Y-m-d"), $array_fechas_hab)) {
              //echo "<br>tengo-->".$date->format("Y-m-d");
              $flag_hab = false;
            }
          }
          if ($flag_hab) {
            array_push($array_habs, $hab_ciclo);
          }
          $array_fechas_hab = array();
          $hab_ciclo = $value->id;
          array_push($array_fechas_hab, $value->fecha);
          //TERMINO LA COMPARACION DE UNA HABITACION
        }
        $i++;
      }
      return $array_habs;
    }
}
