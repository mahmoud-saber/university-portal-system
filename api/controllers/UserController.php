<?php

namespace api\controllers;

use Yii;
use yii\web\Response;
use common\models\User;
use yii\rest\Controller;

class UserController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // ضبط التنسيق ليكون JSON
        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;

        return $behaviors;
    }

    public function actionIndex()
    {
        $users = User::find()->all();

        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'id' => $user->id,
                'username' => $user->username,
                'Email' => $user->email,
                'role' => $user->role,
                'Joined' => Yii::$app->formatter->asDate($user->created_at),
            ];
        }

        return [
            'status' => 'success',
            'message' => 'User list',
            'data' => $data
        ];
    }
}