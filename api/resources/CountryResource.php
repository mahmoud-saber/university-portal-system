<?php

namespace api\resources;

use common\models\Country;

class CountryResource extends  Country
{
    public function fields()
    {
        return [
            'id',
            'name',
        ];
    }
}
