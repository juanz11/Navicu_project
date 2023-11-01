<?php namespace HesperiaPlugins\Hoteles\Components;

use Cms\Classes\ComponentBase;
use HesperiaPlugins\Hoteles\Models\Upselling as UpsellingModel;
use HesperiaPlugins\Hoteles\Models\Moneda;
use Flash;
use Session;
use Redirect;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class Upselling extends ComponentBase{

  public $upselling;
  public $monedas;
  public $fecha_inicial_calendario;
  public $fecha_final_calendario;
  public $fechas_no_disponibles;
  public $implement = [
        'HesperiaPlugins.Hoteles.Behaviors.UtilityFunctions'
    ];

  public function defineProperties(){
    return [
      'slug' => [
        'title' => 'slug',
        'description' => 'slug desde la url',
        'type' => 'string',
        'default' => '{{ :slug }}'],
      'destino' => [
        'title' => 'Destino',
        'description' => 'Pagina destino para pagar',
        'type' => 'string',
        'default' => '/'
        ]
    ];
  }

  public function componentDetails(){
    return [
      'name'=> 'Upselling',
      'description' => 'vista individual de un upselling'
    ];
  }

  public function onRun(){
    $this->addJs('assets/js/upselling.js');
    $this->addJs('assets/js/bootstrap-datepicker.js');
    $this->addJs('assets/js/bootstrap-datepicker.es.js');
    $this->addCss('assets/css/bootstrap-datepicker.css');

    if (empty($this->upselling)) {
        $this->monedas = $this->page['monedas'] = $this->cargarMonedas();
        $this->upselling = $this->page['upselling'] = $this->cargarUpselling();
    }
  }
  /*public function onRender(){

  }*/

  public function cargarUpselling(){
    $hoy = new Carbon;
    $propiedades["checkin"] = $hoy;
    $propiedades["checkout"] = $hoy;
    $propiedades["moneda"] = 0; // NO SE PARA QUE HICE ESTO
    if (count($this->monedas)>0) {
      $propiedades["moneda"] = $this->monedas[0]["id"];
    }

    $slug = $this->property('slug');

    $upselling = new UpsellingModel;

    $upselling = $upselling->isClassExtendedWith('RainLab.Translate.Behaviors.TranslatableModel')
        ? $upselling->transWhere('slug', $slug)
        : $upselling->where('slug', $slug);

    $upselling = $upselling->first();
    if ($upselling != null) {
      $propiedades["hotel"] = $upselling->hotel_id;
      //$upselling->precios = $upselling->isDisponible($propiedades);
      $fechas_disp = $upselling->getFechasDisponibles();
      //trace_log($fechas_disp);
      //$upselling->fechas_disponibles = $fechas_disp;

      $first = new Carbon (current($fechas_disp));
      $last = new Carbon (end($fechas_disp));

      $this->fecha_inicial_calendario = $first->format("d-m-Y");
      $this->fecha_final_calendario = $last->format("d-m-Y");

      $this->setFechasNoDisponibles($fechas_disp);
      
      $this->page["propiedades"]= $propiedades;
    }else{
      echo "NO TENGO UPSELLING";
    }

    return $upselling;
  }

  public function cargarMonedas(){
    $monedas = Moneda::select("id", "moneda", "acronimo")->where("ind_activo", 1)->orderBy("id", "DESC")->get();
    return $monedas;
  }

  public function onIrApagar(){
    $data = post();
  
    $fechaDisfrute = new Carbon($data["fecha_disfrute"]);
    //var_dump($data);
    if ($upselling = UpsellingModel::find($data["id-ups"])) {
      $propiedades["checkin"] = $fechaDisfrute;
      $propiedades["checkout"] = $fechaDisfrute;
      $propiedades["moneda"] = $data["moneda"];
      $propiedades["hotel"] = $upselling->hotel_id;
      
      $precios = $upselling->getPreciosMultiMoneda($propiedades);
      
      $disponible = false;
      foreach ($precios as  $precio) {
        if ($precio["moneda_id"] == $data["moneda"] && $precio["precio"] >0 ) {
          $disponible = true;
          $data["precio"] = $precio["precio"]*$data["cantidad"];
        }
      }
      if ($disponible) {
        $compra =array($data);
        Session::put("compra_individual", $compra);
        return Redirect::to($this->property("destino"));
      }else{
        Flash::error("Este producto no se encuentra disponile");
      }
    }
  }

  public function onConsultarDisponibilidad(){
    $data = post();
    try {
      $fecha_disfrute = new Carbon($data["fecha"]);
      $upselling = UpsellingModel::find($data["id"]);

      $propiedades["checkin"] = $fecha_disfrute;
      $propiedades["checkout"] = $fecha_disfrute;
      $propiedades["moneda"] = $data["moneda"];

      $upselling->precios = $upselling->isDisponible($propiedades);

      return [
          '#contenedor-precios' => $this->renderPartial('@precios.htm', [
            'propiedades' => $propiedades,
            'upselling' => $upselling
          ])
      ];
      //trace_log($upselling->precios);
    } catch (\Exception $e) {
      trace_log($e->getMessage());
    }

    //trace_log($data);
    //return true;
  }

  public function setFechasNoDisponibles($fechas_disp){
    //trace_log($fechas_disp);
    if(isset($fechas_disp[0])){
      $first = $fechas_disp[0];
      $last = end($fechas_disp);

      //trace_log($fechas_disp);

      $period = CarbonPeriod::create($first, $last);
      //trace_log($period);
      foreach ($period as $date) {
        if (!in_array($date->format('Y-m-d'), $fechas_disp)) {
          $this->fechas_no_disponibles .= $date->format('d-m-Y').",";
        }
      }
    }
    

    //$this->fechas_no_disponibles = $period->toArray();
  }
}
