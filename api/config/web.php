<?php
$config = [
    'homeUrl' => Yii::getAlias('@apiUrl'),
    'controllerNamespace' => 'api\controllers',
    'defaultRoute' => 'site/index',
    'bootstrap' => ['maintenance'],
    'modules' => [
       // 'v1' => \api\modules\v1\Module::class
    ],
    'timeZone' =>'Africa/Cairo',

    'components' => [
        'errorHandler' => [
            'errorAction' => 'site/error'
        ],
        'maintenance' => [
            'class' => common\components\maintenance\Maintenance::class,
            'enabled' => function ($app) {
                if (env('APP_MAINTENANCE') === '1') {
                    return true;
                }
                return $app->keyStorage->get('frontend.maintenance') === 'enabled';
            }
        ],
        'request' => [
            'enableCookieValidation' => false,
            'enableCsrfValidation' => false,
            'enableCsrfCookie' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'mailer' => [
            'class' => yii\swiftmailer\Mailer::class,
            'viewPath' => Yii::getAlias('@common').'/mail',
            'messageConfig' => [
                'charset' => 'UTF-8',
                'from' => env('ADMIN_EMAIL'),
            ]
        ],


        'user' => [
             'class' => yii\web\User::class,
            'identityClass' => common\models\User::class,
            'enableSession' => false,
            'loginUrl' =>null, // ['/user/sign-in/login'],
            'enableAutoLogin' =>false, // true,
        ]
    ]
];

return $config;
