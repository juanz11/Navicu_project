<?php namespace HesperiaPlugins\Hoteles\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use HesperiaPlugins\Hoteles\Models\Paquete as PaqueteModel;
use Input;
use Validator;
use ValidationException;
use DB;
use Carbon\Carbon;
use Model;
use Log;
use Redirect;


class Paquete extends Controller
{
    public $implement = [
    	'Backend\Behaviors\ListController',
    	'Backend\Behaviors\FormController',
    	'Backend\Behaviors\ReorderController',    
		'Backend\Behaviors\RelationController'];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('HesperiaPlugins.Hoteles', 'main-menu-item3', 'side-menu-item');
        $this->addJs('/plugins/hesperiaplugins/hoteles/assets/js/link_detalle_backend.js');
    }

    public function reporte_paquetes(){

      $config = $this->makeConfig('$/hesperiaplugins/hoteles/models/paquete/paquete_fields.yaml');
      $config->model = new \HesperiaPlugins\Hoteles\Models\Paquete;
      $widget = $this->makeFormWidget('Backend\Widgets\Form', $config);
      $this->vars['widget'] = $widget;
      $this->pageTitle = 'Reporte de Paquetes';
    }

    public function onReportePaquetes(){
      $form = Input::get();
      $validator = Validator::make(
          $form,['desde' => 'required','hasta' => 'required']
      );

      if ($validator->fails()) throw new ValidationException($validator);
      
      $paquetes = PaqueteModel::whereHas('reservaciones', function($query) use($form){
      $desde = new Carbon($form["desde"]);
      $hasta = new Carbon($form["hasta"]);
       
      $query->whereBetween(DB::raw('DATE(created_at)'),[$desde->toDateString(), $hasta->toDateString()])
              ->where('moneda_id',$form["moneda"]); })->get();
      return [
        '#partialContents' => $this->makePartial('tabla_paquetes', ['resultadoPaquetes' => $paquetes, 'dates' => $form])
      ];
    }

    public function listInjectRowClass($record, $definition = null) {

        if ($record->ind_activo == 0) {
            return 'safe disabled';
        }
    }

    public function onDuplicate() {
        $checked_items_ids = input('checked');
        
           foreach ($checked_items_ids as $id) {
        //    $disponible = DB::table("hesperiaplugins_hoteles_hotel");
        //    ->where("a.id", "=", $this->$id);
           $hotel=Paquetemodel::find($id);
           $ultimo= DB::table("hesperiaplugins_hoteles_paquete")->max('id') + 1;
          // log::info($ultimo);
  
          $clone = $hotel->replicate();
          $clone->id = $ultimo.$clone->id;
          $clone->slug = now()->timestamp."_".$clone->slug;
          //log::info($clone);
          $clone->save();
        
           }
           return Redirect::refresh();

      
     
    }
    
}