<?php namespace HesperiaPlugins\Hoteles\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class FechaCalendario extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController',        'Backend\Behaviors\ReorderController'    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    protected $calendario;
    protected $upselling;
    
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('HesperiaPlugins.Hoteles', 'main-menu-item3', 'side-menu-item2');
    }

    public function index($calendario=null, $upselling=null){
      $this->calendario = $calendario;
      $this->upselling = $upselling;
      $this->asExtension('ListController')->index();
    //
    // Do any custom code here
    //

    // Call the ListController behavior index() method
    //$this->asExtension('ListController')->index();
    }


    public function listExtendQuery($query){
      if ($this->calendario) {
        $query->where("calendario_id", $this->calendario);
      }

    }
}
