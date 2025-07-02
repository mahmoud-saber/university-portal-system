<?php

return [
    // 'class' => 'yii\rest\UrlManager',
    'enablePrettyUrl' => true,
    'enableStrictParsing' => true,
    'showScriptName' => false,

    'rules' =>
    \yii\helpers\ArrayHelper::merge(
        [
            ['pattern' => 'settings', 'route' => 'site/settings'],
            ['pattern' => 'terms', 'route' => 'site/terms'],
            ['pattern' => 'support-team', 'route' => 'site/support-team'],
            ['pattern' => '/user/login', 'route' => 'user/login'],
            ['pattern' => '/user/signup', 'route' => 'user/signup'],
            ['pattern' => '/user/verify', 'route' => 'user/verify'],
            ['pattern' => '/user/resend-verify-email-code', 'route' => 'user/resend-verify-email-code'],
            ['pattern' => '/user/request-reset-password', 'route' => 'user/request-reset-password'],
            ['pattern' => '/user/verify-reset-password-token', 'route' => 'user/verify-reset-password-token'],
            ['pattern' => '/user/reset-password', 'route' => 'user/reset-password'],
            [
                'class' => 'yii\rest\UrlRule', 'controller' => 'contactus', 'only' => ['create', 'options'], 'extraPatterns' => [
                    'POST ' => 'create',
                ], 'pluralize' => false,
            ],

            /**************************  User Signup   ********************************************/
            [
                'class' => 'yii\rest\UrlRule',
                'controller' => 'user',
                'only' => ['login',  'signup', 'verify', 'request-reset-password', 'verify-reset-password-token', 'reset-password', 'options'],
                'extraPatterns' => [
                    'POST signup' => 'signup',
                    'POST login' => 'login',
                    'POST verify' => 'verify',
                    'POST request-reset-password' => 'request-reset-password',
                    'POST verify-reset-password-token' => 'verify-reset-password-token',
                    'POST reset-password' => 'reset-password',
                ], 'pluralize' => false,
            ],

            [
                'class' => 'yii\rest\UrlRule',
                'controller' => 'profile',
                'only' => ['index', 'update', 'complete-profile-data', 'change-password', 'options','current-subscriptions','previous-subscriptions'
                ,'subscription-info'],
                'extraPatterns' => [
                    'GET /' => 'index',
                    'PUT /' => 'update',
                    'PUT complete-profile-data' => 'complete-profile-data',
                    'PUT change-password' => 'change-password',
                    'GET current-subscriptions' => 'current-subscriptions',
                    'GET previous-subscriptions' => 'previous-subscriptions',
                    'GET subscription-info' => 'subscription-info',

                ],
                'pluralize' => false,
            ],




        ],
        require(__DIR__ . '/urls/_AcademyadminUrlManager.php'),

    ),

];
