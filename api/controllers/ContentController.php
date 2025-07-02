<?php

namespace api\controllers;

use api\helpers\ResponseHelper;
use api\resources\PageResource;
use api\resources\SettingsResource;
use common\models\Page;
use Yii;

class ContentController extends RestController
{
    public function beforeAction($action)
    {
        if (isset($_REQUEST['lang']) && $_REQUEST['lang'] == 'ar') {
            \Yii::$app->language = 'ar';
        }
        return parent::beforeAction($action);
    }

    public function actionTermsConditions()
    {
        $termsConditionsObj = PageResource::findOne(2);

        if ($termsConditionsObj) {
            return ResponseHelper::sendSuccessResponse($termsConditionsObj);
        } else {
            return ResponseHelper::sendFailedResponse(Yii::t('common', 'Not Found'), 404);
        }
    }


}
