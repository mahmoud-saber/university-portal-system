<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = '📚 Courses';
?>

<div class="course-index container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><?= Html::encode($this->title) ?></h3>
        <?= Html::a('➕ Add Course', ['create_course'], ['class' => 'btn btn-success']) ?>
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



    <!-- 🔍 Search Form -->
    <form method="get" action="<?= Url::to(['index_course']) ?>" class="form-inline mb-3">
        <input type="text" name="q" value="<?= Html::encode(Yii::$app->request->get('q')) ?>" class="form-control mr-2"
            placeholder="Search by course name">
        <button type="submit" class="btn btn-primary">🔍 Search</button>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Teacher</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($courses)): ?>
                <?php foreach ($courses as $course): ?>
                <tr>
                    <td><?= Html::encode($course->name) ?></td>
                    <td><?= Html::encode($course->description) ?></td>
                    <td><?= Html::encode($course->teacher->username) ?></td>
                    <td><?= Yii::$app->formatter->asDatetime($course->created_at) ?></td>
                    <td>
                        <?= Html::a('<i class="fas fa-edit"></i>', ['update_course', 'id' => $course->id], [
                                    'class' => 'btn btn-sm btn-primary',
                                    'title' => 'Edit',
                                ]) ?>
                        <?= Html::a('<i class="fas fa-trash"></i>', ['delete_course', 'id' => $course->id], [
                                    'class' => 'btn btn-sm btn-danger',
                                    'title' => 'Delete',
                                     
                                ]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">No courses found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>