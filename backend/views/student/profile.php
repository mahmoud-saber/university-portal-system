<?php

use yii\helpers\Html;

$this->title = '👤 Student Profile';
?>

<div class="student-profile container mt-4">
    <div class="card shadow-sm border-primary">
        <div class="card-header bg-primary text-white">
            <h4><?= Html::encode($this->title) ?></h4>
        </div>

        <div class="card-body">
            <table class="table table-bordered">

                <tr>
                    <th>Username</th>
                    <td><?= Html::encode($student->username) ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?= Html::encode($student->email) ?></td>
                </tr>
                <tr>
                    <th>Role</th>
                    <td><?= Html::encode($student->role) ?></td>
                </tr>
                <tr>
                    <th>Created At</th>
                    <td><?= Yii::$app->formatter->asDatetime($student->created_at) ?></td>
                </tr>
                <tr>
                    <th>Updated At</th>
                    <td><?= Yii::$app->formatter->asDatetime($student->updated_at) ?></td>
                </tr>
            </table>


        </div>
    </div>
</div>