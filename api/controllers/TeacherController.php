<?php

namespace api\controllers;

use Yii;
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
use common\models\CourseRegistration;
use yii\web\ServerErrorHttpException;


class TeacherController extends Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'only' => ['index', 'view', 'delete', 
            'update', 'create-document', 
            'index-document', 'updat-document','delete-document'],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => ['index', 'view', 'delete', 'update',
             'create-document',
              'index-document', 'updat-document','delete-document'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => [
                        'index',
                        'view',
                        'delete',
                        'update',
                        ////////////////////
                        'index-document',
                        'create-document',
                        'updat-document',
                        'delete-document'
                    ],
                    'roles' => ['@'],
                    'matchCallback' => function () {
                        return Yii::$app->user->identity->role === 'teacher';
                    },
                ],
            ],
        ];

        return $behaviors;
    }


    public function actionIndex()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'teacher') {
            throw new \yii\web\ForbiddenHttpException('Only teachers can access this page.');
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

        $data = [];
        foreach ($registrations as $reg) {
            $grade = Grade::find()
                ->where([
                    'student_id' => $reg->student_id,
                    'course_id' => $reg->course_id,
                ])
                ->one();

            $data[] = [
                'student_name' => $reg->student->username ?? '',
                'course_name' => $reg->course->name ?? '',
                'course_description' => $reg->course->description ?? '',
                'grade' => $grade ? $grade->grade : 'N/A',
            ];
        }

        return [
            'success' => true,
            'count' => count($data),
            'data' => $data,
        ];
    }
    //////////////////////////////view//enter id of course_registration
    public function actionView($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $model = CourseRegistration::findOne($id);
        if (!$model || $model->course->teacher_id !== Yii::$app->user->id) {
            throw new ForbiddenHttpException('Access denied.');
        }

        $gradeModel = Grade::findOne([
            'student_id' => $model->student_id,
            'course_id' => $model->course_id,
        ]);

        return [
            'success' => true,
            'data' => [
                'registration_id' => $model->id,
                'student_id' => $model->student_id,
                'student_name' => $model->student->username ?? '',
                'course_id' => $model->course_id,
                'course_name' => $model->course->name ?? '',
                'teacher_name' => $model->course->teacher->username ?? '',
                'grade' => $gradeModel ? $gradeModel->grade : 'N/A',
                'created_at' => Yii::$app->formatter->asDatetime($model->created_at),
            ]
        ];
    }
    /////////////////////////////delete//enter id of course_registration
    public function actionDelete($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $model = CourseRegistration::findOne($id);

        if ($model === null) {
            throw new \yii\web\NotFoundHttpException('التسجيل المطلوب غير موجود.');
        }

        // تحقق أن المستخدم Teacher وهو صاحب الدورة
        if (!Yii::$app->user->identity || Yii::$app->user->identity->role !== 'teacher') {
            throw new \yii\web\ForbiddenHttpException('هذا الإجراء مخصص للمعلمين فقط.');
        }

        if (!isset($model->course) || $model->course->teacher_id !== Yii::$app->user->id) {
            throw new \yii\web\ForbiddenHttpException('غير مصرح لك بحذف هذا التسجيل.');
        }

        if ($model->delete() !== false) {
            return [
                'success' => true,
                'message' => 'تم حذف التسجيل بنجاح.',
            ];
        } else {
            throw new \yii\web\ServerErrorHttpException('حدث خطأ أثناء حذف التسجيل.');
        }
    }

    //////////////////////////////////update
    public function actionUpdate($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'teacher') {
            throw new \yii\web\ForbiddenHttpException('هذا الإجراء مخصص للمعلمين فقط.');
        }

        $teacherId = Yii::$app->user->id;

        $model = CourseRegistration::findOne($id);
        if (!$model) {
            throw new \yii\web\NotFoundHttpException('التسجيل غير موجود.');
        }

        if (!$model->course || $model->course->teacher_id !== $teacherId) {
            throw new \yii\web\ForbiddenHttpException('غير مصرح لك بتحديث هذا التسجيل.');
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

        $data = Yii::$app->request->post();

        if ($model->load($data, '') && $gradeModel->load($data, '')) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    $gradeModel->student_id = $model->student_id;
                    $gradeModel->course_id = $model->course_id;

                    if ($gradeModel->save()) {
                        $transaction->commit();
                        return [
                            'success' => true,
                            'message' => 'تم تحديث التسجيل والدرجة بنجاح.',
                            'data' => [
                                'registration_id' => $model->id,
                                'student_id' => $model->student_id,
                                'course_id' => $model->course_id,
                                'grade' => $gradeModel->grade,
                            ],
                        ];
                    }
                }
                $transaction->rollBack();
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        return [
            'success' => false,
            'errors' => [
                'registration' => $model->getErrors(),
                'grade' => $gradeModel->getErrors(),
            ],
        ];
    }
    ////////////////////////doc
    public function actionIndexDocument()
    {
        $userId = Yii::$app->user->id;

        $documents = Document::find()
            ->where(['user_id' => $userId])
            ->orderBy(['created_at' => SORT_DESC])
            ->asArray()
            ->all();

        return ['success' => true, 'documents' => $documents];
    }
    //////////////////////////////////////////////create
    public function actionCreateDocument()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $model = new Document();
        $model->user_id = Yii::$app->user->id;

        $uploadedFile = UploadedFile::getInstanceByName('file_path');
        if (!$uploadedFile) {
            return ['success' => false, 'message' => 'No file uploaded.'];
        }

        $model->original_name = $uploadedFile->name;
        $uniqueName = uniqid() . '.' . $uploadedFile->extension;
        $uploadPath = Yii::getAlias('@webroot/upload/') . $uniqueName;

        if ($uploadedFile->saveAs($uploadPath)) {
            $model->file_path = 'upload/' . $uniqueName;
            $model->file_type = $uploadedFile->type;
            $model->created_at = date('Y-m-d H:i:s');
            $model->updated_at = date('Y-m-d H:i:s');

            if ($model->save(false)) {
                return [
                    'success' => true,
                    'message' => 'File uploaded successfully.',
                    'document' => [
                        'id' => $model->id,
                        'original_name' => $model->original_name,
                        'file_path' => Yii::$app->request->hostInfo . '/' . $model->file_path,
                        'file_type' => $model->file_type,
                        'created_at' => date('Y-m-d H:i:s', $model->created_at)

                    ]
                ];
            }
            return ['success' => false, 'message' => 'Failed to save document in database.'];
        }

        return ['success' => false, 'message' => 'Failed to save file on server.'];
    }
    //////////////////////////////////////
    public function actionUpdateDocument($id)
    {
        $model = Document::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Document not found.');
        }

        $oldPath = Yii::getAlias('@webroot/') . $model->file_path;
        $uploadedFile = UploadedFile::getInstanceByName('file_path');

        if ($uploadedFile) {
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }

            $model->original_name = $uploadedFile->name;
            $uniqueName = uniqid() . '.' . $uploadedFile->extension;
            $uploadPath = Yii::getAlias('@webroot/uploads/') . $uniqueName;

            if ($uploadedFile->saveAs($uploadPath)) {
                $model->file_path = 'uploads/' . $uniqueName;
                $model->file_type = $uploadedFile->type;
                $model->updated_at = date('Y-m-d H:i:s');
            } else {
                return ['success' => false, 'message' => 'Failed to save new file.'];
            }
        }

        if ($model->save(false)) {
            return ['success' => true, 'message' => 'Document updated.', 'document' => $model];
        }

        return ['success' => false, 'message' => 'Failed to update document.'];
    }

    public function actionDeleteDocument($id)
    {
        $model = Document::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Document not found.');
        }

        $filePath = Yii::getAlias('@webroot/') . $model->file_path;
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        $model->delete();

        return ['success' => true, 'message' => 'Document deleted.'];
    }

    public function actionAssignment()
    {
        if (Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException('Login required.');
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
                ->asArray()
                ->all();
        } elseif ($user->role === 'teacher') {
            $courseIds = Course::find()
                ->select('id')
                ->where(['teacher_id' => $userId])
                ->column();

            $studentIds = CourseRegistration::find()
                ->select('student_id')
                ->where(['course_id' => $courseIds])
                ->column();

            $documents = Document::find()
                ->where(['user_id' => $studentIds])
                ->orderBy(['created_at' => SORT_DESC])
                ->asArray()
                ->all();
        } else {
            throw new ForbiddenHttpException('Access denied.');
        }

        return ['success' => true, 'documents' => $documents];
    }
}