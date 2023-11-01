<?php namespace HesperiaPlugins\Hoteles\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class TipoAtributo extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('HesperiaPlugins.Hoteles', 'main-menu-item', 'side-menu-item2');
    }
}
