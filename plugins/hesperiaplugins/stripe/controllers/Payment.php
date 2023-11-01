<?php namespace hesperiaplugins\Stripe\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Payment extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'gestion_general_stripe' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('hesperiaplugins.Stripe', 'main-menu-item');
    }
}
