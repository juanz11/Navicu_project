<?php

return [
    'plugin' => [
        'description' => 'Генератор robots.txt',
        'settings'    => [
            'title'       => 'Robots',
            'description' => 'Настройка robots.txt',
        ],
    ],
    'models' => [
        'robots' => [
            'enabled'    => 'Активность',
            'link'       => 'Открыть robot.txt',
            'user-agent' => [
                'placeholder' => 'Все',
            ],
            'action'     => [
                'label'       => 'Действие',
                'placeholder' => 'Запретить',
                'options'     => [
                    'Allow'    => 'Разрешить',
                    'Disallow' => 'Запретить',
                ],
            ],
            'path'       => 'Путь',
        ],
    ],
];
