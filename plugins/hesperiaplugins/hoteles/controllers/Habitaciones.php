<?php namespace HesperiaPlugins\Hoteles\Controllers;
use HesperiaPlugins\Hoteles\Models\Habitacion;
use DB;

use Backend\Classes\Controller;
use BackendMenu;
use Redirect;


class Habitaciones extends Controller
{
    public $implement = ['Backend\Behaviors\ListController',
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
        BackendMenu::setContext('HesperiaPlugins.Hoteles', 'main-menu-item', 'side-menu-item');
    }
    public function onDuplicate() {
        $checked_items_ids = input('checked');
        
           foreach ($checked_items_ids as $id) {
        //    $disponible = DB::table("hesperiaplugins_hoteles_hotel");
        //    ->where("a.id", "=", $this->$id);
           $hotel=Habitacion::find($id);
           $ultimo= DB::table("hesperiaplugins_hoteles_habitaciones")->max('id') + 1;
          // log::info($ultimo);
  
          $clone = $hotel->replicate();
          $clone->id = $ultimo.$clone->id;
      
          //log::info($clone);
          $clone->save();
        
           }
        return Redirect::refresh();

      
     
        }
}