<?php
use yii\helpers\Html;
?>
<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>Student Name</th>
            <th>Course Name</th>
            <th>Teacher Name</th>
            <th>Grade</th>
            <th>Registered At</th>
            <th>Updated At</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($registrations as $index => $reg): ?>
        <tr>
            <td><?= $index + 1 ?></td>
            <td><?= Html::encode($reg->student->username ?? 'N/A') ?></td>
            <td><?= Html::encode($reg->course->name ?? 'N/A') ?></td>
            <td><?= Html::encode($reg->course->teacher->username ?? 'N/A') ?></td>
            <td><?= Html::encode($reg->grade_value  ?? 'N/A') ?></td>
            <td><?= Yii::$app->formatter->asDatetime($reg->created_at) ?></td>
            <td><?= Yii::$app->formatter->asDatetime($reg->updated_at) ?></td>

            <?php endforeach; ?>
    </tbody>
</table>