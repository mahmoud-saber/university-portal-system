<?php

namespace api\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

class ContractJobs extends ActiveRecord
{
    public $jobs;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contract_id', 'job_id', 'seniority_level_id', 'engagement_id', 'additional_option_id', 'cost', 'addon_cost'], 'required'],
            [['contract_id', 'job_id', 'seniority_level_id', 'engagement_id', 'additional_option_id'], 'integer'],
            [['cost', 'addon_cost'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contract_jobs';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'status' => Yii::t('common', 'Status'),
        ];
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => time(),
            ]
        ];
    }
}
