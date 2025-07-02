<?php

namespace api\models;

use api\helpers\ResponseHelper;
use common\models\User;
use common\models\UserProfile;
use Yii;
use yii\base\Model;

/**
 * Model representing a Profile Form.
 */
class ProfileForm extends Model
{
    const SCENARIO_UPDATE = 'upload_files';

    public $username;
    public $fullname;
    public $mobile;
    public $binary_picture;

    /**
     * Returns the validation rules for attributes.
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['fullname', 'mobile'], 'required'],
            [['fullname'], 'string', 'min' => 6, 'max' => 50],
            [['mobile'], 'string', 'max' => 15], 
            [['mobile'], 'match', 'pattern' => '/^[0-9]+$/', 'message' => 'رقم الجوال يجب أن يحتوي على أرقام فقط.'],
            [['binary_picture'], 'safe'],
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
            'mobile' => Yii::t('common', 'Mobile'),
            'binary_picture' => Yii::t('common', 'Profile Picture'),
        ];
    }

    /**
     * Saves the profile data.
     *
     * @return bool
     */
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
