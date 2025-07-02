<?php

namespace api\helpers;
//namespace app\helpers;
//use api\helpers\ResponseHelper;

use Yii;

class ResponseHelper
{

    public static function sendFailedResponse($errors, $code = 400)
    {
        Yii::$app->response->setStatusCode($code);
        if (is_array($errors)) {
            $message = $errors;
        } else {
            $message['error'] = $errors;
        }
        return ['success' => false, 'status' => $code, 'errors' => $message];
    }

    public static function sendSuccessResponse($data = false, $code = 200)
    {
        Yii::$app->response->setStatusCode($code);
        return ['success' => true, 'status' => $code, 'data' => $data];
    }
}
