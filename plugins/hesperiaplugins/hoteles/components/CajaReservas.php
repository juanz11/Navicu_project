<?php namespace HesperiaPlugins\Hoteles\Components;

use Cms\Classes\ComponentBase;
use HesperiaPlugins\Hoteles\Models\Hotel;
use HesperiaPlugins\Hoteles\Models\Fecha;
use HesperiaPlugins\Hoteles\Models\Habitacion;
use HesperiaPlugins\Hoteles\Models\Moneda;
use HesperiaPlugins\Hoteles\Models\Upselling;
use Flash;
use Session;
use Input;
use Redirect;
use Validator;
use Db;
use Carbon\Carbon;

class CajaReservas extends ComponentBase{
  
  public $hoteles;
  public $checkIn;
  public $checkOut;
  public $hotelDefault;
  public $listaMonedas;
  public $seleccion;
  public $moneda_inicial;


  public $implement = [
    'HesperiaPlugins.Hoteles.Behaviors.UtilityFunctions'
  ];
  public function defineProperties(){
    return [
      'checkIn' => [
        'title' => 'Check In',
        'description' => 'Fecha de llegada',
        'type' => 'string',
      ],
      'checkOut' => [
        'title' => 'Check Out',
        'description' => 'Fecha de Salida',
        'type' => 'string',
      ],

      'hotel_default' => [
        'title' => 'Hotel Inicial',
        'description' => 'Hotel Seleccionado',
        'type' => 'dropdown',
      ],
      'mostrar_moneda' => [
        'title' => 'Mostrar Moneda',
        'description' => 'Mostrar u ocultar el campo de moneda',
        'type' => 'checkbox',
      ],
      'codigo_promo' => [
        'title' => 'Mostrar Cod. Promo',
        'description' => 'Mostrar u ocultar el campo de codigo',
        'type' => 'checkbox',
      ],
      'moneda' => [
        'title' => 'Moneda',
        'description' => 'Moneda Inicial',
        'type' => 'dropdown',
      ],
      'redirect' => [
        'title' => 'Redirigir a:',
        'description' => 'Pagina destino luego de consultar',
        'type' => 'string',
      ]
    ];
  }

  public function componentDetails(){
    return [
      'name'=> 'Caja de Reservas',
      'description' => 'Caja de reservas principal'
    ];
  }

  public function onRender(){

    $this->hoteles = $this->cargar_hoteles();
    $this->listaMonedas = Moneda::isActiva()->get()->toArray();

  }

  protected function cargar_hoteles(){
    $hoteles = Hotel::select("id", "nombre")->get();
    return $hoteles->toArray();
  }

  public function onRun(){
    // This code will be executed when the page or layout is
    // loaded and the component is attached to it.
    $this->addJs('assets/js/caja_reservas.js');
    $this->addJs('assets/js/bootstrap-datepicker.js');
    $this->addJs('assets/js/bootstrap-datepicker.es.js');

    $this->addCss('assets/css/reservaciones.css');
    $this->addCss('assets/css/bootstrap-datepicker.css');

    $data = Session::all();

    Session::forget(['seleccion',"items", "upsellings_paq", "paquete_id",
     "seleccion_paquete"]);


    if ($this->property("redirect")=="" && isset($data["disponibilidad"])) {
        $array_ids = $data["disponibilidad"];
        if ($props = Session::get('propiedades')) {
        //echo " -- tengo propiedades";
        //$arrayProp = $data["disponibilidad"]["propiedades"]; //id_hotel, checkin, checkout, moneda
        $this->hotelDefault = $props["hotel"];
        $this->checkIn = $props["checkin"];
        $this->checkOut = $props["checkout"];
        //cargo habitaciones en pagina
        //DEBEMOS TRATAR DE ELIMINAR ESTAS VARIABLES Y USAR SOLO PROPS
        $this->page["hotel"] = $props["hotel"];
        $this->page["checkin"] = $props["checkin"];
        $this->page["checkout"] = $props["checkout"];
        $this->page["moneda"] = $props["moneda"];

        $this->page["props"] = $props;

        $this->page["habitaciones"] = Habitacion::whereIn('id', $array_ids)->where("status", "=", "1")
        ->get();
      }else{
        //$this->cargar_desde_url();
      }
    }else{
      //Session::forget(["disponibilidad", "propiedades"]);
      $this->cargar_desde_url();
      //Session::reflash();
      //echo "no existe disponibilidad";
    }
  }

  public function onCheck(){
    $data = post();

    if (!isset($data["moneda"]) || $data["moneda"]==null) {
      if ($this->property("moneda")!=null) {
        $data["moneda"] =  $this->property("moneda");
      }else{
          //$data["moneda"] = $this->page["moneda"]; //MOSCA
      }
    }

    if ($this->listaMonedas == null) {
      $this->listaMonedas = Moneda::isActiva()->get()->toArray();
    }

    //var_dump($data);
    $validator = Validator::make(
      [
      'checkin' => $data["checkin"],
      'checkout' => $data["checkout"],
      'hotel' => $data["hotel"]
      ],
      [
        'checkin' => 'required|dateformat:d-m-Y',
        'checkout' => 'required|dateformat:d-m-Y|after:checkin',
        'hotel' => 'required|numeric|min:1'
      ]
    );
    if ($validator->fails()) {
      Flash::warning('Formato de fechas incorrecto');
    }else{
      

      $fecha = new Fecha();
      //var_dump($data);
      $resultado = $fecha->buscarDisponibilidad($data);

      if ($resultado!=null) {
       // Session::put("disponibilidad", $resultado);
        foreach ($this->listaMonedas as $key => $value) {
          if($data["moneda"]==$value["id"]){
            $data["acronimo"]=$value["acronimo"];
          }
        }

        $data["numero_noches"] = $this->getNumeroNoches($data);

        Session::put('propiedades', $data);

        Session::put('disponibilidad', $resultado);

        $props = Session::get("propiedades");
        //$array = array_add($resultado, 'propiedades', $data);
        if ($this->property('redirect')!="") {
          $redirect = $this->property('redirect');
          //Session::flush();
          
          return Redirect::to($redirect);
        }else{
          //PASAMOS LAS PROPIEDADES A LA PAGINA PORQUE LA SESSION NO LLEGA
          //LO MISMO DE ARRIBA
         // Session::put('propiedades', $data);
          $this->page["props"] = $props;
          $this->page["hotel"] = $props["hotel"];
          $this->page["checkin"] = $props["checkin"];
          $this->page["checkout"] = $props["checkout"];
          $this->page["moneda"] = $props["moneda"]; //problema 1
          $this->page["habitaciones"] = Habitacion::whereIn('id', $resultado)->where("status", "=", "1")->get();
        }

      }else{
        /*borro las propiedades (checkin, etc) de la sesion*/
        Flash::warning("No tenemos habitaciones disponibles en las fechas indicadas, ¿Has probado seleccionando otra moneda?");
        Session::forget(['propiedades','disponibilidad']);
        //Session::flush();
        $this->page["habitaciones"] = null;
      }
      
    }
    /*borrar la seleccion cuando se haga una busqueda*/
    Session::forget('seleccion');
    //$this->page["seleccion"]=null;
  }

  public function getHotelesOptions(){
    $hoteles = Hotel::select("id", "nombre")->get();
    $array = ["" => "Ninguno"];
    foreach ($hoteles as $key => $value) {
      $array[$key] = $value->nombre;
    }
    return $array;
  }

  public function getMonedaOptions(){
    //
     //Moneda::select("moneda", "id")-isActiva()->get()->toArray();
    $moneda = Db::table('hesperiaplugins_hoteles_moneda as a')->where("ind_activo", 1)
    ->lists('a.moneda', 'a.id');
    return $moneda;
  }

  public function onAddSeleccion(){

    $data = post();
    $session = Session::all();
    //trace_log($data);
    //obtengo la cantidad disponible de la habitaciones
    $validator = Validator::make(
      [
      'precio' => $data["precio"]
      ],
      [
        'precio' => 'required|regex: ^[1-9][0-9]*$^',
      ],
      ['precio.regex' => "No tenemos precio disponible para ésta ocupación/régimen"
      ]
    );
    if ($validator->fails()) {
      //  echo "$retorno";
        //Flash::error($messages);
        $messages = $validator->messages();
        $retorno = "";
        foreach ($messages->all('<li>:message</li>') as $message) {
          $retorno .= $message;
        }

        return Flash::error($retorno);
    }else{
      if (isset($session["propiedades"])) {
        $propiedades = $session["propiedades"];

        $checkoutaux = new Carbon ($propiedades["checkout"]);
        $propiedades["checkout"] = $checkoutaux->subDay();
        $hab =  Habitacion::find($data["habitacion_id"]);
        $disponible = $hab->getCantidadDisponible($propiedades);
        if ($disponible > 0 ) {
          if (isset($session["seleccion"]) && $session["seleccion"]!=null) {
            //SI HAY AL MENOS UNA HABITACION SELECCIONADA
            $total_seleccionadas = 0;
            foreach ($session["seleccion"] as $key => $value) {
              if ($value["habitacion_id"]==$data["habitacion_id"]) {
                $total_seleccionadas++;
              }
            }

            if ($total_seleccionadas < $disponible) {
              $array =   $session["seleccion"];
              $precio_alojamiento = $data["precio"]; //guardo el precio antes de verificar los upsellings
              $data["alojamiento"] = $precio_alojamiento;
              $data["upsellings"] = array();

              $data = $this->preparar_upsellings($data);
              $precio_upsellings = 0;
              foreach ($data["upsellings"] as $key => $value) {
                $precio_upsellings = $value["precio"] + $precio_upsellings;
              }
              $obDescuentos = $hab->getDescuentos($propiedades);
              $arrDescuentos = array();
              if ($obDescuentos != null ) {
                foreach ($obDescuentos as $descuento) {
                  $desc = array(
                    "descuento_id" => $descuento->id,
                    "porcentaje" => $descuento->porcentaje,
                    "concepto" => $descuento->concepto,
                    "codigo_promocional" => $descuento->codigo_promocional,
                    "noches_gratis" => $descuento->noches_gratis
                  );
                  array_push($arrDescuentos, $desc);
                }
              }
              $data["descuentos"] = $arrDescuentos;

              $data["precio"] = $precio_upsellings + $precio_alojamiento;
              
              array_push($array, $data);
              Session::put('seleccion', $array);
            }else{
              Flash::warning('Ésta habitación ya no se encuetra disponible');
            }
          }else{
            //NO HAY NADA SELECCIONADO AÚN
            $precio_alojamiento = $data["precio"]; //guardo el precio antes de verificar los upsellings
            $data["alojamiento"] = $precio_alojamiento;
            $data["upsellings"] = array();

            $data = $this->preparar_upsellings($data);
            $precio_upsellings = 0;
            foreach ($data["upsellings"] as $key => $value) {
              $precio_upsellings = $value["precio"] + $precio_upsellings;
            }

            $obDescuentos = $hab->getDescuentos($propiedades);
              $arrDescuentos = array();
              if ($obDescuentos != null ) {
                foreach ($obDescuentos as $descuento) {
                  $desc = array(
                    "descuento_id" => $descuento->id,
                    "porcentaje" => $descuento->porcentaje,
                    "concepto" => $descuento->concepto,
                    "codigo_promocional" => $descuento->codigo_promocional,
                    "noches_gratis" => $descuento->noches_gratis
                  );
                  array_push($arrDescuentos, $desc);
                }
              }
            $data["descuentos"] = $arrDescuentos;

            $data["precio"] = $precio_upsellings + $precio_alojamiento;

            $array = array("0" => $data);
            //AQUI SE VERIFICAN LOS UPSELLINGS
            Session::put("seleccion", $array);
          }
        }else{
          Flash::warning('Ésta habitación ya no se encuetra disponible');
        }
      }else{
        Flash::warning("Su sesión a caducado, vuelva a consultar nuestra disponibilidad");
      }

      $this->page["seleccion"] = Session::get('seleccion');
      if (isset($propiedades)) {
        
        $this->page["propiedades"] = $propiedades;
      }
      
    }
  }

  public function preparar_upsellings($data){
    $ids_upsellings = array(); // array de ids para el query
    $upsellings_cantidad = array();// array de upsellings id-cantidad
    $session = Session::all();
    $propiedades = $session["propiedades"];

    foreach ($data as $key => $value) {
      if (count($aux = explode("-",$key)) == 2 && $value > 0) {
        array_push($ids_upsellings, $aux[1]);
        //array_push($upsellings_cantidad, $aux[1]);
        $upsellings_cantidad[$aux[1]]["cantidad"] = $value;
      }
    }
    if (count($ids_upsellings)>0) {
      $hotel = Hotel::find($propiedades["hotel"]);

      $impuestos = $hotel->getImpuestos($propiedades["moneda"]);
      $upsellings = Upselling::select(["titulo", "id", "tipo_inventario", "sumable", "ind_calendario", "disponible"])->whereIn("id",$ids_upsellings)->get();
      foreach ($upsellings as $key => $value) {
        if ($upsellings_cantidad[$value->id]["cantidad"]>1) {
          $titulo = $value->titulo." X".$upsellings_cantidad[$value->id]["cantidad"];
        }else{
          $titulo = $value->titulo;
        }
        $upsellings_cantidad[$value->id]["titulo"] = $titulo;
        $precio = $value->isDisponible($propiedades);

        $precioConImpuestos = $value->getPrecioConImpuestos($precio, $impuestos);

        $upsellings_cantidad[$value->id]["precio"] = $precioConImpuestos*$upsellings_cantidad[$value->id]["cantidad"];

      }
    }
    $data["upsellings"] = $upsellings_cantidad;
    return $data;
  }
  public function onDeleteSeleccion(){
    $data = post();
    $session = Session::all();
    if (isset($session["seleccion"]) && $session["seleccion"]!=null) {
      $array_actual = $session["seleccion"];
      //$key = array_search($data["index"], $array);
      unset($array_actual[$data["index"]]);
      $nuevo_array = array();
      foreach ($array_actual as $key => $value) {
        array_push($nuevo_array,$value);
      }
      Session::put('seleccion', $nuevo_array);
      $this->page["seleccion"] = Session::get('seleccion');
      //Session::put('seleccion', (
    }
  }

  public function onIrApagar(){
    $data =  Session::get('seleccion');
    $propiedades = Session::get('propiedades');
    if ($data != null && $propiedades !=null) {
      $habitacion = new Habitacion;
      $items = $habitacion->getResumenReserva($data, $propiedades);


      if (count($data)==count($items)) {
        //Flash::success("hola".count($data));
        Session::put("seleccion", $data);
        return Redirect::to("pago-de-reserva");
      }else{
        Flash::error("Ha ocurrido algo inesperado, vuelve a consultar la disponibilidad para reservar");
      }
    }
  }

  /*public function cambiarFormatoPrecio($val){
    $valor = number_format($val, 0 ,",", "." );
    return $valor;
  }*/

  public function onCargarPrecios(){
    $data = post();
    $sesion = Session::all();
    if (isset($sesion["propiedades"])) {
      $propiedades = $sesion["propiedades"];
      $habitacion = Habitacion::find($data["habitacion"]);
      $impuestos = $habitacion->hotel->getImpuestos($propiedades["moneda"]);
      $descuentos = $habitacion->getDescuentos($propiedades);
      $precios = $habitacion->getPrecios($propiedades["checkin"], $propiedades["checkout"], $propiedades["moneda"]);
      $upsellings = Upselling::whereHas("categorias", function($query){
        $query->where("categoria_id", 1);
      })->where("hotel_id", $propiedades["hotel"])->get();
        return [
            '#modal-hab-precios' => $this->renderPartial('@modal_precios.htm', [
              'habitacion' => $habitacion,
              'upsellings' => $upsellings,
              'propiedades' => $sesion["propiedades"],
              'precios' => $precios,
              'impuestos' => $impuestos,
              'descuentos' => $descuentos
            ])
        ];
    }else{
      Flash::error("Ha ocurrido algo inesperado, vuelve a consultar la disponibilidad para reservar");
    }
  }

  public function cargar_desde_url(){
    $array = array(
      "checkin"=> null,
      "checkout" => null,
      "moneda"=> null,
      "hotel" => null
    );
    if ($this->property("checkIn")) {
      try {
        $fecha = new Carbon($this->property("checkIn"));
        $today = new Carbon();

        //var_dump($today->diffInDays($fecha, false));

        if ($today->diffInDays($fecha, false)>= 0 ) {
          $this->checkIn = $fecha->format("d-m-Y");
          $array["checkin"] = $fecha->format("d-m-Y");
        }

      } catch (\Exception $e) {
        trace_log("function cargar_desde_url".$e->getMessage());
      }
    }
    if ($this->property("checkOut")) {
      try {
        $fecha = new Carbon($this->property("checkOut"));
        $this->checkOut = $fecha->format("d-m-Y");
        $array["checkout"] = $fecha->format("d-m-Y");
      } catch (\Exception $e) {
        trace_log("function cargar_desde_url".$e->getMessage());
      }
    }

    if ($this->property("hotel_default")){
      $array["hotel"] = $this->property("hotel_default");
    }

    $array["moneda"] = $this->property("moneda");

    $validator = Validator::make(
      [
      'checkin' => $array["checkin"],
      'checkout' => $array["checkout"],
      'hotel' => $array["hotel"]
      ],
      [
        'checkin' => 'required|dateformat:d-m-Y',
        'checkout' => 'required|dateformat:d-m-Y|after:checkin',
        'hotel' => 'required|numeric|min:1'
      ]
    );
    if (!$validator->fails()) {
      $fecha = new Fecha();
      $resultado = $fecha->buscarDisponibilidad($array);

      if(count($resultado)>0){
        $array["numero_noches"] = $this->getNumeroNoches($array);
        $array["cod_promo"]="";
        Session::put("propiedades", $array);
        $this->page["props"] = $array;
        $this->page["hotel"] = $array["hotel"];
        $this->page["checkin"] = $array["checkin"];
        $this->page["checkout"] = $array["checkout"];
        $this->page["moneda"] = $array["moneda"];
        $this->page["habitaciones"] = Habitacion::whereIn('id', $resultado)->where("status", "=", "1")->get();
      }
    }
  }

  public function isBlocked($propiedades, $tipo){
    
    $checkin = new Carbon($propiedades["checkin"]);
    $checkout = new Carbon($propiedades["checkout"]);
    $arrayDates = array();
    if ($tipo == 1) {
      //NAVIDAD
      $date1 = new Carbon("24-12-2019");
      $date2 = new Carbon("25-12-2019");
    }elseif($tipo == 2){
      $date1 = new Carbon("31-12-2019");
      $date2 = new Carbon("01-01-2020");
    }
    
    array_push($arrayDates, new Carbon($checkin));

    $flag = false;

    for ($i=0; $i < $checkin->diffInDays($checkout); $i++) { 
      $newDate = $checkin->addDay();
      array_push($arrayDates, new Carbon($newDate));
    }

    foreach ($arrayDates as $date) {
      //echo($date->toDateString()."---".$date1->toDateString());
      if ($date->toDateString() == $date1->toDateString() || $date->toDateString() == $date2->toDateString()) {
        //echo("ENTRO AQUI");
        $flag = true;
      }
    }
    if($propiedades["hotel"] == 2 || $propiedades["hotel"] == 4 || $propiedades["hotel"] == 5){
      return $flag;
    }else{
      return false;
    }
    
  }

  public function getNumeroNoches($propiedades){
    $checkin = new Carbon($propiedades["checkin"]);
    $checkout = new Carbon($propiedades["checkout"]);

    return $checkin->diffInDays($checkout);

  }

}
?>
