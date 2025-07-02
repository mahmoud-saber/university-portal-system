<?php

namespace api\models;

use yii\base\BaseObject;
use api\resources\UserResource;
use common\helpers\FirebaseNotification;

class GlobalNotificationQueue extends BaseObject implements \yii\queue\JobInterface
{
    public $title;
    public $message;
    public $currentAdminId;

    public function execute($queue)
    {
        $users = UserResource::find()->select(['id'])->where(['!=', 'id', $this->currentAdminId])->all();
        foreach ($users as $user) {
            (new FirebaseNotification)->insert([
                $user->id => [
                    'title' => $this->title,
                    'message' => $this->message,
                    'seen' => false,
                ],
            ], 'userNotifications');
        }
    }
}
