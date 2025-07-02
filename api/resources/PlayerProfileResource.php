<?php

namespace api\resources;

use Yii;

class PlayerProfileResource extends \common\models\User
{
    public function fields()
    {
        return [
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
            'academy_logo' => function () {
                return$this->userProfile->academy? $this->userProfile->academy->getLogo():"";
            },
            'picture' => function () {
                return $this->userProfile->getAvatar();
            },
            'joined_at' => function () {
                return date('Y-m-d', $this->created_at);
            },

            'current_sports' => function () {
                return [
                    'count' => $this->currentSubscriptionCount(),
                    'sports' =>$this->currentSubscriptionSports(),
            ];

            },
            'completed_sports' => function () {
                return [
                    'count' => $this->previousSubscriptionCount(),
                    'sports' =>$this->previousSubscriptionSports(),
                ];

            },
            'certificates' => function () {

            },

        ];
    }
}
