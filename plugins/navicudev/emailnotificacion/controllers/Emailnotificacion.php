<?php namespace navicudev\Emailnotificacion\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Emailnotificacion extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('navicudev.Emailnotificacion', 'main-menu-item');
    }
}
