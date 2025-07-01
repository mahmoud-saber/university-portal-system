<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var \common\models\Document[] $documents */

$this->title = '📄 Documents';
?>

<div class="container mt-4">
    <h3><?= Html::encode($this->title) ?></h3>

    <?php if (empty($documents)): ?>
    <div class="alert alert-info mt-3">No documents to display.</div>
    <?php else: ?>
    <table class="table table-bordered table-hover mt-3">
        <thead class=" table-dark">
            <tr>
                <th>#</th>
                <th>File Name</th>
                <th>ٍstudent</th>
                <th>Uploaded At</th>
                <th>Download</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($documents as $index => $doc): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= Html::encode($doc->original_name) ?></td>
                <td><?= Html::encode($doc->user->username ?? 'Unknown') ?></td>
                <td><?= Yii::$app->formatter->asDatetime($doc->created_at) ?></td>
                <td>
                    <?= Html::a('⬇️ Download', Url::to('@web/assignments/' . basename($doc->file_path)), [
                                'class' => 'btn btn-sm btn-primary',
                                'target' => '_blank'
                            ]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>