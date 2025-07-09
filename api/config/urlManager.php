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
                    /////////////////////////////
                     'GET indexcourse' => 'index-course',
                     'POST  createcourse' =>  'create-course',
                     'DELETE deletecourse/<id:\d+>'=>'delete-course',
                     'PUT updatecourse/<id:\d+>'=>'update-course',
                    ////////////////////////////////////// 
                     'GET  indexteacher' =>  'index-teacher',
                     'POST  createteacher' =>  'create-teacher',
                     'DELETE deleteteacher/<id:\d+>'=>'delete-teacher',
                     'PUT updateteacher/<id:\d+>'=>'update-teacher',
                     /////////////////////////////////////////////////
                     'GET  indexstudent' =>  'index-student',
                     'POST  createstudent' =>  'create-student',
                     'DELETE deletestudent/<id:\d+>'=>'delete-student',
                     'PUT updatestudent/<id:\d+>'=>'update-student',
                     
                ],
            ],
        ],

    ];