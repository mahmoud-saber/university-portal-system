<?php

namespace api\controllers;

use Yii;
use cheatsheet\Time;
use common\models\User;
use api\models\UserSignup;
use yii\base\DynamicModel;
use common\models\UserToken;
use api\helpers\ResponseHelper;
use api\resources\UserResource;
use common\models\Notification;
use api\controllers\MyRestController;
use common\commands\SendEmailCommand;

class UserController extends MyRestController
{

    public $prefix = '966';

    public function actionLogin()
    {
        $params = \Yii::$app->request->post();

        if (isset($params['mobile']) && isset($params['password'])) {
            $user = UserResource::find()
                ->active()
                ->andWhere( ['mobile' => $params['mobile']])
                ->one();
            if (!$user) {
                return ResponseHelper::sendFailedResponse(['identity' => Yii::t('common', 'Please check your data and validate your mobile.')], 400);
            }

            $valid_password = Yii::$app->getSecurity()->validatePassword($params['password'], $user->password_hash);
            if ($valid_password) {
                $user->access_token = Yii::$app->getSecurity()->generateRandomString(40);
                $user->logged_at = time();
                $user->save(false);
                $data = ['token' => $user->access_token, 'profile' => $user];
                return ResponseHelper::sendSuccessResponse($data);
            } else {
                return ResponseHelper::sendFailedResponse(['identity' => Yii::t('common', 'Your login data is not correct.')]);
            }
        } else {
            return ResponseHelper::sendFailedResponse(['identity' => Yii::t('common', 'Your login data is not correct.')]);
        }
    }

    public function actionSignup()
    {
        $params = \Yii::$app->request->post();

        $model = new UserSignup();
        $model->load(['UserSignup' => $params]);

        $registerUser = $model->signup();
        if ($registerUser['status']) {
            $user = UserResource::find()->where(['id' => $registerUser['user']->id])->one();
            $token = UserToken::create($user->id, UserToken::TYPE_ACTIVATION, Time::SECONDS_IN_A_DAY);
            Yii::$app->commandBus->handle(new SendEmailCommand([
                'to' => $user->email,
                'subject' => Yii::t('common', 'Verify email for {name}', ['name' => $user->username]),
                'view' => 'new-user-verify-email',
                'params' => [
                    'user' => $user,
                    'token' => $token->token
                ]
            ]));
            $message = Yii::t('common', 'Your account has been successfully created. Check your email for further instructions.');
            return ResponseHelper::sendSuccessResponse($message);
        } else {
            return ResponseHelper::sendFailedResponse(array_merge($model->getFirstErrors(), $registerUser['errors']));
        }
    }

    public function actionVerify()
    {
        $params = \Yii::$app->request->post();
        $email = $params['email'];
        $token = $params['token'];
        $model = DynamicModel::validateData(compact('email', 'token'), [
            [['email', 'token'], 'required'],
            ['email', 'email'],
        ]);

        if ($model->hasErrors()) {
            return ResponseHelper::sendFailedResponse($model->getFirstErrors());
        }

        $user = User::findOne(['email' => $email]);
        if (!$user) {
            return ResponseHelper::sendFailedResponse(['email' => \Yii::t('common', 'Please check the entered data')]);
        }

        $token = UserToken::find()
            ->byType(UserToken::TYPE_ACTIVATION)
            ->byToken($token)
            ->notExpired()
            ->one();
        if ($token) {
            $user = UserResource::find()->where(['id' => $token->user_id])->one();
            $user->updateAttributes([
                'status' => User::STATUS_ACTIVE,
            ]);
            $user->logged_at = time();
            $user->save(false);
            $token->delete();

            if (Yii::$app->user->identity->user_type == User::USER_TYPE_DOCTOR) {
                Notification::addToAdmin(Notification::TOPIC_ADIMN_DOCTOR_REGISTERED, $token->user_id);
            }

            return ResponseHelper::sendSuccessResponse([
                'message' => Yii::t('common', 'Your account has been successfully activated.'),
                'token' => $user->access_token,
                'profile' => $user
            ]);
        } else {
            return ResponseHelper::sendFailedResponse(['token' => Yii::t('common', 'Token not valid.')]);
        }
    }

    public function actionResendVerifyEmailCode()
    {
        $params = \Yii::$app->request->post();
        $email = $params['email'];
        $model = DynamicModel::validateData(['email' => $email], [
            ['email', 'required'],
            ['email', 'email'],
        ]);

        if ($model->hasErrors()) {
            return ResponseHelper::sendFailedResponse($model->getFirstErrors());
        }

        $user = User::findOne(['email' => $email]);
        if ($user) {
            $token = UserToken::create($user->id, UserToken::TYPE_ACTIVATION, Time::SECONDS_IN_A_DAY);
            if ($user->save()) {
                \Yii::$app->commandBus->handle(new SendEmailCommand([
                    'to' => $user->email,
                    'subject' => Yii::t('common', 'Verify email for {name}', ['name' => $user->username]),
                    'view' => 'new-user-verify-email',
                    'params' => [
                        'user' => $user,
                        'token' => $token->token
                    ]
                ]));
                $message = \Yii::t('common', 'verify email code sent successfully.');
                return ResponseHelper::sendSuccessResponse($message);
            }
        }
        return ResponseHelper::sendFailedResponse(['email' => \Yii::t('common', 'Please check the entered data')], 404);
    }

    public function actionRequestResetPassword()
    {
        $params = \Yii::$app->request->post();
        $email = $params['email'];
        $model = DynamicModel::validateData(['email' => $email], [
            ['email', 'required'],
            ['email', 'email'],
        ]);

        if ($model->hasErrors()) {
            return ResponseHelper::sendFailedResponse($model->getFirstErrors());
        }

        $user = User::findOne(['email' => $email]);
        if ($user) {
            $token = UserToken::create($user->id, UserToken::TYPE_PASSWORD_RESET, Time::SECONDS_IN_A_DAY);
            if ($user->save()) {
                \Yii::$app->commandBus->handle(new SendEmailCommand([
                    'to' => $user->email,
                    'subject' => Yii::t('common', 'Password reset for {name}', ['name' => $user->username]),
                    'view' => 'password-reset-token',
                    'params' => [
                        'user' => $user,
                        'token' => $token->token
                    ]
                ]));
                $message = \Yii::t('common', 'Email reset password sent successfully');
                return ResponseHelper::sendSuccessResponse($message);
            }
        }
        return ResponseHelper::sendFailedResponse(['email' => \Yii::t('common', 'Please check the entered data')], 404);
    }

    public function actionVerifyResetPasswordToken()
    {
        $params = \Yii::$app->request->post();
        $token = $params['token'];
        $model = DynamicModel::validateData(['token' => $token], [
            ['token', 'required'],
        ]);

        if ($model->hasErrors()) {
            return ResponseHelper::sendFailedResponse($model->getFirstErrors(), 400);
        }

        $token = UserToken::find()
            ->byType(UserToken::TYPE_PASSWORD_RESET)
            ->byToken($token)
            ->one();
        if ($token) {
            $tokenNotExpired = UserToken::find()
                ->byType(UserToken::TYPE_PASSWORD_RESET)
                ->byToken($token)
                ->notExpired()
                ->one();
            if ($tokenNotExpired) {
                return ResponseHelper::sendSuccessResponse(Yii::t('common', 'Token is valid.'));
            } else {
                return ResponseHelper::sendFailedResponse(['token' => Yii::t('common', 'Token not valid.')]);
            }
        } else {
            return ResponseHelper::sendFailedResponse(['token' => Yii::t('common', 'Token not valid.')], 404);
        }
    }

    public function actionResetPassword()
    {
        $params = \Yii::$app->request->post();
        $email = $params['email'];
        $token = $params['token'];
        $password = $params['password'];
        $confirm_password = $params['confirm_password'];
        $model = DynamicModel::validateData(compact('email', 'token', 'password', 'confirm_password'), [
            [['email', 'token', 'password', 'confirm_password'], 'required'],
            ['email', 'email'],
            [
                'confirm_password', 'compare', 'compareAttribute' => 'password',
                'message' => Yii::t('common', "Passwords don't match"),
            ],
        ]);

        if ($model->hasErrors()) {
            return ResponseHelper::sendFailedResponse($model->getFirstErrors());
        }

        $user = User::findOne(['email' => $email]);
        if (!$user) {
            return ResponseHelper::sendFailedResponse(['email' => \Yii::t('common', 'Please check the entered data')], 404);
        }

        $token = UserToken::find()->where(['user_id' => $user->id])
            ->byType(UserToken::TYPE_PASSWORD_RESET)
            ->byToken($token)
            ->notExpired()
            ->one();
        if ($token) {
            $user = UserResource::find()->where(['id' => $token->user_id])->one();
            $user->password = $model->password;
            if ($user->save()) {
                $token->delete();
                return ResponseHelper::sendSuccessResponse([
                    'message' => Yii::t('common', 'Your password has been reset successfully.'),
                    'token' => $user->access_token,
                    'profile' => $user
                ]);
            }
        } else {
            return ResponseHelper::sendFailedResponse(['token' => Yii::t('common', 'Token not valid.')]);
        }
    }
}
