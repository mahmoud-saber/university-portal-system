<?php 

use yii\widgets\ActiveForm;
use yii\helpers\Html;

/** @var $model \common\models\Document */

$this->title = 'Upload Document';
?>

<div class="container mt-4">
    <h3><?= Html::encode($this->title) ?></h3>

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'file_path')->fileInput() ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('create', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>