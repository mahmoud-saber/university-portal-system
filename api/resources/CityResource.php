<?php

namespace api\resources;

use common\models\City;

class CityResource extends City
{
    public function fields()
    {
        return [
            'id',
            'name',
        ];
    }
}
