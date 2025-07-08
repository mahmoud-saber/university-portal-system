<?php

namespace api\resources;

use common\models\User;

class UserResource
{
    public static function toArray(User $user)
    {
        return [
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => date('Y-m-d H:i:s', $user->created_at),

        ];
    }


    public static function Arraylogin(User $user)
    {
        return ([
            'success' => true,
            'access_token' => $user->access_token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ]);
    }
}