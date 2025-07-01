<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = '📝 Update Document';
?>

<div class="container mt-4">
    <h3><?= Html::encode($this->title) ?></h3>

    <div class="card p-3">
        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>


        <?= $form->field($model, 'file_type')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'file_path')->fileInput() ?>

        <div class="form-group mt-3">
            <?= Html::submitButton('Save Changes', ['class' => 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>