<?php namespace HesperiaPlugins\Hoteles\Components;

use Cms\Classes\ComponentBase;
use HesperiaPlugins\Hoteles\Models\Paquete as PaqueteModel;
use HesperiaPlugins\Hoteles\Models\Moneda;
use HesperiaPlugins\Hoteles\Models\Fecha;
use HesperiaPlugins\Hoteles\Models\Habitacion;
use Carbon\Carbon;
use Db;
use Session;
use Flash;
use Validator;
use ValidationException;
use Redirect;
class Paquete extends ComponentBase{

  public $paquete;
  public $monedas;
  public $diasDiff;
  public $fechaInicioCheckIn;
  public $fechaInicioCheckOut;

  public $init_checkout;
  public $moneda_inicial;

  public $implement = [
    'HesperiaPlugins.Hoteles.Behaviors.UtilityFunctions'
  ];

  public function defineProperties(){
    return [
      'slug' => [
          'title'       => 'Slug',
          'description' => 'identificador de paquete',
          'default'     => '{{ :slug }}',
          'type'        => 'string'
      ],
      'moneda' => [
        'title' => 'Moneda',
        'description' => 'Moneda Inicial',
        'type' => 'dropdown',
      ],
    ];
  }

  public function componentDetails(){
    return [
      'name'=> 'Vista especifica de un paquete',
      'description' => 'lorem ipsum'
    ];
  }

  public function onRender(){
    $this->moneda_inicial = $this->property("moneda");
  }
  public function onRun(){
    $this->addJs('assets/js/bootstrap-datepicker.js');
    $this->addJs('assets/js/bootstrap-datepicker.es.js');
    $this->addCss('assets/css/bootstrap-datepicker.css');
    $this->addJs('assets/js/paquete.js');
    $this->paquete = $paquete = PaqueteModel::where("slug", $this->param('slug'))->first();
    $this->monedas = Moneda::isActiva()->get()->toArray();

    if ($paquete) {

      $this->initFechas($paquete);
      $fechaDesde = Carbon::now();
      $fechaHasta = new Carbon($paquete->fecha_hasta);

      $this->diasDiff = $fechaDesde->diffInDays($fechaHasta);
      $this->fechaDesde = $fechaDesde->format("d-m-Y");
      $this->page["record"] = $paquete;
    }else{
      return Redirect::to("404");
    }
    Session::forget('seleccion_paquete');
  }

  public function initFechas($paquete){
    $fecha_desde_paq = new Carbon($paquete->fecha_desde);
    $fecha_hasta_paq = new Carbon($paquete->fecha_hasta);
    $hoy = new Carbon();

    if ($fecha_desde_paq >= $hoy) {
      $this->fechaInicioCheckIn = $fecha_desde_paq->format('d-m-Y');
    }else{
      $this->fechaInicioCheckIn = $hoy->format('d-m-Y');
    }
    $aux = new Carbon($this->fechaInicioCheckIn);
    $this->fechaInicioCheckOut = $aux->addDays($paquete->min_noches)->format('d-m-Y');

  }

  public function onCheck(){

    $paquete = PaqueteModel::where("slug", $this->param('slug'))->first();
    $form = post();
    $checkin = new Carbon($form["checkin"]);
    $checkout = new Carbon($form["checkout"]);

    $diffDays = $checkin->diffInDays($checkout);

    //echo "$diffDays";
    $in ="";

    for ($i=$paquete->min_noches; $i <= $paquete->max_noches; $i++) {
      $in.="$i,";
    }

    $validator = Validator::make(
      [
      'checkin' => $form["checkin"],
      'checkout' => $form["checkout"],
      'diffDays' => $diffDays
      ],
      [
        'checkin' => 'required|dateformat:d-m-Y',
        'checkout' => 'required|dateformat:d-m-Y|after:checkin',
        //'diffDays' => 'between:'.$paquete->min_noches.', '.$paquete->max_noches.''
        'diffDays' => "in:".$in
      ],
      ['in' => "Debes alojarte entre ".$paquete->min_noches." y ".$paquete->max_noches." para poder reservar este paquete"
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

        Flash::error($retorno);
    }else{
      $data = array(
        "checkin" => $form["checkin"],
        "checkout" => $form["checkout"],
        "moneda" => $form["moneda"],
        "hotel" => $paquete->hotel->id,
        "paquete_id" =>$paquete->id
      );

      $fecha = new Fecha();
      $habs_disponibles = $fecha->buscarDisponibilidad($data);
      $habs_front = array();
      $j=0;
      foreach ($paquete->habitaciones as $value2) {
        if(!in_array($value2->id, $habs_front) && in_array($value2->id, $habs_disponibles)){
          $habs_front[$j]=$value2->id;
          $j++;
        }
      }
      if (count($habs_disponibles)>0) {
        Session::put('propiedades', $data);
        $this->page["propiedades"] = $data;
        $this->page["habs_disponibles"] =  Habitacion::whereIn('id', $habs_front)->where("status", "=", "1")->get();
        $this->page["upsellings"] = $paquete->upsellings;
        $this->page["impuestos"] = $paquete->hotel->impuestos->where("moneda_id", $data["moneda"]);
        $this->page["moneda_usada"] = Moneda::find($form["moneda"]);
      }else{
          Flash::warning("No tenemos habitaciones disponibles en las fechas indicadas, ¿Has probado seleccionando otra moneda?");
      }

      //BORRAMOS LA SELECCION AL BUSCAR
      $this->page["seleccion"] = null;
      Session::forget("seleccion_paquete");
      Session::forget("upsellings_paq");
    }

  }

  public function onCargarPrecios(){
    $data = post();
    $sesion = Session::all();

    if (isset($sesion["propiedades"])) {
      $habitacion = Habitacion::find($data["habitacion"]);

      $precios = $habitacion->getPrecios($sesion["propiedades"]["checkin"], $sesion["propiedades"]["checkout"], $sesion["propiedades"]["moneda"]);

      $habitacion->setArrayOcupaciones($precios);

      $habitacion->setArrayRegimenes($precios);

      $impuestos = $habitacion->hotel->impuestos->where("moneda_id", $sesion["propiedades"]["moneda"]);

      $paquete = PaqueteModel::where("slug", $this->param('slug'))->first();


      $descuento = array("porcentaje" => "".$paquete->porcentaje,
      "concepto" =>"Descuento por paquete");

      //getPrecios($date1, $date2, $moneda)

      return [
          '#modal-content-form' => $this->renderPartial('@formulario_precios.htm', [
            'habitacion' => $habitacion,
            'precios' => $precios,
            'impuestos' => $impuestos,
            'descuento' => $descuento,
            'propiedades' => $sesion["propiedades"]
            ])
      ];
    }else{
      Flash::error("Ha ocurrido algo inesperado, vuelve a consultar la disponibilidad para reservar");
    }

  }

  public function onGuardarSeleccion(){
    $data = post();
    $session = Session::all();

    if (isset($session["propiedades"])) {
      $propiedades = $session["propiedades"];
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

          Flash::warning($retorno);
      }else{
        $checkoutaux = new Carbon ($propiedades["checkout"]);

        $propiedades["checkout"] = $checkoutaux->subDay();
        $hab = new Habitacion();
        //obtengo la cantidad disponible de la habitaciones
        $disponible = $hab::find($data["habitacion_id"])->getCantidadDisponible($propiedades);
        //busco la seleccion en sesion
        if ($disponible > 0) {

          if (isset($session["seleccion_paquete"]) && $session["seleccion_paquete"]!=null) {
            //si ya hay habitaciones seleccionadas
            $total_seleccionadas = 0;
            foreach ($session["seleccion_paquete"] as $key => $value) {
              if ($value["habitacion_id"]==$data["habitacion_id"]) {
                $total_seleccionadas++;
              }
            }
            if ($total_seleccionadas < $disponible) {
              $array =   $session["seleccion_paquete"];
              $data["precio_formateado"] = $hab->cambiarFormatoPrecio($data["precio"], $propiedades["moneda"]);
              array_push($array, $data);
              Session::put('seleccion_paquete', $array);

            }else{

              Flash::warning('Esta habitacion ya no se encuetra disponible');
            }
            //Session::put('seleccion', (
          }else{
            $data["precio_formateado"] = $hab->cambiarFormatoPrecio($data["precio"], $propiedades["moneda"]);
            $array = array("0" => $data);
            //echo "no tengo seleccion".$this->page["seleccion"]." ala";
            Session::put("seleccion_paquete", $array);
          }
        }else{
          Flash::warning('Esta habitacion ya no se encuetra disponible');
        }
        $total_alojamiento = 0;
        $selected = null;
        if (Session::get('seleccion_paquete')!== null) {
          $selected = Session::get('seleccion_paquete');
        }

        if ($selected != null) {
          foreach ($selected as $value) {
            $acronimo = $value["acronimo"];
            $total_alojamiento = $value["precio"] + $total_alojamiento;
          }
          $hab_obj = new Habitacion();
          $this->page["seleccion"] = Session::get('seleccion_paquete');
          $this->page["total_alojamiento"] = $this->cambiarFormatoPrecio($total_alojamiento, $propiedades["moneda"])." ".$acronimo;
          $this->page["total_aloj_raw"] = $total_alojamiento;
        }

        return [
            '#seleccion-content' => $this->renderPartial('@lista_seleccion.htm')
        ];
      }
    }else{
      Flash::error("Ha ocurrido algo inesperado, vuelve a consultar la disponibilidad para reservar");
    }
    //Session::flash('seleccion', $array);
  }

  public function onDeleteSeleccion(){
    $data = post();
    $session = Session::all();
    if (isset($session["seleccion_paquete"]) && $session["seleccion_paquete"]!=null) {
      $array_actual = $session["seleccion_paquete"];
      //$key = array_search($data["index"], $array);
      unset($array_actual[$data["index"]]);
      $nuevo_array = array();
      foreach ($array_actual as $key => $value) {
        array_push($nuevo_array,$value);
      }
      Session::put('seleccion_paquete', $nuevo_array);
      $total_alojamiento = 0;
      $acronimo="";
      foreach (Session::get('seleccion_paquete') as $value) {

        if (isset($value["acronimo"])) {
          $acronimo = $value["acronimo"];
        }
        $total_alojamiento = $value["precio"] + $total_alojamiento;
      }

      $hab_obj = new Habitacion();
      $this->page["total_alojamiento"] = $hab_obj->cambiarFormatoPrecio($total_alojamiento, $session["propiedades"]["moneda"])." ".$acronimo;
      $this->page["seleccion"] = Session::get('seleccion_paquete');
      //Session::put('seleccion', (
    }
  }

  public function getMonedaOptions(){
    //
     //Moneda::select("moneda", "id")-isActiva()->get()->toArray();
    $moneda = Db::table('hesperiaplugins_hoteles_moneda as a')->where("a.ind_activo", 1)
    ->lists('a.moneda', 'a.id');
    return $moneda;
  }

  public function onIrApagar(){
    $data = post();
    $array_ids = array();
    $session = Session::all();
    $ups_seleccionados = array();

    //echo $this->paquete->id;
    //exit();

    if (isset($session["seleccion_paquete"]) && count($session["seleccion_paquete"])>0) {
      foreach ($data as $key => $value) {
        $aux = explode("-", $key);
        $ups_seleccionados[$aux[1]] = $value;
        array_push($array_ids, $aux[1]);
      }

      if (count($ups_seleccionados)>0) {
        $paquete = PaqueteModel::where("slug", $this->param('slug'))->first();
        $flag_obligatorio = true;

        //var_dump(count($paquete->upsellings));
        //exit();
        foreach ($paquete->upsellings as $key => $value) {
          if ($value->pivot->obligatorio == 1){
              if (!isset($ups_seleccionados["$value->id"]) || $ups_seleccionados["$value->id"]<1) {
                $flag_obligatorio=false;
              }
          }
        }

        if ($flag_obligatorio) {
          //Flash::success("todos los ogligatorios seleccionados");
          Session::put("seleccion", $session["seleccion_paquete"]);
          Session::put("seleccion_upsellings_paq", $ups_seleccionados);
          Session::put("paquete_id", $paquete->id);
          //var_dump($ups_seleccionados);
          return Redirect::to("pago-de-reserva");
        }else{
          Flash::error("Por cada servicio adicional obligatorio debes elegir al menos uno");
        }

      }else{
          Flash::error("Debes seleccionar al menos un servicio adicional");
      }

    }else{
      Flash::error("Debes seleccionar al menos una habitación");
    }


  }
}
