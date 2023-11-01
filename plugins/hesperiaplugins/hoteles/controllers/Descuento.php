<?php namespace HesperiaPlugins\Hoteles\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Descuento extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController',
  'Backend\Behaviors\ReorderController','Backend\Behaviors\RelationController'];

    public $listConfig = [
    'hoteles' => 'config_list_hoteles.yaml',
    'descuentos' => 'config_list.yaml'
    ];
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $relationConfig = 'config_relation.yaml';

    protected $hotel;

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('HesperiaPlugins.Hoteles', 'main-menu-item2', 'side-menu-item3');
    }

    public function index($hotel=null){
      $this->hotel =  \hesperiaplugins\Hoteles\Models\Hotel::find($hotel);
      $this->vars['hotel'] = $hotel;
      $this->asExtension('ListController')->index();
    }

    public function listExtendQuery($query){
   // Extend the list query to filter by the user id
    if ($this->hotel)
        $query->where('id', $this->hotel->id);
    }
    public function list_hoteles(){
      $this->asExtension('ListController')->index();
    }
}
