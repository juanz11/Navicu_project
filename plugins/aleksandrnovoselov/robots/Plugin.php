<?php

namespace AleksandrNovoselov\Robots;

use System\Classes\PluginBase;
use System\Classes\SettingsManager;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
    }

    public function registerSettings()
    {
        return [
            'alupoint-calc' => [
                'label'       => 'aleksandrnovoselov.robots::lang.plugin.settings.title',
                'description' => 'aleksandrnovoselov.robots::lang.plugin.settings.description',
                'category'    => SettingsManager::CATEGORY_MISC,
                'icon'        => 'icon-file-text-o',
                'class'       => Models\Robots::class,
                'keywords'    => 'robots txt',
                'permissions' => ['aleksandr_novoselov.robots'],
            ],
        ];
    }

}
