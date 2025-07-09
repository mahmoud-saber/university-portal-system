<?php

namespace api\controllers;

use Yii;
use yii\web\Response;
use common\models\User;
use yii\rest\Controller;
use yii\filters\AccessControl;
use api\resources\UserResource;
use yii\web\ForbiddenHttpException;
use yii\web\UnauthorizedHttpException;

class UserController extends Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;

        // مصادقة التوكن Bearer Token
        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'only' => ['profile', 'logout'],
        ];

        // صلاحيات الوصول
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => ['signup', 'login', 'profile', 'logout'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['signup', 'login'],
                    'roles' => ['?'],
                ],
                [
                    'allow' => true,
                    'actions' => ['profile', 'logout'],
                    'roles' => ['@'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $publicActions = ['signup', 'login'];
        if (in_array($action->id, $publicActions)) {
            return true;
        }

        if (Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException('You are not allowed to access this resource.');
        }
        return true;
    }

/////////////////////////siginup
    public function actionSignup()
    {
        $request = Yii::$app->request;
        $model = new User();
        $model->load($request->post(), '');

        if ($model->validate()) {
            $model->plainPassword = $model->plainPassword ?? Yii::$app->security->generateRandomString(8);

            if ($model->save()) {
                return [
                    'status' => 'success',
                    'message' => 'User registered successfully.',
                    'access_token' => $model->access_token,
                ];
            }
        }

        return [
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $model->getErrors(),
        ];
    }


    public function actionLogin()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;
        $username = $request->post('username');
        $password = $request->post('password');

        if (empty($username) || empty($password)) {
            return $this->asJson([
                'success' => false,
                'message' => 'Username and password are required.',
            ]);
        }

        $user = User::findByUsername($username);

        if (!$user || !$user->validatePassword($password)) {
            return $this->asJson([
                'success' => false,
                'message' => 'Invalid username or password.',
            ]);
        }

        if ($user->status != User::STATUS_ACTIVE) {
            return $this->asJson([
                'success' => false,
                'message' => 'Your account is not active.',
            ]);
        }

        // Generate new token
        $user->generateAccessToken();

        if (!$user->save(false)) {
            return $this->asJson([
                'success' => false,
                'message' => 'Failed to save access token.',
            ]);
        }

        return $this->asJson([
           UserResource::Arraylogin($user)
            ]);
    }

    public function actionProfile()
    {

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user = Yii::$app->user->identity;

        if (!$user) {
            throw new \yii\web\UnauthorizedHttpException('Unauthorized access.');
        }

        return UserResource::toArray($user);
    }
    ////////////////////////log out 

    public function actionLogout()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $identity = Yii::$app->user->identity;

        if (!$identity) {
            throw new UnauthorizedHttpException('Unauthorized access.');
        }

        $user = User::findOne($identity->id);

        if (!$user) {
            throw new UnauthorizedHttpException('User not found.');
        }

        $user->access_token = null;

        if ($user->save(false)) {
            return [
                'success' => true,
                'message' => 'Logout successful.',
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to logout.',
        ];
    }
}