<?php namespace HesperiaPlugins\Hoteles\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Db;
class Hotel extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController',
    'Backend\Behaviors\ReorderController','Backend\Behaviors\RelationController',];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('HesperiaPlugins.Hoteles', 'main-menu-item');
    }
    
    public function relationExtendPivotWidget($widget, $field, $model){
            
        $data = post();
        
        if ($field == 'regimenes') {
            if (isset($data["Regimen"])) {

                if ($data["Regimen"]["pivot"]["defecto"] == 1 ) {
                    
                    $hotelId = $model->id;
                    $foreignKey = (isset($data["foreign_id"]) ? $data["foreign_id"] : $data["manage_id"]);
                    
                    Db::table('hesperiaplugins_hoteles_hotel_regimen')->where('hotel_id', $hotelId)
                    ->where("regimen_id", "!=", $foreignKey)
                    ->update(['defecto' => 0]);

                }
                
              }
        }
    }
}
