<?php namespace HesperiaPlugins\Hoteles\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class PrecioFecha extends Controller
{
    public $implement = ['Backend\Behaviors\ListController',
    'Backend\Behaviors\FormController',
    'Backend\Behaviors\ReorderController',
    'Backend.Behaviors.RelationController'];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $relationConfig = 'config_relation.yaml';
    protected $idfecha;
    public function __construct()
    {
      parent::__construct();
      BackendMenu::setContext('HesperiaPlugins.Hoteles', 'main-menu-item');
    }

    /*public function index($idfecha=null){
      $this->idfecha = $idfecha;
      $this->asExtension('ListController')->index();
    }
    public function listExtendQuery($query){
      if ($this->idfecha)
          $query->where('fecha_id', $this->idfecha);

    }*/
}
