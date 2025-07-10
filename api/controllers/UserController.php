<?php

namespace api\controllers;

use Yii;
use yii\web\Response;
use common\models\User;
use yii\rest\Controller;
use common\models\Course;
use yii\filters\AccessControl;
use api\resources\UserResource;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\UnauthorizedHttpException;

class UserController extends Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;

        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'only' => [
                'profile',
                'logout',

                'index-course',
                'create-course',
                'delete-course',
                'update-course',


                'index-teacher',
                'create-teacher',
                'update-teacher',
                'delete-teacher',

                'index-student',
                'create-student',
                'delete-student',
                'update-student',
            ],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,

            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['signup', 'login'],
                    'roles' => ['?'],
                ],
                //any user can logout and show info
                [
                    'allow' => true,
                    'actions' => ['logout', 'profile'],
                    'roles' => ['@'],                ],
                [
                    'allow' => true,
                    'actions' => [
                        'profile',
                        'logout',

                        'index-teacher',
                        'create-teacher',
                        'delete-teacher',
                        'update-teacher',

                        'index-student',
                        'create-student',
                        'delete-student',
                        'update-student',

                        'index-course',
                        'create-course',
                        'delete-course',
                        'update-course',
                    ],
                    'roles' => ['@'],
                    'matchCallback' => function () {
                        return Yii::$app->user->identity->role === 'admin';
                    },
                ],
            ],
        ];

        return $behaviors;
    }


    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $publicActions = ['signup', 'login'];
        if (in_array($action->id, $publicActions)) {
            return true;
        }

        if (Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException('You are not allowed to access this resource.');
        }
        return true;
    }

    /////////////////////////siginup
    public function actionSignup()
    {
        $request = Yii::$app->request;
        $model = new User();
        $model->load($request->post(), '');

        if ($model->validate()) {
            $model->plainPassword = $model->plainPassword ?? Yii::$app->security->generateRandomString(8);

            if ($model->save()) {
                return [
                    'status' => 'success',
                    'message' => 'User registered successfully.',
                    'access_token' => $model->access_token,
                ];
            }
        }

        return [
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $model->getErrors(),
        ];
    }

    //////////////////////login
    public function actionLogin()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;
        $username = $request->post('username');
        $password = $request->post('password');

        if (empty($username) || empty($password)) {
            return $this->asJson([
                'success' => false,
                'message' => 'Username and password are required.',
            ]);
        }

        $user = User::findByUsername($username);

        if (!$user || !$user->validatePassword($password)) {
            return $this->asJson([
                'success' => false,
                'message' => 'Invalid username or password.',
            ]);
        }

        if ($user->status != User::STATUS_ACTIVE) {
            return $this->asJson([
                'success' => false,
                'message' => 'Your account is not active.',
            ]);
        }

        // Generate new token
        $user->generateAccessToken();

        if (!$user->save(false)) {
            return $this->asJson([
                'success' => false,
                'message' => 'Failed to save access token.',
            ]);
        }

        return $this->asJson([
            UserResource::Arraylogin($user)
        ]);
    }
    //////////////profile
    public function actionProfile()
    {

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user = Yii::$app->user->identity;

        if (!$user) {
            throw new \yii\web\UnauthorizedHttpException('Unauthorized access.');
        }

        return UserResource::toArray($user);
    }
    //////////////end profile////////////////////////////////////////////////////

    ///////////teacher

    public function actionIndexTeacher()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

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

        return [
            'success' => true,
            'count' => count($teachers),
            'data' => array_map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'username' => $teacher->username,
                    'email' => $teacher->email,
                    'created_at' => date('Y-m-d H:i:s', $teacher->created_at),
                    'updated_at' => date('Y-m-d H:i:s', $teacher->updated_at),
                ];
            }, $teachers),
        ];
    }
    ////////////create 
    public function actionCreateTeacher()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $model = new User();
        $model->role = 'teacher';

        $data = Yii::$app->request->post();
        if ($model->load($data, '') && $model->save()) {
            return [
                'success' => true,
                'message' => 'Teacher created successfully.',
                'data' => [
                    'id' => $model->id,
                    'username' => $model->username,
                    'email' => $model->email,
                    'created_at' => date('Y-m-d H:i:s', $model->created_at),
                ],
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to create teacher.',
            'errors' => $model->getErrors(),
        ];
    }
    ///////////////////update teacher

    public function actionUpdateTeacher($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = User::findOne($id);
        if (!$model || $model->role !== 'teacher') {
            throw new NotFoundHttpException("Teacher not found with ID $id.");
        }

        $data = Yii::$app->request->bodyParams;
        $model->load($data, '');

        if ($model->save()) {
            return [
                'status' => 'success',
                'message' => 'Teacher updated successfully.',
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Failed to update teacher.',
            'errors' => $model->getErrors(),
        ];
    }
    ////////////////////////delet teacher

    public function actionDeleteTeacher($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = User::findOne($id);
        if (!$model || $model->role !== 'teacher') {
            throw new NotFoundHttpException("Teacher not found with ID $id.");
        }

        if ($model->delete()) {
            return [
                'status' => 'success',
                'message' => 'Teacher deleted successfully.',
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Failed to delete teacher.',
        ];
    }





    //////////////////////////course 
    public function actionIndexCourse()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $query = Course::find()->with('teacher');

        $search = Yii::$app->request->get('q');
        if (!empty($search)) {
            $query->andFilterWhere([
                'or',
                ['like', 'name', $search],
                ['like', 'description', $search],
            ]);
        }

        $courses = $query->orderBy(['id' => SORT_ASC])->all();

        return [
            'success' => true,
            'count' => count($courses),
            'data' => array_map(function ($course) {
                return [
                    'id' => $course->id,
                    'name' => $course->name,
                    'description' => $course->description,
                    'teacher_id' => $course->teacher_id,
                    'teacher_name' => $course->teacher->username ?? null,
                    'created_at' => date('Y-m-d H:i:s', $course->created_at),
                    'updated_at' => date('Y-m-d H:i:s', $course->updated_at),
                ];
            }, $courses),
        ];
    }


    /////////create course
    public function actionCreateCourse()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $model = new Course();

        $data = Yii::$app->request->post();

        if ($model->load($data, '') && $model->save()) {
            return [
                'success' => true,
                'message' => 'Course created successfully.',
                'data' => [
                    'id' => $model->id,
                    'name' => $model->name,
                    'description' => $model->description,
                    'teacher_id' => $model->teacher_id,
                    'created_at' => date('Y-m-d H:i:s', $model->created_at),
                ],
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to create course.',
            'errors' => $model->getErrors(),
        ];
    }
    ///////////////update course



    public function actionUpdateCourse($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = Course::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException("Course not found with ID $id.");
        }

        $data = Yii::$app->request->bodyParams;

        $model->load($data, '');

        if ($model->save()) {
            return [
                'status' => 'success',
                'message' => 'Course updated successfully.',
                'data' => $model,
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Failed to update course.',
            'errors' => $model->getErrors(),
        ];
    }

    //////////delete course
    public function actionDeleteCourse($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $model = Course::findOne($id);
            if (!$model) {
                return [
                    'status' => 'error',
                    'message' => "Course with ID $id not found.",
                ];
            }

            if ($model->delete() !== false) {
                return [
                    'status' => 'success',
                    'message' => 'Course deleted successfully.',
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Failed to delete the course due to database constraints.',
                ];
            }
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'message' => 'Exception: ' . $e->getMessage(),
            ];
        }
    }
    ////////////////////////////end course


    //////////////////////////////student
    public function actionIndexStudent()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

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

        return [
            'success' => true,
            'count' => count($students),
            'data' => array_map(function ($student) {
                return [
                    'id' => $student->id,
                    'username' => $student->username,
                    'email' => $student->email,
                    'created_at' => date('Y-m-d H:i:s', $student->created_at),
                    'updated_at' => date('Y-m-d H:i:s', $student->updated_at),
                ];
            }, $students),
        ];
    }
    ////create_function

    public function actionCreateStudent()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $model = new User();
        $model->role = 'student';

        $data = Yii::$app->request->post();
        if ($model->load($data, '') && $model->save()) {
            return [
                'success' => true,
                'message' => 'Student created successfully.',
                'data' => [
                    'id' => $model->id,
                    'username' => $model->username,
                    'email' => $model->email,
                    'created_at' => date('Y-m-d H:i:s', $model->created_at),
                ],
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to create student.',
            'errors' => $model->getErrors(),
        ];
    }
    //////////update student


    public function actionUpdateStudent($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = User::findOne($id);
        if (!$model || $model->role !== 'student') {
            throw new NotFoundHttpException("Student not found with ID $id.");
        }

        $data = Yii::$app->request->bodyParams;

        $model->load($data, '');

        if ($model->save()) {
            return [
                'status' => 'success',
                'message' => 'Student updated successfully.',
                'data' => $model,
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Failed to update student.',
            'errors' => $model->getErrors(),
        ];
    }
    ///////////////delet student
    public function actionDeleteStudent($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = User::findOne($id);
        if (!$model || $model->role !== 'student') {
            throw new NotFoundHttpException("Student not found with ID $id.");
        }

        if ($model->delete()) {
            return [
                'status' => 'success',
                'message' => 'Student deleted successfully.',
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Failed to delete student.',
        ];
    }
    ////////////////////////////////////end student


    ////////////////////////log out 
    public function actionLogout()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $identity = Yii::$app->user->identity;

        if (!$identity) {
            throw new UnauthorizedHttpException('Unauthorized access.');
        }

        $user = User::findOne($identity->id);

        if (!$user) {
            throw new UnauthorizedHttpException('User not found.');
        }

        $user->access_token = null;

        if ($user->save(false)) {
            return [
                'success' => true,
                'message' => 'Logout successful.',
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to logout.',
        ];
    }
}