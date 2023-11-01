<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;
use Db;
use HesperiaPlugins\Hoteles\Models\Descuento;
use HesperiaPlugins\Hoteles\Models\Hotel;
use HesperiaPlugins\Hoteles\Models\Upselling;
use Session;

use October\Rain\Database\Collection as Collection;
use Carbon\Carbon;
/**
 * Model
 */
class Habitacion extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Validation
     */
    public $rules = [
      'nombre'      => 'required',
      'capacidad'   => 'required|numeric',
      'descripcion' => 'required'
    ];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'hesperiaplugins_hoteles_habitaciones';

    public $attachOne = [
      'foto_portada'=> 'System\Models\File'
    ];

    public $attachMany = [
      'galeria'=> 'System\Models\File'
    ];

    public $belongsTo = [
     'hotel' => ['HesperiaPlugins\Hoteles\Models\Hotel', 'key' => 'hotel_id']
     //VARIABLE - RUTA DEL MODELO - KEY--> CLAVE-FORANEA EN MI TABLA
    ];

    public $hasMany = [
     'fechas' => ['HesperiaPlugins\Hoteles\Models\Fecha', 'key' => 'habitacion_id'],
     'descuentosHabitacion' => [
       'HesperiaPlugins\Hoteles\Models\DescuentoHabitacion',
       'key' => 'habitacion_id',
       'conditions' => 'cantidad > 0'
       ]
     //VARIABLE - RUTA DEL MODELO - KEY--> CLAVE-FORANEA EN MI TABLA
   ];

   public $belongsToMany =[
     'atributos' => [
       'HesperiaPlugins\Hoteles\Models\Atributo',
       'table' => 'hesperiaplugins_hoteles_habitacion_atributo',
       'conditions' => 'tipo_atributo_id = 1',
       'pivot' => ['orden'],
     ]
   ];

   public $implement = [
     'HesperiaPlugins.Hoteles.Behaviors.UtilityFunctions',
   ];
   public $ocupaciones = array();
   public $regimenes = array();

    public function getPrecios($date1, $date2, $moneda, $flag = true){

      $array_regimenes_ids= array();

      $regimenes_hotel = $this->hotel->regimenes;

      foreach ($regimenes_hotel as $key => $value) {
        array_push($array_regimenes_ids, $value->id);
      }
      //$order =  array('b.ocupacion', );
      $begin = new \DateTime($date1);
      $end = new \DateTime($date2);
      $end->sub(new \DateInterval('P1D'));
      $result = Db::table('hesperiaplugins_hoteles_fechas as a')
      ->select("b.ocupacion", "d.acronimo", "d.id as moneda_id", "b.regimen_id", "b.precio", "c.nombre as regimen","a.fecha", 'c.descripcion as detalle_regimen')
      ->join("hesperiaplugins_hoteles_precios_fechas as b", "a.id", "=", "b.fecha_id" )
      ->join("hesperiaplugins_hoteles_regimen as c", "b.regimen_id", "=", "c.id" )
      ->join("hesperiaplugins_hoteles_moneda as d", "b.moneda_id", "=", "d.id")
      //->groupBy('b.ocupacion', 'b.regimen_id')
      ->whereBetween('a.fecha', [$begin->format("Y-m-d"), $end->format("Y-m-d")])
      ->where('a.habitacion_id', "=", $this->id)
      ->where('c.status', "=", 1)
      //->where('b.precio', ">", 0)
      ->where("d.id", "=", $moneda)
      ->when($flag, function ($query) {
          return $query->where("a.disponible", ">", 0);
      })
      ->whereIn('b.regimen_id', $array_regimenes_ids)
      ->orderBy('b.ocupacion')
      ->orderBy('regimen_id')->get();
      //$result->get();
      //var_dump($result);

      $ocup_regimen_array = array(); //GUARDA LAS COMBINACIONES OCUPACION - REGIMEN
      //SE DEBE HACER UN CICLO PARA LLENAR TODOS LOS INDICES POSIBLES (OCUPACION-REGIMEN)
      //POR SI NO CARGAN TODA LA DATA NECESARIA

      foreach ($result as $value) {
        $index = $value->ocupacion."-".$value->regimen_id;
        if (!array_key_exists($index, $ocup_regimen_array)) {
          //SI NO HA SIDO PROCESADO ESA OCUPACIO-REGIMEN
          $ocup_regimen_array[$index]["fechas"] = array();
          $ocup_regimen_array[$index]["nombre_regimen"] = $value->regimen;
          $ocup_regimen_array[$index]["total"] = 0;
          $ocup_regimen_array[$index]["acronimo"] = $value->acronimo;
          $ocup_regimen_array[$index]["ocupacion"] = $value->ocupacion;
          $ocup_regimen_array[$index]["regimen_id"] = $value->regimen_id;
          $ocup_regimen_array[$index]["detalle_regimen"] = $value->detalle_regimen;
        }
        if (!in_array($value->fecha, $ocup_regimen_array[$index]["fechas"])) {
          array_push($ocup_regimen_array[$index]["fechas"], $value->fecha);

        }
        if ($value->precio > 0 && $ocup_regimen_array[$index]["total"] != -1) {
          $ocup_regimen_array[$index]["total"] = $ocup_regimen_array[$index]["total"] + $value->precio;
        }else{
          $ocup_regimen_array[$index]["total"] = -1;
        }
      }
      //trace_log($ocup_regimen_array);
      return $ocup_regimen_array;
    }

    public function getResumenReserva($seleccion, $propiedades){
      $begin = new \DateTime($propiedades["checkin"]);
      $end = new \DateTime($propiedades["checkout"]);

      //var_dump($seleccion);

      $end->sub(new \DateInterval('P1D'));
      /*QUE NECESITO?
      NOMBRE HAB - ID, SUM DE PRECIOS, ACRONIMO, REGIMEN_ID,
      */
      $first = null;

      foreach ($seleccion as $habitacion) {
        $next = Db::table('hesperiaplugins_hoteles_fechas as a')
        ->select("e.id as habitacion_id", "b.ocupacion", "d.acronimo", "d.id as moneda_id", "b.regimen_id", "b.precio",
        "e.nombre", "c.nombre as regimen", Db::raw('sum(b.precio) as precio'))
        ->join("hesperiaplugins_hoteles_precios_fechas as b", "a.id", "=", "b.fecha_id" )
        ->join("hesperiaplugins_hoteles_regimen as c", "b.regimen_id", "=", "c.id" )
        ->join("hesperiaplugins_hoteles_moneda as d", "b.moneda_id", "=", "d.id")
        ->join("hesperiaplugins_hoteles_habitaciones as e", "e.id", "=", "a.habitacion_id")
        ->groupBy('b.ocupacion', 'b.regimen_id')
        ->whereBetween('a.fecha', [$begin->format("Y-m-d"), $end->format("Y-m-d")])
        ->where('a.habitacion_id', "=", $habitacion["habitacion_id"])
        ->where('c.status', "=", 1)
        ->where("d.id", "=", $propiedades["moneda"])
        ->where("a.disponible", ">", 0)
        ->where('b.precio', ">", 0) //PENDIENTE CON ESTO
        ->where('b.ocupacion', "=", $habitacion["ocupacion"])
        //->where('b.moneda_id', "=", $habitacion["codigo_moneda"])
        ->where('b.regimen_id', "=", $habitacion["regimen_id"]);
        if ($first==null)
          $first = $next;
        else
          $first= $first->unionAll($next);
      }
      $list = $first->get();

      $hotel = Hotel::find($propiedades["hotel"]);
      //var_dump($hotel->id);
      $impuestos = $hotel->getImpuestos($propiedades["moneda"]);

      //var_dump($impuestos->all());
      $descuentos = array();
      if (isset($propiedades["paquete_id"]) && $propiedades["paquete_id"]>0) {
        $paquete = Paquete::select("porcentaje")->where("id", $propiedades["paquete_id"])->first();
        $descuentos = array("".$paquete->porcentaje);
      //  array_push($descuentos, $descuento_paq);

      }

      foreach ($list as $key => $item) {
        $this->id = $item->habitacion_id;
      /*  foreach ($variable as $key => $value) {
          // code...
        }*/
        
        $precioConImpuestos = $this->getPrecioConImpuestos($item->precio, $impuestos);

        $precio = $precioConImpuestos;

        if (!isset($propiedades["paquete_id"])) {
          $descuentos = $this->getDescuentos($propiedades);
          $obHabitacion = Habitacion::find($item->habitacion_id);
          $precios_noches_gratis = $obHabitacion->getPrecioNochesGratis($descuentos);
          
        }
        $ups_hab = 0;
        if (isset($seleccion[$key]["upsellings"])) {
          $item->upsellings = $seleccion[$key]["upsellings"];
          foreach ($seleccion[$key]["upsellings"] as $value) {
            $ups_hab = $value["precio"] + $ups_hab;
          }
        }

        /*trace_log($precios_noches_gratis);
        if($precios_noches_gratis!=null){
            $indice = $item["ocupacion"].$item["regimen"];
            $precioFinal = $precios_noches_gratis[$indice]["precio"] - (precioAntes - precioPorcentaje) %}
              
        }else{
          $precioFinal = $this->getPrecioConDescuentos($precio, $descuentos);
        }*/

        
        /*$item->alojamiento = $precioFinal;
        //echo $precio."<br>".$precioFinal;
        $item->precio = $this->cambiarFormatoPrecio($precioFinal+$ups_hab, $propiedades["moneda"]);
        $item->precioRaw = $precioFinal+$ups_hab;
        $item->descuentos = $descuentos;*/

        //NO VERIFICO DISPONIBILIDAD NI PRECIO DE UPSELLINGS, OJO!
      }
      return $seleccion;

    }

    public function setArrayOcupaciones($array){
      $i = 0;
      $arrayAux = array();
      foreach ($array as $key => $value) {
        if ($array instanceof Collection) {
          $aux = explode("-", $value->ocupacion);
        }else{
          $aux = explode("-", $value["ocupacion"]);
        }

        if (!in_array($aux[0]."-".$aux[1], $arrayAux)) {
          $this->ocupaciones[$i]["codigo"] = "$aux[0]-$aux[1]";
          $this->ocupaciones[$i]["descripcion"] = "$aux[0] Adultos - $aux[1] Niños";
          $arrayAux[$i] = $aux[0]."-".$aux[1];
          $i++;
        }

      }
    }
    public function setArrayRegimenes($array){
      $i = 0;
      $arrayAux = array();
      $array_regimenes_ids= array();

      $regimenes_hotel = $this->hotel->regimenes;

      foreach ($regimenes_hotel as $key => $value) {
        array_push($array_regimenes_ids, $value->id);
      }

      foreach ($array as $key => $value) {

        if ($array instanceof Collection) {
          if (!in_array($value->regimen_id, $arrayAux) && in_array($value->regimen_id,$array_regimenes_ids)) {
            $this->regimenes[$i]["codigo"] = $value->regimen_id;
            $this->regimenes[$i]["descripcion"] = $value->regimen;
            $this->regimenes[$i]["detalle"] = $value->descripcion;
            $arrayAux[$i] = $value->regimen_id;
            $i++;
          }
        }else{
          if (!in_array($value["regimen_id"], $arrayAux) && in_array($value["regimen_id"],$array_regimenes_ids)) {
            $this->regimenes[$i]["codigo"] = $value["regimen_id"];
            $this->regimenes[$i]["descripcion"] = $value["nombre_regimen"];
            if(isset($value["detalle_regimen"])){
              $this->regimenes[$i]["detalle"] = $value["detalle_regimen"];
            }
            
            $arrayAux[$i] = $value["regimen_id"];
            $i++;
          }
        }
        //trace_log($array);
      }

    }

    public function getMenorPrecio($array, $impuestos = null){
      //var_dump($array);
      $propiedades = Session::get("propiedades");
      $menor=0;
      $moneda = "";
      $moneda_id = null;
      $precioFull = 0;

      foreach ($array as $key => $value) {

        if ($array instanceof Collection) {

          if ($value->total > 0 && $value->total < $menor) {
            $menor = $value->total;
          }else if($value->total > 0 && $menor == 0){
            $menor = $value->total;
          }
          $moneda = $value->acronimo;
        }else{

          if ($value["total"] > 0 && $value["total"] < $menor) {
            $menor = $value["total"];

          }else if($value["total"] > 0 && $menor == 0){
            $menor = $value["total"];
          }
          $moneda = $value["acronimo"];
        }
      }
      $impuestoTotal = 0;
      foreach ($impuestos as $key => $value) {
        $impuestoTotal+=($menor)*($value->valor)/100;
        //echo "<br> $precioFull";
        //var_dump($value);
      }

      $test = $menor+$impuestoTotal;
      $test = $this->cambiarFormatoPrecio($test, $propiedades["moneda"]);
      return $test." ".$moneda;
      //return $keys
    }

    public function getOcupacion($string){
      $aux = explode("-", $string);
      $result = "$aux[0] Adultos - $aux[1] Niños";
      return $result;
    }
    public function getPrecioConImpuestos($precio, $impuestos){
      $precioFull = $precio;
      if ($impuestos instanceof Collection || is_array($impuestos)) {
        //echo "entro aqui";
        foreach ($impuestos as $key => $value) {
          //echo "precio neto: $precio"."-impuesto: $value->valor"."-full:$precioFull";
          $precioFull = $precioFull + ($precio)*($value->valor)/100;
        }
      }else if($impuestos!=null){
        $precioFull = (($precio)*($impuestos))/100;
      }
      return $precioFull;

    }
    public function getPrecioConDescuentos($precio, $descuentos){
      $precioFull = $precio;
      
      $propiedades = Session::get("propiedades");

      foreach ($descuentos as $key => $value) {
        
        if ($key == "porcentaje"){
          //trace_log($value);
          $descuento = 0;
          if(is_numeric($value)){
            $descuento = $value;
          }else if(is_numeric($value["porcentaje"])){
            $descuento = $value["porcentaje"];
          }
          $precioFull =  $precioFull - (($precio)*($descuento))/100;

        }else if(isset($value["porcentaje"])){
          //trace_log("paso por 2");
          $descuento = 0;
          if(is_numeric($value["porcentaje"])){
            $descuento = $value["porcentaje"];
          }

          $precioFull = $precioFull - (($precio)*($descuento))/100;
        }

      }
      //var_dump($precioFull);
      return $precioFull;
    }

    public function getPrecioNochesGratis($descuentos){
      $propiedades = Session::get("propiedades");
      $nochesGratis = 0;
      $precios = null;

      foreach ($descuentos as $key => $value) {
        if($value["noches_gratis"] > 0){
          $nochesGratis = $nochesGratis + $value["noches_gratis"];
        } 
      }

      if($nochesGratis >0 ){//SI HAY NOCHES GRATIS
        $firstDate = new Carbon($propiedades["checkin"]);
        $firstDate->addDays($nochesGratis);
        $endDate = new Carbon ($propiedades["checkout"]);
        if ($firstDate->diffInDays($endDate) > 0 ) { //SI LAS CANTIDAD DE NOCHES GRATIS NO SUPERA LA ESTADIA
          $precios =  $this->getPrecios($firstDate->format("Y-m-d"), $propiedades["checkout"], $propiedades["moneda"], $flag = true);
        }
      }
     
      return $precios;
             
    }
    /*public function cambiarFormatoPrecio($precio){
      $valor = money_format($precio, 0 ,",", "." );
      return $precio;
    }*/

    public function getDescuentos($propiedades){
      $begin = new \DateTime($propiedades["checkin"]);
      $end = new \DateTime($propiedades["checkout"]);
      /*//var_dump($propiedades);
      $descuentos = Db::table("hesperiaplugins_hoteles_descuentos as a")
      ->join('hesperiaplugins_hoteles_descuento_habitacion as b', 'a.id', '=', 'b.descuento_id')
      //->whereBetween('a.fecha_desde', [$begin->format("Y-m-d"), $end->format("Y-m-d")])
      ->where('a.fecha_desde', '<=', $begin->format("Y-m-d"))
      ->where('a.fecha_hasta', '>=', $end->format("Y-m-d"))
      ->where('a.ind_activo', '=', 1)
      ->where('b.habitacion_id', '=', $this->id);

      if (isset($propiedades["cod_promo"]) && $propiedades["cod_promo"]!="") {
        $descuentos->where('a.codigo_promocional', '=', $propiedades["cod_promo"])
        ->where("b.cantidad", ">", 0);
      }else{
        $descuentos->where('a.codigo_promocional', "");
      }*/

      $descuentosHab = $this->descuentosHabitacion;
      $arrayDescuestosAplicables = array();

      foreach ($descuentosHab as $key => $value) {

        //trace_log($value->descuento_id);
        $descuento = $value->getDescuentoDisponible($propiedades);
        if ($descuento != null) {
          array_push($arrayDescuestosAplicables, $descuento);
        }
      }
      
      //trace_log(count($arrayDescuestosAplicables));

      return $arrayDescuestosAplicables;
    }

    public function getCantidadDisponible($propiedades){
      $begin = new Carbon($propiedades["checkin"]);
      $end = new Carbon($propiedades["checkout"]);

      //$end->subDay();

      $disponible = DB::table('hesperiaplugins_hoteles_fechas')
      ->whereBetween('fecha', [$begin->format("Y-m-d"), $end->format("Y-m-d")])
      ->where('habitacion_id', '=', $this->id)
      ->min('disponible');

      return $disponible;
    }

    public function scopeDisponibleEnPaquete($query, $type){

      return $query->where("hotel_id", $type->hotel->id);

    }

    public function getUpsellings($propiedades){

      $upsellings = Upselling::whereHas('categorias', function ($query) {
          $query->where('categoria_id', '=', 1);
      })->get();

      return $upsellings;
    }

    public function getPrecioAnterior($precioConPorcentajeAplicado, $precioNochesGratis, $precioBase){

    }

    public function getPermutaOcupaciones(){
      $array = array();
      $kids = 0;
      $adultos = 1;
      while ($adultos <= $this->capacidad) {
        $ocupacion="";
        if ($adultos==1 && $kids == 0 ) {
            $ocupacion = $adultos."-".$kids;
            //echo "$ocupacion<br>";
            array_push($array, $ocupacion);
            $kids ++;
            while ($adultos + $kids <= $this->capacidad) {
              $ocupacion = $adultos."-".$kids;
              //ES LA PRIMERA VUELTA, ALL GOOD
              //echo "-$ocupacion-<br>";
              array_push($array, $ocupacion);
              $kids ++;
            }
        }else{
          $adultos++;
          $kids = 0;
          while ($adultos + $kids <= $this->capacidad) {
            $ocupacion = $adultos."-".$kids;
            //echo "$ocupacion-<br>";
            array_push($array, $ocupacion);
            $kids ++;
          }
        }
    }
    return $array;
  }

  public function soloAdultos(){
     $flag = $this->hotel->solo_adultos;

     if ($flag == 1) {
       return true;
     }else{
       return false;
     }
  }

  public function getPaquetePivotRegime() {
    $regimenes = array();

    foreach ($this->hotel->regimenes as $regimen) {
      $regimenes[$regimen->id] = $regimen->nombre;
    } 

    

    return $regimenes;
  }
}
