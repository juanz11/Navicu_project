<?php namespace HesperiaPlugins\Hoteles\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use HesperiaPlugins\Hoteles\Models\Reservacion as Reservacion;
use backend\Models\User;
use HesperiaPlugins\Hoteles\Models\Cotizacion as CotizacionModel;
use Input;
use Validator;
use ValidationException;
use Carbon\Carbon;
use DB;

class Cotizacion extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController','Backend\Behaviors\ReorderController'];

    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $listConfig = [
      'template' => 'config_list.yaml',
      'agentes' => 'config_agentes_list.yaml'
    ];
    protected $agente_id;
    protected $moneda_id;
    protected $hotel_id;
    protected $desde;
    protected $hasta;

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('HesperiaPlugins.Hoteles', 'main-menu-item3', 'side-menu-item5');
        $this->addJs('/plugins/hesperiaplugins/hoteles/assets/js/link_detalle_backend.js');
    }

    public function getStats(){
    	$total = Reservacion::where("origen_id", "=", 2)->count();
    	$confirmadas = Reservacion::where("origen_id", "=", 2)->where("status_id", "=", 1)->count();
    	$stats = ["total" => $total, "confirmadas" => $confirmadas];
    	return $stats;
    }

   /* public function agentes(){
        $config = $this->makeList($this->agentes);
        //$config->model = new \Backend\Models\User;
        $config->model = new \Backend\Models\User;
        $widget = $this->makeLists('Backend\Widgets\Lists', $config);
        $widget = $this->makeLists($this->agentes);
    }*/

    public function revenue_agentes(){
        $config = $this->makeConfig('$/hesperiaplugins/hoteles/models/cotizacion/agentes_fields.yaml');
        $config->model = new \HesperiaPlugins\Hoteles\Models\Cotizacion;
        $widget = $this->makeFormWidget('Backend\Widgets\Form', $config);
        $this->vars['widget'] = $widget;
        $this->pageTitle = 'Lista de Agentes';
    }

    public function onBuscarAgentes(){
        $cotizacion = new CotizacionModel();
        $form = Input::get();
        $messages = [
          'required' => ' :attribute es requerido.',
          'after' => ' campo hasta debe ser despues del campo desde',
      ];
      $validator = Validator::make(
          $form,
          [
            'desde' => 'required',
            'hasta' => 'required|after:desde',
          ],
          $messages
      );

      if ($validator->fails()) {
       throw new ValidationException($validator);
      }

      $resultado = $cotizacion->buscarAgentes($form);
      
      return [
          '#partialContents' => $this->makePartial('tabla_agentes',['resultado_agentes' => $resultado, 'dates' => $form])
      ];
    }

    public function detalle_agente($id = null, $moneda = null, $hotel = null, $inicio = null, $fin = null){
      
      $this->agente_id = $id; //VARIABLE QUE USO EN EL METODO listExtendQueryBefore
      $this->moneda_id = $moneda;
      $this->hotel_id = $hotel;

      if ($inicio != null || $fin != null) {

        $this->desde = new Carbon($inicio);
        $this->hasta = new Carbon($fin);
      }
      
      $this->vars['agente_id'] = $id;
      $this->pageTitle = 'Detalle de Agente';
      $this->asExtension('ListController')->index();
    }

    public function listExtendQueryBefore($query){
      
      if ($this->agente_id) {

        if($this->moneda_id){

          $query->leftJoin("hesperiaplugins_hoteles_reservacion as r", "r.id","cotizable_id")
          ->leftJoin("hesperiaplugins_hoteles_compra as cp", "cp.id","cotizable_id")
          ->whereRaw("(r.moneda_id =".$this->moneda_id." or cp.moneda_id = ".$this->moneda_id.")");
        }

        if($this->hotel_id){

          $query->leftJoin(DB::raw(
            "(select distinct upg.upgradable_id as upgradable, ups.hotel_id as hotel_id
                from hesperiaplugins_hoteles_upgrades as upg
                inner join hesperiaplugins_hoteles_upselling as ups on upg.upselling_id = ups.id
              ) as us"), "us.upgradable","cp.id")
            ->whereRaw("(r.hotel_id =".$this->hotel_id." or us.hotel_id = ".$this->hotel_id.")");
        }

        $query->where('usable_id',$this->agente_id);
      }
    }

    public function listFilterExtendScopes($filter) {

      if($this->desde != null && $this->hasta != null) {

        $filter->setScopeValue('fecha',
          ["0" => new Carbon($this->desde),
           "1" => new Carbon($this->hasta)]);
      } else {

        $filter->setScopeValue('fecha', null);
      }
    }
}