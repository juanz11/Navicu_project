<?php

return [
    'plugin'      => [
        'name'        => 'Robots',
        'description' => 'Robots.txt generator',
        'settings'    => [
            'title'       => 'Robots',
            'description' => 'Configure robots.txt',
        ],
    ],
    'models'      => [
        'robots' => [
            'enabled'    => 'Enabled',
            'title'      => 'Robots',
            'link'       => 'Goto robot.txt',
            'user-agent' => [
                'label'       => 'User-Agent',
                'placeholder' => 'All',
            ],
            'action'     => [
                'label'       => 'Action',
                'placeholder' => 'Disallow',
                'options'     => [
                    'Allow'    => 'Allow',
                    'Disallow' => 'Disallow',
                ],
            ],
            'path'       => 'Path',
        ],
    ],
    'premissions' => [
        'robots' => 'AN Robots',
    ],
];
