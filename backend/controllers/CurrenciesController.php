<?php
namespace backend\controllers;
use Yii;
use common\models\Products; 
use common\models\Currencies;
use backend\models\CurrenciesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\Sitesettings;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
class CurrenciesController extends Controller
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
         if(isset($model->sitename)) {
         Yii::$app->view->title =  $model->sitename;     
        }
        else
        {
                     Yii::$app->view->title =  "Classifieds";     
        }
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }
    public function actionIndex()
    {   $this->layout="page";
        $searchModel = new CurrenciesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize=10;
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function beforeAction($action) {  
      if (Yii::$app->user->isGuest) {            
                return $this->goHome();          
            }
            return true;
          $this->enableCsrfValidation = false;   
           return parent::beforeAction($action);
        }
     public function actionGetbraintreeid() {
        $data =  yii::$app->Myclass->getbraintreemerchantid($_POST['shortcode']);
     echo $data; 
  }
    public function actionView($id)
    {
      $model = $this->loadModel($id);
      $merchant_sc_id = yii::$app->Myclass->getbraintreemerchantid($model->currency_shortcode);
        return $this->render('view', [
            'model' => $model,'merchant_sc_id'=>$merchant_sc_id
        ]);
    }
public function loadModel($id)
  {
    $model=Currencies::find()->where(['id' => $id])->one();
    if($model===null)
    throw new CHttpException(404,'The requested page does not exist.');
    return $model;
  }
    public function actionCreate()
    {
        $model=new Currencies();
    if ($model->load(Yii::$app->request->post())) {
      if(($_POST['Currencies']['paymenttype'] == 1) && trim($_POST['Currencies']['currency_merchant_id']) == "") {
      Yii::$app->session->setFlash('error',Yii::t('app','Some fields are required'));
            return $this->redirect(['create']);
      }
      $paycurrency= $_POST['Currencies']['currency_shortcode'];
            $site_datas = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
      $sitepaystatus = Json::decode($site_datas->braintree_merchant_ids,true);
            if ($sitepaystatus!="") {
         if (array_key_exists($paycurrency, $sitepaystatus)) {
            unset($sitepaystatus[$paycurrency]);
            $paystatus['merchant_account_id'] = $_POST['Currencies']['currency_merchant_id'];
            $site_datas->braintree_merchant_ids = Json::encode(array_merge($sitepaystatus,array($paycurrency=>$paystatus)));
         } else {
            $paystatus['merchant_account_id'] = $_POST['Currencies']['currency_merchant_id'];
        if($sitepaystatus=='')
               $site_datas->braintree_merchant_ids = Json::encode(array($paycurrency=>$paystatus));  
            else
               $site_datas->braintree_merchant_ids = Json::encode(array_merge($sitepaystatus,array($paycurrency=>$paystatus)));  
         }
     }
     else
     {
           $paystatus['merchant_account_id'] = $_POST['Currencies']['currency_merchant_id'];
            $site_datas->braintree_merchant_ids = Json::encode(array_merge(array($paycurrency=>$paystatus)));
     }
      $model->attributes=$_POST['Currencies'];
            if ($model->validate()) {
      if($model->save(false) && $site_datas->save(false)) {
        Yii::$app->session->setFlash('success',Yii::t('app','Currency created'));
                return $this->redirect(['index']);
            }
        }
    }
        return $this->render('create', [
                'model' => $model,
            ]);    
    }
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $selected = $model->currency_shortcode;
        $merchant_sc_id =  yii::$app->Myclass->getbraintreemerchantid($model->currency_shortcode);
    if(isset($_POST['Currencies']))
    { 
      $model->attributes=$_POST['Currencies'];
       $paycurrency= $model->currency_shortcode;
            $site_datas = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
            $sitepaystatus = Json::decode($site_datas->braintree_merchant_ids,true);
            if ($sitepaystatus!="") {
      if (array_key_exists($paycurrency, $sitepaystatus)) {
            unset($sitepaystatus[$paycurrency]);
            $paystatus['merchant_account_id'] = $_POST['Currencies']['currency_merchant_id'];
            $site_datas->braintree_merchant_ids = Json::encode(array_merge($sitepaystatus,array($paycurrency=>$paystatus)));
         } else {
             $paystatus['merchant_account_id'] = $_POST['Currencies']['currency_merchant_id'];
        if($sitepaystatus=='')
               $site_datas->braintree_merchant_ids = Json::encode(array($paycurrency=>$paystatus));  
            else
             $site_datas->braintree_merchant_ids = Json::encode(array_merge($sitepaystatus,array($paycurrency=>$paystatus)));  
         }
        }
        else
        {
              $paystatus['merchant_account_id'] = $_POST['Currencies']['currency_merchant_id'];
               $site_datas->braintree_merchant_ids = Json::encode(array_merge(array($paycurrency=>$paystatus)));
        }
      if($model->validate()) {
        $model->save(false);
        $site_datas->save(false);
        Yii::$app->session->setFlash('success',Yii::t('app','Currency updated'));
             return $this->redirect(['index']);
      }
    }
        return $this->render('update', [
            'model'=>$model,'selected'=>$selected,'merchant_sc_id'=>$merchant_sc_id
        ]);
    }
    public function actionDelete($id) 
    {
      $model = $this->findModel($id);
      $currencySymbol = $model->currency_symbol.'-'.$model->currency_shortcode;
      $totalProductsCount = Products::find()->where(['currency'=> $currencySymbol])->count();
      if($totalProductsCount<=0){
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success',Yii::t('app','Currency Deleted'));
      } else {
        Yii::$app->session->setFlash('success',Yii::t('app','Currency exists in the products')); 
      }
      return $this->redirect(['index']);
    }   
    protected function findModel($id)
    {
        if (($model = Currencies::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
    public function actionCancel()
    {
        return $this->redirect(['index']);
    }
}