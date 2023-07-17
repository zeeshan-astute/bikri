<?php
namespace backend\controllers;

use backend\models\BannerapprovedSearch;
use backend\models\BannercancelledSearch;
use backend\models\BannerpaidSearch;
use backend\models\BannersSearch;
use Braintree;
use Braintree\Exception;
use common\models\Banners;
use common\models\Sitesettings;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

error_reporting(0);
class BannersController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
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

    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $this->layout = "page";
        $searchModel = new BannersSearch();
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 10;
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'sitesettings' => $siteSettings,
        ]);
    }

    public function actionPaidbanner()
    {
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $this->layout = "page";
        $searchModel = new BannerpaidSearch();
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $currencySymbols = explode("-", $siteSettings->bannerCurrency);
        $currencySymbol = trim($currencySymbols[0]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 10;
        return $this->render('paidbanner', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'sitesettings' => $siteSettings,
            'selectedcurrency' => $currencySymbol,
        ]);
    }

    public function actionBannerlist()
    {
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $this->layout = "page";
        $searchModel = new BannerapprovedSearch();
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $currencySymbols = explode("-", $siteSettings->bannerCurrency);
        $currencySymbol = trim($currencySymbols[0]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 10;
        return $this->render('bannerlist', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'sitesettings' => $siteSettings,
            'selectedcurrency' => $currencySymbol,
        ]);
    }

    public function actionCancelled()
    {
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $this->layout = "page";
        $searchModel = new BannercancelledSearch();
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $currencySymbols = explode("-", $siteSettings->bannerCurrency);
        $currencySymbol = trim($currencySymbols[0]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 10;
        return $this->render('cancelled', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'sitesettings' => $siteSettings,
            'selectedcurrency' => $currencySymbol,
        ]);
    }

    public function actionView($id)
    {
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionViewbanner($id)
    {
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        return $this->render('viewbanner', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionDeletevideo($details)
    {
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $models = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $path1 = realpath(Yii::$app->basePath . '/../');
        $videofileDir = realpath($path1 . '/frontend/web/media/banners/videos') . '/' . $models->bannervideo;
        if (file_exists($videofileDir)) {
            unlink($videofileDir);
            $models->bannervideo = null;
            if ($models->save(false)) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'File Deleted'));
                return $this->redirect(['bannervideo']);
            }
        } else {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Error delete file'));
            $this->redirect(['bannervideo']);
        }
    }

    public function actionCreate()
    {
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $models = new Banners;
        $all_banners = Banners::find()->where(['status' => ""])->all();
        if (isset($_POST['Banners'])) {
            if (count($all_banners) < 10) {
                $models->attributes = $_POST['Banners'];
                $webImage = UploadedFile::getInstances($models, 'bannerimage');
                $appImage = UploadedFile::getInstances($models, 'appbannerimage');
                $webImageValues = $appImageValues = array();
                $webImageValues = getimagesize($webImage[0]->tempName);
                $appImageValues = getimagesize($appImage[0]->tempName);
                if ($appImage[0]->size < 1000000 && $webImage[0]->size < 1000000 && $webImageValues[0] == "1920" && $webImageValues[1] == "400" && $appImageValues[0] == "1024" && $appImageValues[1] == "500" && (end($webImageValues) == "image/jpeg" || end($webImageValues) == "image/png") && (end($appImageValues) == "image/jpeg" || end($appImageValues) == "image/png") && count($webImageValues) >= 6 && count($appImageValues) >= 6) {
                    if (!empty($webImage)) {
                        $imageName = explode(".", $webImage[0]->name);
                        $models->bannerimage = rand(000, 9999) . '-' . yii::$app->Myclass->productSlug($imageName[0]) . '.' . $webImage[0]->extension;
                    }
                    if (!empty($webImage)) {
                        $path1 = realpath(Yii::$app->basePath . '/../');
                        $path = realpath($path1 . '/frontend/web/media/banners') . '/';
                        $webImage[0]->saveAs($path . $models->bannerimage);
                    }
                    if (!empty($appImage)) {
                        $imageName = explode(".", $appImage[0]->name);
                        $models->appbannerimage = rand(000, 9999) . '-' . yii::$app->Myclass->productSlug($imageName[0]) . '.' . $appImage[0]->extension;
                    }
                    if (!empty($appImage)) {
                        $path1 = realpath(Yii::$app->basePath . '/../');
                        $path = realpath($path1 . '/frontend/web/media/banners') . '/';

                        if ($appImageValues['mime'] === 'image/jpeg') {
                            $image = imagecreatefromjpeg($appImage[0]->tempName);
                            imagejpeg($image, $path . $models->appbannerimage, 75);
                        }

                        if ($appImageValues['mime'] === 'image/png') {
                            $image = imagecreatefrompng($appImage[0]->tempName);
                            imagepng($image, $path . $models->appbannerimage, 9);
                        }
                    }
                    if ($models->save(false)) {
                        return $this->redirect(['index']);
                    }

                } else {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Please upload the image with the specified resolution & size'));
                    return $this->redirect(['create']);
                }
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'You can upload maximum 5 banners only'));
                return $this->redirect(['index']);
            }
        }
        return $this->render('create', [
            'model' => $models,
        ]);
    }

    public function actionUpdate($id)
    {
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $models = $this->findModel($id);
        $oldbanner = $models->bannerimage;
        $oldappbanner = $models->appbannerimage;
        if (isset($_POST['Banners'])) {
            $models->attributes = $_POST['Banners'];
            $webImage = UploadedFile::getInstances($models, 'bannerimage');
            $appImage = UploadedFile::getInstances($models, 'appbannerimage');
            $path1 = realpath(Yii::$app->basePath . '/../');
            $path = realpath($path1 . '/frontend/web/media/banners') . '/';
            if (!empty($webImage)) {
                $webImageValues = array();
                $webImageValues = getimagesize($webImage[0]->tempName);
                if ($webImageValues[0] == "1920" && $webImageValues[1] == "400" && (end($webImageValues) == "image/jpeg" || end($webImageValues) == "image/png") && count($webImageValues) >= 6) {
                    $imageName = explode(".", $webImage[0]->name);
                    $models->bannerimage = rand(000, 9999) . '-' . yii::$app->Myclass->productSlug($imageName[0]) . '.' . $webImage[0]->extension;
                    $webImage[0]->saveAs($path . $models->bannerimage);
                } else {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Please upload the image with the specified size'));
                    return $this->redirect(['update', 'id' => $models->id]);
                }
            } else {
                $models->bannerimage = $oldbanner;
            }
            if (!empty($appImage)) {
                $appImageValues = array();
                $appImageValues = getimagesize($appImage[0]->tempName);
                if ($appImageValues[0] == "1024" && $appImageValues[1] == "500" && (end($appImageValues) == "image/jpeg" || end($appImageValues) == "image/png") && count($appImageValues) >= 6) {
                    $imageName = explode(".", $appImage[0]->name);
                    $models->appbannerimage = rand(000, 9999) . '-' . yii::$app->Myclass->productSlug($imageName[0]) . '.' . $appImage[0]->extension;
                    $appImage[0]->saveAs($path . $models->appbannerimage);
                } else {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Please upload the image with the specified size'));
                    return $this->redirect(['update', 'id' => $models->id]);
                }
            } else {
                $models->appbannerimage = $oldappbanner;
            }
            if ($models->save(false)) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Banner updated'));
            }

            return $this->redirect(['index']);
        }
        return $this->render('update', [
            'model' => $models,
        ]);
    }

    public function actionDelete($id)
    {
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', Yii::t('app', 'Banner deleted'));
        return $this->redirect(['index']);
    }

    public function actionBannerenable()
    {
        $enablestatus = $_POST['enablestatus'];
        $videoenablestatus = $_POST['videoenablestatus'];
        $sitesettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $sitesettings->bannerstatus = $_POST['enablestatus'];
        if ($sitesettings->bannervideoStatus == 1) {
            $sitesettings->bannervideoStatus = $videoenablestatus;
        }
        $sitesettings->save(false);
        echo $sitesettings['bannerstatus'];
    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionBannervideoenable()
    {
        $enablestatus = $_POST['enablestatus'];
        $bannerenablestatus = $_POST['bannerenablestatus'];
        $sitesettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $sitesettings->bannervideoStatus = $enablestatus;
        if ($sitesettings->bannerstatus == 1) {
            $sitesettings->bannerstatus = $bannerenablestatus;
        }
        $sitesettings->save(false);
    }

    public function actionAppbannerenable()
    {
        $enablestatus = $_POST['enablestatus'];
        $sitesettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $sitesettings->appbannerStatus = $enablestatus;
        $sitesettings->save(false);
    }

    protected function findModel($id)
    {
        if (($model = Banners::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionBannervideo()
    {
        $sitesettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $models = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $oldBannerVideo = $models->bannervideo;
        
        if (Yii::$app->request->isPost) {

            if ($_POST['Sitesettings']) {
            
                $models->bannerText = $_POST['Sitesettings']['bannerText'];
                $models->bannervideoStatus = $_POST['Sitesettings']['bannervideoStatus'];
                $models->save(false);
            }
            
            $model->bannervideo = UploadedFile::getInstance($models, 'bannervideo');;
            $video = $model->bannervideo;
            $allowedExts = array("mp4");
            $allowedExts2 = array("jpg", "jpeg", "png");
            $path1 = realpath(Yii::$app->basePath . '/../');
            $fileDir = realpath($path1 . '/frontend/web/media/banners/videos') . '/';
            if (!empty($video)) {
                $imageName = explode(".", $video);
                $extension = $imageName[1];
                $videoname = rand(000, 9999) . '-' . yii::$app->Myclass->productSlug($imageName[0]) . '.' . $imageName[1];
            } else {
                $models->bannervideo = $oldBannerVideo;
            }

            if (!empty($extension)) {
                if (in_array($extension, $allowedExts)) {
                    if ($_FILES["file"]["size"] < 52428800) {
                        if ($_FILES["file"]["error"] > 0) {
                            Yii::$app->session->setFlash('error', Yii::t('app', 'Error video file upload'));
                        } else {
                            $models->bannervideo = UploadedFile::getInstance($models, 'bannervideo');
                            $videofileDir2 = realpath($path1 . '/frontend/web/media/banners/videos') . '/';
                            $videoUpload = UploadedFile::getInstance($models, 'bannervideo');
                            if (!is_null($videoUpload)) {

                                $videoUpload->saveAs($videofileDir2 . $videoname);
                            }
                            if (!is_dir($videofileDir2 . $videoname)) {
                                chmod($videofileDir2 . $videoname, 0777);
                            }
                            Yii::$app->db->createCommand()
                                ->update('hts_sitesettings', ['bannervideo' => $videoname], 'id = 1')
                                ->execute();
                            Yii::$app->session->setFlash('success', Yii::t('app', 'File uploaded successfully'));
                            return $this->redirect(['bannervideo']);
                        }
                    } else {
                        Yii::$app->session->setFlash('error', Yii::t('app', 'Maximum video upload size only 50 MB'));
                        return $this->redirect(['bannervideo']);
                    }
                } else {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Invalid video file type'));
                    return $this->redirect(['bannervideo']);
                }
            }
            return $this->render('bannervideo', array(
                'model' => $models, 'sitesettings' => $sitesettings,
            ));
        }
    
        return $this->render('bannervideo', array(
            'model' => $models,'sitesettings' => $sitesettings,
        ));
    }

    // public function actionBannervideo()
    // {
    //     $sitesettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
    //     if (Yii::$app->user->isGuest) {
    //         return $this->goHome();
    //     }
    //     $models = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
    //     $oldBannerVideo = $models->bannervideo;

    //     echo "<pre>"; print_r($_POST['Sitesettings']); die;
        
    //     if (isset($_POST['submit'])) {
           
    //         if ($_POST['Sitesettings']['bannerText'] != "") {
    //             $bannertext = $_POST['Sitesettings']['bannerText'];
    //             $models->bannerText = $bannertext;
    //             $models->save(false);
    //         }
    //         $video = $_FILES['file']['name'];
            
    //         $allowedExts = array("mp4");
    //         $allowedExts2 = array("jpg", "jpeg", "png");
    //         $path1 = realpath(Yii::$app->basePath . '/../');
    //         $fileDir = realpath($path1 . '/frontend/web/media/banners/videos') . '/';
    //         if (!empty($video)) {
    //             $imageName = explode(".", $video);
    //             $extension = $imageName[1];
    //             $videoname = rand(000, 9999) . '-' . yii::$app->Myclass->productSlug($imageName[0]) . '.' . $imageName[1];
    //         } else {
    //             $models->bannervideo = $oldBannerVideo;
    //         }
    //         if (!empty($extension)) {
    //             if (in_array($extension, $allowedExts)) {
    //                 if ($_FILES["file"]["size"] < 50643703) {
    //                     if ($_FILES["file"]["error"] > 0) {
    //                         Yii::$app->session->setFlash('error', Yii::t('app', 'Error video file upload'));
    //                     } else {
    //                         if (move_uploaded_file($_FILES["file"]["tmp_name"], $fileDir . $videoname)) {
    //                             if ($oldBannerVideo != "") {
    //                                 $path1 = realpath(Yii::$app->basePath . '/../');
    //                                 $videofileDir = realpath($path1 . '/frontend/web/media/banners/videos') . '/' . $oldBannerVideo;
    //                                 if (file_exists($videofileDir)) {
    //                                     unlink($videofileDir); //delete video
    //                                 }
    //                             }
    //                             Yii::$app->db->createCommand()
    //                                 ->update('hts_sitesettings', ['bannervideo' => $videoname], 'id = 1')
    //                                 ->execute();
    //                             Yii::$app->session->setFlash('success', Yii::t('app', 'File uploaded successfully'));
    //                             return $this->redirect(['bannervideo']);
    //                         } else {
    //                             Yii::$app->session->setFlash('error', Yii::t('app', 'Error Move video File upload'));
    //                             return $this->redirect(['bannervideo']);
    //                         }
    //                     }
    //                 } else {
    //                     Yii::$app->session->setFlash('error', Yii::t('app', 'Maximum video upload size only 50 MB'));
    //                     return $this->redirect(['bannervideo']);
    //                 }
    //             } else {
    //                 Yii::$app->session->setFlash('error', Yii::t('app', 'Invalid video file type'));
    //                 return $this->redirect(['bannervideo']);
    //             }
    //         }
    //     }
    //     return $this->render('bannervideo', array(
    //         'model' => $models, 'sitesettings' => $sitesettings,
    //     ));
    // }

    public function actionCancel()
    {
        return $this->redirect(['index']);
    }

    public function actionBannercurrency()
    {
        echo "Daata Hi";
        $currency = $_GET['currency'];
        $str = explode("-", $currency);
        $currency = $str[2] . '-' . $str[0];
        echo $currency;
        $siteSettingsModel = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $siteSettingsModel->bannerCurrency = $currency;
        $siteSettingsModel->save(false);
        $siteSettingsModels = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        echo $siteSettingsModels->bannerCurrency;
    }

    public function actionRefund($id)
    {
        $paymentData = Banners::find()->where(['id' => $id])->one();
        $tx = $paymentData->tranxId;
        $paytype = $paymentData->paymentMethod;
        $curr = $paymentData['currency']; //echo $curr;die;
        if ($paytype == "Paypal Adaptive") {
            $this->canceladaptive($id);
            return;
        } else if ($paytype == "Braintree") {
            $this->cancelbraintree($id);
            return;
        } else if ($paytype == "Stripe") {
            $this->cancelstripe($id);
            return;
        }
        $amt = $paymentData->totalCost;
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $paymentsettings = Json::decode($siteSettings->paypal_settings, true);
        $paymenttype = $paymentsettings['paypalType'];
        $apiuserid = $paymentsettings['paypalApiUserId'];
        $apipassword = $paymentsettings['paypalApiPassword'];
        $apisignature = $paymentsettings['paypalApiSignature'];
        $apiappid = $paymentsettings['paypalAppId'];
        $info = array(
            'USER' => $apiuserid,
            'PWD' => $apipassword,
            'SIGNATURE' => $apisignature,
            'Version' => '94',
            'METHOD' => 'RefundTransaction',
            'TransactionId' => $tx,
            'REFUNDTYPE' => 'Partial',
            'AMT' => $amt,
            'CurrencyCode' => $curr,
        );
        if ($paymenttype == '2') {
            $apipoint = 'https://api-3t.sandbox.paypal.com/nvp';
        } else {
            $apipoint = 'https://api-3t.paypal.com/nvp';
        }
        $apiEndpoint = $apipoint;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $apiEndpoint);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($info));
        curl_setopt($curl, CURLOPT_POST, true);
        $result = curl_exec($curl);
        parse_str($result, $parsed_result);
        if ($parsed_result['ACK'] == 'Success') {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Refund Successfully credited'));
            $adModel = Banners::findOne($id);
            $adModel->status = "cancelled";
            $adModel->trackPayment = "refunded";
            $adModel->save(false);
            $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
            $check = Users::find()->where(['userId' => $adModel->userid])->one();
            return $this->redirect(['cancelled']);
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Unfortunately Refund is not credited'));
            return $this->redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function actionAccept($id)
    {
        $adModel = Banners::find()->where(['id' => $id])->one();
        $adModel->status = "approved";
        $adModel->save(false);
        Yii::$app->session->setFlash('success', Yii::t('app', 'Banner approved'));
        return $this->redirect(['bannerlist']);
    }

    public function canceladaptive($id)
    {
        $paymentData = Banners::find()->where(['id' => $id])->one();
        $tx = $paymentData->tranxId;
        $amt = $paymentData->totalCost;
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $paymentsettings = Json::decode($siteSettings->paypal_settings, true);
        $paymenttype = $paymentsettings['paypalType'];
        $apiuserid = $paymentsettings['paypalApiUserId'];
        $apipassword = $paymentsettings['paypalApiPassword'];
        $apisignature = $paymentsettings['paypalApiSignature'];
        $apiappid = $paymentsettings['paypalAppId'];
        $info = array(
            "X-PAYPAL-SECURITY-USERID:" . $apiuserid . "",
            "X-PAYPAL-SECURITY-PASSWORD:" . $apipassword . "",
            "X-PAYPAL-SECURITY-SIGNATURE:" . $apisignature . "",
            "X-PAYPAL-APPLICATION-ID:" . $apiappid . "",
            "X-PAYPAL-REQUEST-DATA-FORMAT:NV",
            "X-PAYPAL-RESPONSE-DATA-FORMAT:JSON",
        );
        $requestEnvelope = [
            'errorLanguage' => "en_US",
            "detailLevel" => "ReturnAll",
        ];
        $packet = [
            "requestEnvelope" => $requestEnvelope,
            "payKey" => $tx,
        ];
        if ($paymenttype == '2') {
            $apipoint = 'https://svcs.sandbox.paypal.com/AdaptivePayments/Refund?payKey=' . $tx . '&requestEnvelope.errorLanguage=en_US';
        } else {
            $apipoint = 'https://svcs.paypal.com/AdaptivePayments/Refund';
        }
        $apiEndpoint = $apipoint;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $apiEndpoint);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
        curl_setopt($curl, CURLOPT_SSL_CIPHER_LIST, ' TLSv1 ');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $info);
        $result = curl_exec($curl);
        $result = json_decode($result, true);
        print_r($result);
        if ($result['responseEnvelope']['ack'] == 'success') {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Refund Successfully credited'));
            $adModel = Banners::find()->where(['id' => $id])->one();
            $adModel->status = "cancelled";
            $adModel->trackPayment = "refunded";
            $adModel->save(false);
            return $this->redirect(['index']);
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Unfortunately Refund is not credited'));
            return $this->redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function cancelbraintree($id)
    {
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $brainTreeSettings = json_decode($siteSettings->braintree_settings, true);
        $paymenttype = "sandbox";
        if ($brainTreeSettings['brainTreeType'] == 1) {
            $paymenttype = "live";
        }
        $paymenttype = "sandbox";
        $merchantid = $brainTreeSettings['brainTreeMerchantId'];
        $publickey = $brainTreeSettings['brainTreePublicKey'];
        $privatekey = $brainTreeSettings['brainTreePrivateKey'];
        $params = array(
            "testmode" => $paymenttype,
            "merchantid" => $merchantid,
            "publickey" => $publickey,
            "privatekey" => $privatekey,
        );
        Braintree\Configuration::environment($paymenttype);
        Braintree\Configuration::merchantId($merchantid);
        Braintree\Configuration::publicKey($publickey);
        Braintree\Configuration::privateKey($privatekey);
        $paymentData = Banners::find()->where(['id' => $id])->one();
        $tx = $paymentData->tranxId;
        $amt = $paymentData->totalCost;
        try {
            $result = Braintree\Transaction::refund($tx);
        } catch (Braintree\Exception\NotFound $e) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Amount not credited. Please check the braintree details'));
            return $this->redirect(['paidbanner']);
        }
        if (isset($result->success)) {
            if (strcmp($result->message, "Cannot refund transaction unless it is settled.") == 0) {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot refund a transaction unless it is settled.'));
                return $this->redirect(['paidbanner']);
            }
            $adModel = Banners::find()->where(['id' => $id])->one();
            $adModel->status = "cancelled";
            $adModel->trackPayment = "refunded";
            $adModel->save(false);
            $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
            Yii::$app->session->setFlash('success', Yii::t('app', 'Refunded successfully'));
            return $this->redirect(['paidbanner']);
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Amount not credited. Please login into the braintree and check transaction status'));
            return $this->redirect(['paidbanner']);
        }
    }

    public function cancelstripe($id)
    {
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $stripeSettings = json_decode($siteSettings->stripe_settings, true);
        $paymenttype = "sandbox";
        if($stripeSettings['stripeType'] == 1){
            $paymenttype = "live";
        }
        $paymenttype = "sandbox";
        $paymentData = Banners::find()->where(['id'=>$id])->one();
        $transactionId = $paymentData['tranxId'];
        // echo "<pre>"; echo $transactionId; die;
        $secretkey=$stripeSettings['stripePrivateKey']; 
        $url = 'https://api.stripe.com/v1/refunds';
        $data = array('payment_intent' => $transactionId);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '. $secretkey,'Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($result,true);

        // echo "<pre>";print_r($output);die;

        if(count($output['error']) > 0) 
        {
            Yii::$app->session->setFlash('error',$output['error']['message']);
            return $this->redirect(['paidbanner']);
        }
        else
        {
            if($output['status'] == 'succeeded') 
            {
                if (strcmp($result->message,"Cannot refund transaction unless it is settled.") == 0) {         
                    Yii::$app->session->setFlash('error',Yii::t('app','Cannot refund a transaction unless it is settled.'));
                    return $this->redirect(['paidbanner']);
                }
                $adModel = Banners::find()->where(['id'=>$id])->one();
                $adModel->status = "cancelled";
                $adModel->trackPayment = "refunded";
                $adModel->save(false);
                $siteSettings =Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
                Yii::$app->session->setFlash('success',Yii::t('app','Refunded successfully'));
                return $this->redirect(['paidbanner']);
            }
            else
            {
                Yii::$app->session->setFlash('error',Yii::t('app','Amount not credited. Please login into the stripe and check transaction status'));
                return $this->redirect(['paidbanner']);
            }
        }
    }
    
    /*public function cancelstripe($id)
    {
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $stripeSettings = json_decode($siteSettings->stripe_settings, true);
        $paymenttype = "sandbox";
        if ($stripeSettings['stripeType'] == 1) {
            $paymenttype = "live";
        }
        $paymenttype = "sandbox";
        $paymentData = Banners::find()->where(['id' => $id])->one();
        $tx = $paymentData->tranxId;
        $secretkey = $stripeSettings['stripePrivateKey'];
        $url = 'https://api.stripe.com/v1/charges/' . $tx . '/refund';
        $data = array('reason' => 'requested_by_customer');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $secretkey, 'Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($result, true);
        if (isset($output['error'])) {
            Yii::$app->session->setFlash('error', Yii::t('app', "Amount not credited. Please login into the stripe and check transaction status"));
            return $this->redirect(['paidbanner']);
        } else {
            if ($output['status'] == 'succeeded') {
                if (strcmp($result->message, "Cannot refund transaction unless it is settled.") == 0) {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot refund a transaction unless it is settled.'));
                    return $this->redirect(['paidbanner']);
                }
                $adModel = Banners::find()->where(['id' => $id])->one();
                $adModel->status = "cancelled";
                $adModel->trackPayment = "refunded";
                $adModel->save(false);
                $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
                Yii::$app->session->setFlash('success', Yii::t('app', 'Refunded successfully'));
                return $this->redirect(['paidbanner']);
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Amount not credited. Please login into the stripe and check transaction status'));
                return $this->redirect(['paidbanner']);
            }
        }
    }*/
}
