<?php namespace HesperiaPlugins\Hoteles\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Flash;
use Mail;
use Session;
use HesperiaPlugins\Hoteles\Models\Reservacion as ReservaModel;
use HesperiaPlugins\Hoteles\Models\DetalleReservacion as DetalleModel;
use HesperiaPlugins\Hoteles\Models\Fecha;
use HesperiaPlugins\Hoteles\Models\Moneda;
use HesperiaPlugins\Hoteles\Models\Upgrade;
use HesperiaPlugins\Hoteles\Models\Habitacion;
use HesperiaPlugins\Hoteles\Models\Upselling;
use HesperiaPlugins\Hoteles\Models\Hotel;
use HesperiaPlugins\Hoteles\Models\Cotizacion;
use HesperiaPlugins\Hoteles\Models\Compra;
use HesperiaPlugins\Hoteles\FormWidgets\Detalles;
use RainLab\User\Models\User;
use RainLab\User\Models\UserGroup;
use Redirect;
use Input;
use Db;
use Validator;
use ValidationException;
use Auth;
use Carbon\Carbon;
use HesperiaPlugins\Hoteles\Models\Observacion;

class Reservacion extends Controller
{
    public $implement = [
      'Backend\Behaviors\ListController',
      'Backend\Behaviors\FormController',
      'Backend\Behaviors\ReorderController',
      'Backend\Behaviors\RelationController',
      'HesperiaPlugins.Hoteles.Behaviors.UtilityFunctions',
    ];

    public $listConfig = [
      'default' => 'config_list.yaml',
      'detalle_paquete' => 'config_detalle_list.yaml',
      'detalle_upselling' => 'config_detalle_ups_list.yaml'
    ];
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $relationConfig = 'config_relation.yaml';

    protected $agente_id;
    protected $paquete_id;
    protected $upselling_id;
    protected $desde;
    protected $hasta;
    protected $hotel_id;
    protected $moneda_id;

    //NUEVO
    protected $hoteles;
    protected $monedas;



    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('HesperiaPlugins.Hoteles', 'main-menu-item3');
         
         $this->addJs('/plugins/hesperiaplugins/hoteles/assets/js/fecha/fecha.min.js');
         
         $this->addJs('/plugins/hesperiaplugins/hoteles/assets/js/bootstrap-datepicker.min.js');
         $this->addCss('/plugins/hesperiaplugins/hoteles/assets/css/fecha/hotel-datepicker.css');
         $this->addCss('/plugins/hesperiaplugins/hoteles/assets/css/bootstrap-datepicker.css');

    }
    public function create(){

      $this->addJs('/plugins/hesperiaplugins/hoteles/assets/js/fecha/hotel-datepicker.js');
      $this->addJs('/plugins/hesperiaplugins/hoteles/assets/js/reservas_backend.js');
      $this->bodyClass = 'compact-container';
      Session::forget(["seleccion", "busqueda"]);
      BackendMenu::setContext('', '');
      $hoteles = Hotel::select("nombre", "id")->with("impuestos")->get();
      $monedas = Moneda::select("moneda", "id", "acronimo")->where("ind_activo", 1)->orderBy("id", "DESC")->get();
      $var_iniciales = array(
        "hoteles"=> $hoteles->toArray(),
        "monedas" => $monedas->toArray()
      );
      Session::put("var_iniciales", $var_iniciales);
      Session::forget(["seleccion", "busqueda"]);
      return $this->asExtension('FormController')->create();
    }



    public function onAddSeleccion(){
        $data = Input::get();
        $busqueda = Session::get("busqueda");
        $var_iniciales = Session::get("var_iniciales");
        $porcentaje = "";
        $concepto ="";
        //var_dump($data);
        $nombre_regimen = Db::table("hesperiaplugins_hoteles_regimen")->select("nombre")->where("id", "=", $data["regimen"])->take(1)->get();
        $hab_temp = Habitacion::find($data["hab"]);

        $nombre_ocupacion = $hab_temp->getOcupacion($data["ocupacion"]);

        if (isset($data["porcentaje"]) && $data["concepto"]) {
          $porcentaje = $data["porcentaje"];
          $concepto = $data["concepto"];
        }
        $ids_upsellings = array();
        $cantidades_upsellings = array();
        $fechas_disfrute = array();
        //VERIFICO SI LOS CAMPOS QUE LLEGAN SON UPSELLINGS
        foreach ($data as $key => $value) {
          $aux = explode("-", $key);

          if (count($aux)==3 && $value > 0 && $aux[0]=="hab") {
            //ES UN UPSELLING
            array_push($ids_upsellings, $aux[2]);
            $cantidades_upsellings[$aux[2]] = $value;
          }elseif ($aux[0]=="fechadisfrute") {

            $fechas_disfrute[$aux[1]] = $value;
          }
        }
        $ups_result = Upselling::select(["titulo", "id", "tipo_inventario", "sumable", "ind_calendario", "disponible"])->whereIn("id", $ids_upsellings)->get();
        $ups_disponibles = array();
        //SE BUSCAN LOS IMPUESTOS YA PRECARGADOS AL INICIAR EL CONTROLADOR
        $impuestos = null;
        foreach ($var_iniciales["hoteles"] as $hotel ) {
          if ($hotel["id"] == $busqueda["hotel"]) {
            $impuestos = $hotel["impuestos"];
          }
        }

        foreach ($ups_result as $key => $upselling) {

          $precio_neto = $upselling->isDisponible($busqueda);
          $precio = $upselling->getPrecioConImpuestos($precio_neto, $impuestos);
          if ($precio > 0) {
            $titulo = ($cantidades_upsellings[$upselling->id] > 1 ? $upselling->titulo." X".$cantidades_upsellings[$upselling->id] :  $upselling->titulo);

             $pre_ups = [
               "titulo" => $titulo,
               "upselling_id" => $upselling->id,
               "cantidad" => $cantidades_upsellings[$upselling->id],
               "precio" => $precio*$cantidades_upsellings[$upselling->id],
               "fecha_disfrute" => $fechas_disfrute[$upselling->id]
             ];
              array_push($ups_disponibles, $pre_ups);
          }

        }
        // tratamiendo de upsellings:

        $det =  array(
           'habitacion_id' => $data["hab"],
           'nombre' => $hab_temp->nombre,
           'ocupacion' => $data["ocupacion"],
           'precio' => $data["precio"],
           'regimen_id' => $data["regimen"],
           'nombre_ocupacion' => $nombre_ocupacion,
           'nombre_regimen' => $nombre_regimen[0]->nombre,
           'porcentaje' => $porcentaje,
           'concepto' => $concepto,
           'ups' => $ups_disponibles
         );
         $session = Session::all();
         if (isset($session["seleccion"])) {
           $seleccion = $session["seleccion"];
           array_push($seleccion, $det);
           Session::put("seleccion", $seleccion);
         }else{
           $seleccion = array();
           array_push($seleccion, $det);
           Session::put("seleccion", $seleccion);
         }
         return [
             '#contentSeleccion' => $this->makePartial('seleccion',
               ["seleccion" => $seleccion, "busqueda" => $busqueda])
         ];
        //return json_encode($det, JSON_UNESCAPED_UNICODE);
    }

    public function onAddUpsellingSec(){
      $data = Input::get();
      $session = Session::all();
      $busqueda = $session["busqueda"];
      $var_iniciales = $session["var_iniciales"];
      $ids_upsellings = array();
      $cantidades_upsellings = array();

      $ups_result = Upselling::find($data["id"]);
      $ups_disponibles = array();
      //SE BUSCAN LOS IMPUESTOS YA PRECARGADOS AL INICIAR EL CONTROLADOR
      $impuestos = null;
      foreach ($var_iniciales["hoteles"] as $hotel ) {
        if ($hotel["id"] == $busqueda["hotel"]) {
          $impuestos = $hotel["impuestos"];
        }
      }
      $precio_neto = $ups_result->isDisponible($busqueda)*$data["cantidad"];
      $precio = $ups_result->getPrecioConImpuestos($precio_neto, $impuestos);
        if ($precio > 0) {
          $titulo = ($data["cantidad"] > 1 ? $ups_result->titulo." X".$data["cantidad"] :  $ups_result->titulo);
          $fecha_disfrute = null;
          if($ups_result->ind_calendario == 1){
            $date = new Carbon($data["fecha_disfrute"]);
            $fecha_disfrute = $date->format("d-m-Y");
          }

           $pre_ups = [
             "titulo" => $titulo,
             "cantidad" => $data["cantidad"],
             "precio" => $precio,
             "upselling_id" => $ups_result->id,
             "fecha_disfrute" => $fecha_disfrute
           ];
          array_push($ups_disponibles, $pre_ups);
        }

      //}
      if (isset($session["seleccion"])) {
        $seleccion_actual = $session["seleccion"];
        $seleccion = array_merge($seleccion_actual, $ups_disponibles);

      }else{
        $seleccion = $ups_disponibles;

      }
      Session::put("seleccion", $seleccion);
      //Session::put("seleccion", $seleccion);
      return [
          '#contentSeleccion' => $this->makePartial('seleccion',
            ["seleccion" => $seleccion, "busqueda" => $busqueda])
      ];
    }
    public function onAprobar($id){
      $form = Input::get();
      //trace_log($form);
      $pago_insite = false;
      if(isset($form["insite"])){
        $pago_insite = $form["insite"];
      }

      $reservacion = ReservaModel::find($id);
      //trace_log($reservacion->getResumen());

      if($reservacion->status_id != 1){
        $reservacion->restarInventarios(); //RESTA LA OCUPACION
      }
      if ($reservacion->aprobar($pago_insite, false)) {
        Flash::success("Proceso Completado");

      }else{
        Flash::error("No se ha podido completar su solicitud");
      }
      return Redirect::refresh();
      
    }

    public function onCheck(){
      $fecha = new Fecha();
      $form = Input::get();
      Session::forget("seleccion");

      if (!empty($form["fechas"])) {
        $fechas = explode("|", $form["fechas"]);

        $checkin = new Carbon($fechas[0]);

        $checkout = new Carbon($fechas[1]);

        $data = array(
          "checkin" => $checkin->format("d-m-Y"),
          "checkout" => $checkout->format("d-m-Y"),
          "hotel" => $form["Reservacion"]["hotel"],
          "moneda" => $form["Reservacion"]["moneda"],
        );
        //var_dump($data);
        //$end =  new Carbon($form["Reservacion"]["checkout"]);

        //$checkout = $end->subDays(1);

        $messages = [
            'required' => ' :attribute es requerido.',
            'after' => 'checkout debe ser despues del checkin',
        ];

        $validator = Validator::make(
            $data,
            [
              'checkin' => 'required',
              'checkout' => 'required|after:checkin',
              'hotel' => 'required'
            ],
            $messages
        );

        if ($validator->fails()) {
         throw new ValidationException($validator);
        }

        $fechas_estadia = $data;
        $var_iniciales = Session::get("var_iniciales");

        if (isset($var_iniciales["hoteles"])) {
          foreach ($var_iniciales["hoteles"] as  $hotel) {
            if ($data["hotel"]==$hotel["id"]) {
              $fechas_estadia["nombre_hotel"] = $hotel["nombre"];
            }
          }
        }

        if (isset($var_iniciales["monedas"])) {
          foreach ($var_iniciales["monedas"] as  $moneda) {
            if ($data["moneda"]==$moneda["id"]) {
              $fechas_estadia["nombre_moneda"] = $moneda["moneda"];
              $fechas_estadia["acronimo"] = $moneda["acronimo"];
            }
          }
        }
        $data["checkout"] = $checkout->subDays(1);
        $this->vars['props'] = $data;
        $resultado = $fecha->buscarDisponibilidad($data);
        $this->vars['habitaciones_disponibles'] = $resultado;
        $upsellings = Upselling::select(["id", "titulo","cantidad_min", "cantidad_max", "tipo_inventario",
         "sumable", "disponible", "ind_calendario"])->
        whereHas("categorias", function($query){
          $query->where("id", ">", 0);
        })->where("hotel_id", $data["hotel"])->get();
        $upsellings_habs =  array(); //DISPONIBLE EN HABITACIONES
        $upsellings_secundarios =  array(); //PAQUETES Y UNITARIOS

        $impuestos = null;
        foreach ($var_iniciales["hoteles"] as $hotel ) {
          if ($hotel["id"] == $form["Reservacion"]["hotel"]) {
            $impuestos = $hotel["impuestos"];
          }
        }
        foreach ($upsellings as $upselling) {
          //echo "hola";
          $precio_neto = $upselling->isDisponible($data);

          $precio = $upselling->getPrecioConImpuestos($precio_neto, $impuestos);
          if ($precio) {
            foreach ($upselling->categorias as $categoria) {
              if ($categoria->id == 1) {
                $upselling->precio = $precio;
                array_push($upsellings_habs, $upselling);
              }else{
                $upselling->precio = $precio;
                if (!in_array($upselling, $upsellings_secundarios)&& $upselling->sumable == 0) {
                  array_push($upsellings_secundarios, $upselling);
                }

              }

            }
          }

        }

        //GUARDO LA SESSION PARA USAR EN OTRAS FUNCIONES
        Session::put('busqueda', $fechas_estadia);
        return [
            '#partialContents' => $this->makePartial('tabla_habs'),
            '#contentUpsellingsHabs' => $this->makePartial('lista_upsellings',
              ["upsellings" => $upsellings_habs, "prefix" => "hab",
              "busqueda" => $fechas_estadia]),
            '#contentFechasEstadia' => $this->makePartial('fechas_estadia',
              ["propiedades" => $fechas_estadia ]),
            '#contentUpsellingsSecundarios' => $this->makePartial('lista_upsellings',
            ["upsellings" => $upsellings_secundarios, "prefix" => "paq"]),
            '#contentSeleccion' => $this->makePartial('seleccion',
              ["seleccion" => null])
        ];

      }else{
          Flash::error("Elije las fechas a consultar");
      }
      //var_dump($data);
    }
    public function onMostrarPrecios(){
      $data = Input::get();

      $busqueda = Session::get("busqueda");
      //var_dump($data);
      $id = $data["hab"];


      $checkin = new Carbon($busqueda["checkin"]);

      $checkout = new Carbon($busqueda["checkout"]);
      //$checkout = $aux_checkout->subDays(1);


      //$props = $data["Reservacion"];
      $descuento = null;

      $habitacion = Habitacion::find($id);

      $precios = $habitacion->getPrecios($checkin, $checkout,
      $busqueda["moneda"]);

      $impuestos = $habitacion->hotel->getImpuestos($busqueda["moneda"]);

      //$cantidad_disponible = $habitacion->getCantidadDisponible($props);

      if (isset($data["checkbox"])) {
        $descuento = array("porcentaje" => $data["porcentaje"],
      "concepto" => $data["concepto"]);

        foreach ($precios  as $key => $precio) {
          $precios[$key]["total"] = $habitacion->getPrecioConDescuentos($precio["total"], $descuento);
        }
      }

      $habitacion->setArrayOcupaciones($precios);

      $habitacion->setArrayRegimenes($precios);

      return [
          '#contentPreciosModal' => $this->makePartial('precios_hab', [
            'habitacion' => $habitacion, 'precios' => $precios,
            'impuestos' => $impuestos,
            'busqueda' => $busqueda
          ])
      ];
    /*  return $this->makePartial('modal_precios_hab',['habitacion'=> $habitacion, 'precios' => $precios,
      'impuestos' => $impuestos]);*/
    }

    public function onMostrarPreciosUps(){
      $data = Input::all();
      $session = Session::get("busqueda");
      $upselling = Upselling::find($data["ups_id"]);

      $precio_disponible = null;

      if($upselling->ind_calendario == 1){
        $fechas = $upselling->getFechasDisponibles();
        $first_date = head($fechas);
        $last_date = last($fechas);

        $result = $upselling->getPrecioDisponibilidad($session["moneda"], $first_date);
        $precio_disponible = head($result->toArray());
        $fecha_disfrute_config = [
          "label"=>"Fecha Disfrute",
          "span" =>"full",
          "type"=> "datepicker",
          "mode" => "date",
          "format" => "d-m-yy",
          "minDate" => $first_date,
          "maxDate" => $last_date,
          "cssClass" => "datepicker-upselling",
        ];
      }else{
        $first_date = new Carbon($session["checkin"]);

        $result = $upselling->getPrecioDisponibilidad($session["moneda"], $first_date);
        $precio_disponible = head($result->toArray());
        $upselling->fecha_disfrute = $first_date;
        $upselling->cantidad = 1;
        $upselling->precio = $precio_disponible->precio;

        $fecha_disfrute_config = [
          "label"=>"Fecha Disfrute",
          "span" =>"full",
          "type"=> "datepicker",
          "mode" => "date",
          "format" => "d-m-yy",
          "default" => $first_date,
          "cssClass" => "hidden",
        ];
      }

      $attrs = array(
        "fields" => [
          "fecha_disfrute" => $fecha_disfrute_config,
          "precio" =>[
            "label" => "Precio",
            "type" => "text",
            "span" => "left",
            "default" => "0",
            "readOnly" => true
            ],
          "cantidad" =>[
            "label" => "Cantidad",
            "type" => "dropdown",
            "span" => "right",
            "options" =>
              ["0" => "0"]
            ],
            "id" =>[
              "label" => " ",
              "type" => "text",
              "default" => $upselling->id,
              "cssClass" => "hidden"
              ]
          ]
      );

      $config = $this->makeConfig($attrs);
      $config->model = $upselling;
      $widget = $this->makeWidget('Backend\Widgets\Form', $config);
      $widget->bindToController();
      return [
          '#formContentPreciosUps' => $this->makePartial('modal_precios_ups',
          ["widget" => $widget, "busqueda" => $session])
      ];

    }

    public function  onCambiarFechaDisfrute(){

      $data = Input::all();

      $busqueda = Session::get("busqueda");
      $var_iniciales = Session::get("var_iniciales");
      $upselling = Upselling::find($data["id"]);
      $fecha = new Carbon($data["fecha_disfrute"]);
      $result = $upselling->getPrecioDisponibilidad($busqueda["moneda"], $fecha);
      $precio_disponible = head($result->toArray());
      $impuestos = null;
      foreach ($var_iniciales["hoteles"] as $hotel ) {
        if ($hotel["id"] == $busqueda["hotel"]) {
          $impuestos = $hotel["impuestos"];
        }
      }
      //foreach ($precio_disponible as $key => $value) {
      $precio_disponible->precio = $upselling->getPrecioConImpuestos($precio_disponible->precio, $impuestos);
      //}
      return json_encode($precio_disponible);
    }
    public function onGuardarReservacion(){

      $agente = $this->user;

      $post = post();
      $data = $post["Reservacion"];
      $session = Session::all();

      $messages = [
          'required' => ' :attribute es requerido.',
      ];

      $validator = Validator::make(
          $data,
          [
            'huesped' => 'required',
            'usuario' => 'required',
            'identificacion' => 'required',
            'huesped' => 'required',
            'contacto' => 'required',
            'identificacion' => 'required',
          ],
          $messages
      );

      if ($validator->fails()) {
       throw new ValidationException($validator);
      }
      //echo count($session["seleccion"]);
      //return 0;
      if (!isset($session["seleccion"]) || count($session["seleccion"])<1) {
        Flash::error("Elige al menos una habitación o servicio para cotizar");
        return false;
      }
      $user = User::where("email", $data["usuario"])->first();
      //PASO 1
      if (!$user) {
        //NO EXISTE EL USUARIO, LO CREO Y LE PONGO GRUPO DE NO CONFIRMADOS
        $pass = uniqid();
        $group = UserGroup::find(3);

        $user = Auth::register([
          'name' => '',
          'email' => $data["usuario"],
          'password' => $pass,
          'password_confirmation' => $pass,
        ]);

        /*$user = User::create(['name' => '', 'surname' => '', 'password' => $pass,
        'password_confirmation'=> $pass,'email' => $data["usuario"]]);*/

        $user->groups()->add($group);
        //return "no existe";
      }
      //PASO 2 verifico si el hotel tiene impuestos que ya están en la sesion :)
      $impuestos = null;
      foreach ($session["var_iniciales"]["hoteles"] as $hotel ) {
        if ($hotel["id"] == $session["busqueda"]["hotel"]) {
          $impuestos = $hotel["impuestos"];
        }
      }

      $begin = new Carbon($session["busqueda"]["checkin"]);
      $end = new Carbon($session["busqueda"]["checkout"]);

      $total = 0;

      $tiene_habitacion = false;
      foreach ($session["seleccion"] as $item) {
        if (isset($item["habitacion_id"])) {
          $tiene_habitacion = true;
        }
        $total = $total+$item["precio"];
        if (isset($item["ups"])) {
          foreach ($item["ups"] as $ups) {
            $total = $total+$ups["precio"];
          }
        }
      }

      if ($tiene_habitacion) {
        $origen = 2;
        if($data["status"]==1){
          $origen = 3;
        }
        $fechaAhora = Carbon::now();
        $fechaVigencia = $fechaAhora->addDays(1);
        $code = uniqid();
        $reservacion = ReservaModel::create([
          'huesped' => $data["huesped"],
          'checkin' => $begin->format("Y-m-d"),
          'checkout' => $end->format("Y-m-d"),
          'moneda_id' => $session["busqueda"]["moneda"],
          'usuario_id' => $user->id,
          'status_id' => $data["status"],
          'total' => $total,
          'identificacion' => $data["identificacion"],
          'contacto' => $data["contacto"],
          'comentarios' => $data["comentarios"],
          'hotel_id' => $data["hotel"],
          'fecha_vigencia' => $fechaVigencia,
          'origen_id' => $origen,
          'codigo' => $code
          //'pago_insite' => $data["pago_insite"]
        ]);
        $reservacion->save();
        //GUARDO LAS HABITACIONES EN LA RESERVACION
        foreach ($session["seleccion"] as $key => $item ) {

          if (isset($item["habitacion_id"])) {
            //SI ES UNA HABITACION
            $detalle = new DetalleModel([
             'habitacion_id' => $item["habitacion_id"],
             'ocupacion' => $item["ocupacion"],
             'precio' => $item["precio"],
             'regimen_id' => $item["regimen_id"],
             'huespedes' => []
              ]);
            $detalle = $reservacion->detalles()->add($detalle);
            $ultimo_detalle = $reservacion->detalles()->latest("id")->first();
            if ($item["porcentaje"]!="") {
                Db::table('hesperiaplugins_hoteles_descuento_reserva')->insert(
                  ['descuento_id' => 12, 'porcentaje' =>$item["porcentaje"],
                'detalle_id' => $ultimo_detalle->id, 'concepto' => $item["concepto"]]
                );
             }
            if(isset($item["ups"])){
               foreach ($item["ups"] as  $ups_hab) {
                 if (isset($ups_hab["fecha_disfrute"])
                  && $ups_hab["fecha_disfrute"]!=null && $ups_hab["fecha_disfrute"]!="NA") {
                   $fecha_disfrute =  new Carbon($ups_hab["fecha_disfrute"]);
                 }else{
                   $fecha_disfrute =  null;
                 }

                 $upgrade = new Upgrade([
                   'precio' => $ups_hab["precio"],
                   'upgradable_type' => "HesperiaPlugins\Hoteles\Models\DetalleReservacion",
                   'upgradable_id' => $ultimo_detalle->id,
                   'upselling_id' => $ups_hab["upselling_id"],
                   'moneda_id' => $session["busqueda"]["moneda"],
                   'cantidad' => $ups_hab["cantidad"],
                   'fecha_disfrute' => $fecha_disfrute
                 ]);
               $ultimo_detalle->upgrades()->add($upgrade);
               }
             }

          }else{
            //HAY UPSELLINGS SECUNDARIOS, TIPO PAQUETE PUES
            if (isset($item["fecha_disfrute"])
              && $item["fecha_disfrute"]!=null && $item["fecha_disfrute"]!="NA") {
              $fecha_disfrute =  new Carbon($item["fecha_disfrute"]);
            }else{
              $fecha_disfrute =  null;
            }
            $upgrade = new Upgrade([
              'precio' => $item["precio"],
              'upgradable_type' => "HesperiaPlugins\Hoteles\Models\Reservacion",
              'upgradable_id' => $reservacion->id,
              'upselling_id' => $item["upselling_id"],
              'moneda_id' => $session["busqueda"]["moneda"],
              'cantidad' => $item["cantidad"],
              'fecha_disfrute' => $fecha_disfrute
            ]);

          $reservacion->upgrades()->add($upgrade);
          }

        }
        if ($impuestos) {
          foreach ($impuestos as $key => $value) {
            Db::table('hesperiaplugins_hoteles_impuesto_reserva')->insert(
              ['impuesto_id' => $value["id"], 'reserva_id' => $reservacion->id,
            'valor' => $value["valor"]]
            );
          }
        }
        $cotizacion = new Cotizacion([
          "cotizable_id" => $reservacion->id,
          "cotizable_type" => "HesperiaPlugins\Hoteles\Models\Reservacion",
          "usable_type" => "Backend\Models\User",
          "usable_id" => $agente->id,
        ]);
        $cotizacion->save();
        
        if($data["status"] == 1){
          Mail::send("hesperiaplugins.hoteles::mail.aprobacion_reserva",
            $reservacion->getResumen(), function($message) use ($user) {
            $message->to($user->email);
          });
          
        }else{

            Mail::send("hesperiaplugins.hoteles::mail.pre_reserva",
            $reservacion->getResumen(), function($message) use ($user) {
            $message->to($user->email);

          });
        }
        
        Flash::success("Proceso Completado");
        return($this->makeRedirect("update", $reservacion));
      }else{
        //creo una compra
        $fechaAhora = Carbon::now();
        $fechaVigencia = $fechaAhora->addDays(1);

        $code = uniqid();
        $compra = new Compra ([
          "nombre_cliente" => $data["huesped"],
          "identificacion" => $data["tipo_documento"].$data["identificacion"],
          "usuario_id" => $user->id,
          "comentario" => $data["comentarios"],
          "status_id" => 2,
          "moneda_id" => $session["busqueda"]["moneda"],
          "origen_id" => 2,
          "contacto" => $data["codigo_telefono"].$data["contacto"],
          "total" => $total,
          "fecha_vigencia" => $fechaVigencia,
          'pago_insite' => $data["pago_insite"],
          'codigo' => $code
        ]);

        $compra->save();
        foreach ($session["seleccion"] as $key => $item ) {

          if (isset($item["fecha_disfrute"])
          && $item["fecha_disfrute"]!=null && $item["fecha_disfrute"]!="NA") {
            $fecha_disfrute =  new Carbon($item["fecha_disfrute"]);
          }else{
            $fecha_disfrute =  null;
          }

          $upgrade = new Upgrade([
            'precio' => $item["precio"],
            'upgradable_type' => "HesperiaPlugins\Hoteles\Models\Compra",
            'upgradable_id' => $compra->id,
            'upselling_id' => $item["upselling_id"],
            'moneda_id' => $session["busqueda"]["moneda"],
            'cantidad' => $item["cantidad"],
            'fecha_disfrute' => $fecha_disfrute
          ]);

          $compra->upgrades()->add($upgrade);

          if ($impuestos) {
            foreach ($impuestos as $key => $value) {
              Db::table('hesperiaplugins_hoteles_impuestables')->insert(
                ['impuesto_id' => $value["id"], 'impuestable_id' => $compra->id,
                "impuestable_type" => 'HesperiaPlugins\Hoteles\Models\Compra',
              'valor' => $value["valor"]]
              );
            }
          }

        }

        $cotizacion = new Cotizacion([
          "cotizable_id" => $compra->id,
          "cotizable_type" => "HesperiaPlugins\Hoteles\Models\Compra",
          "usable_type" => "Backend\Models\User",
          "usable_id" => $agente->id,
        ]);
        $cotizacion->save();
        Mail::send("hesperiaplugins.hoteles::mail.pre_compra",
          $compra->getResumen(), function($message) use ($compra) {
          $message->to($compra->usuario->email);

        });

        Flash::success("Proceso Completado");
        return Redirect::to('backend/hesperiaplugins/hoteles/compra')->with('message', 'Proceso Completado');
      }

    }

    function onReenviarCotizacion($id){

      $reservacion = ReservaModel::find($id);
      //  trace_log($reservacion->getResumen());
      try{
        Mail::send("hesperiaplugins.hoteles::mail.pre_reserva",
          $reservacion->getResumen(), function($message) use ($reservacion) {
          $message->to($reservacion->usuario->email);
        });
        Flash::success("Proceso Completado");
      }
      catch(\Exception $e){
          //var_dump();
          Flash::error("No se ha podido completar la operación, intente más tarde".$e->getMessage());
      }

    }


    function onReenviarAnulada($id){

      $reservacion = ReservaModel::find($id);
      //  trace_log($reservacion->getResumen());
      try{
        Mail::send("hesperiaplugins.hoteles::mail.reserva_anulada",
          $reservacion->getResumen(), function($message) use ($reservacion) {
          $message->to($reservacion->usuario->email);
        });
        Flash::success("Proceso Completado");
      }
      catch(\Exception $e){
          //var_dump();
          Flash::error("No se ha podido completar la operación, intente más tarde".$e->getMessage());
      }

    }

    function onCambiarStatus($id){
      $form = Input::get();

      //var_dump($form);

      $data = array(
        "motivo" => $form["descripcion"],
      );

      $messages = [
          'required' => ' :attribute es requerido.',
      ];

      $validator = Validator::make(
          $data,
          [
            'motivo' => 'required',

          ],
          $messages
      );

      if ($validator->fails()) {
       throw new ValidationException($validator);
      }

      $reservacion = ReservaModel::find($id);

      //$reservacion->status = $form["status"];

      $usuario = $this->user;

      //$reservacion->save();


      $observacion = Observacion::create(['descripcion' => $form["descripcion"], 'usable_type' => 'Backend\Models\User',
      'usable_id' => $usuario->id, 'observable_id'=> $reservacion->id, 'observable_type' => 'HesperiaPlugins\Hoteles\Models\Reservacion']);

      $observacion->save();

      Flash::success("Proceso Completado");

      return Redirect::refresh();

    }

    public function onRemoveSeleccion(){
      $data = Input::all();
      $nueva_seleccion = array();
      $seleccion_actual = Session::get("seleccion");
      $busqueda = Session::get("busqueda");

      unset($seleccion_actual[$data["index"]]);

      if (isset($seleccion_actual) && $seleccion_actual != null) {
        foreach ($seleccion_actual as $key => $value) {
          array_push($nueva_seleccion, $value);
        }
      }
      
      Session::put("seleccion", $seleccion_actual);

      return [
              '#contentSeleccion' => $this->makePartial('seleccion',
                ["seleccion" => $seleccion_actual, "busqueda" => $busqueda])
          ];
    }
    public function reporte_reservas(){
      $config = $this->makeConfig('$/hesperiaplugins/hoteles/models/reservacion/reporte_fields.yaml');
      $config->model = new \HesperiaPlugins\Hoteles\Models\Reservacion;
      $widget = $this->makeWidget('Backend\Widgets\Form', $config);
      $this->vars['widget'] = $widget;
      $this->pageTitle = 'Reporte de Reservas';
    }

    public function onReporte(){
      $reservacion = new ReservaModel();
      $form = Input::get();

      $validator = Validator::make(
          $form, ['desde' => 'required','hasta' => 'required']
      );

      if ($validator->fails()) {
       throw new ValidationException($validator);
      }

      $resultado = $reservacion->reportReservas($form);
      $this->vars['resultado'] = $resultado;
      return [
          '#partialContents' => $this->makePartial('tabla_report_reservas')
      ];
    }

    public function detalle_agente($id = null, $moneda = null, $hotel = null, $inicio = null, $fin = null){
      $this->agente_id = $id; //VARIABLE QUE USO EN EL METODO listExtendQueryBefore
      $this->moneda_id = $moneda;
      $this->hotel_id = $hotel;
      $this->desde = $inicio;
      $this->hasta = $fin;
      $this->vars['agente_id'] = $id;
      $this->pageTitle = 'Detalle de Agente';
      $this->asExtension('ListController')->index();
    }

    public function detalle_paquete($id = null, $moneda = null, $inicio = null, $fin = null){

      $this->paquete_id = $id;
      $this->desde = $inicio;
      $this->hasta = $fin;
      $this->moneda_id = $moneda; //VARIABLES QUE USO EN EL METODO listExtendQueryBefore

      $this->vars['paquete_id'] = $this->paquete_id;
      BackendMenu::setContext('HesperiaPlugins.Hoteles', 'main-menu-item3', 'side-menu-item');

      $this->pageTitle = 'Detalle de Paquete';
      $this->asExtension('ListController')->index();
    }

    public function detalle_upselling($id = null, $moneda = null, $inicio = null, $fin = null){

      $this->upselling_id = $id;
      $this->desde = $inicio;
      $this->hasta = $fin;
      $this->moneda_id = $moneda;
      $this->vars['upselling_id'] = $this->upselling_id;

      $this->pageTitle = 'Detalle de Upselling';
      $this->asExtension('ListController')->index();
    }

    public function listExtendQueryBefore($query){

      if ($this->agente_id){
        $query->join('hesperiaplugins_hoteles_cotizacion as b',
          'hesperiaplugins_hoteles_reservacion.id', '=', 'b.cotizable_id')
              ->join('backend_users as c', 'b.usable_id', '=', 'c.id')
              ->where('b.usable_id', $this->agente_id);
      }

      if($this->paquete_id){

        $query->where('paquete_id', $this->paquete_id)->get();
      }

      if($this->upselling_id){

        $begin = new Carbon($this->desde);
        $end = new Carbon($this->hasta);
        //if
        $query->when($this->desde, function($query) use($begin, $end) {

          return $query->whereHas("detalles", function($query) use($begin, $end){

            $query->whereHas("upgrades",function($q) use($begin, $end){

              $q->where('upselling_id',$this->upselling_id)->whereBetween("created_at", [$begin, $end])->where('moneda_id',$this->moneda_id);
            });

          })->orWhereHas('upgrades',function($query) use($begin, $end){
                $query->where('upselling_id',$this->upselling_id)->whereBetween("created_at", [$begin, $end])->where('moneda_id',$this->moneda_id);
        });
            //else
        }, function($query){

             return $query->whereHas("detalles", function($query){

              $query->whereHas("upgrades",function($q){

                $q->where('upselling_id',$this->upselling_id)->where('moneda_id',1);
              });

            })->orWhereHas('upgrades',function($query){
                  $query->where('upselling_id',$this->upselling_id)->where('moneda_id',1);;
          });
        })->get();
      }
    }

    public function listFilterExtendScopes($filter) {
      if ($this->paquete_id) {

        $filter->removeScope('cotizacion');
      }

      if($this->desde != null && $this->hasta != null) {

        $filter->setScopeValue('fecha',
          ["0" => new Carbon($this->desde),
           "1" => new Carbon($this->hasta)]);
      }

      if($this->moneda_id) {

        $filter->setScopeValue('moneda', ["$this->moneda_id" => 'moneda']);
      }

      if($this->hotel_id) {

        $filter->setScopeValue('hotel', ["$this->hotel_id" => 'hotel']);
      }
    }
}
