<?php

namespace api\controllers;

use common\models\Faq;
use api\resources\FaqResource;
use api\helpers\ResponseHelper;

class FaqController extends MyActiveController
{
    public $modelClass = Faq::class;

    public function actionIndex()
    {
        $faq = FaqResource::find()->where(['status' => FaqResource::STATUS_ACTIVE])->all();
        return ResponseHelper::sendSuccessResponse($faq);
    }
}
