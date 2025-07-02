<?php

namespace api\models;

use Yii;
use common\models\User;
use yii\base\Model;

/**
 * Model representing  ChangePassword API.
 */

class ChangePassword extends Model
{
    public $current_password;
    public $new_password;
    public $confirm_password;

    /**
     * Returns the validation rules for attributes.
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['current_password', 'new_password', 'confirm_password'], 'required'],
            ['current_password', 'validatePassword'],
            [['new_password'], 'string', 'min' => 8],
            [['new_password'], 'match', 'pattern' => '/^(?=.*[\d@#$%])(?=.*[a-z])(?=.*[A-Z])[a-zA-Z0-9@#$%]{8,}$/'],
            [['current_password', 'new_password', 'confirm_password'], 'string', 'max' => 255],
            ['confirm_password', 'compare', 'compareAttribute' => 'new_password', 'message' => Yii::t('common', 'Passwords don\'t match')],
        ];
    }

    public function validatePassword($attribute)
    {
        if (!$this->hasErrors()) {
            $user = User::findOne(['id' => \Yii::$app->user->identity->getId()]);
            if (!$user->validatePassword($this->current_password)) {
                $this->addError($attribute, Yii::t('common', 'Current password does not match.'));
            }
        }
    }

    /**
     * Returns the attribute labels.
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'current_password' => Yii::t('common', 'Current password'),
            'new_password' => Yii::t('common', 'New password'),
            'confirm_password' => Yii::t('common', 'Confirm password'),
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = User::findOne(['id' => \Yii::$app->user->identity->getId()]);
        $user->setPassword($this->new_password);
        $user->save(false);
        return true;
    }
}
