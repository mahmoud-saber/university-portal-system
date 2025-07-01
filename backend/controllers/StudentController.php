<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use yii\web\Controller;
use common\models\Grade;
use common\models\Course;
use yii\web\UploadedFile;
use common\models\Document;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use common\models\CourseRegistration;

class StudentController extends Controller
{
    public $layout = 'student';

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
                            return \Yii::$app->user->identity->role === 'student';
                        },
                    ],
                ],
            ],
        ];
    }
    public function actionProfile()
    {
        $studentId = Yii::$app->user->id;
        $student = User::findOne($studentId);
        return $this->render('profile', ['student' => $student]);
    }


    public function actionDashboard()
    {
        $courseCount  = Course::find()->count();

        return $this->render('dashboard', [
            'courseCount' => $courseCount,
        ]);
    }

    public function actionIndex()
    {
         if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'student') {
            throw new ForbiddenHttpException('Only students can access this page.');
        }

        $studentId = Yii::$app->user->id;
        $search = Yii::$app->request->get('q');

        $query = Course::find()
            ->joinWith(['students', 'teacher'])
            ->distinct()
            ->where(['course_registration.student_id' => $studentId]);

        if (!empty($search)) {
            $query->andFilterWhere([
                'or',
                ['like', 'course.name', $search],
                ['like', 'course.description', $search],
                ['like', 'user.username', $search]    
            ]);
        }

        $courses = $query->all();

        return $this->render('index_studentcourse', [
            'courses' => $courses,
            'search' => $search,
        ]);
    }


    // create student_courses /
    public function actionCreate()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'student') {
            throw new ForbiddenHttpException('Only students can access this page.');
        }

        $model = new CourseRegistration();

         $model->student_id = Yii::$app->user->id;

         $courses = Course::find()->all();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', '✅ You have been registered in the course.');
            return $this->redirect(['index']);
        }

        return $this->render('create_studentcourse', [
            'model' => $model,
            'courses' => $courses,
        ]);
    }


    public function actionView($id)
    {
        $course = Course::findOne($id);

        if (!$course) {
            throw new NotFoundHttpException('Course not found.');
        }

        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'student') {
            throw new ForbiddenHttpException('Only students can access this page.');
        }

        $studentId = Yii::$app->user->id;

        $isRegistered = CourseRegistration::find()
            ->where(['student_id' => $studentId, 'course_id' => $id])
            ->exists();

        if (!$isRegistered) {
            throw new ForbiddenHttpException('You are not registered in this course.');
        }

        return $this->render('view_studentcourse', [
            'model' => $course,
        ]);
    }
    ///////update student_course

    public function actionUpdate($student_id, $course_id)
    {
        $model = CourseRegistration::findOne([
            'student_id' => $student_id,
            'course_id' => $course_id,
        ]);

        if (!$model) {
            throw new NotFoundHttpException('Registration not found.');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Student course registration updated successfully.');
            return $this->redirect(['index']);
        }

        $students = User::find()->where(['role' => 'student'])->all();
        $courses = Course::find()->all();

        return $this->render('update_studentcourse', [
            'model' => $model,
            'students' => $students,
            'courses' => $courses,
        ]);
    }
    ///////////Grades

    public function actionGrades()
    {
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $studentId = Yii::$app->user->id;

        $query = CourseRegistration::find()
            ->joinWith(['course.teacher']) 
            ->where(['student_id' => $studentId]);

        $search = Yii::$app->request->get('q');
        if (!empty($search)) {
            $query->andFilterWhere([
                'or',
                ['like', 'course.name', $search],
                ['like', 'user.username', $search],
            ]);
        }

        $registrations = $query->orderBy(['created_at' => SORT_DESC])->all();

        foreach ($registrations as $reg) {
            $grade = Grade::find()
                ->where([
                    'student_id' => $reg->student_id,
                    'course_id' => $reg->course_id,
                ])
                ->one();

             $reg->grade_value = $grade ? $grade->grade : 'N/A';
        }

        return $this->render('grades_student', [
            'registrations' => $registrations,
            'search' => $search,
        ]);
    }

    //////////////Document
    public function actionDocument()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $userId = Yii::$app->user->id;
        $user = Yii::$app->user->identity;

        if ($user->role === 'student') {

             $courseIds = CourseRegistration::find()
                ->select('course_id')
                ->where(['student_id' => $userId])
                ->column();

             $teacherIds = Course::find()
                ->select('teacher_id')
                ->where(['id' => $courseIds])
                ->column();

             $documents = Document::find()
                ->where(['user_id' => $teacherIds])
                ->orderBy(['created_at' => SORT_DESC])
                ->all();
        } elseif ($user->role === 'teacher') {

             $courseIds = Course::find()
                ->select('id')
                ->where(['teacher_id' => $userId])
                ->column();

             $documents = Document::find()
                ->where(['course_id' => $courseIds])
                ->orderBy(['created_at' => SORT_DESC])
                ->all();
        } else {
            throw new ForbiddenHttpException('Access denied.');
        }

        return $this->render('index_document', [
            'documents' => $documents,
        ]);
    }
    public function actionAnswer()
    {
        $model = new Document();

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $model->user_id = Yii::$app->user->id;

            $uploadedFile = UploadedFile::getInstance($model, 'file_path');

            if ($uploadedFile) {
                 $model->original_name = $uploadedFile->name;

                 $uniqueName = uniqid() . '.' . $uploadedFile->extension;
                $uploadPath = Yii::getAlias('@webroot/assignments/') . $uniqueName;

                 if ($uploadedFile->saveAs($uploadPath)) {
                    $model->file_path = 'assignments/' . $uniqueName;
                    $model->file_type = $uploadedFile->type;
                    $model->created_at = date('Y-m-d H:i:s');
                    $model->updated_at = date('Y-m-d H:i:s');
                } else {
                    Yii::$app->session->setFlash('error', 'File upload failed.');
                    return $this->redirect(['create']);
                }

                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'File uploaded successfully.');
                    return $this->redirect(['document']);
                } else {
                    Yii::$app->session->setFlash('error', 'Failed to save document in DB.');
                }
            } else {
                Yii::$app->session->setFlash('error', 'No file selected.');
            }
        }

        return $this->render('answer', [
            'model' => $model,
        ]);
    }
}