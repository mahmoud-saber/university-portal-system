<?php

namespace api\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Response;
use common\models\Grade;
use yii\rest\Controller;
use common\models\Course;
use yii\web\UploadedFile;
use common\models\Document;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;
use yii\web\BadRequestHttpException;
use common\models\CourseRegistration;
use yii\web\ServerErrorHttpException;


class StudentController extends Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'only' => [
                'student-courses',
                'register-course',
                'view',
                'grades',
                'update-student-course',
                'document','document',
                'answer','answer',
            ],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => [
                'student-courses',
                'register-course',
                'view',
                'grades',
                'update-student-course',
                'document',
                'answer'
            ],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => [
                        'student-courses',
                        'register-course',
                        'view',
                        'grades',
                        'update-student-course',
                        'document','answer',
                        

                    ],
                    'roles' => ['@'],
                    'matchCallback' => function () {
                        return Yii::$app->user->identity->role === 'student';
                    },
                ],
            ],
        ];

        return $behaviors;
    }



    public function actionStudentCourses()
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'student') {
            throw new ForbiddenHttpException('Only students can access this endpoint.');
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
                ['like', 'user.username', $search],
            ]);
        }

        $courses = $query->all();

        $data = [];

        foreach ($courses as $course) {
            $data[] = [
                'id' => $course->id,
                'name' => $course->name,
                'description' => $course->description,
                'teacher' => $course->teacher->username ?? 'N/A',
                'student' => Yii::$app->user->identity->username ?? 'N/A',
            ];
        }

        return [
            'success' => true,
            'count' => count($data),
            'data' => $data,
        ];
    }

    ///////////////////////////
    public function actionRegisterCourse()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'student') {
            throw new \yii\web\ForbiddenHttpException('Only students can register for courses.');
        }

        $studentId = Yii::$app->user->id;
        $body = Yii::$app->request->post();

        if (empty($body['course_id'])) {
            return [
                'success' => false,
                'message' => 'course_id is required.'
            ];
        }

        $existing = \common\models\CourseRegistration::findOne([
            'student_id' => $studentId,
            'course_id' => $body['course_id']
        ]);

        if ($existing) {
            return [
                'success' => false,
                'message' => 'You are already registered in this course.'
            ];
        }

        $model = new \common\models\CourseRegistration();
        $model->student_id = $studentId;
        $model->course_id = $body['course_id'];

        if ($model->save()) {
            $course = \common\models\Course::find()
                ->with('teacher')
                ->where(['id' => $model->course_id])
                ->one();

            return [
                'success' => true,
                'message' => 'Registered successfully.',
                'data' => [
                    'course_id' => $course->id,
                    'course_name' => $course->name,
                    'course_description' => $course->description,
                    'teacher_name' => $course->teacher->username ?? 'N/A',
                    'student' => Yii::$app->user->identity->username ?? 'N/A',

                ]
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to register.',
            'errors' => $model->errors
        ];
    }
    ////////////////////////
    public function actionView($id)
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'student') {
            throw new ForbiddenHttpException('Only students can access this endpoint.');
        }

        $course = Course::findOne($id);
        if (!$course) {
            throw new NotFoundHttpException('Course not found.');
        }

        $studentId = Yii::$app->user->id;

        $isRegistered = CourseRegistration::find()
            ->where(['student_id' => $studentId, 'course_id' => $id])
            ->exists();

        if (!$isRegistered) {
            throw new ForbiddenHttpException('You are not registered in this course.');
        }

        return [
            'success' => true,
            'data' => [
                'id' => $course->id,
                'name' => $course->name,
                'description' => $course->description,
                'teacher' => $course->teacher->username ?? 'N/A',
                'student' => Yii::$app->user->identity->username ?? 'N/A',

            ],
        ];
    }

    ////////////////update
    public function actionUpdateStudentCourse($student_id)
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'student') {
            throw new ForbiddenHttpException('Only students can access this endpoint.');
        }

        if (Yii::$app->user->id != $student_id) {
            throw new ForbiddenHttpException('You can only update your own registration.');
        }

        $body = Yii::$app->request->getBodyParams();

        if (empty($body['course_id']) || empty($body['old_course_id'])) {
            throw new BadRequestHttpException('Missing required parameters: old_course_id and course_id.');
        }

        $model = CourseRegistration::findOne([
            'student_id' => $student_id,
            'course_id' => $body['old_course_id'],
        ]);

        if (!$model) {
            throw new NotFoundHttpException('Registration not found.');
        }

        $model->course_id = $body['course_id'];

        if ($model->save()) {
            return [
                'success' => true,
                'message' => 'Course registration updated successfully.',
                'data' => [
                    'student_id' => $student_id,
                    'new_course_id' => $body['course_id'],
                ],
            ];
        }

        throw new ServerErrorHttpException('Failed to update course.');
    }
    /////////////////////////grads

    public function actionGrades()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'student') {
            throw new \yii\web\ForbiddenHttpException('Only students can access this endpoint.');
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

        $data = [];

        foreach ($registrations as $reg) {
            $grade = Grade::findOne([
                'student_id' => $reg->student_id,
                'course_id' => $reg->course_id,
            ]);

            $data[] = [
                'course_id' => $reg->course->id,
                'course_name' => $reg->course->name,
                'teacher' => $reg->course->teacher->username ?? 'N/A',
                'grade' => $grade ? $grade->grade : 'N/A',
            ];
        }

        return [
            'success' => true,
            'count' => count($data),
            'data' => $data,
        ];
    }
    /////////////////////////////////// index documents
    public function actionDocument()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            throw new \yii\web\UnauthorizedHttpException('You must be logged in.');
        }

        $userId = Yii::$app->user->id;
        $user = Yii::$app->user->identity;
        $documents = [];

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
            throw new \yii\web\ForbiddenHttpException('Access denied.');
        }

        $responseData = [];

        foreach ($documents as $doc) {
            $responseData[] = [
                'id' => $doc->id,
                'title' => $doc->original_name,
                'uploaded_by' => $doc->user->username ?? 'N/A',
                'file_url' => Yii::$app->request->hostInfo . '/uploads/',
                'created_at' => Yii::$app->formatter->asDatetime($doc->created_at),
            ];
        }

        return [
            'success' => true,
            'count' => count($responseData),
            'documents' => $responseData,
        ];
    }
    ///////////////////////////////answer
    
    public function actionAnswer()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new Document();

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post(), '');

            $model->user_id = Yii::$app->user->id;

            $uploadedFile = UploadedFile::getInstanceByName('file_path');

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
                    return ['status' => 'error', 'message' => 'فشل رفع الملف.'];
                }

                if ($model->save(false)) {
                    return [
                        'status' => 'success',
                        'message' => 'تم رفع الملف بنجاح.',
                        'data' => [
                            'id' => $model->id,
                            'file_name' => $model->original_name,
                            'file_url' => Url::to('@web/assignments/' . $uniqueName, true),
                            'file_type' => $model->file_type,
                            'uploaded_at' => date('Y-m-d H:i:s', strtotime($model->created_at)),
                        ]
                    ];
                } else {
                    return ['status' => 'error', 'message' => 'فشل في حفظ البيانات في قاعدة البيانات.'];
                }
            } else {
                return ['status' => 'error', 'message' => 'لم يتم اختيار ملف.'];
            }
        }

        return ['status' => 'error', 'message' => 'الطلب يجب أن يكون من نوع POST.'];
    }
}