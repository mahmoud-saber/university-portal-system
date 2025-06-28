<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = '➕ Add New Teacher';
?>

<div class="teacher-create container mt-4">
    <div class="card shadow-sm border-primary">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>
        </div>

        <div class="card-body">
            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'username')->textInput([
                'maxlength' => true,
                'placeholder' => 'Enter username...',
                'class' => 'form-control'
            ]) ?>

            <?= $form->field($model, 'email')->input('email', [
                'placeholder' => 'Enter email...',
                'class' => 'form-control'
            ]) ?>
            <?= $form->field($model, 'plainPassword')->passwordInput([
                'placeholder' => 'Enter password...',
                'class' => 'form-control'
            ]) ?>

            <?= $form->field($model, 'role')->hiddenInput(['value' => 'teacher'])->label(false) ?>

            <div class="form-group mt-3 text-right">
                <?= Html::submitButton('<i class="fas fa-save"></i> Save', ['class' => 'btn btn-success']) ?>
                <?= Html::a('<i class="fas fa-arrow-left"></i> Cancel', ['index'], ['class' => 'btn btn-secondary']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>