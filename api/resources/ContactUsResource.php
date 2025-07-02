<?php

namespace api\resources;

use common\models\ContactUs;


class ContactUsResource extends ContactUs
{
    public function fields()
    {
        return [
            'id',
            'name',
            'email',
            'phone',
            'message',
            'created_at' => function ($model) {
                return  $model->created_at;
            }
        ];
    }
}
