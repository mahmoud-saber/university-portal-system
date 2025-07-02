<?php

namespace api\resources;

use common\models\District;

class DistrictResource extends  District
{
    public function fields()
    {
        return [
            'id',
            'name',
            'slug',
        ];
    }


}
