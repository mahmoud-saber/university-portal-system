<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\CourseRegistration $model */
/** @var common\models\Grade $gradeModel */
/** @var array $courses */
/** @var array $students */

$this->title = 'Register Student in a Course';
$this->params['breadcrumbs'][] = ['label' => 'Registrations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<h1><?= Html::encode($this->title) ?></h1>

<div class="course-registration-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'student_id')->dropDownList($students, ['prompt' => 'Select Student']) ?>

    <?= $form->field($model, 'course_id')->dropDownList($courses, ['prompt' => 'Select Course']) ?>

    <?= $form->field($gradeModel, 'grade')->dropDownList([
        'A' => 'A',
        'B' => 'B',
        'C' => 'C',
        'D' => 'D',
        'F' => 'F'
    ], ['prompt' => 'Select Grade']) ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Register', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>