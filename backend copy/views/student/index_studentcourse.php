<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = '📚 Courses with Students';
?>

<div class="container mt-5 course-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><?= Html::encode($this->title) ?></h3>
        <?= Html::a('➕ Add Student Course', ['create'], ['class' => 'btn btn-success']) ?>
    </div>

    <!-- ✅ Flash Message -->
    <?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= Yii::$app->session->getFlash('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- 🔍 Search Form -->
    <form method="get" action="<?= Url::to(['index']) ?>" class="row g-2 mb-4">
        <div class="col-md-6">
            <input type="text" name="q" value="<?= Html::encode($search ?? '') ?>" class="form-control"
                placeholder="🔍 Search by course or student name">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </form>

    <!-- 📋 Table -->
    <div class="table-responsive">
        <table class="table table-bordered  align-middle text-nowrap ">
            <thead class="table-dark">
                <tr class="text-center">
                    <th>📘 Course</th>
                    <th>📝 Description</th>
                    <th>👨‍🎓 Students</th>
                    <th>👨‍🏫 Teacher</th>
                    <th>🕒 Created At</th>
                    <th>⚙️ Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($courses)): ?>
                <?php foreach ($courses as $course): ?>
                <tr>
                    <td><?= Html::encode($course->name) ?></td>
                    <td><?= Html::encode($course->description) ?></td>
                    <td>
                        <?php if (!empty($course->students)): ?>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($course->students as $student): ?>
                            <li class="d-flex justify-content-between align-items-center">
                                <span><?= Html::encode($student->username) ?></span>

                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                        <em class="text-muted">No students enrolled</em>
                        <?php endif; ?>
                    </td>
                    <td><?= Html::encode($course->teacher->username ?? 'N/A') ?></td>
                    <td><?= Yii::$app->formatter->asDatetime($course->created_at) ?></td>
                    <td class="text-center">
                        <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $course->id], [
                                    'class' => 'btn btn-sm btn-outline-secondary',
                                    'title' => 'View Course',
                                ]) ?>
                        <span>
                            <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'student_id' => $student->id, 'course_id' => $course->id], [
                                        'class' => 'btn btn-sm btn-outline-primary me-1',
                                        'title' => 'Edit Student Course',
                                    ]) ?>

                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center text-muted">No courses found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>