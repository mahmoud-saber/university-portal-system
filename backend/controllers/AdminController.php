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
                            return \Yii::$app->user->identity->role === 'admin';
                        },
                    ],
                ],
            ],
        ];
    }
    //views admin/dashboard
    // This action renders the admin dashboard
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



    //get information admin
    public function actionProfile()
    {
        $adminId = Yii::$app->user->id;
        $admin = User::findOne($adminId);
        return $this->render('profile', ['admin' => $admin]);
    }


    //views teachers/index
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

    //views teachers/create
    // This action creates a new teacher user
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
    //views teachers/update
    // This action updates an existing teacher user
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
    // delete teacher
    // This action deletes a teacher user
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


    ///////////////////////////////////////////////////////////students
    //views teachers/index
    public function actionIndex_student()
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

    //views teachers/create
    // This action creates a new teacher user
    public function actionCreate_student()
    {
        $model = new User();
        $model->role = 'student';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Student created successfully.');
            return $this->redirect(['index_student']);
        }

        return $this->render('student_create', ['model' => $model]);
    }
    // This action updates an existing student user
    public function actionUpdate_student($id)
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
    //delete student
    // This action deletes a teacher user
    public function actionDelete_student($id)
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

    public function actionIndex_course()
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


    public function actionCreate_course()
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

    public function actionUpdate_course($id)
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


    public function actionDelete_course($id)
    {
        $model = Course::findOne($id);
        if ($model) {
            $model->delete();
        }
        Yii::$app->session->setFlash('danger', 'Course deleted successfully.');
        return $this->redirect(['index_course']);
    }
}