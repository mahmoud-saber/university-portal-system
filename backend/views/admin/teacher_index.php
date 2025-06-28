<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = '👨‍🏫 Teachers';
?>

<div class="teacher-index container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><?= Html::encode($this->title) ?></h3>
        <?= Html::a('➕ Add Teacher', ['create'], ['class' => 'btn btn-success']) ?>
    </div>
    <!-- ✅ Flash Message -->
    <?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= Yii::$app->session->getFlash('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    <!-- ✅ Flash Message -->
    <?php if (Yii::$app->session->hasFlash('update')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= Yii::$app->session->getFlash('update') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    <!-- ✅ Flash Message -->
    <?php if (Yii::$app->session->hasFlash('danger')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= Yii::$app->session->getFlash('danger') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- 🔍 نموذج البحث -->
    <form method="get" action="<?= Url::to(['index']) ?>" class="form-inline mb-3">
        <input type="text" name="q" value="<?= Html::encode(Yii::$app->request->get('q')) ?>" class="form-control mr-2"
            placeholder="Search by username or email">
        <button type="submit" class="btn btn-primary">🔍 Search</button>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($teachers)): ?>
                <?php foreach ($teachers as $teacher): ?>
                <tr>
                    <td><?= Html::encode($teacher->username) ?></td>
                    <td><?= Html::encode($teacher->email) ?></td>
                    <td><?= Yii::$app->formatter->asDatetime($teacher->created_at) ?></td>

                    <td>
                        <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $teacher->id], [
                                    'class' => 'btn btn-sm btn-primary',
                                    'title' => 'Edit'
                                ]) ?>
                        <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $teacher->id], [
                                    'class' => 'btn btn-sm btn-danger',
                                    'title' => 'Delete',
                                    'data' => ['confirm' => 'Are you sure you want to delete this teacher?', 'method' => 'post']
                                ]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="4">No teachers found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>