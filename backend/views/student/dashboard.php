<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $teacherCount */
/** @var int $studentCount */
/** @var int $courseCount */

$this->title = 'Dashboard';
?>

<?php
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css');
?>

<div class="container py-4">
    <h2 class="mb-4">Welcome, <?= Html::encode(Yii::$app->user->identity->username) ?> 👋</h2>

    <div class="row g-4">



        <!-- Courses -->
        <div class="col-md-4">
            <div class="card border-0 shadow rounded-4 bg-info text-white h-100">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="bi bi-journal-bookmark-fill fs-1"></i>
                    </div>
                    <h5 class="card-title">Total Courses</h5>
                    <p class="display-5">
                        <a href="<?= Url::to(['course/index']) ?>" class="text-white text-decoration-none">
                            <?= $courseCount ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>