<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head(); ?>

    <!-- Bootstrap + FontAwesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= Yii::getAlias('@web') ?>/css/syle-admin.css">
</head>

<body>
    <?php $this->beginBody(); ?>
    <div class="sidebar">
        <div class="brand">
            <i class="fas fa-user-shield"></i> Student Panel
        </div>
        <a href="<?= \yii\helpers\Url::to(['student/dashboard']) ?>"><i class="fas fa-home"></i> Dashboard</a>
        <a href="<?= \yii\helpers\Url::to(['student/profile']) ?>">
            <i class="fas fa-book-open"></i> Profile</a>
        <a href="<?= \yii\helpers\Url::to(['student/index']) ?>">
            <i class="fas fa-book-open"></i> Courses</a>
        <a href="<?= \yii\helpers\Url::to(['student/grades']) ?>">
            <i class="fas fa-clipboard-list"></i> Grades</a>
        <a href="<?= \yii\helpers\Url::to(['student/document']) ?>"><i class="fas fa-book-open"></i> document</a>

        <!-- Logout button as form -->
        <a> <?= Html::beginForm(['site/logout'], 'post') ?> <?= Html::submitButton(
                                                                '<i class="fas fa-sign-out-alt"></i> Logout',
                                                                ['class' => 'btn btn-link text-left', 'style' => 'color:#ccc; padding-left: 0; text-decoration: none;']
                                                            ) ?> <?= Html::endForm() ?>
        </a>
    </div>


    <div class="content">
        <div class="topbar">
            <?= Html::encode($this->title) ?>
        </div>

        <?= Breadcrumbs::widget([
            'links' => $this->params['breadcrumbs'] ?? [],
        ]) ?>

        <?= $content ?>
    </div>
    <footer class="footer bg-light text-center py-3 mt-4">
        <div class="container">
            <span class="text-muted">&copy; <?= date('Y') ?> University Portal System. All rights reserved.</span>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php $this->endBody(); ?>
</body>

</html>
<?php $this->endPage(); ?>