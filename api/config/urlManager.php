<?php

use yii\rest\UrlRule;

return [
        'enablePrettyUrl' => true,
        'showScriptName' => false,
        'enableStrictParsing' => true,

        'rules' => [
            [
                'class' => UrlRule::class,
                'controller' => ['user','teacher'],
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
                     //////////////////////////////////////////////// teacher
                     'GET index' =>'index',
                     'GET view/<id:\d+>' =>'view',
                     'PUT update/<id:\d+>'=>'update',
                     'DELETE delete/<id:\d+>'=>'delete',
                     ////////////////////////////////////////doc
                     'GET indexdocument'=>'index-document', 
                     'POST  createdocument' =>  'create-document',
                     'PUT  updatedocument/<id:\d+>' =>  'update-document',
                     'DELETE  deletedocument/<id:\d+>' =>  'delete-document',
                                         
                ],
            ],
        ],

    ];