<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = '✏️ Update Student Course Registration';
?>

<div class="container mt-4">
    <h2><?= Html::encode($this->title) ?></h2>

    <div class="card p-3 shadow">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'student_id')->dropDownList(
            \yii\helpers\ArrayHelper::map($students, 'id', 'username'),
            ['prompt' => 'Select a student']
        ) ?>

        <?= $form->field($model, 'course_id')->dropDownList(
            \yii\helpers\ArrayHelper::map($courses, 'id', 'name'),
            ['prompt' => 'Select a course']
        ) ?>

        <div class="form-group mt-3">
            <?= Html::submitButton('💾 Save Changes', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('🔙 Back', ['index'], ['class' => 'btn btn-secondary ms-2']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>