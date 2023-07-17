<?php
namespace backend\controllers;

use backend\models\SignupForm;
use backend\models\UsersSearch;
use common\models\Sitesettings;
use common\models\Userdevices;
use common\models\Users;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

error_reporting(0);
class UsersController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actions()
    {
        $model = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        if (isset($model->sitename)) {
            Yii::$app->view->title = $model->sitename;
        } else {
            Yii::$app->view->title = "Classifieds";
        }
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        return true;
    }

    public function actionIndex()
    {
        $this->layout = "page";
        $searchModel = new UsersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 10;
        $total = $dataProvider->query->count();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'total' => $total,
        ]);
    }

    public function actionView($id)
    {
        $userdevicedet = Userdevices::find()->where(['user_id' => $id])->orderBy(['id' => SORT_DESC])->one();
        return $this->render('view', [
            'model' => $this->findModel($id),
            'userdevicemodel' => $userdevicedet,
        ]);
    }

    public function actionCreate()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                $user = new Users();
                /*Mobile OTP addons*/
                $phoneNum = $_POST['SignupForm']['phone'];
                $phone = preg_replace("/[^0-9]/", "", $phoneNum);
                /*Mobile OTP addons*/
                $checkExistence = Users::find()->where(['phone'=>$phone])->count();
                if($checkExistence > 0)
                {
                    Yii::$app->session->setFlash('success',Yii::t('app','Phone number already exist'));
                    return $this->redirect(['index']);
                }
                $user->userstatus = 1;
                $user->activationStatus = 1;
                $user->username = $_POST['SignupForm']['username'];
                $user->email = $_POST['SignupForm']['email'];
                $user->phone = $phone;
                $user->name = $_POST['SignupForm']['name'];
                $user->setPassword($_POST['SignupForm']['password']);
                $user->password_encrypt = base64_encode($_POST['SignupForm']['password']);
                $user->generateAuthKey();
                $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
                $default_list_count  = !empty($siteSettings->default_list_count ) ? $siteSettings->default_list_count  : 0;
                if($default_list_count == 0)
                    $user->subscription_enable = 1; 
                else
                    $user->remaining_free_posts = $default_list_count;
                $user->save();
                $mailer = Yii::$app->mailer->setTransport([
                    'class' => 'Swift_SmtpTransport',
                    'host' => $siteSettings['smtpHost'],
                    'username' => $siteSettings['smtpEmail'],
                    'password' => $siteSettings['smtpPassword'],
                    'port' => $siteSettings['smtpPort'],
                    'encryption' => 'tls',
                ]);
                Yii::$app->session->setFlash('success', Yii::t('app', 'User Created'));
                return $this->redirect(['index']);
            }
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }
    
     public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if($model->phone != "")
        $model->phone = "+".$model->phone;
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                $sModel = Users::findOne($id);
                /* Mobile OTP addon old */
                $phoneNum = $_POST['Users']['phone'];
                $phone = preg_replace("/[^0-9]/", "", $phoneNum);
                $checkExistence = Users::find()->where(['phone'=>$phone])->count();
                $checkdata = Users::find()->where(['phone'=>$phone])->one();
                if($checkExistence > 0 && $phone != "")
                {
                    if($checkdata->userId != $id){
                        Yii::$app->session->setFlash('success',Yii::t('app','Phone number already exist'));
                        return $this->redirect(['index']);
                    }
                }
                /* Mobile OTP addon end */
                $sModel->name = $_POST['Users']['name'];
                $sModel->phone = $phone; // phone number addons
                $sModel->mobile_status = 1;
                $sModel->save(false);
                Yii::$app->session->setFlash('success', Yii::t('app', 'User Updated'));
                return $this->redirect(['index']);
            }
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', Yii::t('app', 'User Deleted'));
        return $this->redirect(['index']);
    }
    public function actionStatus($id)
    {
        $model = $this->findModel($id);
        if ($_GET['status'] == 'inactive') {
            $model->userstatus = 0;
            $model->save(false);
            Yii::$app->session->setFlash('success', Yii::t('app', 'User Deactivated'));
        } else if ($_GET['status'] == 'active') {
            $model->userstatus = 1;
            $model->save(false);
            Yii::$app->session->setFlash('success', Yii::t('app', 'User Activated'));
        }
        $searchModel = new UsersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->redirect(Yii::$app->request->referrer);
    }
    public function actionResend($id)
    {
        $user = $this->findModel($id);
        $emailTo = $user->email;
        $link = Yii::$app->urlManager->createAbsoluteUrl('/verify/' . base64_encode($emailTo));
        $verifyLink = str_replace("/Sajilokharidbikri@2021", "", $link);
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $mailer = Yii::$app->mailer->setTransport([
            'class' => 'Swift_SmtpTransport',
            'host' => $siteSettings['smtpHost'],
            'username' => $siteSettings['smtpEmail'],
            'password' => $siteSettings['smtpPassword'],
            'port' => $siteSettings['smtpPort'],
            'encryption' => 'tls',
        ]);
       
        try {
            $userModel = new Users();
            
            if ($userModel->reverifyEmail($user['email'], $verifyLink, $user['name'])) {
                Yii::$app->session->setFlash("success", Yii::t('app', 'User Reverfication mail has been sent Successfully'));
            }
        } catch (\Swift_TransportException $exception) {
            Yii::$app->session->setFlash('error', 'Sorry, Email verify mail not send, SMTP Connection error check email setting');
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    protected function findModel($id)
    {
        if (($model = Users::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionCancel()
    {
        return $this->redirect(['index']);
    }
}
