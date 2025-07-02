<?php

namespace api\resources;

use common\models\InvitationForm;

class InvitationFormResource extends InvitationForm
{

    public function fields()
    {
        return [
            'id',
            'name',
            'phone',
            'email',
            'q_prefer_to',
            'q_right_time',
            'q_sport',
            'q_age',
//            'created_at' => function () {
//                return date('Y-m-d', $this->created_at);
//            },

        ];
    }
}
