<?php

namespace backend\controllers;

use common\models\LoginForm;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirectByRole();
        }

        $this->layout = 'blank';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirectByRole();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }
    public function actionIndex()
    {
        return $this->redirectByRole();
    }
    private function redirectByRole()
    {
        $role = Yii::$app->user->identity->role;

        switch ($role) {
            case 'admin':
                return $this->redirect(['admin/dashboard']);
            case 'teacher':
                return $this->redirect(['teacher/dashboard']);
            case 'student':
                return $this->redirect(['student/dashboard']);
            default:
                return $this->goHome(); 
        }
    }

        public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }
}