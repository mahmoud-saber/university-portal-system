<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use yii\web\Controller;
use common\models\Grade;
use common\models\Course;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
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

    ////////////////////////////////////////////////
    public function actionProfile()
    {
        $teacherId = Yii::$app->user->id;
        $teacher = User::findOne($teacherId);
        return $this->render('profile', ['teacher' => $teacher]);
    }

    /////////////////////////////////////////////////////index

    public function actionIndex()
    {
        // تأكد أن المستخدم الحالي هو معلم
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'teacher') {
            throw new \yii\web\ForbiddenHttpException('Only teachers can access this page.');
        }

        $teacherId = Yii::$app->user->id;
        $search = Yii::$app->request->get('q');

        // جلب التسجيلات التي تخص الكورسات التي يدرّسها المعلم
        $query = CourseRegistration::find()
            ->joinWith(['student', 'course.teacher']) // تأكد من توفر العلاقات: student و course
            ->where(['course.teacher_id' => $teacherId]);

        if (!empty($search)) {
            $query->andFilterWhere([
                'or',
                ['like', 'user.username', $search],        
                ['like', 'course.name', $search],         
                ['like', 'course.description', $search],  //  الكورس
            ]);
        }

        $registrations = $query->all();

        // جلب الدرجات بشكل غير مباشر وربطها مع كل تسجيل
        foreach ($registrations as $reg) {
            $grade = \common\models\Grade::find()
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
    /////////////////////////////////////////view 
    public function actionView($id)
    {
        $model = CourseRegistration::findOne($id);
        if (!$model || $model->course->teacher_id !== Yii::$app->user->id) {
            throw new \yii\web\ForbiddenHttpException('Access denied.');
        }

        $gradeModel = \common\models\Grade::findOne([
            'student_id' => $model->student_id,
            'course_id' => $model->course_id,
        ]);

        return $this->render('view_teacher_students', [
            'model' => $model,
            'gradeModel' => $gradeModel,
        ]);
    }


    ///////////////////////////////////////////create
    public function actionCreate()
    {
        // تأكد أن المستخدم الحالي هو مدرس
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'teacher') {
            throw new \yii\web\ForbiddenHttpException('Only teachers can access this page.');
        }

        $teacherId = Yii::$app->user->id;
        $model = new CourseRegistration();
        $gradeModel = new \common\models\Grade();

        // جلب الطلاب المسجلين فقط في كورسات المدرس الحالي
        $students = CourseRegistration::find()
            ->joinWith(['student', 'course'])
            ->where(['course.teacher_id' => $teacherId])
            ->select(['user.id', 'user.username'])
            ->groupBy(['user.id'])
            ->asArray()
            ->all();

        $studentList = \yii\helpers\ArrayHelper::map($students, 'id', 'username');

        // جلب الكورسات الخاصة بالمدرس فقط
        $courses = Course::find()
            ->where(['teacher_id' => $teacherId])
            ->select(['name', 'id'])
            ->indexBy('id')
            ->column();

        // معالجة النموذج
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
    //////////////////////////////////////////update
    public function actionUpdate($id)
    {
        // التأكد أن المستخدم الحالي هو مدرس
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'teacher') {
            throw new \yii\web\ForbiddenHttpException('Only teachers can access this page.');
        }

        $teacherId = Yii::$app->user->id;

        // جلب نموذج تسجيل الطالب باستخدام الـ ID
        $model = CourseRegistration::findOne($id);
        if (!$model) {
            throw new \yii\web\NotFoundHttpException('Registration not found.');
        }

        // تأكد أن الكورس يعود للمدرس الحالي
        if ($model->course->teacher_id !== $teacherId) {
            throw new \yii\web\ForbiddenHttpException('You do not have permission to update this registration.');
        }

        // جلب نموذج الدرجة المرتبط بنفس الطالب والكورس
        $gradeModel = Grade::findOne([
            'student_id' => $model->student_id,
            'course_id' => $model->course_id,
        ]);

        if (!$gradeModel) {
            $gradeModel = new Grade();
            $gradeModel->student_id = $model->student_id;
            $gradeModel->course_id = $model->course_id;
        }

        // جلب الطلاب والكورسات الخاصة بالمدرس
        $students = CourseRegistration::find()
            ->joinWith(['student', 'course'])
            ->where(['course.teacher_id' => $teacherId])
            ->select(['user.id', 'user.username'])
            ->groupBy(['user.id'])
            ->asArray()
            ->all();

        $studentList = \yii\helpers\ArrayHelper::map($students, 'id', 'username');

        $courses = Course::find()
            ->where(['teacher_id' => $teacherId])
            ->select(['name', 'id'])
            ->indexBy('id')
            ->column();

        // معالجة التحديث
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

//////////////////////////////////////////////////////doucment




    ///////////////////////////////////////delete
    public function actionDelete($id)
    {
        $model = \common\models\CourseRegistration::findOne($id);

        if ($model === null) {
            throw new \yii\web\NotFoundHttpException('التسجيل المطلوب غير موجود.');
        }

        $model->delete();

        Yii::$app->session->setFlash('success', 'تم حذف التسجيل بنجاح.');
        return $this->redirect(['index']);
    }
}