<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;
use Db;
use HesperiaPlugins\Hoteles\Models\Moneda;
use Carbon\Carbon;
/**
 * Model
 */
class Upselling extends Model
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
    public $table = 'hesperiaplugins_hoteles_upselling';

    public $implement = [
          'HesperiaPlugins.Hoteles.Behaviors.UtilityFunctions'
      ];

    public $morphMany = [
        'calendario' => ['HesperiaPlugins\Hoteles\Models\Calendario', 'name' => 'calendarizable']
    ];

    public $morphToMany = [
        'tags' => ['HesperiaPlugins\Hoteles\Models\Tag', 'name' => 'taggable',
          'table' => 'hesperiaplugins_hoteles_taggables'],
        'impuestos' => ['HesperiaPlugins\Hoteles\Models\Impuesto', 'name' => 'impuestable',
          'table' => 'hesperiaplugins_hoteles_impuestables']
    ];
    public $attachOne = [
      'imagen'=> 'System\Models\File'
    ];
    private $precio_estadia;
    protected $with = ['hotel']; //Carga por defecto la relacion hotel, y evito el problema N +1

    public $belongsToMany =[
      'categorias' => [
        'HesperiaPlugins\Hoteles\Models\CategoriaUpselling',
        'table' => 'hesperiaplugins_hoteles_rel_categoria_upselling',
        'key'      => 'upselling_id',
        'otherKey' => 'categoria_id'
      ],
      'paquetes' => [
        'HesperiaPlugins\Hoteles\Models\Paquete',
        'table' => 'hesperiaplugins_hoteles_paquete_upselling',
        'key'      => 'upselling_id',
        'otherKey' => 'paquete_id',
        'pivot' => ['obligatorio'],
      ],
    ];

    public $hasMany = [
        'upgrades' => 'HesperiaPlugins\Hoteles\Models\Upgrade'
    ];

    public $belongsTo = [
     'hotel' => ['HesperiaPlugins\Hoteles\Models\Hotel', 'key' => 'hotel_id']
     //VARIABLE - RUTA DEL MODELO - KEY--> CLAVE-FORANEA EN MI TABLA
    ];
    public function getMonedaOptions(){
      $moneda = Db::table('hesperiaplugins_hoteles_moneda as a')->lists('a.moneda', 'a.id');
      return $moneda;
    }

    public function getHotelIdOptions(){
        $arOptions = ["0" => "Ninguno"];

         $hoteles = Db::table('hesperiaplugins_hoteles_hotel as b')->lists('b.nombre', 'b.id');

         foreach ($hoteles as $index => $hotel) {
          
          $arOptions[$index] = $hotel;
         }
        // array_unshift($hotel, ["0" => "Ninguno"]);
         //trace_log($hotel);
        return $arOptions;
    }

    /*public function getCantidadOptions(){
      $array = array();
      for ($i=1; $i <= $this->cantidad_max; $i++) {
        $array[$i] = $i;
      }
      return $array;
    }*/

    public function isDisponible($propiedades){
      $begin = new \DateTime($propiedades["checkin"]);
      $end = new \DateTime($propiedades["checkout"]);
      $interval = new \DateInterval('P1D');
      $daterange = new \DatePeriod($begin, $interval, $end);

      $precio = 0;
      if (isset($this->calendario[0])) { //SI TIENE PRECIOS CARGADOS EN EL CALENDARIO

        $fechas_calendario = $this->calendario[0]->fechas;
        $fechas_confirmadas = array();
        if ($this->tipo_inventario == "1") { //SI TIENE INVENTARIO POR FECHA

          if ($this->sumable == 0) { //SI NO ES SUMABLE
            $dates = $fechas_calendario->where("fecha", ">=", $begin->format("Y-m-d"))
            ->where("fecha", "<=",$end->format("Y-m-d"))
            ->where("disponible", ">", 0);
            //var_dump($test->fecha);
            $precio = $this->cargarPrecios($propiedades, $dates->take(1));
            //echo "tengo-->".$precio;
            foreach ($dates as $fecha) {
              $aux_fecha = new Carbon($fecha->fecha);
              array_push($fechas_confirmadas, ["fecha" => $aux_fecha->format("d-m-Y"), "disponible" => $fecha->disponible]);
            }
            if (count($fechas_confirmadas)>0) {
              $this->fechas_confirmadas = $fechas_confirmadas;
              $this->disponible_inicial = $fechas_confirmadas[0]["disponible"];
            }
            //$this->fechas_confirmadas = $fechas_confirmadas;
          }else{ //ES SUMABLE
            $dates = $fechas_calendario->where("fecha", ">=", $begin->format("Y-m-d"))
            ->where("fecha", "<=",$end->format("Y-m-d"))
            ->where("disponible", ">", 0);
            foreach ($dates as $fecha) {
              array_push($fechas_confirmadas, $fecha->fecha);
            }
            $flag = true;
            foreach($daterange as $date){
                if (in_array($date->format("Y-m-d"), $fechas_confirmadas) && $flag) {
                $flag = true;
              }else{
                $flag = false;
              }
            }
            if ($flag) {
              $precio = $this->cargarPrecios($propiedades, $dates);
            }
            //$this->fechas_confirmadas = $fechas_confirmadas;
            $this->disponible_inicial = $this->cantidad_max;
          }
        }else{ //INVENTARIO GENERAL
          if ($this->disponible > 0) {

            $dates = $fechas_calendario->where("fecha", ">=", $begin->format("Y-m-d"))
            ->where("fecha", "<=",$end->format("Y-m-d"));

            if ($this->sumable == 0) { // no es sumable
              $fechas = $dates->take(1);
            }else{
              $fechas = $dates;
            }
            $precio = $this->cargarPrecios($propiedades, $fechas);
            foreach ($dates as $fecha) {
              $aux_fecha = new Carbon($fecha->fecha);
              array_push($fechas_confirmadas, ["fecha" => $aux_fecha->format("d-m-Y"), "disponible" => $this->disponible]);
            }

            if($this->sumable == 0){
              $this->fechas_confirmadas = $fechas_confirmadas;
            }
            $this->disponible_inicial = $this->disponible;
          }
        }
      }

      return $precio;

    }

    public function getPrecioConImpuestos($precio, $impuestos){

      $precioFull = $precio;
      if ($impuestos instanceof Collection || is_array($impuestos) || is_object($impuestos)) {
        foreach ($impuestos as $key => $value) {
          if (is_array($value)) {
            $precioFull = $precioFull + ($precio)*($value["valor"])/100;
          }elseif(is_object($impuestos)){
            $precioFull = $precioFull + ($precio)*($value->valor)/100;
          }

          //echo "precio:$precio - imp->$value->valor";
        }
      }else if($impuestos!=null){
        $precioFull = (($precio)*($impuestos))/100;
      }
      return round($precioFull);
    }

    public function cargarPrecios($propiedades, $dates){
      //echo "llegue a cargarprecios";
      $precioFull = 0;
      if (count($dates)>1) {
        foreach ($dates as $date) {
          foreach ($date->precios->where("moneda_id", $propiedades["moneda"]) as $precio) {
            $precioFull = $precioFull + $precio->precio;
          }
          $fecha = new Carbon($date->fecha);
          $this->fecha_precio = $fecha->format("d-m-Y");
        }
      }else{

        $date = $dates->first();
        if ($date) {
          $precio = $date->precios->where("moneda_id", $propiedades["moneda"])
          ->where("precio", ">", 0)->first();
          $fecha = new Carbon($date->fecha);
          $this->fecha_precio = $fecha->format("d-m-Y");
          if ($precio!==null) {
            $precioFull = $precio->precio; //ESTO DIO ALERTA, MOSCA
          }

        }

      }

      //echo "precio: $precioFull";
      return $precioFull;
    }

    public function cambiarFormatoPrecio($precio){
      $valor = number_format($precio, 0 ,",", "." );
      return $valor;
    }

    public function getResumenUpsellings($seleccion_ups, $paquete_id, $propiedades){
      $ids_upsellings = array();
      $array_upselligs = array();
      foreach ($seleccion_ups as $key => $value) {
        if ($value > 0) {
          array_push($ids_upsellings, $key);
        }
      }

      $upsellings = $this->whereIn("id", $ids_upsellings)->
      whereHas('paquetes', function ($query) use ($paquete_id) {
          $query->where('paquete_id', '=', $paquete_id);
      })->get();

      $impuestos = Hotel::find($propiedades["hotel"])->impuestos;

      foreach ($upsellings as $ups) {
        $item["id"] = $ups->id;
        //if ($ups->sumable==0) {
          $item["titulo"] = $ups->titulo." x".$seleccion_ups[$ups->id];
          $precio_unitario = $ups->isDisponible($propiedades);
          $precio_base = $precio_unitario*$seleccion_ups[$ups->id];
          if ($seleccion_ups[$ups->id]>0) {
            $item["cantidad"] = $seleccion_ups[$ups->id];
          }
        /*}else{
          $item["titulo"] = $ups->titulo;
          $precio_base = $ups->isDisponible($propiedades);
          $item["cantidad"] = 1;
        }*/

        foreach ($impuestos as $key => $impuesto) {
          if ($impuesto->moneda_id != $propiedades["moneda"]) {
            unset($impuestos[$key]);
          }
        }
        $item["precio"] = $ups->getPrecioConImpuestos($precio_base, $impuestos);
        array_push($array_upselligs, $item);
      }
      return $array_upselligs;
    }

    public function getUpsSolicitados(){

      $upg = 0;
      foreach ($this->upgrades as $key => $upgrade) {

        $upg =  $upgrade->cantidad + $upg;
      }
      return $upg;
    }

    public function getUpsConfirmados(){

      $paquete = "HesperiaPlugins\Hoteles\Models\Reservacion";
      $cajaReservas = "HesperiaPlugins\Hoteles\Models\DetalleReservacion";
      $confirmadosCr = 0; $confirmadosPaqt = 0;

      foreach ($this->upgrades as $key => $upgrade) {

        if($upgrade->upgradable_type == $cajaReservas && $upgrade->upgradable->reservacion && $upgrade->upgradable->reservacion->status_id == 1)

            $confirmadosCr = $upgrade->cantidad + $confirmadosCr;

        if($upgrade->upgradable_type == $paquete && $upgrade->upgradable && $upgrade->upgradable->status_id == 1)

          $confirmadosPaqt  = $upgrade->cantidad + $confirmadosPaqt;
      }

      return $total = $confirmadosCr + $confirmadosPaqt;
    }

    public function getUpsRevenue(){

      $paquete = "HesperiaPlugins\Hoteles\Models\Reservacion";
      $cajaReservas = "HesperiaPlugins\Hoteles\Models\DetalleReservacion";
      $revenueCr = 0; $revenuePaqt = 0;

      foreach ($this->upgrades as $key => $upgrade){

       if($upgrade->upgradable_type == $cajaReservas && $upgrade->upgradable->reservacion && $upgrade->upgradable->reservacion->status_id == 1)

          $revenueCr = $upgrade->precio + $revenueCr;

        if($upgrade->upgradable_type == $paquete && $upgrade->upgradable && $upgrade->upgradable->status_id == 1)

          $revenuePaqt = $upgrade->precio + $revenuePaqt;
        }

      return $total = $revenueCr + $revenuePaqt;
    }

    public function scopeHotel($query,$dates){
      if($dates["hotel"] != 0){
        $query->where('hotel_id',$dates["hotel"]);
      }
      return $query;
    }

    public function scopeActive($query){
      $query->where('ind_activo', 1);
      return $query;
    }

  
    public function getPreciosMultiMoneda($propiedades){
      //var_dump($impuestos);
      $monedas = Moneda::where("ind_activo", 1)->orderBy("id", "DESC")->get();
      $precios = array();
      $begin = new \DateTime($propiedades["checkin"]);
      $end = new \DateTime($propiedades["checkout"]);

      $fechas_calendario = $this->calendario[0]->fechas;
      $dates = $fechas_calendario->where("fecha", "=",$end->format("Y-m-d"));

      $impuestos = $this->hotel->impuestos;

      foreach ($monedas as $moneda) {
        $nodo["moneda_id"] = $moneda->id;
        $nodo["moneda"] = $moneda->moneda;
        $nodo["acronimo"] = $moneda->acronimo;
        $nodo["precio_neto"] = null;
        $propiedades["moneda"] = $moneda->id;
        $precio = $this->cargarPrecios($propiedades, $dates);
        $nodo["precio_neto"] = $precio;
        if ($impuestos) {
          foreach ($impuestos as $key => $impuesto) {
            if ($impuesto->moneda_id != $moneda->id) {
              unset($impuestos[$key]);
            }
          }
          $precio = $this->getPrecioConImpuestos($precio, $impuestos);
        }
        $nodo["precio"] = $precio;
        array_push($precios, $nodo);
      }

      return $precios;
    }

    public function getFechasDisponibles(){
      $calendario_id = $this->calendario[0]->id;
      $fechas = Db::table('hesperiaplugins_hoteles_fecha_calendario as a')
      ->where("calendario_id", $calendario_id)
      ->where("disponible", ">", 0)
      ->where("fecha", ">=", Carbon::today()->format("Y-m-d"))->lists('a.fecha');
     
      return $fechas;
    }

    public function getPrecioDisponibilidad($moneda, $fechas){
      $calendario_id = $this->calendario[0]->id;
      $first=null;
      $disponible = "";
      if ($this->tipo_inventario == 1) {
        $disponible = "a.disponible";
      }else{
        $disponible = "d.disponible";
      }
      if(is_array($fechas)){
        //ES UN ARRAY DE FECHAS
        foreach($fechas as $fecha){
          $date = new Carbon($fecha);
          $next = Db::table('hesperiaplugins_hoteles_fecha_calendario as a')
          ->select("a.fecha", "b.precio", $disponible)
          ->join("hesperiaplugins_hoteles_precio_fecha_calendario as b", "a.id", "=", "b.fecha_id" )
          ->join("hesperiaplugins_hoteles_calendario as c", "c.id", "=", "a.calendario_id" )
          ->join("hesperiaplugins_hoteles_upselling as d", "d.id", "=", "c.calendarizable_id")
          ->where('a.fecha', "=", $date->format("Y-m-d"))
          ->where('b.moneda_id', "=", $moneda)
          ->where("a.calendario_id", $calendario_id)
          ->where('c.calendarizable_type', "=", "HesperiaPlugins\\Hoteles\\Models\\Upselling");
          if ($first==null)
            $first = $next;
          else
            $first= $first->union($next);
        }
      }else{
        //ES UNA SOLA FECHA STRING
        $date = new Carbon($fechas);
        $first = Db::table('hesperiaplugins_hoteles_fecha_calendario as a')
        ->select("a.fecha", "b.precio", $disponible)
        ->join("hesperiaplugins_hoteles_precio_fecha_calendario as b", "a.id", "=", "b.fecha_id" )
        ->join("hesperiaplugins_hoteles_calendario as c", "c.id", "=", "a.calendario_id" )
        ->join("hesperiaplugins_hoteles_upselling as d", "d.id", "=", "c.calendarizable_id")
        ->where('a.fecha', "=", $date->format("Y-m-d"))
        ->where('b.moneda_id', "=", $moneda)
        ->where("a.calendario_id", $calendario_id)
        ->where('c.calendarizable_type', "=", "HesperiaPlugins\\Hoteles\\Models\\Upselling");
      }

      $return = $first->orderBy("fecha")->get();
      return $return;
    }

    public function scopeCategoriaUpselling($query, $filter){

      $categoria = implode(",",$filter);

      return $query->whereHas('categorias',function($q) use ($categoria){

        $q->whereIn('categoria_id',[$categoria]);
      });
    }

    public function scopeDisponibleEnPaquete($query, $type){

      return $query->where("hotel_id", $type->hotel->id);
    }
}