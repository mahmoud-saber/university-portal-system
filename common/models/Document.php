<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "document".
 *
 * @property int $id
 * @property int $user_id
 * @property string $file_path
 * @property string|null $file_type
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property User $user
 */
class Document extends \yii\db\ActiveRecord
{

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'document';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                ['file_path'],
                'file',
                'extensions' => ['png', 'jpeg', 'jpg', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'],
                'checkExtensionByMimeType' => true,
                'maxSize' => 10 * 1024 * 1024, // 10 ميجابايت
            ],
            [['file_path', 'file_type', 'original_name', 'user_id'], 'required'],
            [['file_path'], 'string', 'max' => 255],
            [['file_type'], 'string', 'max' => 50],
            [['original_name'], 'string', 'max' => 255],
            [['user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }




    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'file_path' => 'File Path',
            'file_type' => 'File Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}