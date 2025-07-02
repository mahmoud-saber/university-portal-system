<?php

namespace api\models;

use Yii;
use yii\base\Model;
use common\models\User;
use api\helpers\ImageHelper;
use common\models\UserProfile;
use api\helpers\ResponseHelper;

class UserCreate extends Model
{
    public $username;
    public $email;
    public $name;
    public $otp;
    public $password;
    public $password_repeat;
    public $status;
    public $binary_image;
    public $permissions;

    /**
     * Returns the validation rules for attributes.
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'permissions'], 'required'],
            [['password','email'], 'required', 'on' => 'create'],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'email'],
            [['email', 'username', 'name'], 'string', 'max' => 200],
            ['binary_image', 'safe'],
        ];
    }

    /**
     * Returns the attribute labels.
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('common', 'Username'),
            'email' => Yii::t('common', 'Email'),
            'password' => Yii::t('common', 'Password'),
            'password_repeat' => Yii::t('common', 'Repeat Password'),
        ];
    }

    /**
     * Signs up the user.
     * If scenario is set to "rna" (registration needs activation), this means
     * that user need to activate his account using email confirmation method.
     *
     * @return User|null The saved model or null if saving fails.
     */
    public function signup()
    {

        $user = new User();
        $user->username = $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->status = User::STATUS_ACTIVE;
        $user->permissions = implode('|', $this->permissions);
        if ($user->save()) {
            $auth = Yii::$app->authManager;
            $role = $auth->getRole(User::ROLE_MANAGER);
            $auth->assign($role, $user->id);

            $profile = new UserProfile();
            $profile->firstname = $this->name;
            $profile->locale = 'en';
            if ($this->binary_image) {
                $filename = ImageHelper::Base64Image($this->binary_image);
                $profile->avatar_base_url = \Yii::getAlias('@storageUrl') . '/source/';
                $profile->avatar_path = 'profile/' . $filename;
            }
            $user->link('userProfile', $profile);

            return $user;
        }
        return false;
    }

    public function update($id)
    {

        $user = User::findOne($id);
        if($this->email){
            $user->username = $user->email = $this->email;
        }
        $user->permissions = implode('|', $this->permissions);
        if ($this->password) {
            $user->setPassword($this->password);
            $user->generateAuthKey();
        }

        if ($user->save()) {
            $profile = $user->userProfile;
            $name = explode(" ", trim($this->name));
            $profile->firstname = $name[0];
            $profile->lastname = $name[1];
            if ($this->binary_image) {
                $filename = ImageHelper::Base64Image($this->binary_image);
                $profile->avatar_base_url = \Yii::getAlias('@storageUrl') . '/source/';
                $profile->avatar_path = 'profile/' . $filename;
            }
            $profile->save(false);
            return $user;
        }
        return ResponseHelper::sendFailedResponse($user->getFirstErrors());
    }
}
