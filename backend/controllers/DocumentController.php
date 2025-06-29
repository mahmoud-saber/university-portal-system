<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use common\models\Document;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

class DocumentController extends Controller
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


    //////////////////////////////////////////////////////index 

    public function actionIndex()
    {
        $documents = \common\models\Document::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return $this->render('index_document', [
            'documents' => $documents,
        ]);
    }



    /////////////////////////////////////////////////create


    public function actionCreate()
    {
        $model = new \common\models\Document();

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $model->user_id = Yii::$app->user->id;

            $uploadedFile = UploadedFile::getInstance($model, 'file_path');

            if ($uploadedFile) {
                // حفظ الاسم الأصلي
                $model->original_name = $uploadedFile->name;

                // إنشاء اسم فريد للملف
                $uniqueName = uniqid() . '.' . $uploadedFile->extension;
                $uploadPath = Yii::getAlias('@webroot/uploads/') . $uniqueName;

                // محاولة حفظ الملف على السيرفر
                if ($uploadedFile->saveAs($uploadPath)) {
                    $model->file_path = 'uploads/' . $uniqueName;
                    $model->file_type = $uploadedFile->type;
                    $model->created_at = date('Y-m-d H:i:s');
                    $model->updated_at = date('Y-m-d H:i:s');
                } else {
                    Yii::$app->session->setFlash('error', 'File upload failed.');
                    return $this->redirect(['create']);
                }

                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'File uploaded successfully.');
                    return $this->redirect(['index']);
                } else {
                    Yii::$app->session->setFlash('error', 'Failed to save document in DB.');
                }
            } else {
                Yii::$app->session->setFlash('error', 'No file selected.');
            }
        }

        return $this->render('document_teacher_students', [
            'model' => $model,
        ]);
    }



    ////////////////////////////////////////////////update  
    public function actionUpdate($id)
    {
        $model = Document::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Document not found.');
        }

        $oldFilePath = $model->file_path;

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());

            $uploadedFile = UploadedFile::getInstance($model, 'file_path');
            if ($uploadedFile) {
                // حذف الملف القديم إذا كان موجودًا
                if (file_exists(Yii::getAlias('@webroot/uploads/') . basename($oldFilePath))) {
                    unlink(Yii::getAlias('@webroot/uploads/') . basename($oldFilePath));
                }

                // حفظ الاسم الأصلي
                $model->original_name = $uploadedFile->name;

                // حفظ الملف الجديد باسم مشفر
                $uniqueName = uniqid() . '.' . $uploadedFile->extension;
                $uploadPath = Yii::getAlias('@webroot/uploads/') . $uniqueName;
                if ($uploadedFile->saveAs($uploadPath)) {
                    $model->file_path = 'uploads/' . $uniqueName;
                } else {
                    Yii::$app->session->setFlash('error', 'File upload failed.');
                    return $this->redirect(['update', 'id' => $id]);
                }
            } else {
                // إذا لم يتم رفع ملف جديد، احتفظ بالقديم
                $model->file_path = $oldFilePath;
            }

            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Document updated successfully.');
                return $this->redirect(['index']);
            }
        }

        return $this->render('update_document', ['model' => $model]);
    }



    ///////////////////////////////////////////////////////////////////
    public function actionAssignment()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $userId = Yii::$app->user->id;
        $user = Yii::$app->user->identity;

        if ($user->role === 'student') {
            // جلب المواد المسجل فيها الطالب
            $courseIds = \common\models\CourseRegistration::find()
                ->select('course_id')
                ->where(['student_id' => $userId])
                ->column();

            // جلب معرفات المدرسين لهذه المواد
            $teacherIds = \common\models\Course::find()
                ->select('teacher_id')
                ->where(['id' => $courseIds])
                ->column();

            // جلب المستندات المرفوعة من هؤلاء المدرسين
            $documents = \common\models\Document::find()
                ->where(['user_id' => $teacherIds])
                ->orderBy(['created_at' => SORT_DESC])
                ->all();
        } elseif ($user->role === 'teacher') {
            // المواد التي يدرّسها المدرس
            $courseIds = \common\models\Course::find()
                ->select('id')
                ->where(['teacher_id' => $userId])
                ->column();

            // الطلاب المسجلين في تلك المواد
            $studentIds = \common\models\CourseRegistration::find()
                ->select('student_id')
                ->where(['course_id' => $courseIds])
                ->column();

            // جلب المستندات التي رفعها هؤلاء الطلاب
            $documents = \common\models\Document::find()
                ->where(['user_id' => $studentIds])
                ->orderBy(['created_at' => SORT_DESC])
                ->all();
        } else {
            throw new \yii\web\ForbiddenHttpException('Access denied.');
        }

        return $this->render('assignment', [
            'documents' => $documents,
        ]);
    }


    ///////////////////////////////////////////////////////delete

    public function actionDelete($id)
    {
        $model = Document::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Document not found.');
        }

        // حذف الملف من السيرفر إن وجد
        $filePath = Yii::getAlias('@webroot') . '/uploads/' . basename($model->file_path);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Document deleted successfully.');
        return $this->redirect(['index']);
    }
}