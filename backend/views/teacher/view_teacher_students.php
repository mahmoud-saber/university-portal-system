<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var common\models\CourseRegistration $model */
/** @var common\models\Grade $gradeModel */

$this->title = 'Student Registration Details';
$this->params['breadcrumbs'][] = ['label' => 'Registrations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="course-registration-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="card shadow p-4 mt-3">

        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                [
                    'label' => 'Student',
                    'value' => $model->student->username,
                ],
                [
                    'label' => 'Course',
                    'value' => $model->course->name,
                ],
                [
                    'label' => 'Teacher',
                    'value' => $model->course->teacher->username,
                ],
                [
                    'label' => 'Grade',
                    'value' => $gradeModel ? $gradeModel->grade : 'N/A',
                ],
                'created_at:datetime',
            ],
        ]) ?>

        <div class="mt-3">
            <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
            <?= Html::a('Back', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>
</div>