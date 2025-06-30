<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use common\models\Course;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;

class AdminController extends Controller
{
    public $layout = 'admin';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            return Yii::$app->user->identity->role === 'admin';
                        },
                    ],
                ],
            ],
        ];
    }
    public function actionDashboard()
    {
        $teacherCount = User::find()->where(['role' => 'teacher'])->count();
        $studentCount = User::find()->where(['role' => 'student'])->count();
        $courseCount  = Course::find()->count();

        return $this->render('dashboard', [
            'teacherCount' => $teacherCount,
            'studentCount' => $studentCount,
            'courseCount' => $courseCount,
        ]);
    }

    ///////////Techer/////////////////

    public function actionProfile()
    {
        $adminId = Yii::$app->user->id;
        $admin = User::findOne($adminId);
        return $this->render('profile', ['admin' => $admin]);
    }


    public function actionIndex()
    {
        $query = User::find()->where(['role' => 'teacher']);

        $search = Yii::$app->request->get('q');
        if (!empty($search)) {
            $query->andFilterWhere([
                'or',
                ['like', 'username', $search],
                ['like', 'email', $search],
            ]);
        }

        $teachers = $query->orderBy(['id' => SORT_ASC])->all();

        return $this->render('teacher_index', [
            'teachers' => $teachers,
        ]);
    }

    public function actionCreate()
    {
        $model = new User();
        $model->role = 'teacher';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Teacher created successfully.');
            return $this->redirect(['index']);
        }

        return $this->render('teacher_create', ['model' => $model]);
    }
    public function actionUpdate($id)
    {
        $model = User::findOne($id);
        if (!$model || $model->role !== 'teacher') {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('update', 'Teacher updated successfully.');
            return $this->redirect(['index']);
        }

        return $this->render('teacher_update', ['model' => $model]);
    }
    public function actionDelete($id)
    {
        $model = User::findOne($id);
        if (!$model || $model->role !== 'teacher') {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $model->delete();
        Yii::$app->session->setFlash('danger', 'Teacher deleted successfully.');
        return $this->redirect(['index']);
    }


    /////students////////////////////////
    public function actionIndexStudent()
    {
        $query = User::find()->where(['role' => 'student']);

        $search = Yii::$app->request->get('q');
        if (!empty($search)) {
            $query->andFilterWhere([
                'or',
                ['like', 'username', $search],
                ['like', 'email', $search],
            ]);
        }

        $students = $query->orderBy(['id' => SORT_ASC])->all();

        return $this->render('student_index', [
            'students' => $students,
        ]);
    }

    public function actionCreateStudent()
    {
        $model = new User();
        $model->role = 'student';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Student created successfully.');
            return $this->redirect(['index_student']);
        }

        return $this->render('student_create', ['model' => $model]);
    }
    public function actionUpdateStudent($id)
    {
        $model = User::findOne($id);
        if (!$model || $model->role !== 'student') {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('update', 'Student updated successfully.');
            return $this->redirect(['index_student']);
        }

        return $this->render('student_update', ['model' => $model]);
    }
    public function actionDeleteStudent($id)
    {
        $model = User::findOne($id);
        if (!$model || $model->role !== 'student') {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $model->delete();
        Yii::$app->session->setFlash('success', 'Student deleted successfully.');
        Yii::$app->session->setFlash('danger', 'Student deleted successfully.');
        return $this->redirect(['index_student']);
    }

    /////////////////////////// Courses //////////////////////////

    public function actionIndexCourse()
    {
        $query = Course::find();
        $search = Yii::$app->request->get('q');
        if (!empty($search)) {
            $query->andFilterWhere([
                'or',
                ['like', 'name', $search],
            ]);
        }
        $courses = $query->orderBy(['id' => SORT_ASC])->all();
        return $this->render('course_index', ['courses' => $courses]);
    }


    public function actionCreateCourse()
    {
        $model = new Course();

        $teachers = ArrayHelper::map(
            User::find()->where(['role' => 'teacher'])->all(),
            'id',
            'username'
        );

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Student created successfully.');
            return $this->redirect(['index_course']);
        } else {
            Yii::error($model->getErrors());
        }
        return $this->render('course_create', [
            'model' => $model,
            'teachers' => $teachers,
        ]);
    }

    public function actionUpdateCourse($id)
    {
        $model = Course::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $teachers = ArrayHelper::map(
            User::find()->where(['role' => 'teacher'])->all(),
            'id',
            'username'
        );

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('update', 'Course updated successfully.');
            return $this->redirect(['index_course']);
        }

        return $this->render('course_update', [
            'model' => $model,
            'teachers' => $teachers,
        ]);
    }


    public function actionDeleteCourse($id)
    {
        $model = Course::findOne($id);
        if ($model) {
            $model->delete();
        }
        Yii::$app->session->setFlash('danger', 'Course deleted successfully.');
        return $this->redirect(['index_course']);
    }
}