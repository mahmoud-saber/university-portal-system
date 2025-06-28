<?php

use yii\helpers\Html;

$this->title = '📄 My Documents';
?>
<?php if (Yii::$app->session->hasFlash('success')): ?>
<div class="alert alert-success">
    <?= Yii::$app->session->getFlash('success') ?>
</div>
<?php endif; ?>

<div class="container mt-4">
    <h3><?= Html::encode($this->title) ?></h3>

    <p>
        <?= Html::a('📁 Upload New Document', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php if (empty($documents)): ?>
    <div class="alert alert-info">No documents uploaded yet.</div>
    <?php else: ?>
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>File Name</th>
                <th>Uploaded At</th>
                <th>Download</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($documents as $index => $doc): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= Html::encode($doc->original_name) ?></td>
                <td><?= Yii::$app->formatter->asDatetime($doc->created_at) ?></td>
                <td>
                    <?= Html::a('⬇️ Download', Yii::getAlias('@web') . '/uploads/' . basename($doc->file_path), [
                                'class' => 'btn btn-sm btn-primary',
                                'target' => '_blank'
                            ]) ?>
                </td>
                <td>
                    <?= Html::a('📝 Update', ['update', 'id' => $doc->id], ['class' => 'btn btn-sm btn-warning']) ?>
                    <?= Html::a('🗑️ Delete', ['delete', 'id' => $doc->id], [
                                'class' => 'btn btn-sm btn-danger',
                                'data' => [
                                    'confirm' => 'Are you sure you want to delete this document?',
                                    'method' => 'post',
                                ],
                            ]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>