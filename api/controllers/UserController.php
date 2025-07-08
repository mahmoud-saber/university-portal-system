<?php

namespace api\controllers;

use Yii;
use yii\web\Response;
use common\models\User;
use yii\rest\Controller;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;

class UserController extends Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access'] = [
            'class' => \yii\filters\AccessControl::class,
            'only' => ['signup', 'login'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['signup', 'login'],
                    'roles' => ['?'],
                ],
            ],
        ];

        // جعل JSON هو التنسيق الافتراضي
        $behaviors['contentNegotiator']['formats']['application/json'] = \yii\web\Response::FORMAT_JSON;

        return $behaviors;
    }


    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $publicActions = ['signup', 'login'];

        // السماح للأكشنات العامة بدون التحقق من تسجيل الدخول
        if (in_array($action->id, $publicActions)) {
            return true;
        }

        // التحقق من تسجيل الدخول
        if (Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException('You are not allowed to access this resource.');
        }

        // التحقق من أن المستخدم يملك الدور admin فقط
        if (!Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException('Only admin users can access this endpoint.');
        }

        return true;
    }


    // public function actionIndex()
    // {
    //     $users = User::find()->all();

    //     $data = [];

    //     foreach ($users as $user) {
    //         $data[] = [
    //             'id' => $user->id,
    //             'username' => $user->username,
    //             'email' => $user->email,
    //             'role' => $user->role,
    //             'joined' => Yii::$app->formatter->asDate($user->created_at),
    //         ];
    //     }

    //     return [
    //         'status' => 'success',
    //         'message' => empty($data) ? 'No users found' : 'User list',
    //         'data' => $data
    //     ];
    // }

    /////////sign up
    //    public function actionSignup()
    // {
    //     $request = Yii::$app->request;
    //     $model = new User();
    //     $model->load($request->post(), '');

    //     if ($model->validate()) {
    //         // فقط عيّن كلمة المرور كنص عادي (سيتم تشفيرها تلقائيًا في beforeSave)
    //         $model->plainPassword = $model->plainPassword ?? Yii::$app->security->generateRandomString(8);

    //         // لا داعي لاستدعاء setPassword هنا

    //         $model->generateAuthKey();
    //         $model->access_token = Yii::$app->security->generateRandomString(64);

    //         if ($model->save()) {
    //             return [
    //                 'status' => 'success',
    //                 'message' => 'User registered successfully.',
    //                 'access_token' => $model->access_token,
    //             ];
    //         }
    //     }

    //     return [
    //         'status' => 'error',
    //         'message' => 'Validation failed',
    //         'errors' => $model->getErrors(),
    //     ];
    // }


    public function actionSignup()
    {
        $request = Yii::$app->request;
        $model = new User();
        $model->load($request->post(), '');

        if ($model->validate()) {
            // فقط عيّن كلمة المرور كنص عادي (سيتم تشفيرها تلقائيًا في beforeSave)
            $model->plainPassword = $model->plainPassword ?? Yii::$app->security->generateRandomString(8);

            // لا داعي لاستدعاء setPassword هنا

            $model->generateAuthKey();
            $model->access_token = Yii::$app->security->generateRandomString(64);

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


    ///////////////////login
    public function actionLogin()
    {
        $request = Yii::$app->request;
        $username = $request->post('username');
        $password = $request->post('password');

        // البحث عن المستخدم
        $user = \common\models\User::findByUsername($username);

        // التحقق من صحة البيانات
        if (!$user || !$user->validatePassword($password)) {
            return [
                'success' => false,
                'message' => 'Username or password is incorrect.',
            ];
        }

        // توليد access_token جديد (اختياري)
        $user->generateAccessToken();
        $user->save(false);

        // إرجاع بيانات الدخول
        return [
            'success' => true,
            'access_token' => $user->access_token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ];
    }
}