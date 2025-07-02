<?php

namespace api\resources;

use Yii;

class UserResource extends \common\models\User
{
    public function fields()
    {
        return [
            'token' => function () {
                return $this->access_token;
            },
            'id',
            'name' => function () {
                return $this->userProfile->firstname . ' ' . $this->userProfile->lastname;
            },
            'email',
            'mobile' => function () {
            
                return $this->mobile;
            },
            'status'=> function(){
                return [
                    'id' => $this->status,
                    'text' => $this->statuses()[$this->status],
                ];
            },
            'picture' => function () {
                return $this->userProfile->getAvatar();
            },
            'academy_logo' => function () {
                return$this->userProfile->academy? $this->userProfile->academy->getLogo():"";
            },
            'joined_at' => function () {
                return date('Y-m-d', $this->created_at);
            },
        ];
    }
}
