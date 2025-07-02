<?php

namespace api\models;

use api\helpers\ResponseHelper;
use common\models\User;
use common\models\UserProfile;
use Yii;
use yii\base\Model;

/**
 * Model representing  Signup Form.
 */
class UserForm extends Model
{
    const SCENARIO_UPDATE = 'upload_files';

    public $username;
    public $fullname;
    public $email;
    public $mobile;

    public $password;
    public $new_password;
    public $old_password;

    public $binary_picture;

    public $company_name;
    public $company_cr;

    /**
     * Returns the validation rules for attributes.
     *
     * @return array
     */
    public function rules()
    {
        return [

            [['fullname', 'email', 'mobile',], 'required'],
            [['fullname', 'email'], 'filter', 'filter' => 'trim'],
            ['email', 'email'],
            [['email', 'company_name'], 'string', 'max' => 200],
            [['fullname'], 'string', 'min' => 6, 'max' => 50],
            [
                'email', 'unique', 'targetClass' => '\common\models\User',
                'message' => 'Email is already in use.', 'when' => function ($model) {
                    return $model->email != Yii::$app->user->identity->email;
                }
            ],
            [
                'mobile', 'unique', 'targetClass' => '\common\models\User',
                'message' => 'Mobile is already in use.', 'when' => function ($model) {
                    return $model->mobile != Yii::$app->user->identity->mobile;
                }
            ],
            [['binary_picture'],'safe'],
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
            'fullname' => Yii::t('common', 'Fullname'),
            'email' => Yii::t('common', 'Email'),
            'password' => Yii::t('common', 'Password'),
            'password_repeat' => Yii::t('common', 'Repeat Password'),
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = User::findOne(['id' => \Yii::$app->user->identity->getId()]);
        $profile = $user->userProfile;

        $user->username =  $user->email = $this->email;
        $user->mobile = $this->mobile;

        if ($this->new_password != "") {
            if (!$this->old_password)  return 0;
            $valid_password = Yii::$app->getSecurity()->validatePassword($this->old_password, $user->password_hash);
            if (!$valid_password)  return 0;
            $user->setPassword($this->new_password);
        }

        $name = explode(' ', $this->fullname);
        $profile->firstname = $name[0];
        $profile->lastname = $name[1];
        $profile->mobile = $this->mobile;


        if ($profile->save() && $user->save()) {
            return true;
        } else {
            return ResponseHelper::sendFailedResponse(array_merge($profile->getFirstErrors(), $user->getFirstErrors()));
        }
    }

    public function MinifyUsername()
    {
        $username = str_replace(" ", "_", $this->username);
        $username = $username . '_' . rand(100, 1500000);
        return $username;
    }
}
