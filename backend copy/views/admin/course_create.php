<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Course $model */
/** @var yii\widgets\ActiveForm $form */
/** @var array $teachers */

$this->title = '➕ Create Course';
?>

<div class="course-create container mt-4">
    <h3><?= Html::encode($this->title) ?></h3>

    <div class="card p-4 mt-3 shadow-sm">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'description')->textarea(['rows' => 4]) ?>

        <?= $form->field($model, 'teacher_id')->dropDownList($teachers, ['prompt' => 'Select a Teacher'],[
             
        ]) ?>

        <div class="form-group mt-3">
            <?= Html::submitButton('💾 Save', ['class' => 'btn btn-success']) ?>
            <?= Html::a('↩️ Cancel', ['index_course'], ['class' => 'btn btn-secondary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>