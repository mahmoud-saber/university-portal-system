<?php

use yii\rest\UrlRule;

return [
        'enablePrettyUrl' => true,
        'showScriptName' => false,
        'enableStrictParsing' => true,

        'rules' => [
            [
                'class' => UrlRule::class,
                'controller' => ['user'],
                'pluralize' => false,
                'extraPatterns' => [
                    'POST signup' => 'signup',
                    'POST login' => 'login',
                    'GET profile' => 'profile',
                    'DELETE logout' => 'logout',


                ],
            ],
        ],

    ];