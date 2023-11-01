<?php namespace HesperiaPlugins\Hoteles\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Flash;
use Db;
use Redirect;
use \hesperiaplugins\Hoteles\Models\Fecha as FechaModel;
use \hesperiaplugins\Hoteles\Models\PrecioFecha;
class Fecha extends Controller
{
    public $implement = [
      'Backend\Behaviors\ListController',
      'Backend\Behaviors\FormController',
      'Backend\Behaviors\ReorderController',
      'Backend\Behaviors\RelationController'];

    public $listConfig = [
    'habitaciones' => 'config_list_habitaciones.yaml',
    'fechas' => 'config_list_fechas.yaml'
    ];

    //public $listConfig = "config_list_fechas.yaml";
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $relationConfig = 'config_relation.yaml';
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('HesperiaPlugins.Hoteles', 'main-menu-item', 'side-menu-item4');
    }

    protected $habitacion;
    protected $habitacion_info;

    public function index($habitacion=null){
      $this->habitacion = $habitacion;
      $this->habitacion_info = \hesperiaplugins\Hoteles\Models\Habitacion::find($habitacion);
      $this->asExtension('ListController')->index();
      $config = $this->makeConfig("$/hesperiaplugins/hoteles/models/fecha/fields.yaml");
      $fecha = new FechaModel;
      $fecha->habitacion_id = $habitacion;
      $config->model = $fecha;
      $widget = $this->makeWidget("Backend\Widgets\Form", $config);
      $this->vars["modal_form"] = $widget;

      //FORMULARIO DE DISPONIBILIDAD
      $config2 = $this->makeConfig("$/hesperiaplugins/hoteles/models/fecha/fields_disponibilidad.yaml");
      $config2->model = $fecha;
      $widget2 = $this->makeWidget("Backend\Widgets\Form", $config2);
      $this->vars["modal_form_disponibilidad"] = $widget2;
    }

    public function lista_habitaciones(){
      $this->asExtension('ListController')->index();
    }

    public function index_onCargarTarifa($id){
      $post = post();
      $fechaDesde = new \DateTime($post["fecha_desde"]);
      $fechaHasta = new \DateTime($post["fecha_final"]);
      $interval = new \DateInterval('P1D');
      $daterange = new \DatePeriod($fechaDesde, $interval, $fechaHasta);
      $mensaje="";
      foreach($daterange as $date){
        $collection = Db::table('hesperiaplugins_hoteles_fechas as a')
        ->select("a.fecha", "a.id as fecha_id", "b.id as precio_id")
        ->join("hesperiaplugins_hoteles_precios_fechas as b", "a.id", "=", "b.fecha_id")
        ->join("hesperiaplugins_hoteles_moneda as c", "b.moneda_id", "=", "c.id")
        ->where("a.fecha", "=", $date->format("Y-m-d"))
        ->where("b.regimen_id", "=", $post["regimen"])
        ->where("b.ocupacion", "=", $post["ocupacion"])
        ->where("c.id", "=", $post["moneda"])
        ->where("a.habitacion_id", "=", $id)
        ->first();
        if ($collection!=null) {
          $fecha = FechaModel::find($collection->fecha_id);
          $fechaPrecio = PrecioFecha::find($collection->precio_id);
          $fecha->disponible = $post["cantidad"];
          $fechaPrecio->precio = $post["precio"];
          $fechaPrecio->moneda_id = $post["moneda"];
          $fechaPrecio->save();
          $fecha->save();
          $mensaje.=" fecha-".$date->format("Y-m-d")." existe";
        }else{
          if ($fecha = FechaModel::where('fecha', $date->format("Y-m-d"))->
          where('habitacion_id', "=", $id)->first()) {
            $fecha->disponible = $post["cantidad"];
            $precio = new PrecioFecha;
            $precio->precio = $post["precio"];
            $precio->moneda_id = $post["moneda"];
            $precio->ocupacion = $post["ocupacion"];
            $precio->regimen_id = $post["regimen"];
            $precio->fecha_id = $fecha->id;
            $precio->save();
          }else{
            $fecha = new FechaModel;
            $fecha->fecha =  $date->format("Y-m-d");
            $fecha->disponible = $post["cantidad"];
            $fecha->habitacion_id = $id;
            $fecha->save();
            $precio = new PrecioFecha;
            $precio->precio = $post["precio"];
            $precio->moneda_id = $post["moneda"];
            $precio->ocupacion = $post["ocupacion"];
            $precio->regimen_id = $post["regimen"];
            $precio->fecha_id = $fecha->id;
            $precio->save();
          }

          $mensaje.=" fecha-".$date->format("Y-m-d")." no existe";
        }
      }
      Flash::success("Proceso Completado");

    }

    public function index_onCambiarDisponibilidad($id){
      $post = post();
      $fechaDesde = new \DateTime($post["fecha_desde"]);
      $fechaHasta = new \DateTime($post["fecha_final"]);
      $interval = new \DateInterval('P1D');
      $daterange = new \DatePeriod($fechaDesde, $interval, $fechaHasta);
      $mensaje="";

      DB::table('hesperiaplugins_hoteles_fechas')
          ->where("habitacion_id", "=", $id)
          ->whereBetween('fecha', [
            $fechaDesde->format("Y-m-d"),
            $fechaHasta->format("Y-m-d")])
          ->update(['disponible' => $post["cantidad"]]);

      Flash::success("Proceso Completado");

    }
    public function listExtendQuery($query){
   // Extend the list query to filter by the user id
    if ($this->habitacion)
        $query->where('habitacion_id', $this->habitacion);
    }
}
