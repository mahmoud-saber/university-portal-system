<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = '📘 Course Details';
$this->params['breadcrumbs'][] = ['label' => 'My Courses', 'url' => ['index_studentcourse']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="container mt-4">
    <h2><?= Html::encode($this->title) ?></h2>

    <div class="card shadow p-4 mt-3">
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                'name',
                'description:ntext',
                [
                    'label' => 'Teacher',
                    'value' => $model->teacher->username ?? 'N/A',
                ],
                [
                    'label' => 'Created At',
                    'value' => Yii::$app->formatter->asDatetime($model->created_at),
                ],
            ],
        ]) ?>

        <div class="mt-4">
            <?= Html::a('🔙 Back to My Courses', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>
</div>