<?php

namespace api\controllers;

use Yii;
use api\models\UserForm;
use api\helpers\ImageHelper;
use api\models\ChangePassword;
use api\helpers\ResponseHelper;
use api\resources\UserResource;
use yii\base\InvalidParamException;
use common\models\CompleteProfileData;

class ProfileController extends MyActiveController
{

    public $modelClass = UserResource::class;

    public function actionIndex()
    {
        $user = UserResource::findOne(\Yii::$app->user->identity->id);
        return ResponseHelper::sendSuccessResponse($user);
    }

    public function actionChangeLocation()
    {
        $user = UserResource::findOne(\Yii::$app->user->identity->id);
        $userProf = $user->userProfile;
        $params = \Yii::$app->request->post();

        if (isset($params['address']) && isset($params['lat']) && isset($params['lng'])) {
            $userProf->address = $params['address'];
            $userProf->lat = $params['lat'];
            $userProf->lng = $params['lng'];

            $userProf->save();

            return ResponseHelper::sendSuccessResponse(['MESSAGE' => Yii::t('common', 'Location changed successfully')], 200);
        } else {
            return ResponseHelper::sendFailedResponse(['error' => Yii::t('common', 'Not available data')]);
        }
    }

    public function actionUpdate()
    {
        $user = UserResource::findOne(\Yii::$app->user->identity->id);
        $params = \Yii::$app->request->post();
        $model = new UserForm();
        $model->username =  $user->email;
        $model->email =  $user->email;
        if ($model->load(['UserForm' => $params]) && $model->save()) {
            if ($model->binary_picture) {
                $userProfile = $user->userProfile;
                try {
                    $filename = ImageHelper::Base64Image($model->binary_picture);
                    $userProfile->avatar_base_url = \Yii::getAlias('@storageUrl') . '/source/';
                    $userProfile->avatar_path = 'profile/' . $filename;
                } catch (InvalidParamException $e) {
                    return ResponseHelper::sendFailedResponse(['binary_picture' => $e->getMessage()]);
                }

                if (!$userProfile->save(false)) {
                    return ResponseHelper::sendFailedResponse($userProfile->getFirstErrors());
                }
            }

            $user = UserResource::findOne(\Yii::$app->user->identity->id);
            return ResponseHelper::sendSuccessResponse($user);
        } else {
            return ResponseHelper::sendFailedResponse($model->getFirstErrors() ?: ['error' => Yii::t('common', "Invalid access")]);
        }
    }

    public function actionCompleteProfileData()
    {
        $params = \Yii::$app->request->post();
        $model = CompleteProfileData::find()->where(['user_id' => \Yii::$app->user->identity->id])->one();
        if ($model->load(['CompleteProfileData' => $params]) && $model->save()) {
            $name = explode(' ', $model->name);
            $model->firstname = $name[0];
            $model->lastname = $name[1];
            $model->save();
            $user = UserResource::findOne(\Yii::$app->user->identity->id);

            if ($model->binary_picture) {
                try {
                    $filename = ImageHelper::Base64Image($model->binary_picture);
                    $model->avatar_base_url = \Yii::getAlias('@storageUrl') . '/source/';
                    $model->avatar_path = 'profile/' . $filename;
                } catch (InvalidParamException $e) {
                    return ResponseHelper::sendFailedResponse(['binary_picture' => $e->getMessage()]);
                }

                if (!$model->save(false)) {
                    return ResponseHelper::sendFailedResponse($model->getFirstErrors());
                }
            }

            if ($model->binary_cv) {
                try {
                    $filename = ImageHelper::Base64IPdfConverter($model->binary_cv, 'cv');
                    $model->cv_base_url = \Yii::getAlias('@storageUrl') . '/source/';
                    $model->cv_path = 'cv/' . $filename;
                } catch (InvalidParamException $e) {
                    return ResponseHelper::sendFailedResponse(['binary_cv' => $e->getMessage()]);
                }

                if (!$model->save(false)) {
                    return ResponseHelper::sendFailedResponse($model->getFirstErrors());
                }
            }

            return ResponseHelper::sendSuccessResponse($user);
        } else {
            return ResponseHelper::sendFailedResponse($model->getFirstErrors());
        }
    }

    public function actionChangePassword()
    {
        $model = new ChangePassword();
        $params = \Yii::$app->request->post();
        if ($model->load(['ChangePassword' => $params]) && $model->save()) {
            return ResponseHelper::sendSuccessResponse(Yii::t('common', "Password Updated Successfully."));
        } else {
            return ResponseHelper::sendFailedResponse($model->getFirstErrors());
        }
    }
}
