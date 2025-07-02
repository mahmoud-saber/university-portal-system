<?php

namespace api\controllers;

use Yii;
use api\helpers\ResponseHelper;
use api\resources\CityResource;
use api\resources\CountryResource;
use api\resources\DistrictResource;
use common\models\Job;

class LookupsController extends RestController
{

    public function actionCities($country_id)
    {
        $cities = CityResource::find()->where(['country_id' => $country_id])->all();
        return ResponseHelper::sendSuccessResponse($cities);
    }

    public function actionCountry()
    {
        $countries = CountryResource::find()->all();
        return ResponseHelper::sendSuccessResponse($countries);
    }

    public function actionDistricts($city_id)
    {
        $city = CityResource::findOne(['id' => $city_id]);
        if ($city) {
            $district = DistrictResource::find()->where(['city_id' => $city_id])->all();
            return ResponseHelper::sendSuccessResponse($district);
        }
        return ResponseHelper::sendFailedResponse(['district' => Yii::t('common', 'City does not exist.')]);
    }


}
