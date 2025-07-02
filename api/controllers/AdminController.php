<?php

/**
 * Created by PhpStorm.
 * User: engamer
 * Date: 04/02/19
 * Time: 10:03 ص
 */

namespace api\controllers;

use Yii;
use Exception;
use common\models\User;

class AdminController extends MyActiveController
{


    public function beforeAction($action)
    {
        $actions = parent::beforeAction($action);
        if (!Yii::$app->user->identity->myRole(User::ROLE_ADMINISTRATOR) and !Yii::$app->user->identity->myRole(User::ROLE_MANAGER)) {
            throw new Exception("Error Processing Request", 1);
        }
        return $actions;
    }
}
