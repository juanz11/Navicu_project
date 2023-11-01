<?php

namespace AleksandrNovoselov\Robots\Models;

use AleksandrNovoselov\Robots\Classes\BotsAndCrawlers;
use October\Rain\Database\Model;
use October\Rain\Database\Traits\Validation;
use System\Behaviors\SettingsModel;

/**
 * @property int $id
 * @property bool $enabled
 * @property array $robots
 *
 * @mixin \October\Rain\Database\Model
 */
class Robots extends Model
{
    use Validation;

    /**
     * @var array Implement settings model
     */
    public $implement = [
        SettingsModel::class,
    ];

    /**
     * @var array Required permissions to access settings
     */
    public $requiredPermissions = [
        'aleksandr_novoselov.robots',
    ];

    /**
     * @var string Define the unique settings code
     */
    public $settingsCode = 'aleksandr_novoselov_robots';

    /**
     * @var string Define the fields for these settings
     */
    public $settingsFields = 'fields.yaml';

    /**
     * @var array Validation rules
     */
    public array $rules = [
        'robots' => 'nullable|array',
        'robots.*.userAgent' => 'required|string',
        'robots.*.action' => 'required|string|in:Allow,Disallow',
        'robots.*.path' => 'required|string|starts_with:/',

    ];

    public array $customMessages = [];

    public array $attributeNames = [];

    protected $guarded = ['*'];

    private function useDefaults()
    {
        if (!isset($this->robots) || !is_array($this->robots) || !count($this->robots)) {
            $this->robots = [[
                'userAgent' => '*',
                'action'    => 'Disallow',
                'path'      => \Str::start(\Config::get('backend.uri'), '/'),
            ]];
        }
    }

    public function initSettingsData()
    {
        $this->useDefaults();
    }

    public function afterFetch()
    {
        $this->useDefaults();
    }

    //<editor-fold desc="Options">
    public function getActionOptions()
    {
        return \Lang::get('aleksandrnovoselov.robots::lang.models.robots.action.options');
    }

    public function getUserAgentOptions()
    {
        return ['*' => 'aleksandrnovoselov.robots::lang.models.robots.user-agent.placeholder']
               + BotsAndCrawlers::getUserAgentsForRobots();
    }
    //</editor-fold>

}
