<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\CourseRegistration[] $registrations */
/** @var string|null $search */

$this->title = 'My Course Registrations';
?>




<hr>
<?php if (Yii::$app->session->hasFlash('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= Yii::$app->session->getFlash('success') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if (Yii::$app->session->hasFlash('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= Yii::$app->session->getFlash('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if (empty($registrations)): ?>
<p>No students registered in your courses yet.</p>
<?php else: ?>
<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>Student Name</th>
            <th>Course</th>
            <th>Grade</th>
            <th>Registered At</th>
            <th>Registered update</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($registrations as $index => $reg): ?>
        <tr>
            <td><?= $index + 1 ?></td>
            <td><?= Html::encode($reg->student->username ?? 'N/A') ?></td>
            <td><?= Html::encode($reg->course->name ?? 'N/A') ?></td>
            <td><?= Html::encode($reg->grade_value ?? 'N/A') ?></td>
            <td><?= Yii::$app->formatter->asDatetime($reg->created_at) ?></td>
            <td><?= Yii::$app->formatter->asDatetime($reg->updated_at) ?></td>
            <td>
                <?= Html::a('View', ['view', 'id' => $reg->id], ['class' => 'btn btn-sm btn-info']) ?>
                <?= Html::a('Add', ['update', 'id' => $reg->id], ['class' => 'btn btn-sm btn-warning']) ?>
                <?= Html::a('Delete', ['delete', 'id' => $reg->id], [
                            'class' => 'btn btn-sm btn-danger',
                            
                        ]) ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>