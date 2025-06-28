<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "course".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $teacher_id
 * @property int $created_at
 * @property int $updated_at
 *
 * @property CourseRegistration[] $courseRegistrations
 * @property Grade[] $grades
 * @property User $teacher
 */
class Course extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
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
        return 'course';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'default', 'value' => null],
            [['name', 'teacher_id'], 'required'],
            [['description'], 'string'],
            [['teacher_id', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['teacher_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['teacher_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'teacher_id' => 'Teacher ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[CourseRegistrations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCourseRegistrations()
    {
        return $this->hasMany(CourseRegistration::class, ['course_id' => 'id']);
    }

    /**
     * Gets query for [[Grades]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGrades()
    {
        return $this->hasMany(Grade::class, ['course_id' => 'id']);
    }

    /**
     * Gets query for [[Teacher]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTeacher()
    {
        return $this->hasOne(User::class, ['id' => 'teacher_id']);
    }
    public function getStudents()
    {
        return $this->hasMany(User::class, ['id' => 'student_id'])
            ->viaTable('course_registration', ['course_id' => 'id']);
    }
}