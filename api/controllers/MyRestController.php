<?php

namespace api\controllers;

use yii\rest\Controller;

class MyRestController extends Controller
{
    public static function allowedDomains()
    {
        return [
            //'*',
            'http://127.0.0.1:3000',
            'http://localhost:3000',
            'http://bemawhoob.com',
            'https://bemawhoob.com',
            'http://localhost',
            'https://stageacademy.bemawhoob.com',
            'https://demoacademy.bemawhoob.com',
            'http://academy.mawhoob.localhost',
            'https://academy.bemawhoob.com',
            'http://academyadmin.mawhoob.localhost'


        ];
    }


    public function  behaviors()
    {
        $behaviors = parent::behaviors();
        // remove authentication filter if there is one
        unset($behaviors['authenticator']);

        // Add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => self::allowedDomains(),
                'Access-Control-Request-Method' => ['*'],
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];
        return $behaviors;
    }

    public function beforeAction($action)
    {
        if (isset($_REQUEST['lang']) && $_REQUEST['lang'] == 'ar') {
            \Yii::$app->language = 'ar';
        }
        return parent::beforeAction($action);
    }
}
