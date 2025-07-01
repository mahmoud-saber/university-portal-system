<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = '➕ Register Student to Course';
?>

<div class="container mt-4">
    <h2><?= Html::encode($this->title) ?></h2>

    <div class="card p-3">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'course_id')->dropDownList(
            \yii\helpers\ArrayHelper::map($courses, 'id', 'name'),
            ['prompt' => 'Select Course']
        ) ?>

        <div class="form-group mt-3">
            <?= Html::submitButton('📌 Register', ['class' => 'btn btn-success']) ?>
            <?= Html::a('🔙 Back', ['index'], ['class' => 'btn btn-secondary ms-2']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>