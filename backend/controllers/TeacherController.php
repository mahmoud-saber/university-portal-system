<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use yii\web\Controller;
use common\models\Grade;
use common\models\Course;
 use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use common\models\CourseRegistration;

class TeacherController extends Controller
{
    public $layout = 'teacher';

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
                            return \Yii::$app->user->identity->role === 'teacher';
                        },
                    ],
                ],
            ],
        ];
    }

    public function actionDashboard()
    {
        $studentCount = User::find()->where(['role' => 'student'])->count();
        return $this->render('dashboard',['studentCount' => $studentCount]);
    }

    
    public function actionProfile()
    {
        $teacherId = Yii::$app->user->id;
        $teacher = User::findOne($teacherId);
        return $this->render('profile', ['teacher' => $teacher]);
    }


    public function actionIndex()
    {
         if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'teacher') {
            throw new ForbiddenHttpException('Only teachers can access this page.');
        }

        $teacherId = Yii::$app->user->id;
        $search = Yii::$app->request->get('q');

         $query = CourseRegistration::find()
            ->joinWith(['student', 'course.teacher']) 
            ->where(['course.teacher_id' => $teacherId]);

        if (!empty($search)) {
            $query->andFilterWhere([
                'or',
                ['like', 'user.username', $search],        
                ['like', 'course.name', $search],         
                ['like', 'course.description', $search], 
            ]);
        }

        $registrations = $query->all();

        // جلب الدرجات بشكل غير مباشر وربطها مع كل تسجيل
        foreach ($registrations as $reg) {
            $grade =Grade::find()
                ->where([
                    'student_id' => $reg->student_id,
                    'course_id' => $reg->course_id,
                ])
                ->one();

            // حفظ الدرجة في خاصية مؤقتة للعرض في الـ View
            $reg->grade_value = $grade ? $grade->grade : 'N/A';
        }

        return $this->render('index_teacher_students', [
            'registrations' => $registrations,
            'search' => $search,
        ]);
    }

     public function actionCreate()
    {
         if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'teacher') {
            throw new ForbiddenHttpException('Only teachers can access this page.');
        }

        $teacherId = Yii::$app->user->id;
        $model = new CourseRegistration();
        $gradeModel = new Grade();

         $students = CourseRegistration::find()
            ->joinWith(['student', 'course'])
            ->where(['course.teacher_id' => $teacherId])
            ->select(['user.id', 'user.username'])
            ->groupBy(['user.id'])
            ->asArray()
            ->all();

        $studentList = ArrayHelper::map($students, 'id', 'username');

         $courses = Course::find()
            ->where(['teacher_id' => $teacherId])
            ->select(['name', 'id'])
            ->indexBy('id')
            ->column();

         if ($model->load(Yii::$app->request->post()) && $gradeModel->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    $gradeModel->student_id = $model->student_id;
                    $gradeModel->course_id = $model->course_id;
                    if ($gradeModel->save()) {
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'Student registered with grade.');
                        return $this->redirect(['index']);
                    }
                }
                $transaction->rollBack();
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        return $this->render('create_teacher_students', [
            'model' => $model,
            'gradeModel' => $gradeModel,
            'students' => $studentList,
            'courses' => $courses,
        ]);
    }
     public function actionView($id)
    {
        $model = CourseRegistration::findOne($id);
        if (!$model || $model->course->teacher_id !== Yii::$app->user->id) {
            throw new ForbiddenHttpException('Access denied.');
        }

        $gradeModel = Grade::findOne([
            'student_id' => $model->student_id,
            'course_id' => $model->course_id,
        ]);

        return $this->render('view_teacher_students', [
            'model' => $model,
            'gradeModel' => $gradeModel,
        ]);
    }


   
    public function actionUpdate($id)
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'teacher') {
            throw new ForbiddenHttpException('Only teachers can access this page.');
        }

        $teacherId = Yii::$app->user->id;

        $model = CourseRegistration::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Registration not found.');
        }

        if ($model->course->teacher_id !== $teacherId) {
            throw new ForbiddenHttpException('You do not have permission to update this registration.');
        }

        $gradeModel = Grade::findOne([
            'student_id' => $model->student_id,
            'course_id' => $model->course_id,
        ]);

        if (!$gradeModel) {
            $gradeModel = new Grade();
            $gradeModel->student_id = $model->student_id;
            $gradeModel->course_id = $model->course_id;
        }

        $students = CourseRegistration::find()
            ->joinWith(['student', 'course'])
            ->where(['course.teacher_id' => $teacherId])
            ->select(['user.id', 'user.username'])
            ->groupBy(['user.id'])
            ->asArray()
            ->all();

        $studentList = ArrayHelper::map($students, 'id', 'username');

        $courses = Course::find()
            ->where(['teacher_id' => $teacherId])
            ->select(['name', 'id'])
            ->indexBy('id')
            ->column();

        if ($model->load(Yii::$app->request->post()) && $gradeModel->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    $gradeModel->student_id = $model->student_id;
                    $gradeModel->course_id = $model->course_id;
                    if ($gradeModel->save()) {
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'Registration and grade updated.');
                        return $this->redirect(['index']);
                    }
                }
                $transaction->rollBack();
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        return $this->render('update_teacher_students', [
            'model' => $model,
            'gradeModel' => $gradeModel,
            'students' => $studentList,
            'courses' => $courses,
        ]);
    }

    public function actionDelete($id)
    {
        $model = CourseRegistration::findOne($id);

        if ($model === null) {
            throw new NotFoundHttpException('التسجيل المطلوب غير موجود.');
        }

        $model->delete();

        Yii::$app->session->setFlash('success', 'delete sucessful');
        return $this->redirect(['index']);
    }
}