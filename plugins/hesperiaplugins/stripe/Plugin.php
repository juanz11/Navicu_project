<?php namespace hesperiaplugins\Stripe;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
      return [
        'HesperiaPlugins\Stripe\Components\Form' => 'form',
      ];
    }

    public function registerSettings()
    {
        return [
            'config' => [
                'label'       => 'Stripe',
                'icon'        => 'icon-bar-chart-o',
                'description' => 'Stripe configuration',
                'class'       => 'HesperiaPlugins\Stripe\Models\Settings',
                'order'       => 600,
                'permissions' => ["gestion_general_stripe"]
            ]
        ];
    }


    
}
