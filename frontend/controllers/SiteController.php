<?php
namespace frontend\controllers;

use Braintree;
use common\components\HybridAuthIdentity;
use common\models\Banners;
use common\models\Categories;
use common\models\Country;
use common\models\Filter;
use common\models\Filtervalues;
use common\models\LoginForm;
use common\models\Productconditions;
use common\models\Products;
use common\models\Promotiontransaction;
use common\models\Resetpassword;
use common\models\Sitesettings;
use common\models\Userdevices;
use common\models\Users;
use frontend\models\ContactForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use Yii;
use yii\base\InvalidParamException;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Request;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;
use common\components\MyAws;

error_reporting(0);
Html::csrfMetaTags();
class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                ],
            ],
        ];
    }
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'successCallback'],
            ],
        ];
    }
    public function successCallback($client)
    {
        $userid = Yii::$app->user->id;
        if (empty($userid)) {
            $model = new Users();
            $attributes = $client->getUserAttributes();
            if (isset($attributes['email']) && $attributes['email'] != "") {
                $email = $attributes['email'];
            } else if (isset($attributes['emails'][0]['value'])) {
                $email = $attributes['emails'][0]['value'];
            } else {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Can not get email address'));
                $email = "";
            }
            if ($email != "") {
                $user = Users::find()->where(['email' => $email])->one();
                if (!empty($user)) {
                    if ($user->activationStatus == 1 && $user->userstatus == 1) {
                        Yii::$app->user->login($user);
                        Yii::$app->session->setFlash('success', Yii::t('app', 'Welcome') . ' ' . $user->username);
                        $homeUrl = Yii::$app->getUrlManager()->getBaseUrl() . '/';
                        return $this->redirect($homeUrl);
                    } else if ($user['activationStatus'] == 1 && $user['userstatus'] == 0) {
                        Yii::$app->session->setFlash('error', Yii::t('app', 'Your account has been disabled by the Administrator'));
                        return $this->redirect(['site/login']);
                    }
                } else {
                    $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
                    //  return $this->redirect(['site/socialsignup','attributes'=>$attributes]);
                    return $this->redirect(Yii::$app->urlManager->createAbsoluteUrl(['site/socialsignup', 'attributes' => $attributes]));
                }
            }
        } else {
            $attributes = $client->getUserAttributes();
            $user = Users::find()->where(['userId' => $userid])->one();
            $user->facebookId = $attributes['id'];
            $fbdetails['email'] = $attributes['email'];
            $fbdetails['firstName'] = $attributes['first_name'];
            $fbdetails['lastName'] = $attributes['last_name'];
            $fb_detail = json_encode($fbdetails);
            $user->fbdetails = $fb_detail;
            $user->save(false);
            Yii::$app->session->setFlash('success', Yii::t('app', 'Your facebook account has been verified.'));
            return $this->redirect(['user/editprofile']);
        }
    }

    /* Mobile OTP addon changes */
    public function actionLoginwithgoogle()
    {
        $data = $_GET['attributes'];
        $model = new SignupForm(['scenario' => 'signup']);
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $email = json_decode($data)->email;
        $user = Users::find()->where(['email' => $email])->one();
        if (!empty($user)) {
            if($user->activationStatus==1 && $user->userstatus==1) {
                Yii::$app->user->login($user);
                Yii::$app->session->setFlash('success', Yii::t('app','Welcome').' '.$user->username);
                $homeUrl = Yii::$app->getUrlManager()->getBaseUrl().'/';
                return $this->redirect($homeUrl);
            }
            else if($user['activationStatus'] == 1 && $user['userstatus'] == 0 )
            {
                Yii::$app->session->setFlash('error', Yii::t('app','Your account has been disabled by the Administrator'));
                return $this->redirect(['site/login']);
            }
        }
        if ($model->load(Yii::$app->request->post())) {
            $user = base64_encode(json_encode($_POST['SignupForm']));
            return $this->render('phonelogin', ['user' => $user, 'data' => json_decode($data)]);
        }
        return $this->render('loginwithgoogle',['data'=>json_decode($data), 'model'=>$model, 'type'=>'google']);
    }
    public function actionSocialsignup()
    {
        $data = $_GET['attributes'];
        $model = new SignupForm(['scenario' => 'signup']);
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        if ($model->load(Yii::$app->request->post())) {
            $user = base64_encode(json_encode($_POST['SignupForm']));
            return $this->render('phonelogin', ['user' => $user, 'data' => $data, 'type' => 'facebook']);
            
        }
        return $this->render('socialsignup',['data'=>$data, 'model'=>$model]);
    }
    /* Mobile OTP addons changes */

    public function beforeAction($action)
    {
        if ($this->action->id == 'getdate' || $this->action->id == 'currentloc') {
            $this->enableCsrfValidation = false;
        }
        if (parent::beforeAction($action)) {
            $settings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
            if ($settings->site_maintenance_mode == '1') {
                return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl() . '/sitemaintenance');
            }
        }
        return true;
    }
    public function actionIndex()
    {
        // Declaration
        $place = "";
        $lat = "";
        $lon = "";
        $offset = 0;
        $limit = 32;
        $currentdate = date("Y-m-d\T00:00:00.041\Z", time());
        $initialLoad = 0;
        //Model Access
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $searchType = $siteSettings->searchType; // km or miles
        $searchList = $siteSettings->searchList; // 100
        if (Yii::$app->request->isAjax) {
            $initialLoad = 1;
            $limit = (isset($_POST['limit']) && $_POST['limit'] != "") ? trim($_POST['limit']) : 32;
            $offset = (isset($_POST['offset']) && $_POST['offset'] != "") ? trim($_POST['offset']) : 0;
            $lat = (isset($_POST['lat']) && $_POST['lat'] != "") ? trim($_POST['lat']) : "";
            $lon = (isset($_POST['lon']) && $_POST['lon'] != "") ? trim($_POST['lon']) : "";
            $kilometer = (isset($_POST['distance']) && $_POST['distance'] != "") ? trim($_POST['distance']) : $searchList;
            $searchType = (isset($_POST['searchType']) && $_POST['searchType'] != "") ? trim($_POST['searchType']) : $searchType;
            $adsarray = (isset($_POST['adsarray']) && $_POST['adsarray'] != "") ? array_values(array_filter(explode("|", json_decode($_POST['adsarray'])))) : "";
        } else {
            //Top Banner
            if ($siteSettings->paidbannerstatus == 1) {
                $currentdate = date("Y-m-d");
                $bannerCondition = ['or',
                    ['and', // Only Approved Banners
                        ['<=', 'startdate', $currentdate],
                        ['>=', 'enddate', $currentdate],
                        ['=', 'status', 'approved'],
                    ],
                    ['=', 'status', ''], // Default Banners
                ];
                // $banners = Banners::find()->Where($bannerCondition)->all();
                $query = "SELECT * FROM `hts_banners` WHERE (DATE_FORMAT(`startdate`,'%Y-%m-%d') <=  '$currentdate' AND DATE_FORMAT(`enddate`,'%Y-%m-%d') >=  '$currentdate' AND `status` = 'approved') OR  `status` = ''";
                $banners = Banners::findBySql($query)->all();
            } else {
                $banners = Banners::find()->Where(['status' => ''])->all();
            }
            //Location
            if (isset(Yii::$app->session['latitude'])) {
                $lat = Yii::$app->session['latitude'];
                $lon = Yii::$app->session['longitude'];
                $place = Yii::$app->session['place'];
            }
            $kilometer = isset(Yii::$app->session['distance']) ? Yii::$app->session['distance'] : $searchList;
            Yii::$app->session['distance'] = $kilometer;
        }
        $searchList = $kilometer;
        //Removal
        $remove = isset($_POST['remove']) ? $_POST['remove'] : "";
        if (isset($remove) && !empty($remove)) {
            $this->removeLocation(1);
        }
        if ($searchType == 'miles') {
            $kilometer = $kilometer * 1.60934; // 1mile = 1.60934 km
        }
        $locationcriteria = Products::find();
        $locationcriteria->andWhere(['<>', 'soldItem', 1]);
        $locationcriteria->andWhere(['approvedStatus' => 1]);
        if (!empty($lat) && !empty($lon)) {
            $distance = $kilometer * 0.1 / 11;
            $LatN = $lat + $distance;
            $LatS = $lat - $distance;
            $LonE = $lon + $distance;
            $LonW = $lon - $distance;
            $locationcriteria->andWhere(['between', 'longitude', $LonW, $LonE]);
            $locationcriteria->andWhere(['between', 'latitude', $LatS, $LatN]);
        }
        $locationProducts = $locationcriteria->all();
        if (count($locationProducts) == 0) {
            $criteria = Products::find();
            $criteria->andWhere(['<>', 'soldItem', 1]);
            $criteria->andWhere(['approvedStatus' => 1]);
        } else {
            $criteria = clone $locationcriteria;
        }
        $adsCriteria = clone $criteria;
        $adsProducts = "";
        if (Yii::$app->request->isAjax) {
            if (count($adsarray) > 0) {
                $adsCriteria->andWhere(['promotionType' => '1']);
                $adsCriteria->andWhere(['NOT IN', 'productId', $adsarray]);
                $adsCriteria->orderBy(new Expression('rand()'));
                $adsCriteria->limit(8);
                $adsProducts = $adsCriteria->all();
                if (!empty($adsProducts)) {
                    foreach ($adsProducts as $key => $eachProduct) {
                        $tmp[] = $eachProduct->productId;
                    }
                    $tmp = array_filter(array_merge($adsarray, $tmp));
                }
            }
        } else {
            $adsCriteria->andWhere(['promotionType' => '1']);
            $adsCriteria->orderBy(new Expression('rand()'));
            $adsCriteria->limit(8);
            $adsProducts = $adsCriteria->all();
            if (!empty($adsProducts)) {
                foreach ($adsProducts as $key => $eachProduct) {
                    $tmp[] = $eachProduct->productId;
                }
            }
        }
        $adsarray = (!empty($adsProducts)) ? json_encode(implode('|', $tmp)) : ((isset($adsarray) && !empty($adsarray)) ? json_encode(implode('|', $adsarray)) : "");
        $criteria->andWhere(["<>", "promotionType", "1"]);
        $criteria->orderBy(['productId' => SORT_DESC]);
        $limit = 32 - count($adsProducts);
        $criteria->limit($limit);
        $criteria->offset($offset);
        $products = $criteria->all();
        if (Yii::$app->session['paysession'] == "tR87uyuiop") {
            Yii::$app->user->setFlash('success', Yii::t('app', 'Sorry! Unable to pay'));
            Yii::$app->session['paysession'] = "";
        }
        if (Yii::$app->request->isAjax) {
            $totalAjaxProducts = count($products) + count($adsProducts);
            echo count($products) . "~#~" . $adsarray . "~#~" . $totalAjaxProducts . "~#~";
            return $this->renderPartial('indexload', [
                'adsProducts' => $adsProducts,
                'kilometer' => $kilometer, 'products' => $products,
                'searchList' => $searchList, 'searchType' => $searchType, 'lat' => $lat, 'lon' => $lon, 'adsarray' => $adsarray, 'initialLoad' => $initialLoad,
            ]);
        } else {
            return $this->render('index', [
                'banners' => $banners,
                'adsProducts' => $adsProducts,
                'kilometer' => $kilometer, 'products' => $products,
                'searchList' => $searchList, 'searchType' => $searchType,
                'lat' => $lat, 'lon' => $lon, 'adsarray' => $adsarray, 'initialLoad' => $initialLoad, 'place' => $place,
            ]);
        }
    }
    public function actionSearch($search = null, $category = null, $subcategory = null, $sub_subcategory = null)
    {
        if (!empty($category) || (isset($_GET['search']))) {
            //Declaration
            $place = "";
            $lat = "";
            $lon = "";
            $locationReset = 0;
            $subcats = 0;
            $worldData = 0;
            $productcondn = Productconditions::find()->all();
            if (isset(Yii::$app->session['latitude'])) {
                $lat = Yii::$app->session['latitude'];
                $lon = Yii::$app->session['longitude'];
                $place = Yii::$app->session['place'];
            }
            if (isset(Yii::$app->session['place']) && Yii::$app->session['place'] != "")
                $locationReset = 1;
            $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
            $searchType = $siteSettings->searchType;
            $searchList = $siteSettings->searchList;
            $kilometer = Yii::$app->session['distance'] = $searchList;
            Yii::$app->session['distance'] = $kilometer;
            if ($searchType == 'miles') {
                $kilometer = $kilometer * 1.60934; // 1mile = 1.60934 km
            }
            $criteria = Products::find();
            $criteria->andWhere(['<>', 'soldItem', 1]);
            $criteria->andWhere(['approvedStatus' => 1]);
            if (isset(Yii::$app->session['latitude'])) {
                if (!empty($lat) && !empty($lon)) {
                    $distance = $kilometer * 0.1 / 11;
                    $LatN = $lat + $distance;
                    $LatS = $lat - $distance;
                    $LonE = $lon + $distance;
                    $LonW = $lon - $distance;
                    $criteria->andWhere(['between', 'longitude', $LonW, $LonE]);
                    $criteria->andWhere(['between', 'latitude', $LatS, $LatN]);
                    // $locationReset = 1;
                }
            }
            if (!empty($search)) {
                $criteria->andWhere(['like', 'name', htmlentities($search)]);
            }
            if (!empty($category)) {
                $cat = Categories::find()->where(["slug" => $category, "parentCategory" => 0])->one();
                if (!empty($cat)) {
                    $criteria->andWhere(['category' => $cat->categoryId]);
                    $subcats = Categories::find()->where(['parentCategory' => $cat->categoryId])->all();
                }
            }
            $third_level = 0;
            if (!empty($subcategory)) {
                $subcat = Categories::find()->where(["slug" => $subcategory])
                    ->andWhere(['=', 'parentCategory', $cat->categoryId])->one();
                $sub_subcat = Categories::find()->where(["parentCategory" => $subcat->categoryId])
                    ->all();
                if (count($sub_subcat) > 0) {
                    $subcats = Categories::find()->where(['parentCategory' => $subcat->categoryId])->all();
                }
                if (!empty($subcat)) {
                    $criteria->andWhere(["subCategory" => $subcat->categoryId]);
                }
            }
            if (!empty($sub_subcategory)) {
                $sub_subcat = Categories::find()->where(["slug" => $sub_subcategory])
                    ->andWhere(['<>', 'parentCategory', 0])->one();
                $third_level = 1;
                if (!empty($sub_subcat)) {
                    $criteria->andWhere(["sub_subCategory" => $sub_subcat->categoryId]);
                }
            }
            $criteria->orderBy(['promotionType' => SORT_ASC, 'createdDate' => SORT_DESC]);
            $limit = 32;
            $criteria->limit($limit);
            $products = $criteria->all();
            if (count($products) <= 0) {
                $criteria = Products::find();
                $criteria->andWhere(['<>', 'soldItem', 1]);
                $criteria->andWhere(['approvedStatus' => 1]);
                $criteria->orderBy(['promotionType' => SORT_ASC, 'createdDate' => SORT_DESC]);
                $criteria->limit(32);
                $products = $criteria->all();
                $worldData = 1;
            }
            $productcount = count($products);
            if (!empty($subcategory)) {
                $filterCategory = Categories::find()->where(["slug" => $subcategory])->one();
                $getFilter[] = json::decode($filterCategory->filters);
                $filterCount = $filterCategory->filters;
                if ($filterCount) {
                    $filterModel = Filter::find()->where(['in', 'id', $getFilter[0]])->all();
                } else {
                    $filterModel = 0;
                }
            } else {
                $filterModel = 0;
            }
            if (!empty($category) && $category != 'allcategories') {
                if (empty($subcategory)) {
                    $cat = Categories::find()->where(["slug" => $category, "parentCategory" => 0])->one();
                    $subCat = Categories::find()->where(["parentCategory" => $cat->categoryId])->all();
                    $categoryRules = Json::decode($cat->categoryProperty, true);
                    $maincategoryCondition = isset($categoryRules['itemCondition']) ? trim($categoryRules['itemCondition']) : "disable";
                    $valueArray = array();
                    foreach ($subCat as $subcatkey => $subcatval) {
                        $valueArray[] = $subcatval->categoryAttributes;
                    }
                    $subcategoryAttributes = implode(',', $valueArray);
                    $getAttributes = explode(',', $cat->categoryAttributes);
                    $getsubcateAttributes = explode(',', $subcategoryAttributes);
                } elseif (!empty($subcategory)) {
                    if (empty($sub_subcategory)) {
                        $subCat = Categories::find()->where(["slug" => $subcategory])->andWhere(['=', 'parentCategory', $cat->categoryId])->one();
                        $cat = Categories::find()->where(["categoryId" => $subCat->parentCategory])->one();
                        $categoryRules = Json::decode($cat->categoryProperty, true);
                        $subcategoryCondition = isset($categoryRules['itemCondition']) ? trim($categoryRules['itemCondition']) : "disable";
                        $getcateAttributes = explode(',', $subCat->categoryAttributes);
                        $getsubcateAttributes = explode(',', $cat->categoryAttributes);
                        $getAttributes = array_unique(array_merge($getsubcateAttributes, $getcateAttributes));
                    } else if (!empty($sub_subcategory)) {
                        $sub_subCat = Categories::find()->where(["slug" => $sub_subcategory])->andWhere(['=', 'parentCategory', $subcat->categoryId])->one();
                        $subCat = Categories::find()->where(["categoryId" => $sub_subCat->parentCategory])->one();
                        $cat = Categories::find()->where(["categoryId" => $subCat->parentCategory])->one();
                        $categoryRules = Json::decode($cat->categoryProperty, true);
                        $subcategoryCondition = isset($categoryRules['itemCondition']) ? trim($categoryRules['itemCondition']) : "disable";
                        $getsubcateAttributes = explode(',', $cat->categoryAttributes);
                        $getcateAttributes = explode(',', $subCat->categoryAttributes);
                        $getsub_subcateAttributes = explode(',', $sub_subCat->categoryAttributes);
                        $getAttributes = array_unique(array_merge($getsubcateAttributes, $getcateAttributes, $getsub_subcateAttributes));
                    }
                }
                $filters = array();
                foreach (array_filter($getAttributes) as $key => $getAttrval) {
                    $getDatafilter = Filtervalues::find()->where(['filter_id' => $getAttrval,
                        'parentid' => 0,
                        'parentlevel' => 0])->one();
                    $getrangeFilterval = Filter::find()->where(['id' => $getAttrval])->one();
                    $filters[$key]['id'] = $getDatafilter->id;
                    $filters[$key]['name'] = $getDatafilter->name;
                    $filters[$key]['type'] = $getDatafilter->type;
                    $filters[$key]['forrange'] = $getrangeFilterval->value;
                    $getbasearrays = Filtervalues::find()->where([
                        'filter_id' => $getDatafilter->filter_id,
                        'parentid' => $getDatafilter->id])->all();
                    $s = 0;
                    foreach ($getbasearrays as $baseArrval) {
                        $filters[$key]['value'][$s]['id'] = $baseArrval['id'];
                        $filters[$key]['value'][$s]['name'] = $baseArrval['name'];
                        if ($baseArrval->type == 'multilevel') {
                            $getChildval = Filtervalues::find()->where([
                                'parentid' => $baseArrval['id'],
                                'parentlevel' => '4',
                            ])->all();
                            $child = 0;
                            foreach ($getChildval as $skey => $sval) {
                                $filters[$key]['value'][$s]['child'][$child]['id'] = $sval['id'];
                                $filters[$key]['value'][$s]['child'][$child]['name'] = $sval['name'];
                                $child++;
                            }
                        }
                        $s++;
                    }
                }
            } else {
                $filters = array();
            }
            if ($maincategoryCondition == "enable" || $subcategoryCondition == "enable") {
                $productcondn = $productcondn;
            } else {
                $productcondn = [];
            }
            return $this->render('search', [
                'attributes' => $filters,
                'worldData' => $worldData,
                'products' => $products, 'locationReset' => $locationReset,
                'category' => $category, 'subcategory' => $subcategory, 'sub_subcategory' => $sub_subcategory, 'third_level' => $third_level,
                'searchList' => $searchList, 'searchType' => $searchType,
                'productcount' => $productcount,
                'subcats' => $subcats, 'lat' => $lat, 'lon' => $lon, 'productcondn' => $productcondn, 'filterModel' => $filterModel, 'sub_subcat' => $sub_subcat,
            ]);
        } else {
            throw new CHttpException(404, 'Page not found');
        }
    }
    public function actionLoadresults($search = null, $category = null, $subcategory = null, $sub_subcategory = null, $limit = null, $offset = null, $lat = null, $lon = null, $adsOffset = null, $urgent = 0, $ads = 0, $lth = 0, $htl = 0, $catrest = 0, $PriceValue = null, $whereto = null, $filterclass = 0, $posted_within = 0)
    {
        if (isset($_POST['offset'])) {
            $offset = $_POST['offset'];
        }
        if (isset($_POST['lat'])) {
            $lat = $_POST['lat'];
        }
        if (isset($_POST['lon'])) {
            $lon = $_POST['lon'];
        }
        if (isset($_POST['search'])) {
            $search = $_POST['search'];
        }
        if (isset($_POST['urgent'])) {
            $urgent = $_POST['urgent'];
        }
        if (isset($_POST['ads'])) {
            $ads = $_POST['ads'];
        }
        if (isset($_POST['price'])) {
            $PriceValue = $_POST['price'];
        }
        if (isset($_POST['lth'])) {
            $lth = $_POST['lth'];
        }
        if (isset($_POST['htl'])) {
            $htl = $_POST['htl'];
        }
        if (isset($_POST['posted_within'])) {
            $posted_within = trim($_POST['posted_within']);
        }
        if (isset($_POST['productcond'])) {
            $productcond = $_POST['productcond'];
        }
        if (isset($_POST['category'])) {
            $category = $_POST['category'];
        }
        if (isset($_POST['subcategory'])) {
            $subcategory = $_POST['subcategory'];
        }
        if (isset($_POST['sub_subcategory'])) {
            $sub_subcategory = $_POST['sub_subcategory'];
        }
        if (isset($_POST['whereto']) && trim($_POST['whereto']) == "") {
            $this->removeLocation(1);
            $lat = "";
            $lon = "";
        }
        $dropdownValues = (isset($_POST['dropdownvalues'])) ? trim($_POST['dropdownvalues']) : "";
        $multiLevelValues = (isset($_POST['multilevelvalues'])) ? trim($_POST['multilevelvalues']) : "";
        $rangeValues = (isset($_POST['rangevalues'])) ? trim($_POST['rangevalues']) : "";
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $searchType = $siteSettings->searchType;
        $searchList = $siteSettings->searchList;
        $limit = 32;
        $locationReset = 0;
        $initialLoad = 1;
        $worldData = 0;
        $catNoData = 0;
        $condition[] = 'and';
        $otherCondition[] = 'and';
        $dataPrice = array();
        if ($PriceValue != "") {
            $dataPrice = explode(";", $PriceValue);
        }

        $loadMore = isset($_POST['loadMore']) ? trim($_POST['loadMore']) : 0;
        $kilometer = isset($_POST['distance']) ? trim($_POST['distance']) : $searchList;
        if ($searchType == 'miles') {
            $displayInfo = $kilometer . " mi ";
            $kilometer = $kilometer * 1.60934; // 1 mile = 1.60934 km
        }
        $condition[] = ['=', 'approvedStatus', '1'];
        if (!empty($lat) && !empty($lon)) {
            $distance = $kilometer * 0.1 / 11;
            $LatN = $lat + $distance;
            $LatS = $lat - $distance;
            $LonE = $lon + $distance;
            $LonW = $lon - $distance;
            $condition[] = ['between', 'hts_products.longitude', $LonW, $LonE];
            $condition[] = ['between', 'hts_products.latitude', $LatS, $LatN];
        }
        
        if (isset($dataPrice[0]) != "" && isset($dataPrice[1]) != "") {
            if ($dataPrice[0] == '0' && $dataPrice[1] >= '5000') {
                $condition[] = ['>=', 'hts_products.price', '0'];
            } else if ($dataPrice[0] > '0' && $dataPrice[1] >= '5000') {
                $condition[] = ['>=', 'hts_products.price', $dataPrice[0]];
            } else if ($dataPrice[0] == '0' && $dataPrice[1] == '0') {
                $condition[] = ['=', 'hts_products.price', $dataPrice[0]];
            } else if ($dataPrice[0] == '0' && $dataPrice[1] < '5000') {
                $condition[] = ['<=', 'hts_products.price', $dataPrice[1]];
            } else {
                $condition[] = ['>=', 'hts_products.price', $dataPrice[0]];
                $condition[] = ['<=', 'hts_products.price', $dataPrice[1]];
            }
        }
        if (!empty($search)) {
            $condition[] = ['like', 'hts_products.name', $search];
        }
        if (!empty($category)) {
            if ($category != 'allcategories') {
                $cat = Categories::find()->where(["slug" => strtolower($category)])->one();
                if (!empty($cat)) {
                    $condition[] = ['hts_products.category' => $cat->categoryId];
                    $otherCondition[] = ['hts_products.category' => $cat->categoryId];
                    $catNoData = 1;
                }
                if (!empty($subcategory)) {
                    $subcat = Categories::find()->where(["slug" => strtolower($subcategory)])->andWhere(['parentCategory' => $cat->categoryId])->one();
                    if (!empty($subcat)) {
                        $condition[] = ['hts_products.subCategory' => $subcat->categoryId];
                        $otherCondition[] = ['hts_products.subCategory' => $subcat->categoryId];
                        $catNoData = 1;
                    }
                    if (!empty($sub_subcategory)) {
                        $sub_subcat = Categories::find()->where(["slug" => strtolower($sub_subcategory)])->andWhere(['parentCategory' => $subcat->categoryId])->one();
                        if (!empty($sub_subcat)) {
                            $condition[] = ['hts_products.sub_subCategory' => $sub_subcat->categoryId];
                            $otherCondition[] = ['hts_products.sub_subCategory' => $sub_subcat->categoryId];
                            $catNoData = 1;
                        }
                    }
                }
            }
        }

        if (!empty($productcond)) {

            $productclass = array();
            foreach ($productcond as $productc) {
                $alter_condition = str_replace('-', ' ', $productc);
                $productConditionModel = Productconditions::find()->where(['condition' => $alter_condition])->one();
                $productclass[] = $productConditionModel->id;

            }
            $condition[] = ['IN', 'hts_products.productCondition', $productclass];
        }

        $date = date('d-M-Y');
        if ($posted_within === "last24hrs") {
            $prev_date = strtotime($date . ' -1 day');
            $condition[] = ['>=', 'hts_products.createdDate', $prev_date];
        } elseif ($posted_within === "last7days") {
            $prev_week = strtotime($date . ' -7 day');
            $condition[] = ['>=', 'hts_products.createdDate', $prev_week];
        } elseif ($posted_within === "last30days") {
            $prev_month = strtotime($date . ' -30 day');
            $condition[] = ['>=', 'hts_products.createdDate', $prev_month];
        }
        if ($urgent == '1') {
            $condition[] = ['=', 'hts_products.promotionType', '2'];
        }
        $criteria = Products::find();
        $condition[] = ['<>', 'soldItem', 1];
        $advFilterList = "";
        $advFilter[] = "and";
        $advFilteror[] = "or";
        if (!empty($dropdownValues)) {
            $subQuery = (new Query())->select('*')->from('hts_productfilters');
            $criteria->leftJoin(['u' => $subQuery], 'u.product_id=hts_products.productId');
            $advFilterList = "added";
            $dropdownData = array_map('intval', explode(',', $dropdownValues));
            if (count($dropdownData) > 0) {
                $advFlag = 0;
                foreach ($dropdownData as $key => $value) {
                    $filter_val = Filtervalues::find()->where(['id' => $value])->one();
                    $advFilter[$filter_val->filter_id][0] = "or";
                    if ($advFlag == 0) {
                        $fid = $filter_val->filter_id;
                        $slag = 'u';
                        $advFilter[$filter_val->filter_id][] = ['and',
                            ['=', 'u.level_two', $value],
                            ['=', 'u.level_three', 0]];
                    } else {
                        if ($fid == $filter_val->filter_id) {
                            $fid = $filter_val->filter_id;
                            $fslag = $slag;
                            $advFilter[$filter_val->filter_id][] = ['and',
                                ['=', $fslag . '.level_two', $value],
                                ['=', $fslag . '.level_three', 0],
                            ];
                        } else {
                            $slag = 'd' . $advFlag;
                            $subQuery = (new Query())->select('*')->from('hts_productfilters');
                            $criteria->leftJoin([$slag => $subQuery], $slag . '.product_id=u.product_id');
                            $fid = $filter_val->filter_id;
                            $fslag = $slag;
                            $advFilter[$filter_val->filter_id][] = ['and',
                                ['=', $fslag . '.level_two', $value],
                                ['=', $fslag . '.level_three', 0]];
                        }
                    }
                    ++$advFlag;
                }
            }
        }
        $multiLevelData = array();
        if (empty($dropdownValues) && !empty($multiLevelValues)) {
            $subQuery = (new Query())->select('*')->from('hts_productfilters');
            $criteria->leftJoin(['u' => $subQuery], 'u.product_id=hts_products.productId');
            $advFilterList = "added";
            $multiLevelData = array_map('intval', array_values(array_filter(explode(',', $multiLevelValues))));
            if (count($multiLevelData) > 0) {
                $mlFlag = 0;
                foreach ($multiLevelData as $key => $value) {
                    $filter_val = Filtervalues::find()->where(['id' => $value])->one();
                    $advFilter[$filter_val->filter_id][0] = "or";
                    if ($mlFlag == 0) {
                        $fid = $filter_val->filter_id;
                        $slag = 'u';
                        $advFilter[$filter_val->filter_id][] = ['and',
                            ['<>', 'u.level_two', 0],
                            ['=', 'u.level_three', $value],
                        ];
                    } else {
                        if ($fid == $filter_val->filter_id) {
                            $fid = $filter_val->filter_id;
                            $fslag = $slag;
                            $advFilter[$filter_val->filter_id][] = ['and',
                                ['<>', $fslag . '.level_two', 0],
                                ['=', $fslag . '.level_three', $value],
                            ];
                        } else {
                            $slag = 'm' . $mlFlag;
                            $subQuery = (new Query())->select('*')->from('hts_productfilters');
                            $criteria->leftJoin([$slag => $subQuery], $slag . '.product_id=u.product_id');
                            $fid = $filter_val->filter_id;
                            $fslag = $slag;
                            $advFilter[$filter_val->filter_id][] = ['and',
                                ['<>', $fslag . '.level_two', 0],
                                ['=', $fslag . '.level_three', $value],
                            ];
                        }
                    }
                    ++$mlFlag;
                }
            }
        } elseif (!empty($dropdownValues) && !empty($multiLevelValues)) {
            $subQuery = (new Query())->select('*')->from('hts_productfilters');
            $criteria->leftJoin(['v' => $subQuery], 'v.product_id=hts_products.productId');
            $advFilterList = "added";
            $multiLevelData = array_map('intval', array_values(array_filter(explode(',', $multiLevelValues))));
            if (count($multiLevelData) > 0) {
                $mlFlag = 0;
                foreach ($multiLevelData as $key => $value) {
                    $filter_val = Filtervalues::find()->where(['id' => $value])->one();
                    $advFilter[$filter_val->filter_id][0] = "or";
                    if ($mlFlag == 0) {
                        $fid = $filter_val->filter_id;
                        $slag = 'v';
                        $advFilter[$filter_val->filter_id][] = ['and',
                            ['<>', 'v.level_two', 0],
                            ['=', 'v.level_three', $value],
                        ];
                    } else {
                        if ($fid == $filter_val->filter_id) {
                            $fid = $filter_val->filter_id;
                            $fslag = $slag;
                            $advFilter[$filter_val->filter_id][] = ['and',
                                ['<>', $fslag . '.level_two', 0],
                                ['=', $fslag . '.level_three', $value],
                            ];
                        } else {
                            $slag = 'm' . $mlFlag;
                            $subQuery = (new Query())->select('*')->from('hts_productfilters');
                            $criteria->leftJoin([$slag => $subQuery], $slag . '.product_id=v.product_id');
                            $fid = $filter_val->filter_id;
                            $fslag = $slag;
                            $advFilter[$filter_val->filter_id][] = ['and',
                                ['<>', $fslag . '.level_two', 0],
                                ['=', $fslag . '.level_three', $value],
                            ];
                        }
                    }
                    ++$mlFlag;
                }
            }
        }
        if (!empty($rangeValues)) {
            $rangeData = json_decode('[' . ltrim(rtrim($rangeValues, ','), ',') . ']', true);
            if (count($rangeData) > 0) {
                $advFlag = 0;
                foreach ($rangeData as $key => $value) {
                    $valueRange = explode(';', $value['value']);
                    if (count($value) > 0 && count($valueRange) == 2) {
                        if ($advFlag == 0) {
                            $subQuery = (new Query())->select('*')->from('hts_productfilters');
                            if (empty($dropdownValues) && empty($multiLevelValues)) {
                                $criteria->leftJoin(['u' => $subQuery], 'u.product_id=hts_products.productId');
                                $advFilter[] = ['and',
                                    ['=', 'u.filter_id', $value['id']],
                                    ['>=', 'u.filter_values', $valueRange[0]],
                                    ['<=', 'u.filter_values', $valueRange[1]],
                                    ['=', 'u.filter_type', 'range'],
                                ];
                            } elseif (!empty($dropdownValues) && !empty($multiLevelValues)) {
                                $criteria->leftJoin(['w' => $subQuery], 'w.product_id=u.product_id');
                                $advFilter[] = ['and',
                                    ['=', 'w.filter_id', $value['id']],
                                    ['>=', 'w.filter_values', $valueRange[0]],
                                    ['<=', 'w.filter_values', $valueRange[1]],
                                    ['=', 'w.filter_type', 'range'],
                                ];
                            } else {
                                $criteria->leftJoin(['v' => $subQuery], 'v.product_id=u.product_id');
                                $advFilter[] = ['and',
                                    ['=', 'v.filter_id', $value['id']],
                                    ['>=', 'v.filter_values', $valueRange[0]],
                                    ['<=', 'v.filter_values', $valueRange[1]],
                                    ['=', 'v.filter_type', 'range'],
                                ];
                            }
                            ++$advFlag;
                        } else {
                            $slag = 'r' . $advFlag;
                            $criteria->leftJoin([$slag => $subQuery], $slag . '.product_id=u.product_id');
                            $advFilter[] = ['and',
                                ['=', $slag . '.filter_id', $value['id']],
                                ['<=', $slag . '.filter_values', $valueRange[1]],
                                ['=', $slag . '.filter_type', 'range'],
                            ];
                            ++$advFlag;
                        }
                    }
                }
            }
        }
        $orderBy = 0;
        if ($ads == '1') {
            $criteria->orderBy(['likes' => SORT_DESC]);
            $orderBy = 1;
        }
        if ($lth == 1) {
            $criteria->andWhere(['<>', 'soldItem', 1]);
            $criteria->orderBy(['hts_products.price' => SORT_ASC]);
            $orderBy = 1;
        } elseif ($htl == 1) {
            $criteria->andWhere(['<>', 'soldItem', 1]);
            $criteria->orderBy(['hts_products.price' => SORT_DESC]);
            $orderBy = 1;
        }
        if ($orderBy == 0) {
            $criteria->andWhere(['<>', 'soldItem', 1]);
            $criteria->orderBy(['hts_products.promotionType' => SORT_ASC, 'hts_products.createdDate' => SORT_DESC]);
        }

        if (!empty($dropdownValues) || !empty($multiLevelValues) || !empty($rangeValues)) {
            $criteria->andWhere($advFilter);
            $criteria->groupBy('u.product_id');
        }

        $criteria->andWhere($condition);
        $criteria->limit($limit);
        if (isset($offset)) {
            $criteria->offset($offset);
        }
        $products = $criteria->all();
        $orginalCount = count($products);
        if ($catNoData == 1 && count($products) == 0) {
            $criteria = Products::find()->where($otherCondition);
            $criteria->andWhere(['<>', 'soldItem', 1]);
            $criteria->andWhere(['approvedStatus' => '1']);
            $criteria->orderBy(['promotionType' => SORT_ASC, 'createdDate' => SORT_DESC]);
            $criteria->limit($limit);
            $criteria->offset($offset);
            $products = $criteria->all();
        }
        if (count($products) == 0) {
            $criteria = Products::find();
            $criteria->andWhere(['<>', 'soldItem', 1]);
            $criteria->andWhere(['approvedStatus' => '1']);
            $criteria->orderBy(['promotionType' => SORT_ASC, 'createdDate' => SORT_DESC]);
            $criteria->limit($limit);
            $criteria->offset($offset);
            $products = $criteria->all();
            $worldData = 1;
        }

        $productcount = count($products);
        $echo_val = $productcount . "~#~" . $orginalCount . "~#~";
        // echo $criteria->createCommand()->getRawSql(); die;
        return $this->renderPartial('loadresults', ['echo_val' => $echo_val, 'initialLoad' => $initialLoad, 'products' => $products, 'locationReset' => $locationReset, 'category' => $category, 'subcategory' => $subcategory, 'loadMore' => $loadMore, 'productcount' => $productcount, 'worldData' => $worldData, 'sub_subcategory' => $sub_subcategory]);
    }

    public function actionSitemap()
    {
        $getCategories = Categories::find()->where(['parentCategory' => '0'])->all();
        $getLocations = Country::find()->all();
        return $this->render('sitemap', [
            'categories' => $getCategories,
            'locations' => $getLocations,
        ]);
    }
    
    public function actionLogin()
    {
        if (Yii::$app->urlManager->createAbsoluteUrl('site/login') != Yii::$app->request->referrer) {
            Yii::$app->user->setReturnUrl(Yii::$app->request->referrer);
            $_SESSION['loginreturnurl'] = Yii::$app->request->referrer;
        }
        $model = new LoginForm(['scenario' => 'login']);
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if (isset($_POST['rememberMe'])) {
                    $rem = 1;
                } else {
                    $rem = 0;
                }
                $user = Users::find()->where(['email' => $_POST['LoginForm']['username']])->one();
                if ($user['activationStatus'] == 1 && $user['userstatus'] == 1) {
                    $model->login($rem);
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Welcome') . ' ' . $user['username']);
                    if (Yii::$app->user->returnUrl == Yii::$app->urlManager->createAbsoluteUrl('site/signup')) {
                        return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl() . '/');
                    }  else if (Yii::$app->user->returnUrl == Yii::$app->urlManager->createAbsoluteUrl('site/ajaxsignup')) {
                        return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/');
                    }   else if (Yii::$app->user->returnUrl == Yii::$app->urlManager->createAbsoluteUrl('site/ajaxlogin')) {
                        return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/');
                    }   else if (Yii::$app->user->returnUrl == Yii::$app->urlManager->createAbsoluteUrl('site/login')) {
                        return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl() . '/');
                    } else if (Yii::$app->user->returnUrl == Yii::$app->urlManager->createAbsoluteUrl('login')) {
                        return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl() . '/');
                    } else {
                        return $this->redirect(Yii::$app->user->returnUrl);
                    }
                } else if ($user['activationStatus'] == 0 && $user['userstatus'] == 0) {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Please verify your account by given mail id'));
                    return $this->redirect(['site/login']);
                } else if ($user['activationStatus'] == 0 && $user['userstatus'] == 1) {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Please verify your account by given mail id'));
                    return $this->redirect(['site/login']);
                } else if ($user['activationStatus'] == 1 && $user['userstatus'] == 0) {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Your account has been disabled by the Administrator'));
                    return $this->redirect(['site/login']);
                }
            }
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }
    public function actionLoginn()
    {
        if (Yii::$app->urlManager->createAbsoluteUrl('site/login') != Yii::$app->request->referrer) {
            Yii::$app->user->setReturnUrl(Yii::$app->request->referrer);
            $_SESSION['loginreturnurl'] = Yii::$app->request->referrer;
        }
        $model = new LoginForm(['scenario' => 'login']);
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if (isset($_POST['rememberMe'])) {
                    $rem = 1;
                } else {
                    $rem = 0;
                }
                $user = Users::find()->where(['email' => $_POST['LoginForm']['username']])->one();
                if ($user['activationStatus'] == 1 && $user['userstatus'] == 1) {
                    $model->login($rem);
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Welcome') . ' ' . $user['username']);
                    if (Yii::$app->user->returnUrl == Yii::$app->urlManager->createAbsoluteUrl('site/signup')) {
                        return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl() . '/');
                    } else if (Yii::$app->user->returnUrl == Yii::$app->urlManager->createAbsoluteUrl('site/login')) {
                        return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl() . '/');
                    } else if (Yii::$app->user->returnUrl == Yii::$app->urlManager->createAbsoluteUrl('login')) {
                        return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl() . '/');
                    } else {
                        return $this->redirect(Yii::$app->user->returnUrl);
                    }
                } else if ($user['activationStatus'] == 0 && $user['userstatus'] == 0) {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Please verify your account by given mail id'));
                    return $this->redirect(['site/login']);
                } else if ($user['activationStatus'] == 0 && $user['userstatus'] == 1) {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Please verify your account by given mail id'));
                    return $this->redirect(['site/login']);
                } else if ($user['activationStatus'] == 1 && $user['userstatus'] == 0) {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Your account has been disabled by the Administrator'));
                    return $this->redirect(['site/login']);
                }
            }
        }
        return $this->render('loginn', [
            'model' => $model,
        ]);
    }
    public function actionLogout()
    {
        Yii::$app->user->logout(false);
        Yii::$app->session->setFlash('success', Yii::t('app', 'Logged out'));
        return $this->goHome();
    }
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail('jeyalakshmibe86@gmail.com')) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }
            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }
    public function actionAbout()
    {
        return $this->render('about');
    }
    public function actionSignup()
    {
        $user = new Users();
        $model = new SignupForm(['scenario' => 'signup']);
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        if ($model->load(Yii::$app->request->post())) {
            $phone_no = explode('+', $model->phone);
            $phone = $phone_no[1];
            $usermobileCheck = Users::find()->where(['phone' => $phone])->one();
            if (!empty($usermobileCheck)) {
                Yii::$app->session->setFlash("success", Yii::t('app', 'PhoneNumber already exist'));
                return $this->render('signup', ['model' => $model]);
            }
            $model->userstatus = 1;
            if ($siteSettings['signup_active'] == 'no') {
                $model->activationStatus = 1;
            } else {
                $model->activationStatus = 0;
            }
            if ($user = $model->signup()) {
                if ($siteSettings['signup_active'] == 'yes') {
                    $mailer = Yii::$app->mailer->setTransport([
                        'class' => 'Swift_SmtpTransport',
                        'host' => $siteSettings['smtpHost'],
                        'username' => $siteSettings['smtpEmail'],
                        'password' => $siteSettings['smtpPassword'],
                        'port' => $siteSettings['smtpPort'],
                        'encryption' => 'tls',
                    ]);
                    $user = new Users();
                    $emailTo = $_POST['SignupForm']['email'];
                    $verifyLink = Yii::$app->urlManager->createAbsoluteUrl('/verify/' . base64_encode($emailTo));
                    try {
                        if ($user->verifyEmail($_POST['SignupForm']['email'], $verifyLink, $_POST['SignupForm']['name'])) {
                            Yii::$app->session->setFlash("success", Yii::t('app', 'Please verify your acccount using given email id'));
                            return $this->redirect(['site/login']);
                        }
                    } catch (\Swift_TransportException $exception) {
                        Yii::$app->session->setFlash('error', Yii::t('app', 'Sorry, Verify mail not send, SMTP Connection error check email setting'));
                        return $this->redirect(['site/login']);
                    }
                } else {
                    Yii::$app->session->setFlash("success", Yii::t('app', 'Your account has been created, Please Login'));
                    return $this->redirect(['site/login']);
                }
            }
        }
        return $this->render('signup', [
            'model' => $model,
        ]);
    }
    public function actionUsersignup()
    {$model = new SignupForm();
        if (isset($_POST['SignupForm'])) {
            $users = new Users();
            Yii::$app->user->logout();
            return $this->redirect(['site/login']);
        }
    }
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
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
                $model->sendEmail();
                Yii::$app->session->setFlash('success', Yii::t('app', 'Reset password link has been mailed to you'));
                Yii::$app->user->logout();
                return $this->redirect(['site/login']);
                //print_r($exception);die();
            } catch (\Swift_TransportException $exception) {
            	print_r($exception);die();
                Yii::$app->session->setFlash('error', Yii::t('app', 'Sorry, Verify mail not send, SMTP Connection error check email setting'));
                return $this->redirect(['site/login']);
            }
        }
        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl() . '/');
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            $getid = $model->getid();
            $getuserid = $getid->userId;
            $userdetails = Users::find()->where(['userId' => $getuserid])->one();
            $emailTo = $userdetails->email;
            $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
            $mailer = Yii::$app->mailer->setTransport([
            'class' => 'Swift_SmtpTransport',
            'host' => $siteSettings['smtpHost'],
            'username' => $siteSettings['smtpEmail'],
            'password' => $siteSettings['smtpPassword'],
            'port' => $siteSettings['smtpPort'],
            'encryption' => 'tls',
             ]);
        $user = new Users();
        try {
            if ($user->AfterforgotEmail($emailTo, $userdetails->name)) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'New password saved.'));
                 Yii::$app->user->logout();
                return $this->redirect(['site/login']);
            }
            }
            catch (\Swift_TransportException $exception) {
            return $this->redirect(['site/login']);
            }
        }
        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
    public function actionAutosearch()
    {
        $searchstring = $_GET['term'];
        $products = Products::find()->where(['like', 'name', $searchstring])->andWhere(['approvedStatus' => 1])->andWhere(['<>', 'soldItem', 1])->all();
        foreach ($products as $productKey => $product) {
            $productnames[] = $product->name;
        }
        return Json::encode($productnames);
    }
    public function getPlacename($lat, $lon)
    {
        if (isset($_SESSION['language'])) {
            $lang = $_SESSION['language'];
        } else {
            $lang = 'en';
        }
        $url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=" .
            $lat . "," . $lon . "&sensor=false&language=" . $lang;
        $json = @file_get_contents($url);
        $data = $json;
        if (!empty($data)) {
            $status = $data->status;
        } else {
            $status = '';
        }
        $address = '';
        $session = Yii::$app->session;
        if ($status == "OK") {
            $address = $data->results[0]->formatted_address;
            $result = explode(",", $address);
            $count = count($result);
            $country = $result[$count - 1];
            $state = $result[$count - 2];
            $city = $result[$count - 3];
            Yii::$app->session->set('cityName', $city . ',' . $country);
        }
        Yii::$app->session->set('latitude', $lat);
        Yii::$app->session->set('longitude', $lon);
    }
    public function removeLocation($removeFlag = 0)
    {
        $session = Yii::$app->session;
        unset($session['latitude']);
        unset($session['longitude']);
        unset($session['curr_latitude']);
        unset($session['curr_longitude']);
        unset($session['place']);
        unset($session['curr_place1']);
        $session->remove('latitude');
        $session->remove('longitude');
        $session->remove('curr_latitude');
        $session->remove('curr_longitude');
        $session->remove('curr_place1');
        $session->remove('place');
        return;
    }
    public function actionLanguage()
    {
        $language = $_GET['language'];
        $session = Yii::$app->session;
        Yii::$app->session->set('language', $language);
        if (!Yii::$app->user->isGuest) {
            $user = Users::find()->where(['userId' => Yii::$app->user->id])->one();
            $user->user_lang = $language;
            $user->save(false);
        }
        return "";
    }
    public function actionSociallogin($type = null)
    {
        if (!isset($_GET['provider']) && $type == null) {
            return $this->redirect(array('/site/login'));
        }
        try {
            if (isset($_GET['provider'])) {
                $type = $_GET['provider'];
            }
            $_SESSION['provider'] = $type;
            $haComp = new HybridAuthIdentity();
            if (!$haComp->validateProviderName($type)) {
                throw new CHttpException('500', 'Invalid Action. Please try again.');
            }

            $haComp->adapter = $haComp->hybridAuth->authenticate($type);
            $haComp->userProfile = $haComp->adapter->getUserProfile();
            if ($haComp->adapter->id == 'Twitter') {
                $userStatus = $haComp->twitLogin();
            } else {
                $userStatus = $haComp->login();
            }
            if ($userStatus === true) {
                Yii::app()->user->setFlash('success', Yii::t('app', 'You have successfully logged in.'));
                $this->redirect(Yii::app()->homeUrl);
            } elseif ($userStatus == "disabled") {
                Yii::app()->user->setFlash('success', Yii::t('app', 'Your account has been disabled by the Administrator.'));
                $haComp->hybridAuth->logoutAllProviders();
                $this->redirect(Yii::app()->homeUrl);
            } elseif ($userStatus == "no-email") {
                Yii::app()->user->setFlash('success', Yii::t('app', 'Unable to retrive your email, Please check with your social login provider'));
                $haComp->hybridAuth->logoutAllProviders();
                $this->redirect(Yii::app()->homeUrl);
            } else {
                $this->actionSocialsignup($userStatus);
            }
        } catch (Exception $e) {
            $this->redirect(array('login'));
            return;
        }
    }
    public function actionChangepassword()
    {
        $id = \Yii::$app->user->id;
        try {
            $model = new \frontend\models\ChangePasswordForm($id);
        } catch (InvalidParamException $e) {
            throw new \yii\web\BadRequestHttpException($e->getMessage());
        }
        if ($model->load(\Yii::$app->request->post()) && $model->validate() && $model->changePassword()) {
            \Yii::$app->session->setFlash('success', Yii::t('app', 'Password Changed!'));
        }
        return $this->render('changepassword', [
            'model' => $model,
        ]);
    }
    public function actionAjaxLogin()
    {
        if ($_POST['lo-submitt'] == 1) {
            $model = new LoginForm();
            if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
                Yii::$app->response->format = yii\web\Response::FORMAT_JSON;
                return \yii\widgets\ActiveForm::validate($model);
            }
            $user = Users::find()->where(['email' => $_POST['LoginForm']['username']])->one();
            if ($model->load(Yii::$app->request->post())) {
                if (isset($_POST['rememberMe'])) {
                    $rem = 1;
                } else {
                    $rem = 0;
                }
                if ($user['activationStatus'] == 1 && $user['userstatus'] == 1) {
                    $model->login($rem);
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Welcome') . ' ' . $user['username']);
                    if (Yii::$app->user->returnUrl == Yii::$app->urlManager->createAbsoluteUrl('site/signup')) {
                        return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl() . '/');
                    } else if (Yii::$app->user->returnUrl == Yii::$app->urlManager->createAbsoluteUrl('site/login')) {
                        return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl() . '/');
                    } else if (Yii::$app->user->returnUrl == Yii::$app->urlManager->createAbsoluteUrl('site/ajaxsignup')) {
                        return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl() . '/');
                    } else if (Yii::$app->user->returnUrl == Yii::$app->urlManager->createAbsoluteUrl('site/ajaxlogin')) {
                        return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl() . '/');
                    } else if (Yii::$app->user->returnUrl == Yii::$app->urlManager->createAbsoluteUrl('login')) {
                        return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl() . '/');
                    } else {
                        return $this->redirect(Yii::$app->user->returnUrl);
                    }
                } else if ($user['activationStatus'] == 0 && $user['userstatus'] == 0) {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Please verify your account by given mail id'));
                    return $this->redirect(['site/login']);
                } else if ($user['activationStatus'] == 0 && $user['userstatus'] == 1) {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Please verify your account by given mail id'));
                    return $this->redirect(['site/login']);
                } else if ($user['activationStatus'] == 1 && $user['userstatus'] == 0) {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Your account has been disabled by the Administrator'));
                    return $this->redirect(['site/login']);
                } else {
                    throw new HttpException(404, 'Page not found');
                }
            }
        }
    }

    /*public function actionAjaxsignup()
    {
        if ($_POST['si-submitt'] == 1) {
            $model = new SignupForm(['scenario' => 'signup']);
            $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
            if ($model->load(Yii::$app->request->post())) {
                $model->userstatus = 1;
                if ($siteSettings['signup_active'] == 'no') {
                    $model->activationStatus = 1;
                } else {
                    $model->activationStatus = 0;
                }
                if ($model->signup()) {
                    if ($siteSettings['signup_active'] == 'yes') {
                        $mailer = Yii::$app->mailer->setTransport([
                            'class' => 'Swift_SmtpTransport',
                            'host' => $siteSettings['smtpHost'],
                            'username' => $siteSettings['smtpEmail'],
                            'password' => $siteSettings['smtpPassword'],
                            'port' => $siteSettings['smtpPort'],
                            'encryption' => 'tls',
                        ]);
                        $user = new Users();
                        $emailTo = $_POST['SignupForm']['email'];
                        $verifyLink = Yii::$app->urlManager->createAbsoluteUrl('/verify/' . base64_encode($emailTo));
                        try {
                            if ($user->verifyEmail($_POST['SignupForm']['email'], $verifyLink, $_POST['SignupForm']['name'])) {
                                Yii::$app->session->setFlash("success", Yii::t('app', 'Please verify your acccount using given email id'));
                                return $this->redirect(['site/login']);
                            }
                        } catch (\Swift_TransportException $exception) {
                            return $this->redirect(['site/login']);
                        }
                    } else {
                        Yii::$app->session->setFlash("success", Yii::t('app', 'Your account has been created, Please Login'));
                        return $this->redirect(['site/login']);
                    }
                } else {
                    Yii::$app->response->format = yii\web\Response::FORMAT_JSON;
                    return \yii\widgets\ActiveForm::validate($model);
                }
            }
        }
    }*/

    /* Mobile OTP addon changes */
    public function actionAjaxsignup()
    {
		if($_POST['si-submitt'] == 1) {
		    $model = new SignupForm(['scenario' => 'signup']);
			if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
		            Yii::$app->response->format = Response::FORMAT_JSON;
		            return $err = ActiveForm::validate($model);
		    } else
		    {
		        if(Yii::$app->request->post())
				{
					$user = base64_encode(json_encode($_POST['SignupForm']));
			        return $this->render('phonelogin', ['user' => $user]);
				}
			}
	 	}
 	}
    /* Mobile OTP addon changes */

    public function actionAjaxsignup1()
    {
        $model = new SignupForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $err = ActiveForm::validate($model);
        }
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        if ($model->load(Yii::$app->request->post())) {
            $model->userstatus = 1;
            if ($siteSettings['signup_active'] == 'no') {
                $model->activationStatus = 1;
            } else {
                $model->activationStatus = 0;
            }
            if ($user = $model->signup()) {
                if ($siteSettings['signup_active'] == 'yes') {
                    $mailer = Yii::$app->mailer->setTransport([
                        'class' => 'Swift_SmtpTransport',
                        'host' => $siteSettings['smtpHost'],
                        'username' => $siteSettings['smtpEmail'],
                        'password' => $siteSettings['smtpPassword'],
                        'port' => $siteSettings['smtpPort'],
                        'encryption' => 'tls',
                    ]);
                    $user = new Users();
                    $emailTo = $_POST['SignupForm']['email'];
                    $verifyLink = Yii::$app->urlManager->createAbsoluteUrl('/verify/' . base64_encode($emailTo));
                    try {
                        if ($user->verifyEmail($_POST['SignupForm']['email'], $verifyLink, $_POST['SignupForm']['name'])) {
                            Yii::$app->session->setFlash("success", Yii::t('app', 'Please verify your acccount using given email id'));
                            return $this->redirect(['site/login']);
                        }
                    } catch (\Swift_TransportException $exception) {
                        return $this->redirect(['site/login']);
                    }
                } else {
                    Yii::$app->session->setFlash("success", Yii::t('app', 'Your account has been created, Please Login'));
                    return $this->redirect(['site/login']);
                }
            } else {
                Yii::$app->response->format = yii\web\Response::FORMAT_JSON;
                return \yii\widgets\ActiveForm::validate($model);
            }
        }
    }
    public function actionCheck_mailstatus()
    {
        $email = $_POST['email'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
            throw new HttpException(500, 'Malicious Activity');
        }
        $userCondition = Users::find();
        $userCondition->andWhere(['email' => $email]);
        $userdetails = $userCondition->one();
        if (empty($userdetails)) {
            echo '0';die;
        } else {
            if ($userdetails->activationStatus == '0') {
                echo '1-' . $userdetails->userId;die;
            } else {
                echo '2';die;
            }
        }
    }
    public function actionForgotpassword()
    {
        $model = new Users();
        $model->scenario = 'forgetpassword';
        if (isset($_POST['PasswordResetRequestForm'])) {
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'forgetpassword-form') {
                echo ActiveForm::validate($model);
                Yii::$app->end();
            }
            $email = $_POST['PasswordResetRequestForm']['email'];
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emailErr = "Invalid email format";
                throw new HttpException(500, 'Malicious Activity');
            }
            $criteria = Users::find();
            $criteria->andWhere(["email" => $email]);
            $criteria->andWhere(["<>", "userstatus", 2]);
            $check = $criteria->one();
            if (!empty($check)) {
                $resetPasswordCheck = PasswordResetRequestForm::find()->where(['userId' => $check->userId])->one();
                if ($check->userstatus == 1 && $check->activationStatus == 1) {
                    if (empty($resetPasswordCheck)) {
                        $createdDate = time();
                        $randomValue = rand(10000, 100000);
                        $resetPasswordData = base64_encode($check->userId . "-" . $createdDate . "-" . $randomValue);
                        $resetPasswordModel = new Resetpassword();
                        $resetPasswordModel->userId = $check->userId;
                        $resetPasswordModel->resetData = $resetPasswordData;
                        $resetPasswordModel->createdDate = $createdDate;
                        $resetPasswordModel->save();
                    } else {
                        $resetPasswordData = $resetPasswordCheck->resetData;
                    }
                    if (!empty($resetPasswordData)) {
                        $resetPasswordLink = Yii::$app->urlManager->createAbsoluteUrl('/resetpassword?resetLink=' . $resetPasswordData);
                        $siteSettings = Sitesettings::find()->where(['id' => SORT_DESC])->one();
                        $mailer = Yii::$app->mailer->setTransport([
                            'class' => 'Swift_SmtpTransport',
                            'host' => $siteSettings['smtpHost'],
                            'username' => $siteSettings['smtpEmail'],
                            'password' => $siteSettings['smtpPassword'],
                            'port' => $siteSettings['smtpPort'],
                            'encryption' => 'tls',
                        ]);
                        try
                        {
                            $followersModel = new Users();
                            $followersModel->sendForgotmail($check->email, $check->name, $uniquecode_pass);
                            Yii::$app->session->setFlash('success', Yii::t('app', 'Reset password link has been mailed to you.'));
                        } catch (\Swift_TransportException $exception) {
                            return $this->redirect(['site/login']);
                        }
                    }
                } elseif ($check->userstatus == 0 && $check->activationStatus == 0) {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Your account has been disabled by the Administrator.'));
                } else {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'User not verified yet, activate the account from the email.'));
                }
            } else {
                $signupUrl = Yii::$app->urlManager->createAbsoluteUrl('signup');
                Yii::$app->session->setFlash('error', Yii::t('app', 'Email Not Found'));
                return $this->redirect($signupUrl);
            }
        }
        return $this->render('/login', ['model' => $model]);
    }
    public function actionPromotioncron()
    {
        $promotionModel = Promotiontransaction::find()->where(['NOT LIKE', 'promotionName', 'urgent'])
            ->andWhere(['LIKE', 'status', 'live'])->all();
        foreach ($promotionModel as $promotion) {
            $promotionCreatedOn = $promotion->createdDate;
            $promotionEndsOn = strtotime('+' . $promotion->promotionTime . ' day', $promotionCreatedOn);
            $currentDate = time();
            if ($promotionEndsOn < $currentDate) {
                $previousPromotion = Promotiontransaction::find()->where(['productId' => $promotion->productId])
                    ->andWhere(['LIKE', 'status', 'Expired'])->one();
                if (isset($previousPromotion)) {
                    $previousPromotion['status'] = "Canceled";
                    $previousPromotion->save(false);
                }
                $productModel = Products::findOne([$promotion['productId']]);
                if (isset($productModel)) {
                    $productModel['promotionType'] = 3;
                    $productModel->save(false);
                }
                $promotion['status'] = "Expired";
                $promotion->save(false);
                $userid = $productModel->userId;
                $userdevicedet = Userdevices::find()->where(['user_id' => $userid])->all();
                $userdata = Users::findOne([$userid]);
                $currentusername = $userdata->name;
                if (count($userdevicedet) > 0) {
                    foreach ($userdevicedet as $userdevice) {
                        $deviceToken = $userdevice->deviceToken;
                        $lang = $userdevice->lang_type;
                        $badge = $userdevice->badge;
                        $badge += 1;
                        $userdevice->badge = $badge;
                        $userdevice->deviceToken = $deviceToken;
                        $userdevice->save(false);
                        if (isset($deviceToken)) {
                            $msg = yii::$app->Myclass->push_lang($lang);
                            $messages = Yii::t('app', 'Your promotion is expired for') . " " . $productModel->name . " " . Yii::t('app', 'by today.') . " " . Yii::t('app', "Kindly repromote for geting more sale for this products. 'Repromote'");
                            yii::$app->Myclass->pushnot($deviceToken, $messages, $badge);
                        }
                    }
                }
            }
        }
        return;
	}
	
    public function actionVerifymail($id)
    {
        $userId = yii::$app->Myclass->checkPostvalue($_GET['id']) ? $_GET['id'] : "";
        $userId = $_GET['id'];
        $userdetails = Users::find()->where(['userId' => $userId])->one();
        $emailTo = $userdetails->email;
        $verifyLink = Yii::$app->urlManager->createAbsoluteUrl('/verify/' . base64_encode($emailTo));
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $mailer = Yii::$app->mailer->setTransport([
            'class' => 'Swift_SmtpTransport',
            'host' => $siteSettings['smtpHost'],
            'username' => $siteSettings['smtpEmail'],
            'password' => $siteSettings['smtpPassword'],
            'port' => $siteSettings['smtpPort'],
            'encryption' => 'tls',
        ]);
        $user = new Users();
        try {
            if ($user->UserverifyEmail($emailTo, $verifyLink, $userdetails->name)) {
                Yii::$app->session->setFlash("success", Yii::t('app', 'Verification email send successfully'));
                return $this->redirect(['site/login']);
            }
        } catch (\Swift_TransportException $exception) {
            return $this->redirect(['site/login']);
        }
    }
    public function actionPaysession()
    {
        Yii::$app->session->setFlash('success', Yii::t('app', 'Sorry! Unable to pay'));
        return $this->redirect(['/']);
    }
    public function actionTest()
    {
        return $this->render('test');
    }
    public function actionLocationplacename()
    {
        $url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=" .
            $_POST['lat'] . "," . $_POST['lon'] . "&sensor=false";
        $json = @file_get_contents($url);
        $data = json_decode($json);
        if (!empty($data)) {
            $status = $data->status;
        } else {
            $status = '';
        }
        $address = '';
        $session = Yii::$app->session;
        if ($status == "OK") {
            $address = $data->results[0]->formatted_address;
            $result = explode(",", $address);
            echo $result;
        }
    }

    public function actionCurrentloc()
    {
        $lat = $_POST['lat'];
        $lon = $_POST['lon'];
        $initialClick = (isset($_POST['initialClick'])) ? trim($_POST['initialClick']) : 0;
        $place = (isset($_POST['place'])) ? trim($_POST['place']) : "";
        $remove = (isset($_POST['remove'])) ? trim($_POST['remove']) : 0;
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        if ($initialClick == 1) {
            $_SESSION['latitude'] = $lat;
            $_SESSION['longitude'] = $lon;
            $_SESSION['curr_latitude'] = $lat;
            $_SESSION['curr_longitude'] = $lon;
            $_SESSION['place'] = $place;
            $_SESSION['curr_place1'] = $place;
            return 1;
        } else if ($remove == 1) {
            $this->removeLocation();
        } else {
            $_SESSION['latitude'] = $lat;
            $_SESSION['longitude'] = $lon;
            $_SESSION['curr_latitude'] = $lat;
            $_SESSION['curr_longitude'] = $lon;
            // $ch = curl_init("https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyBqN_3wtob65prYXsWPGtvaMNzntAzpnes&latlng=" . $lat . "," . $lon);
            $ch = curl_init("https://maps.googleapis.com/maps/api/geocode/json?key=".$siteSettings["googleapikey"]."&latlng=" . $lat . "," . $lon);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            $content = curl_exec($ch);
            curl_close($ch);
            $decryptData = json_decode($content);
            $formatted_address = $decryptData->results[0]->formatted_address;
            Yii::$app->session['place'] = $formatted_address;
            Yii::$app->session['curr_place1'] = $formatted_address;
            Yii::$app->session['reload'] = 1;
            return $formatted_address;
        }
    }

    public function actionAdverister()
    {
        $models = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $ad_lang = $_SESSION['language'];
        $adcontent = Json::decode($models['ad_content'], true);
        if ($adcontent != "") {
            if (array_key_exists($ad_lang, $adcontent)) {
                $ad_desc = $adcontent[$ad_lang]['content'];
            } else {
                $firstelem = array_keys($adcontent)[0];
                $ad_desc = $adcontent[$firstelem]['content'];
            }
        }
        return $this->render('adverister', ['models' => $models, 'ad_desc' => $ad_desc]);
    }
    public function actionBannerstripesessioncreation()
    {   
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $stripeSettings = json_decode($siteSettings->stripe_settings, true);
        $secretkey = $stripeSettings['stripePrivateKey'];
        $stripeform = Yii::$app->request->post();
        $response_url =  Yii::$app->urlManager->createAbsoluteUrl('/site/bannerstripecallback');
        $url = 'https://api.stripe.com/v1/checkout/sessions';

        $locale = $_SESSION['language'];
        $stripe_lang = ['bg','cs','da','nl','en','et','fi','fr','de','el','hu','it','ja','lv','lt','ms','mt','nb','pl','pt','ro','ru','zh','sk','sl','es','sv','tr'];
        if(!in_array($locale, $stripe_lang))
            $locale = 'en';

        $stripe_currency = ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','UGX','VND'
        ,'VUV','XAF','XOF','XPF'];

        $stripeform['amount'] = $stripeform['amount'] * 100;

        if(in_array(strtoupper(trim($stripeform['currency'])),$stripe_currency))
            $stripeform['amount'] = round($stripeform['amount'] / 100);

        $data = array(
            'mode'=>"payment",
            'locale'=>$locale,
            'success_url' => $response_url."/{CHECKOUT_SESSION_ID}",
            'cancel_url' => $response_url."/{CHECKOUT_SESSION_ID}",
            'payment_method_types' => ['card'],
             'line_items' => [[
                'price_data' => [
                  'currency' => $stripeform['currency'],
                  'unit_amount' => $stripeform['amount'],
                  'product_data' => [
                    'name' => 'Banner Payment',
                  ],
                ],
                'quantity' => 1,
              ]],
            'metadata'=>[
                "banner_id" => $stripeform['bannerid'],
            ],
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $secretkey,
            'Content-Type: application/x-www-form-urlencoded'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($result, true);

        if(isset($output['id']) && $output['id'] == ""){
            Yii::$app->session->setFlash("success",Yii::t('app','Your Banner Amount is Small.'));
            return $this->redirect(['/']);
            return true;
        }

        if(isset($output['error']['code']) && $output['error']['code'] == "amount_too_small"){
            Yii::$app->session->setFlash("success",Yii::t('app','Your Banner Amount is Small.'));
            return $this->redirect(['/']);
            return true;
        }
        return '{"status":"true","session_id":"'.$output['id'].'"}';
    }

    public function actionBannerstripecallback($id= ""){
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $stripeSettings = json_decode($siteSettings->stripe_settings, true);
        $paymenttype = "sandbox";
        if ($stripeSettings['stripeType'] == 1)
        {
            $paymenttype = "live";
        }
        else
        {
            $paymenttype = "sandbox";
        }
        $secretkey = $stripeSettings['stripePrivateKey'];
        
        $url = 'https://api.stripe.com/v1/checkout/sessions/'.$id;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $secretkey,
            'Content-Type: application/x-www-form-urlencoded'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($result, true);

        //echo "<pre>"; print_r($output);die;

        if ($output['payment_status'] == 'paid')
        {   
            $bannerData = $output['metadata'];
            $bannerModel = Banners::find()->where(['id' => $bannerData['banner_id']])->one();
            $bannerModel->paidstatus = 1;
            $bannerModel->paymentMethod = "Stripe";
            $bannerModel->tranxId = $output['payment_intent'];
            $bannerModel->trackPayment = "Paid";
            $bannerModel->save(false);
            Yii::$app->session->setFlash('success', Yii::t('app', 'Banner is waiting for admin approval'));
            return $this->goHome();
        }
        else
        {
            return $this->redirect(['/checkout/canceled']);
        }
    }

    ///add banner

    public function actionAddbanner()
    {
        
        if (!Yii::$app->user->id) {            
           return $this->redirect(['site/login']);    
        }

        $models = new Banners;
        if (isset($_POST['Banners'])) {  
           
         $webImage = UploadedFile::getInstances($models, 'bannerimage');
         $appImage = UploadedFile::getInstances($models, 'appbannerimage');
        
         list($width, $height) = getimagesize($webImage[0]->tempName);
         list($width1, $height1) = getimagesize($appImage[0]->tempName);

              
         if ($width == "1920" && $height == "400" && $width1 == "1024" && $height1 == "500") {
             
            if (!empty($webImage)) {
                $imageName = explode(".", $webImage[0]->name);
                $models->bannerimage = rand(000, 9999) . '-' . yii::$app->Myclass->productSlug($imageName[0]) . '.' . $webImage[0]->extension;
            }
            if (!empty($webImage)) {
                $path = realpath(Yii::$app->basePath ."/web/media/banners") . '/';
                $webImage[0]->saveAs($path . $models->bannerimage);
            }

            $appImage = UploadedFile::getInstances($models, 'appbannerimage');
            if (!empty($appImage)) {
                $imageName = explode(".", $appImage[0]->name);
                $models->appbannerimage = rand(000, 9999) . '-' . yii::$app->Myclass->productSlug($imageName[0]) . '.' . $appImage[0]->extension;
            }
            if (!empty($appImage)) {
                $path = realpath(Yii::$app->basePath ."/web/media/banners") . '/';
                $appImage[0]->saveAs($path . $models->appbannerimage);
            }
            
            $model->userid=Yii::$app->user->id;
            $model->bannerurl = $_POST['Banners']['bannerurl'];
            $model->appurl = $_POST['Banners']['appurl'];

            $siteSettings = Sitesettings::find()->where(['id'=>'1'])->one();
            $sitepaystatus = json_decode($siteSettings->braintree_settings,true);
            $userModel =Users::find()->where(['userId'=>Yii::$app->user->id])->one();
            $bannerCurrency = $siteSettings->promotionCurrency; 
            $currencyDetails = explode('-', $bannerCurrency);
            $bannerCurrency = trim($currencyDetails[0]);

                        $baseUrl = Yii::$app->request->baseUrl;

            $models->userid=Yii::$app->user->id;
            $models->bannerurl = $_POST['Banners']['bannerurl'];
            $models->appurl = $_POST['Banners']['appurl'];


            $startDate = date("Y-m-d",strtotime($_POST['Banners']['startdate']));
            $endDate = date("Y-m-d",strtotime($_POST['Banners']['enddate']));

            if($endDate<$startDate){
                Yii::$app->session->setFlash("error",Yii::t('app','end date must be greater than start date'));
                return; 
            } 
            $date1 = date_create($startDate);
            $date2 = date_create($endDate);
            $diff = date_diff($date1,$date2);
            $total_days =$diff->format("%a")+1;         
            
           /* $models->startdate =  $startDate;         
            $models->enddate =  $endDate;*/
             $models->startdate = date('Y-m-d\TH:i:s.B\Z',strtotime($_POST['Banners']['startdate']));         
            $models->enddate =  date('Y-m-d\TH:i:s.B\Z',strtotime($_POST['Banners']['enddate']));
            $models->totaldays = $total_days;
            $createdDate = date("Y-m-d");

            $Per_day_amount = $siteSettings->ad_price;   
            $total_amount = (int) $total_days * $Per_day_amount;
            $models->totalCost=round($total_amount,2);
            $models->paidstatus =0;
            $models->status =0;  
            $models->createdDate=$createdDate;
            $models->currency=$bannerCurrency;
            $models->save(false);
       
            $banner_id = Yii::$app->db->getLastInsertID();
             
            $userid=Yii::$app->user->id;
            $customField = $bannerCurrency."-_-".$total_amount."-_-".$userId."-_-".$banner_id;
            $customField = yii::$app->Myclass->cart_encrypt($customField, "adveristment-det@ils");
            $baseUrl = Yii::$app->request->baseUrl;
         if($_POST['bannerpayment'] == "stripe")
         {
            $ad_id =  yii::$app->Myclass->safe_b64encode($banner_id);
            $redirectUrl = Yii::$app->urlManager->createAbsoluteUrl('/user/bannersview/'.$ad_id);
            return $this->redirect($redirectUrl);

         }else{
            if($sitepaystatus['brainTreeType'] == 2)
                $payMode = "sandbox";
                else
                $payMode = "production";

                $params = array(
                "testmode"   => $payMode,
                "merchantid" => $sitepaystatus['brainTreeMerchantId'],
                "publickey"  => $sitepaystatus['brainTreePublicKey'],
                "privatekey" => $sitepaystatus['brainTreePrivateKey'],
                );
        
                Braintree\Configuration::environment($params["testmode"]);
                Braintree\Configuration::merchantId($params["merchantid"]);
                Braintree\Configuration::publicKey($params["publickey"]);
                Braintree\Configuration::privateKey($params["privatekey"]);

            $merchantAccountId = yii::$app->Myclass->getbraintreemerchantid($bannerCurrency);
      
                if(empty($merchantAccountId)) {
                Yii::$app->session->setFlash("error",Yii::t('app','Payment curreny problem, Try agin later'));
                return $this->redirect(array('/user/profiles'));
             }

                try {
                   if(empty($userModel->braintree_cid)) {
                       $clientToken = Braintree\ClientToken::generate([
                          "merchantAccountId" => $merchantAccountId 
                       ]);
                   } else {
                       $clientToken = Braintree\ClientToken::generate([
                           "customerId" => $userModel->braintree_cid,
                          "merchantAccountId" => $merchantAccountId 
                       ]);
                   }
                } catch(Braintree_Exception_Authentication $e) {
                    //print_r($e);die;
                   Yii::$app->session->setFlash("error",Yii::t('app','Braintree account problem, Try agin later')); 
                   return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/');
                }  catch(Exception $e) {
                 //  print_r($e);die;
                   Yii::$app->session->setFlash("error",Yii::t('app','Something went wrong in credential, please try again'));
                   return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/'); 
                }  
          

            return $this->renderPartial('bannerpaymentprocess', ['price'=>$total_amount, 'bannerCurrency'=>$bannerCurrency,'customField'=> $customField, 'baseUrl'=> $baseUrl, 'clienttoken'=> $clientToken, 'bannerId'=>$banner_id, 'userId'=> Yii::$app->user->id]);
        }
            }  else {
             Yii::$app->session->setFlash('error', Yii::t('app','Please upload the image with the specified size'));
             // return $this->redirect(['site/addbanner','model'=>$models]);
            }
    
      } 
        $settings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        //$Currency = $settings->bannerCurrency;
        $Currency = $settings->promotionCurrency;
        $currencyDetails = explode('-', $Currency);
        $Currencycode = trim($currencyDetails[0]);  
        $perdayCost = $settings->ad_price;
        return $this->render('addbanner',['model'=>$models,'currency'=>$Currencycode,'currency_code'=>$Currencycode,'perdayCost'=>$perdayCost]);
   }
    public function actionGetdate()
    {
        $this->enableCsrfValidation = false;
        $settings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $limit = $settings->ad_limit;
        $dateArr = $_POST['dataArr'];
        foreach ($dateArr as $date) {
            $chkdatetarr = explode("GMT", $date);
            $date_format = strtotime($chkdatetarr[0]);
            $eachDate = date('Y-m-d', $date_format);
			$query = "SELECT * FROM `hts_banners` WHERE DATE_FORMAT(`startdate`,'%Y-%m-%d') <=  '$eachDate' AND DATE_FORMAT(`enddate`,'%Y-%m-%d') >=  '$eachDate' AND `status` = 'approved'";
            $count = Banners::findBySql($query)->count();
            if ((int) $limit <= (int) $count) {
                $result[] = $eachDate . ' ' . '<br>';
            }
        }
        $count = sizeof($result);
        if ($count != 0) {
            $data = json_encode($result);
            return $data;
        } else {
            return 0;
        }

    }
    public function actionGetuserbyemail()
    {
        $emailId = $_POST['email'];
        $password = $_POST['password'];
        $checkMail = Users::find()->where(['email' => $emailId])->one();
        if (empty($checkMail)) {
            return 'nonuser';
        } else {
            $checkUserexistance = Users::find()->where(['email' => $emailId, 'password_hash' => sha1($password)])->one();
            if (empty($password)) {
                return 'passwordempty';
            } elseif (empty($checkUserexistance)) {
                return 'wrongpassword';
            } else {
                return 'user';
            }
        }
    }
    public function actionSitemaintenance()
    {
        $settings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        if ($settings->site_maintenance_mode == '0') {
            return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl() . '/');
        }
        return $this->render('sitemain', ['settings' => $settings]);
    }
    public function actionGetfiltervalues()
    {
        $splitValues = explode(',', $_POST['filterattribute']);
        $resultArray = array();
        foreach (array_filter($splitValues) as $key => $val) {
            $loadvalues = Filtervalues::find()->where(['id' => $val])->one();
            $resultArray[$key]['id'] = $loadvalues->id;
            $resultArray[$key]['name'] = $loadvalues->name;
        }
        return json_encode($resultArray);
    }
    public function actionGetrangefilter()
    {
        $getRange = explode(',', $_POST['rangevalues']);
        $rangeVal = array();
        foreach ($getRange as $key => $val) {
            $filterVals = Filter::find()->where(['id' => $val])->one();
            $rangeVal[$key]['id'] = $val;
            $rangeVal[$key]['name'] = $filterVals->name;
            $rangeVal[$key]['range'] = $filterVals->value;
        }
        return json_encode($rangeVal);
    }
    public function actionGetpricerange()
    {
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        return $siteSettings->pricerange;
    }
    /***  Mobile OPT addon start ***/
    public function actionPhonelogin()
    {
       return $this->render('phonelogin');
    }
    public function actionLoginwithotp()
    {
        $phone = $_POST['phone_no'];
        $phone = preg_replace("/[^0-9]/", "", $phone);
        $user = Users::find()->where(['phone' => $phone])->one();
        $login = array();
        if(!empty($user))
        {
            $login['username'] = $user->email;
            $login['password'] = base64_decode($user->password_encrypt);
            $model = new LoginForm(['scenario' => 'login']);
            $model->attributes = $login;
            if ($model->validate()) {
                $rem=0;
                if($user['activationStatus']==1 && $user['userstatus']==1) {
                    $model->login($rem);
                    Yii::$app->session->setFlash('success', Yii::t('app','Welcome').' '.$user['username']);
                    // return $this->redirect(Yii::$app->user->returnUrl);
                    if (Yii::$app->user->returnUrl == Yii::$app->urlManager->createAbsoluteUrl('site/signup')) {
                        return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/');
                    }
                    else if (Yii::$app->user->returnUrl == Yii::$app->urlManager->createAbsoluteUrl('site/ajaxsignup')) {
                        return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/');
                    }
                    else if (Yii::$app->user->returnUrl == Yii::$app->urlManager->createAbsoluteUrl('site/ajaxlogin')) {
                        return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/');
                    }
                    else  if (Yii::$app->user->returnUrl == Yii::$app->urlManager->createAbsoluteUrl('site/login')) {
                        return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/');
                    }
                    else  if (Yii::$app->user->returnUrl == Yii::$app->urlManager->createAbsoluteUrl('login')) {
                        return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/');
                    }
                    else
                    {
                        return $this->redirect(Yii::$app->user->returnUrl);
                    }
                }
                else if($user['activationStatus'] == 0 && $user['userstatus'] == 0 )
                {
                    Yii::$app->session->setFlash('error', Yii::t('app','Please verify your account by given mail id'));
                    return $this->redirect(['site/login']);
                }
                else if($user['activationStatus'] == 0 && $user['userstatus'] == 1 )
                {
                    Yii::$app->session->setFlash('error', Yii::t('app','Please verify your account by given mail id'));
                    return $this->redirect(['site/login']);
                }
                else if($user['activationStatus'] == 1 && $user['userstatus'] == 0 )
                {
                    Yii::$app->session->setFlash('error', Yii::t('app','Your account has been disabled by the Administrator')); 
                    return $this->redirect(['site/login']);
                }
                return $this->render('loginn', [
                'model' => $model,
                ]); 
            }
            return $this->render('loginn', [
            'model' => $model,
            ]); 
        }
        else
        {
            Yii::$app->session->setFlash('error', Yii::t('app','Phone number not registered.'));
            return $this->redirect(['site/signup']);
        }
    }
    public function actionPhonesignup()
    {
        $phone_no = explode('+', $_POST['phone']);
        $phone = $phone_no[1];
        $email = $_POST['email'];
        $name = $_POST['name'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $password_repeat = $_POST['password_repeat'];
        $userdetails = Users::find()->where(['phone' => $phone])->one();
        if(!empty($userdetails))
        {
            Yii::$app->session->setFlash("success",Yii::t('app','Mobile number already exists'));
            echo "2";
            die();
        }else{
            $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
            $model = new SignupForm(['scenario' => 'signup']);
            if ($model->load(Yii::$app->request->post(), '')) {
                $model->userstatus = 1;
                if ($siteSettings['signup_active'] == 'no') {
                    $model->activationStatus = 1;
                }
                else
                {
                    $model->activationStatus = 0;
                }
                $model->phone = $phone;
                if ($model->signup()) {
                    if ($siteSettings['signup_active'] == 'yes') {
                        $mailer = Yii::$app->mailer->setTransport([
                        'class' => 'Swift_SmtpTransport',
                        'host' => $siteSettings['smtpHost'],  
                        'username' => $siteSettings['smtpEmail'],
                        'password' => $siteSettings['smtpPassword'],
                        'port' => $siteSettings['smtpPort'], 
                        'encryption' =>  'tls', 
                        ]);
                        $user = new Users();
                        $emailTo = $email;
                        $verifyLink = Yii::$app->urlManager->createAbsoluteUrl('/verify/'.base64_encode($emailTo));
                        try {
                            if($user->verifyEmail($email,$verifyLink,$name)) { 
                                Yii::$app->session->setFlash("success",Yii::t('app','Please verify your acccount using given email id'));
                                return $this->redirect(['site/login']);    
                            }
                        } 
                        catch(\Swift_TransportException $exception)
                        {
                            return $this->redirect(['site/login']);
                        }
                    }else{
                        Yii::$app->session->setFlash("success",Yii::t('app','Your account has been created, Please Login'));
                        return $this->redirect(['site/login']);
                    }  
                } else{
                    Yii::$app->response->format = yii\web\Response::FORMAT_JSON;
                    return \yii\widgets\ActiveForm::validate($model);
                }
            }
        }
    }
    public function actionGooglesignup(){
        $model = new SignupForm(['scenario' => 'signup']);
        if ($model->load(Yii::$app->request->post(), '')) {
            $model->userstatus = 1;
            //$model->phone = $_POST['phone'];
            $npp = preg_replace("/[^0-9]/", "", $_POST['phone']);
            $checkphone = Users::find()->where(['phone'=>$npp])->count();
            if($checkphone > 0) {
                Yii::$app->session->setFlash('error', Yii::t('app','Phone number already exist'));
                return $this->redirect(['site/signup']);
            }
            $model->activationStatus = 1;
            if(isset($_POST['givenName']))
            {			
                $model->googleId = $_POST['google_id'];
                $model->facebookId = "";
                $model->fbdetails = "";
            }
            if ($user = $model->signup()) {
                if(!empty($user->googleId))
                {
                    $imagelink = $_POST['url'];
                }
                $header_response = get_headers($imagelink, 1);
                if (strpos($header_response[0], "404") !== false ){
                } 
                else
                {
                    $filename = time().rand(0, 9);
                    $newname = $user->userId.'_'.$filename.'.'."jpg";
                    $path = realpath(Yii::$app->basePath."/web/profile/" ) . "/";
                    $contents=file_get_contents($imagelink);
                    if($contents==false)
                    {
                        $ch = curl_init($imagelink);
                        $fp = fopen($path.$newname, 'wb');
                        curl_setopt($ch, CURLOPT_FILE, $fp);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_exec($ch);
                        curl_close($ch);
                        fclose($fp);						
                    }
                    else
                    {
                        file_put_contents($path.$newname,$contents);
                    }
                    $user->userImage = $newname;
                    $user->save(false);
                    // AWS upload start
                    //$bucket = 'joysaleadonn';
                    $keyname = 'profile/'.$newname;
                    $file = $path . $user->userImage;
                    $ContentType = 'image/jpeg';
                    $uploadimage = MyAws::Awsupload($bucket,$keyname,$ContentType,$file);
                    // AWS upload end
                }
                $user->lastLoginDate=time();
                $user->save(false);
                Yii::$app->user->login($user);
                Yii::$app->session->setFlash('success', Yii::t('app','Welcome').' '.$user->username);
                $homeUrl = Yii::$app->getUrlManager()->getBaseUrl().'/';
                return $this->redirect($homeUrl);
            }
        }
    }
    public function actionFbsignup() {
        $model = new SignupForm(['scenario' => 'signup']);
        if ($model->load(Yii::$app->request->post(), '')) {
            $model->userstatus = 1;
            //$model->phone = $_POST['phone'];
            $npp1 = preg_replace("/[^0-9]/", "", $_POST['phone']);
            $checkphone1 = Users::find()->where(['phone'=>$npp1])->count();
            if($checkphone1 > 0) {
                Yii::$app->session->setFlash('error', Yii::t('app','Phone number already exist'));
                return $this->redirect(['site/signup']);
            }
            $model->activationStatus = 1;
            $model->googleId = "";
            $model->facebookId = $_POST['fbid'];
            $fbdetails['email'] =  $_POST['fbemail'];
            $fbdetails['firstName'] =  $_POST['fbfirstname'];
            $fbdetails['lastName'] =  $_POST['fblastname'];
            $fb_detail = json_encode($fbdetails);
            $model->fbdetails = $fb_detail;
            if ($user = $model->signup()) {
                if(!empty($user->facebookId))
                {
                    $imagelink = "https://graph.facebook.com/" . $_POST['fbid'] . "/picture?width=150&height=150";
                }           	
                $header_response = get_headers($imagelink, 1);
                if (strpos($header_response[0], "404") !== false ){
                } 
                else
                {
                    $filename = time().rand(0, 9);
                    $newname = $user->userId.'_'.$filename.'.'."jpg";
                    $path = realpath(Yii::$app->basePath."/web/profile/" ) . "/";
                    $contents=file_get_contents($imagelink);
                    if($contents==false)
                    {
                        $ch = curl_init($imagelink);
                        $fp = fopen($path.$newname, 'wb');
                        curl_setopt($ch, CURLOPT_FILE, $fp);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_exec($ch);
                        curl_close($ch);
                        fclose($fp);						
                    }
                    else
                    {
                        file_put_contents($path.$newname,$contents);
                    }
                    $user->userImage = $newname;
                    $user->save(false);
                    // AWS upload start
                    //$bucket = 'joysaleadonn';
                    // $keyname = 'profile/'.$newname;
                    // $file = $path . $user->userImage;
                    // $ContentType = 'image/jpeg';
                    // $uploadimage = MyAws::Awsupload($bucket,$keyname,$ContentType,$file);
                    // AWS upload end
                }
                $user->lastLoginDate=time();
                $user->save(false);
                Yii::$app->user->login($user);
                Yii::$app->session->setFlash('success', Yii::t('app','Welcome').' '.$user->username);
                $homeUrl = Yii::$app->getUrlManager()->getBaseUrl().'/';
                return $this->redirect($homeUrl);
            }
        }
    }
    /***  Mobile OPT addon end ***/
}
