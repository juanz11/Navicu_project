<?php namespace HesperiaPlugins\Hoteles\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use HesperiaPlugins\Hoteles\Models\FechaCalendario;
use HesperiaPlugins\Hoteles\Models\PrecioFechaCalendario;
use HesperiaPlugins\Hoteles\Models\Calendario;
use HesperiaPlugins\Hoteles\Models\Upselling as UpsellingModel;
use Db;
use Input;
use Carbon\Carbon;
use Flash;
use Lang;
use Validator;
use ValidationException;
use Redirect;


class Upselling extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController',        'Backend\Behaviors\ReorderController'    ];

    public $listConfig = [
    'upsellings' => 'config_list.yaml'
    ];

    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('HesperiaPlugins.Hoteles', 'main-menu-item3', 'side-menu-item2');
        $this->addJs('/plugins/hesperiaplugins/hoteles/assets/js/link_detalle_backend.js');
    }
    public function update($recordId, $context=null){
      $upselling =  new UpsellingModel;
      //MODAL DE CARGA DE TARIFAS
      $config = $this->makeConfig("$/hesperiaplugins/hoteles/models/upselling/tarifa_fields.yaml");
      $config->model = $upselling;
      $widget = $this->makeWidget("Backend\Widgets\Form", $config);
      $widget->bindToController();
      $this->vars["modal_form"] = $widget;
      //MODAL DE MODIFICACION DE DISPONIBILIDAD

      $upselling =  new UpsellingModel;
      $config2 = $this->makeConfig("$/hesperiaplugins/hoteles/models/upselling/disponibilidad_fields.yaml");
      $config2->model = $upselling;
      $widget2 = $this->makeWidget("Backend\Widgets\Form", $config2);
      $widget2->bindToController();
      $this->vars["disponibilidad_form"] = $widget2;

      return $this->asExtension('FormController')->update($recordId, $context);
    }

    public function update_onSave($recordId, $context=NULL){
      $data = Input::all();
      $upselling =  UpsellingModel::find($recordId);
      $upselling->tipo_inventario = $data["Upselling"]["tipo_inventario"];
      $config = $this->makeConfig("$/hesperiaplugins/hoteles/models/upselling/tarifa_fields.yaml");
      $config->model = $upselling;
      $widget = $this->makeWidget("Backend\Widgets\Form", $config);
      $widget->bindToController();
      //$this->vars["modal_form"] = $widget;

      $upselling2 =  new UpsellingModel;
      $config2 = $this->makeConfig("$/hesperiaplugins/hoteles/models/upselling/disponibilidad_fields.yaml");
      $config2->model = $upselling;
      $widget2 = $this->makeWidget("Backend\Widgets\Form", $config2);


      return [
          '#contentModalToolBar' => $this->makePartial('modal_form_toolbar',
            ["modal_form" => $widget, "formModel" => $upselling, "disponibilidad_form" => $widget2] ),
            $this->asExtension('FormController')->update_onSave($recordId, $context=NULL)
      ];
    }


    public function onModificarDisponibilidad($id){
      $data = post();

      $rules = [
        'fecha_desde' => 'required|date',
        'fecha_hasta' => 'required|after:checkin',
      ];


      $validator = Validator::make(
          $data,
          $rules
      );

      if ($validator->fails()) {
       throw new ValidationException($validator);
      }
      $fechaDesde = new \DateTime($data["fecha_desde"]);
      $fechaHasta = new \DateTime($data["fecha_hasta"]);
      $interval = new \DateInterval('P1D');
      $daterange = new \DatePeriod($fechaDesde, $interval, $fechaHasta);
      $mensaje="";

      DB::table('hesperiaplugins_hoteles_fecha_calendario as a')
          ->join("hesperiaplugins_hoteles_calendario as b", "a.calendario_id", "=", "b.id")
          ->where("b.calendarizable_id", "=", $id)
          ->where("b.calendarizable_type", "=", "HesperiaPlugins\Hoteles\Models\Upselling")
          ->whereBetween('a.fecha', [
            $fechaDesde->format("Y-m-d"),
            $fechaHasta->format("Y-m-d")])
          ->update(['disponible' => $data["disponible"]]);

      Flash::success("Proceso Completado");
    }
    public function onCargarTarifa($id){
      $data = post();


      $record = UpsellingModel::find($id);
      $rules = [
        'fecha_desde' => 'required|date',
        'fecha_hasta' => 'required|after:checkin',
        'precio' => 'required|integer',
      ];

      if (isset($data["disponible"])) {
        $rules =['disponible' => 'required|integer'];
      }
      $validator = Validator::make($data, $rules);

      if ($validator->fails()) {
       throw new ValidationException($validator);
      }

      $fechaDesde = new \DateTime($data["fecha_desde"]);
      $fechaHasta = new \DateTime($data["fecha_hasta"]);

      $interval = new \DateInterval('P1D');
      $daterange = new \DatePeriod($fechaDesde, $interval, $fechaHasta);
      $mensaje="";
      foreach($daterange as $date){

        $collection = Db::table('hesperiaplugins_hoteles_fecha_calendario as a')
        ->select("a.fecha", "a.id as fecha_id", "b.id as precio_id")

        ->join("hesperiaplugins_hoteles_precio_fecha_calendario as b", "a.id", "=", "b.fecha_id")
        ->join("hesperiaplugins_hoteles_moneda as c", "b.moneda_id", "=", "c.id")
        ->join("hesperiaplugins_hoteles_calendario as d", "a.calendario_id", "=", "d.id")
        ->where("a.fecha", "=", $date->format("Y-m-d"))
        ->where("c.id", "=", $data["moneda"])
        ->where("d.calendarizable_type", "=", "HesperiaPlugins\Hoteles\Models\Upselling")
        ->where("d.calendarizable_id", "=", $id)
        ->first();
        /**/
        if ($collection!=null) {
          /* SI EXISTE LA FECHA Y EL PRECIO ESPECIFICO LO MODIFICO*/
          $fecha = FechaCalendario::find($collection->fecha_id); //
          $precioFechaCalendario = PrecioFechaCalendario::find($collection->precio_id);

          if($record->tipo_inventario == 1)
            $fecha->disponible = $data["disponible"];

          $precioFechaCalendario->precio = $data["precio"];
          $precioFechaCalendario->precio_nino = $data["precio_nino"];
          $precioFechaCalendario->moneda_id = $data["moneda"];
          $precioFechaCalendario->save();
          $fecha->save();
          $mensaje.=" fecha-".$date->format("Y-m-d")." existe";
          //LISTO
        }else{
          //NO EXISTE LA FECHA Y PRECIO ESPECIFICO
          //PERO BUSCO A VER SI EXISTE LA FECHA PERO SIN ESE PRECIO
          $calendario = UpsellingModel::find($id)->calendario()->first();

          if ($calendario) {
            //SI TENGO CALENDARIO C/S FECHAS
            $mensaje.="tengo calendario";
            //trace_log($calendario);
            if ($calendario->fechas) {
              $flag=false; //BANDERA PARA SABER SI EXISTE LA FECHA
              $id_fecha = null;
               foreach ($calendario->fechas as $value ) {
                 if ($value->fecha == $date->format("Y-m-d")) {
                   $flag = true;
                   $id_fecha = $value->id;
                 }
               }

               if ($flag) {
                 $mensaje.="<br>la tengo, hay que modificar";
                 $fecha = FechaCalendario::find($id_fecha);
                 if($record->tipo_inventario == 1)
                  $fecha->disponible = $data["disponible"];

                 $fecha->calendario_id = $calendario->id;
                 $fecha->fecha = $date->format("Y-m-d");
                 $fecha->save();

               }else{
                 //$mensaje.="<br>tengo fechas pero no la que paso por parametro";
                 $fecha = new FechaCalendario;
                 $fecha->calendario_id = $calendario->id;
                 $fecha->fecha = $date->format("Y-m-d");
                 if($record->tipo_inventario == 1)
                  $fecha->disponible = $data["disponible"];

                 $fecha->save();
               }

               $precioFechaCalendario = new PrecioFechaCalendario;
               $precioFechaCalendario->precio = $data["precio"];
               $precioFechaCalendario->precio_nino = $data["precio_nino"];
               $precioFechaCalendario->moneda_id = $data["moneda"];
               $precioFechaCalendario->fecha_id = $fecha->id;
               $precioFechaCalendario->save();

            }else{
              $mensaje.="<br>no la tengo, la creo";
              //
              //NO EXISTE NADA, VAMOS A CREARLA!
              /* CREAMOS UN CALENDARIO*/
              $fecha = new FechaCalendario;
              $fecha->fecha = $date->format("Y-m-d");
              $fecha->calendario_id = $calendario->id;
              if($record->tipo_inventario == 1)
                $fecha->disponible = $data["disponible"];

              $fecha->save();
              $precioFechaCalendario = new PrecioFechaCalendario;
              $precioFechaCalendario->fecha_id = $fecha->id;
              $precioFechaCalendario->precio = $data["precio"];
              $precioFechaCalendario->precio_nino = $data["precio_nino"];
              $precioFechaCalendario->moneda_id = $data["moneda"];
              $precioFechaCalendario->save();
            }

          }else{
            $calendario = new Calendario;
            $calendario->calendarizable_type = "HesperiaPlugins\Hoteles\Models\Upselling";
            $calendario->calendarizable_id = $id;
            $calendario->save();

            $fecha = new FechaCalendario;
            $fecha->fecha = $date->format("Y-m-d");
            $fecha->calendario_id = $calendario->id;
            if($record->tipo_inventario == 1)
              $fecha->disponible = $data["disponible"];

            $fecha->save();

            $precioFechaCalendario = new PrecioFechaCalendario;
            $precioFechaCalendario->fecha_id = $fecha->id;
            $precioFechaCalendario->precio = $data["precio"];
            $precioFechaCalendario->precio_nino = $data["precio_nino"];
            $precioFechaCalendario->moneda_id = $data["moneda"];
            $precioFechaCalendario->save();

            $mensaje.="no tengo calendario";

          }
            $mensaje.="no existo";
        }
      }

      Flash::success("Proceso Completado");
    }

    public function reporte_upsellings(){

      $config = $this->makeConfig('$/hesperiaplugins/hoteles/models/upselling/report_upselling_fields.yaml');
      $config->model = new \HesperiaPlugins\Hoteles\Models\Upselling;
      $widget = $this->makeFormWidget('Backend\Widgets\Form', $config);
      $this->vars['widget'] = $widget;
      $this->pageTitle = 'Reporte de Upsellings';

      $this->vars["resultUps"] = $upsellings = UpsellingModel::with(['upgrades' => function($query){
        $query->where('moneda_id',1); }
      ])->get();
    }

    public function onReporteUpsellings(){

      $form = Input::get();
      $tipos_upsellings = array(
        '1' => "HesperiaPlugins\Hoteles\Models\DetalleReservacion",
        '2' => "HesperiaPlugins\Hoteles\Models\Reservacion",
        '3' => "HesperiaPlugins\Hoteles\Models\Compra");

      $rules = [
          'desde' => 'required','hasta' => 'required', 'categorias' => 'required'
      ];
      $validator = Validator::make($form,$rules);

      if ($validator->fails()) throw new ValidationException($validator);

        $modelos = array();

        foreach ($tipos_upsellings as $key => $value) {

          if (in_array($key, $form["categorias"])) {
            array_push($modelos, $value);
          }
        }

        $this->vars['resultUps']  = UpsellingModel::with(['upgrades' => function($query) use($form, $modelos) {

          $query->between($form)->upgradableType($modelos)->moneda($form); }])->whereHas('categorias',function($query) use($form){
              $query->whereIn('categoria_id',$form["categorias"]);

          })->hotel($form)->get();
      return [
        '#partialContents' => $this->makePartial('tabla_report_ups',['dates' => $form])
      ];
    }

    public function onDuplicate() {
      $checked_items_ids = input('checked');
      
         foreach ($checked_items_ids as $id) {
      //    $disponible = DB::table("hesperiaplugins_hoteles_hotel");
      //    ->where("a.id", "=", $this->$id);
         $hotel=Upsellingmodel::find($id);
         $ultimo= DB::table("hesperiaplugins_hoteles_upselling")->max('id') + 1;
        // log::info($ultimo);

        $clone = $hotel->replicate();
        $clone->id = $ultimo.$clone->id;
        $clone->slug = now()->timestamp."_clone_".$clone->slug;
        //log::info($clone);
        $clone->save();
      
         }
       return Redirect::refresh();

    }
  
}
