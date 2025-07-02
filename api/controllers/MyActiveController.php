<?php

/**
 * Created by PhpStorm.
 * User: engamer
 * Date: 04/02/19
 * Time: 10:03 ص
 */

namespace api\controllers;

use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\Controller;


class MyActiveController extends Controller
{

    public $defaultPageSize = 12;
    public $pageSize = 12;
    public $pageSizeLimit = [1, 200];

    public static function allowedDomains()
    {
        return [
           // '*',
            'http://127.0.0.1:3000',
            'http://localhost:3000',
            'http://bemawhoob.com',
            'https://bemawhoob.com',
            'http://localhost',
            'https://stageacademy.bemawhoob.com',
            'https://demoacademy.bemawhoob.com',
            'https://academy.bemawhoob.com',
            'http://academy.mawhoob.localhost',
            'http://academyadmin.mawhoob.localhost'

        ];
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['view']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        return $actions;
    }

    public function  behaviors()
    {
        $behaviors = parent::behaviors();
        // remove authentication filter if there is one
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => self::allowedDomains(),
                'Access-Control-Request-Method' => ['*'],
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];

        // Put in a bearer auth authentication filter
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                HttpBearerAuth::class,
            ]
        ];
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];
        return $behaviors;
    }


    public function beforeAction($action)
    {
        if (isset($_REQUEST['lang']) && $_REQUEST['lang'] == 'ar') {
            \Yii::$app->language = 'ar';
        }
        \Yii::$app->language = 'ar';
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $query = $this->modelClass::find();
        $activeData = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => $this->defaultPageSize, // to set default count items on one page
                'pageSize' => $this->pageSize, //to set count items on one page, if not set will be set from defaultPageSize
                'pageSizeLimit' => $this->pageSizeLimit, //to set range for pageSize

            ],
        ]);
        return $activeData;
    }

    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];
}
