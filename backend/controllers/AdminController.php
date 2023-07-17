<?php
namespace backend\controllers;
use backend\models\BannerapprovedSearch;
use backend\models\OrdersSearchlog;
use backend\models\PromotiontransactionSearch;
use common\models\Admin;
use common\models\AdminLoginForm;
use common\models\AdminPasswordResetRequestForm;
use common\models\AdminResetPasswordForm;
use common\models\Banners;
use common\models\Categories;
use common\models\Country;
use common\models\Logs;
use common\models\Orders;
use common\models\Products;
use common\models\Promotiontransaction;
use common\models\Sitesettings;
use common\models\Userdevices;
use common\models\Users;
use Yii;
use yii\base\InvalidParamException;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

error_reporting(0);
class AdminController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', ''],
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
        $guestModel = new Admin();
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $this->layout = "dashboard";
        $total = 0;
        $bannerAmount = Banners::find()->where(['paidstatus' => '1'])->where(['status' => 'approved'])->all();
        foreach ($bannerAmount as $paidbanner) {
            $getAmt = $paidbanner->totalCost;
            $total = $total + $getAmt;
        }

        $sitesettingsModel = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $promotionAmt = Promotiontransaction::find()->sum('promotionPrice');
        if ($sitesettingsModel->promotionStatus == 0 && $sitesettingsModel->paidbannerstatus == 0) {
            $totalRevenue = 0;
        } elseif ($sitesettingsModel->promotionStatus == 0) {
            $totalRevenue = $total;
        } elseif ($sitesettingsModel->paidbannerstatus == 0) {
            $totalRevenue = $promotionAmt;
        } else {
            $totalRevenue = $promotionAmt + $total;
        }
        $mystring = array();
        $count = array();
        $leastDate = date("d-m-Y", strtotime("-7 days"));
        if (Yii::$app->session->get('reportItems') == 'daily') {
            for ($i = 7; $i >= 0; $i--) {
                $mystring[] = date("Y-m-d", strtotime("-" . $i . "days"));
                $count[] = yii::$app->Myclass->getItemsAdded(date("d-m-Y", strtotime("-" . $i . "days")));
            }
        } else if (Yii::$app->session->get('reportItems') == 'monthly') {
            for ($i = 7; $i >= 0; $i--) {
                $mystring[] = date('Y-m', strtotime('-' . $i . ' month', time()));
                $count[] = yii::$app->Myclass->getItemsAddedMonthly(date('Y-m', strtotime('-' . $i . ' month', time())));
            }
        } else if (Yii::$app->session->get('reportItems') == 'year') {
            for ($i = 7; $i >= 0; $i--) {
                $mystring[] = date('Y', strtotime('-' . $i . ' year', time()));
                $count[] = yii::$app->Myclass->getItemsAddedYearly(date('Y', strtotime('-' . $i . ' year', time())));
            }
        } else {
            for ($i = 7; $i >= 0; $i--) {
                $mystring[] = date("Y-m-d", strtotime("-" . $i . "days"));
                $count[] = yii::$app->Myclass->getItemsAdded(date("d-m-Y", strtotime("-" . $i . "days")));
            }
        }
        $getActiveLoggedUsers = yii::$app->Myclass->getLoggedUsers(date("d-m-Y"));
        $androidmodelLabel = array();
        $androidmodelValue = array();
        $androidmodels = Userdevices::find()
        ->select(['COUNT(*) AS cnt', trim('deviceModel')])
        ->andWhere(['not', ['deviceModel' => null]])
        ->andWhere(['type' => '1'])
        ->groupBy([trim('deviceModel')])->orderBy(['cnt' => SORT_DESC])->all();
        foreach ($androidmodels as $androidmodel) {
            $androidmodelLabel[] = $androidmodel['deviceModel'];
            $androidmodelValue[] = $androidmodel->cnt;
        }
        $androidmodelLabel = array_slice($androidmodelLabel, 0, 7);
        $androidmodelValue = array_slice($androidmodelValue, 0, 7);
        $iosmodelLabel = array();
        $iosmodelValue = array();
        $iosmodels = Userdevices::find()
        ->select(['COUNT(*) AS cnt', trim('deviceModel')])
        ->andWhere(['not', ['deviceModel' => null]])
        ->andWhere(['type' => '0'])
        ->groupBy([trim('deviceModel')])->orderBy(['cnt' => SORT_DESC])->all();
        foreach ($iosmodels as $iosmodel) {
            $iosmodelLabel[] = $iosmodel['deviceModel'];
            $iosmodelValue[] = $iosmodel->cnt;
        }
        $iosmodelLabel = array_slice($iosmodelLabel, 0, 7);
        $iosmodelValue = array_slice($iosmodelValue, 0, 7);
        $catLabel = array();
        $catValue = array();
        $category = Products::find()
        ->select(['COUNT(*) AS cnt', 'category'])
        ->andWhere(['not', ['category' => null]])
        ->groupBy(['category'])->orderBy(['cnt' => SORT_DESC])->all();
        foreach ($category as $cat) {
            $categoryname = Categories::find()->where(['categoryId' => $cat->category])->one();
            $catLabel[] = $categoryname['name'];
            $catValue[] = $cat->cnt;
        }
        $catLabel = array_slice($catLabel, 0, 7);
        $catValue = array_slice($catValue, 0, 7);
        $userCountryLabel = array();
        $userCountryValue = array();
        $GetUser = Users::find()
        ->select(['COUNT(*) AS cty', trim('country')])
        ->where(['not', ['country' => null]])
        ->groupBy([trim('country')])->orderBy(['country' => SORT_DESC])
        ->all();
        foreach ($GetUser as $users) {
            $proo = $users->country;
            $arr[] = trim($proo);
        }
        foreach (array_unique($arr) as $key => $value) {
            $products = Users::find()->where(['like', 'country', $value])->all();
            $userCountry[$value] = count($products);
        }
        $tem = arsort($userCountry);
        $usercoun = array_slice($userCountry, 0, 7);
// Top Countries Products
        $query = new \yii\db\Query;
        $query->select(['count(hts_products.country) as counter', 'hts_products.country', 'hts_country.country as countryname'])->from('hts_products')
        ->leftJoin('hts_country', 'hts_country.code = hts_products.country')
        ->where(['!=', 'hts_products.country', 'NULL'])
        ->andWhere(['!=', 'hts_products.country', ''])
        ->groupBy('hts_products.country')
        ->orderBy('hts_products.country desc');
        $countQuery = clone $query;
        $productcoun = $countQuery->createCommand()->queryAll();
// promotion status
        $promotionads = array();
        $promotionUrgent = array();
        $promotionLabel = array();
        if (Yii::$app->session->get('reportpromotions') == 'daily') {
            for ($i = 7; $i >= 0; $i--) {
                $promotionLabel[] = date("Y-m-d", strtotime("-" . $i . "days"));
            }
            for ($i = 0; $i <= 7; $i++) {
                $promotionads[] = yii::$app->Myclass->getPromotionsAdds(date("d-m-Y", strtotime($leastDate . "+" . $i . "days")));
                $promotionUrgent[] = yii::$app->Myclass->getPromotionsUrgent(date("d-m-Y", strtotime($leastDate . "+" . $i . "days")));
            }
        } else if (Yii::$app->session->get('reportpromotions') == 'monthly') {
            for ($i = 7; $i >= 0; $i--) {
                $promotionLabel[] = date('Y-m', strtotime('-' . $i . ' month', time()));
                $promotionads[] = yii::$app->Myclass->getPromotionsAddsMonthly(date('Y-m', strtotime('-' . $i . ' month', time())));
                $promotionUrgent[] = yii::$app->Myclass->getPromotionsUrgentMonthly(date('Y-m', strtotime('-' . $i . ' month', time())));
            }
        } else if (Yii::$app->session->get('reportpromotions') == 'year') {
            for ($i = 7; $i >= 0; $i--) {
                $promotionLabel[] = date('Y', strtotime('-' . $i . ' year', time()));
                $promotionads[] = yii::$app->Myclass->getPromotionsAddsYearly(date('Y', strtotime('-' . $i . ' year', time())));
                $promotionUrgent[] = yii::$app->Myclass->getPromotionsUrgentYearly(date('Y', strtotime('-' . $i . ' year', time())));
            }
        } else {
            for ($i = 7; $i >= 0; $i--) {
                $promotionLabel[] = date("Y-m-d", strtotime("-" . $i . "days"));
            }
            for ($i = 0; $i <= 7; $i++) {
                $promotionads[] = yii::$app->Myclass->getPromotionsAdds(date("d-m-Y", strtotime($leastDate . "+" . $i . "days")));
                $promotionUrgent[] = yii::$app->Myclass->getPromotionsUrgent(date("d-m-Y", strtotime($leastDate . "+" . $i . "days")));
            }
        }
//User Graph
        $userLabel = array();
        $getRegisteredUsers = array();
        $getLoggedUsers = array();
        if (Yii::$app->session->get('reportuser') == 'daily') {
            for ($i = 7; $i >= 0; $i--) {
                $userLabel[] = date("Y-m-d", strtotime("-" . $i . "days"));
            }
            for ($i = 0; $i <= 7; $i++) {
                $getRegisteredUsers[] = yii::$app->Myclass->getRegisteredUsers(date("d-m-Y", strtotime($leastDate . "+" . $i . "days")));
                $getLoggedUsers[] = yii::$app->Myclass->getLoggedUsers(date("d-m-Y", strtotime($leastDate . "+" . $i . "days")));
            }
        } else if (Yii::$app->session->get('reportuser') == 'monthly') {
            for ($i = 7; $i >= 0; $i--) {
                $userLabel[] = date('Y-m', strtotime('-' . $i . ' month', time()));
                $getRegisteredUsers[] = yii::$app->Myclass->getRegisteredUsersMonthly(date('Y-m', strtotime('-' . $i . ' month', time())));
                $getLoggedUsers[] = yii::$app->Myclass->getLoggedUsersMonthly(date('Y-m', strtotime('-' . $i . ' month', time())));
            }
        } else if (Yii::$app->session->get('reportuser') == 'year') {
            for ($i = 7; $i >= 0; $i--) {
                $userLabel[] = date('Y', strtotime('-' . $i . ' year', time()));
                $getRegisteredUsers[] = yii::$app->Myclass->getRegisteredUsersYearly(date('Y', strtotime('-' . $i . ' year', time())));
                $getLoggedUsers[] = yii::$app->Myclass->getLoggedUsersYearly(date('Y', strtotime('-' . $i . ' year', time())));
            }
        } else {
            for ($i = 7; $i >= 0; $i--) {
                $userLabel[] = date("Y-m-d", strtotime("-" . $i . "days"));
            }
            for ($i = 0; $i <= 7; $i++) {
                $getRegisteredUsers[] = yii::$app->Myclass->getRegisteredUsers(date("d-m-Y", strtotime($leastDate . "+" . $i . "days")));
                $getLoggedUsers[] = yii::$app->Myclass->getLoggedUsers(date("d-m-Y", strtotime($leastDate . "+" . $i . "days")));
            }
        }
        $usercountLabel = array();
// User Devices
        $IOSuser = Userdevices::find()->where(['type' => '0'])->count();
        $Andrioduser = Userdevices::find()->where(['type' => '1'])->count();
        $totalUser = Users::find()->count();
        $webuser = $totalUser - ($IOSuser + $Andrioduser);
        $usercountLabel[] = ['Web', 'Android', 'IOS'];
//android phone model list
        $anmodel = Userdevices::find()
        ->where(['not', ['deviceModel' => null]])
        ->where(['type' => '1'])
        ->groupBy([trim('deviceModel')])->orderBy(['deviceModel' => SORT_DESC])
        ->all();
        foreach ($anmodel as $an) {
            $proo = $an->deviceModel;
            $anval[] = trim($proo);
        }
        foreach (array_unique($anval) as $key => $value) {
            $products = Userdevices::find()->where(['deviceModel' => $value, 'type' => 1])->all();
            $anmodellist[$value] = count($products);
        }
        $anmodellistt = arsort($anmodellist);
//android os list
        $anos = Userdevices::find()
        ->where(['not', ['deviceOS' => null]])
        ->where(['type' => '1'])
        ->groupBy([trim('deviceOS')])->orderBy(['deviceOS' => SORT_DESC])
        ->all();
        foreach ($anos as $an) {
            $proo = $an->deviceOS;
            $anosval[] = trim($proo);
        }
        foreach (array_unique($anosval) as $key => $value) {
            $products = Userdevices::find()->where(['deviceOS' => $value, 'type' => 1])->all();
            $anoslist[$value] = count($products);
        }
        $anoslistt = arsort($anoslist);
//ios phone model list
        $iosmodel = Userdevices::find()
        ->where(['not', ['deviceModel' => null]])
        ->where(['type' => '0'])
        ->groupBy([trim('deviceModel')])->orderBy(['deviceModel' => SORT_DESC])
        ->all();
        foreach ($iosmodel as $ios) {
            $proo = $ios->deviceModel;
            $iosmodelval[] = trim($proo);
        }
        foreach (array_unique($iosmodelval) as $key => $value) {
            $products = Userdevices::find()->where(['deviceModel' => $value, 'type' => 0])->all();
            $iosmodellist[$value] = count($products);
        }
        $iosmodellistt = arsort($iosmodellist);
// os list
        $iosos = Userdevices::find()
        ->where(['not', ['deviceOS' => null]])
        ->where(['type' => '0'])
        ->groupBy([trim('deviceOS')])->orderBy(['deviceOS' => SORT_DESC])
        ->all();
        foreach ($iosos as $ioss) {
            $proo = $ioss->deviceOS;
            $iosval[] = trim($proo);
        }
        foreach (array_unique($iosval) as $key => $value) {
            $products = Userdevices::find()->where(['deviceOS' => $value, 'type' => 0])->all();
            $ioslist[$value] = count($products);
        }
        $ioslistt = arsort($ioslist);
        $userCountryLabel = array();
        $userCountryValue = array();
        $GetUser = Users::find()
        ->select(['COUNT(*) AS cty', trim('country')])
        ->where(['not', ['country' => null]])
        ->groupBy([trim('country')])->orderBy(['country' => SORT_DESC])
        ->all();
        foreach ($GetUser as $users) {
            $proo = $users->country;
            $arr[] = trim($proo);
        }
        foreach (array_unique($arr) as $key => $value) {
            $products = Users::find()->where(['like', 'country', $value])->all();
            $userCountry[$value] = count($products);
        }

        $most_user_country_count = Users::find()
        ->select(['COUNT(*) AS cty', trim('country')])
        ->where(['not', ['country' => null]])
        ->groupBy([trim('country')])->orderBy(['cty' => SORT_DESC])
        ->count();

        if($most_user_country_count > 0){
            $most_user_country = Users::find()
            ->select(['COUNT(*) AS cty', trim('country')])
            ->where(['not', ['country' => null]])
            ->groupBy([trim('country')])->orderBy(['cty' => SORT_DESC])
            ->one();
            $_SESSION['countryname'] = $most_user_country->country;
        }

        if (isset($_SESSION['countryname'])) {
            $subGetUser = Users::find()
            ->select(['COUNT(*) AS cty', trim('city')])
            ->where(['like', 'country', trim($_SESSION['countryname'])])
            ->groupBy([trim('city')])->orderBy(['cty' => SORT_DESC])
            ->all();
            foreach ($subGetUser as $users) {
                $proo = $users->city;
                $subarr[] = trim($proo);
            }
            foreach (array_unique($subarr) as $key => $value) {
                $products = Users::find()->where([trim('city') => $value])->all();
                $cityValue[$value] = count($products);
            }
        } else {
            $session = Yii::$app->session;
            foreach ($userCountry as $key => $value) {
                Yii::$app->session->set('cname', $key);
                $subGetUser = Users::find()
                ->select(['COUNT(*) AS cty', trim('city')])
                ->where(['like', 'country', $key])
                ->groupBy([trim('city')])->orderBy(['cty' => SORT_DESC])
                ->all();
                break;
            }
            foreach ($subGetUser as $users) {
                $proo = $users->city;
                $subarr[] = trim($proo);
            }
            foreach (array_unique($subarr) as $key => $value) {
                $products = Users::find()->where([trim('city') => $value])->all();
                $cityValue[$value] = count($products);
            }
        }
        $subtem = arsort($cityValue);
        $Getproduct = Products::find()
        ->select(['COUNT(*) AS cty', trim('country')])
        ->where(['not', ['country' => null]])
        ->groupBy([trim('country')])->orderBy([trim('country') => SORT_DESC])
        ->all();
        foreach ($Getproduct as $users) {
            $proo = $users->country;
            $proarr[] = trim($proo);
        }
        foreach (array_unique($proarr) as $key => $value) {
            if (!empty($value)) {
                $products = Products::find()->where([trim('country') => trim($value)])->all();
                $productCountry[$value] = count($products);
            }
        }

        $most_product_country_count = Products::find()
        ->select(['COUNT(*) AS cty', trim('country')])
        ->where(['not', ['country' => null]])
        ->groupBy([trim('country')])->orderBy(['cty' => SORT_DESC])
        ->count();

        if($most_product_country_count > 0){
            $most_product_country = Products::find()
            ->select(['COUNT(*) AS cty', trim('country')])
            ->where(['not', ['country' => null]])
            ->groupBy([trim('country')])->orderBy(['cty' => SORT_DESC])
            ->one();
            $_SESSION['product_countrycode'] = $most_product_country->country;

            $countries = Country::find()->where(['code' => trim($most_product_country->country)])->one();
            $_SESSION['product_countryname'] = ($countries->country) ? $countries->country : $most_product_country->country;

        }

        /*if($_SESSION['product_countrycode'] = "" && $_SESSION['product_countryname'] == "")
            {
                $_SESSION['product_countrycode'] = 100;
                $_SESSION['product_countryname'] == "India";
            }*/

        if (isset($_SESSION['product_countrycode'])) {
            $query = new \yii\db\Query;
            $query->select(['count(hts_products.city) as counter', 'hts_products.city', 'hts_country.country as countryname'])->from('hts_products')
            ->leftJoin('hts_country', 'hts_country.code = hts_products.country')
            ->where(['!=', 'hts_products.country', 'NULL'])
            ->andWhere(['!=', 'hts_products.country', ''])
            ->andWhere(['!=', 'hts_products.city', ''])
            ->andWhere(['!=', 'hts_products.city', 'NULL'])
            ->andWhere(['=', 'hts_products.country', $_SESSION['product_countrycode']])
            ->groupBy('hts_products.city')
            ->orderBy('counter desc');
            $countQuery = clone $query;
            $procityValue = $countQuery->createCommand()->queryAll();
        } else {
            $query = new \yii\db\Query;
            $query->select(['count(hts_products.city) as counter', 'hts_products.city', 'hts_country.country as countryname'])->from('hts_products')
            ->leftJoin('hts_country', 'hts_country.code = hts_products.country')
            ->where(['!=', 'hts_products.country', 'NULL'])
            ->andWhere(['!=', 'hts_products.country', ''])
            ->andWhere(['=', 'hts_products.country', "IN"])
            ->groupBy('hts_products.city')
            ->orderBy('counter desc');
            $countQuery = clone $query;
            $procityValue = $countQuery->createCommand()->queryAll();
        }
        $getActiveUsers = yii::$app->Myclass->getActiveUsers(date("d-m-Y"));
        return $this->render('index', [
            'mystring' => $mystring,
            'count' => $count,
            'leastDate' => $leastDate,
            'getActiveUsers' => $getActiveUsers,
            'getActiveLoggedUsers' => $getActiveLoggedUsers,
            'totalRevenue' => $totalRevenue,
            'catLabel' => $catLabel,
            'catValue' => $catValue,
            'promotionads' => $promotionads,
            'promotionUrgent' => $promotionUrgent,
            'promotionLabel' => $promotionLabel,
            'getRegisteredUsers' => $getRegisteredUsers,
            'getLoggedUsers' => $getLoggedUsers,
            'userLabel' => $userLabel,
            'IOSuser' => $IOSuser,
            'Andrioduser' => $Andrioduser,
            'IOSuser' => $IOSuser,
            'Andrioduser' => $Andrioduser,
            'webuser' => $webuser,
            'usercountLabel' => $usercountLabel,
            'productcoun' => $productcoun,
            'anmodellist' => $anmodellist,
            'anoslist' => $anoslist,
            'iosmodellist' => $iosmodellist,
            'ioslist' => $ioslist,
            'userCountry' => $usercoun,
            'cityValue' => $cityValue,
            'productCountry' => $productCountry,
            'androidmodelLabel' => $androidmodelLabel,
            'androidmodelValue' => $androidmodelValue,
            'iosmodelLabel' => $iosmodelLabel,
            'iosmodelValue' => $iosmodelValue,
            'procityValue' => $procityValue]);
    }
    public function actionTest()
    {
//android phone model list
        $anmodel = Userdevices::find()
        ->where(['not', ['deviceModel' => null]])
        ->where(['type' => '1'])
        ->groupBy([trim('deviceModel')])->orderBy(['deviceModel' => SORT_DESC])
        ->all();
        foreach ($anmodel as $an) {
            $proo = $an->deviceModel;
            $anval[] = trim($proo);
        }
        foreach (array_unique($anval) as $key => $value) {
            $products = Userdevices::find()->where(['deviceModel' => $value, 'type' => 1])->all();
            $anmodellist[$value] = count($products);
        }
        $anmodellistt = arsort($anmodellist);
//android os list
        $anos = Userdevices::find()
        ->where(['not', ['deviceOS' => null]])
        ->where(['type' => '1'])
        ->groupBy([trim('deviceOS')])->orderBy(['deviceOS' => SORT_DESC])
        ->all();
        foreach ($anos as $an) {
            $proo = $an->deviceOS;
            $anosval[] = trim($proo);
        }
        foreach (array_unique($anosval) as $key => $value) {
            $products = Userdevices::find()->where(['deviceOS' => $value, 'type' => 1])->all();
            $anoslist[$value] = count($products);
        }
        $anoslistt = arsort($anoslist);
//ios phone model list
        $iosmodel = Userdevices::find()
        ->where(['not', ['deviceModel' => null]])
        ->where(['type' => '0'])
        ->groupBy([trim('deviceModel')])->orderBy(['deviceModel' => SORT_DESC])
        ->all();
        foreach ($iosmodel as $ios) {
            $proo = $ios->deviceModel;
            $iosmodelval[] = trim($proo);
        }
        foreach (array_unique($iosmodelval) as $key => $value) {
            $products = Userdevices::find()->where(['deviceModel' => $value, 'type' => 0])->all();
            $iosmodellist[$value] = count($products);
        }
        $iosmodellistt = arsort($iosmodellist);
// os list
        $iosos = Userdevices::find()
        ->where(['not', ['deviceOS' => null]])
        ->where(['type' => '0'])
        ->groupBy([trim('deviceOS')])->orderBy(['deviceOS' => SORT_DESC])
        ->all();
        foreach ($iosos as $ioss) {
            $proo = $ioss->deviceOS;
            $iosval[] = trim($proo);
        }
        foreach (array_unique($iosval) as $key => $value) {
            $products = Userdevices::find()->where(['deviceOS' => $value, 'type' => 0])->all();
            $ioslist[$value] = count($products);
        }
        $ioslistt = arsort($ioslist);
        return $this->render('test', ['anmodellist' => $anmodellist, 'anoslist' => $anoslist, 'iosmodellist' => $iosmodellist, 'ioslist' => $ioslist]);
    }
    public function actionTestpro()
    {
        $userCountryLabel = array();
        $userCountryValue = array();
        $GetUser = Users::find()
        ->select(['COUNT(*) AS cty', trim('country')])
        ->where(['not', ['country' => null]])
        ->groupBy([trim('country')])->orderBy(['country' => SORT_DESC])
        ->all();
        foreach ($GetUser as $users) {
            $proo = $users->country;
            $arr[] = trim($proo);
        }
        foreach (array_unique($arr) as $key => $value) {
            $products = Users::find()->where(['like', 'country', $value])->all();
            $userCountry[$value] = count($products);
        }
        if (isset($_SESSION['countrycode'])) {
            $subGetUser = Users::find()
            ->select(['COUNT(*) AS cty', trim('city')])
            ->where([trim('country') => $_SESSION['countryname']])
            ->groupBy([trim('city')])->orderBy(['city' => SORT_DESC])
            ->all();
            foreach ($subGetUser as $users) {
                $proo = $users->city;
                $subarr[] = trim($proo);
            }
            foreach (array_unique($subarr) as $key => $value) {
                $products = Users::find()->where([trim('city') => $value])->all();
                $cityValue[$value] = count($products);
            }
        } else {
            $subGetUser = Users::find()
            ->select(['COUNT(*) AS cty', trim('city')])
            ->where([trim('country') => 'India'])
            ->groupBy([trim('city')])->orderBy(['city' => SORT_DESC])
            ->all();
            foreach ($subGetUser as $users) {
                $proo = $users->city;
                $subarr[] = trim($proo);
            }
            foreach (array_unique($subarr) as $key => $value) {
                $products = Users::find()->where([trim('city') => $value])->all();
                $cityValue[$value] = count($products);
            }
        }
        $subtem = arsort($cityValue);
        return $this->render('testpro', ['userCountry' => $userCountry, 'cityValue' => $cityValue]);
    }
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect('index');
        }
        $this->layout = "login";
        $model = new AdminLoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            $logindata = Admin::find()->where(['email' => $model->username])->one();//print_r($logindata->name);die();
            Yii::$app->session->setFlash('success', Yii::t('app', 'Welcome ' . $logindata->name . '..!'));
            //print_r('hi');die();
            return $this->redirect(['index']);
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }
    public function actionLogout()
    {
        Yii::$app->user->logout(false);
        Yii::$app->session->setFlash('success', Yii::t('app', 'Logged out'));
        return $this->goHome();
    }
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionSendpushnot()
    {
        if (isset($_GET['adminData']) && !empty($_GET['adminData'])) {
            $message = $_GET['adminData'];
            $notifyMessage = 'sent message';
            yii::$app->Myclass->addLogs("admin", 0, 0, 0, 0, $notifyMessage, 0, $message);
            $userdevicedet = Userdevices::find()->all();
            if (count($userdevicedet) > 0) {
                foreach ($userdevicedet as $userdevice) {
                    $deviceToken = $userdevice->deviceToken;
                    $badge = $userdevice->badge;
                    $badge += 1;
                    $userdevice->badge = $badge;
                    $userdevice->deviceToken = $deviceToken;
                    $userdevice->save(false);

                    if (isset($deviceToken)) {
                        $messages = $message;
                        yii::$app->Myclass->pushnot($deviceToken, $messages, $badge, 'admin');
                    }
                }
            }
            // Yii::$app->session->setFlash('success',Yii::t('app','This option not available for Demo'));
            Yii::$app->session->setFlash('success', 'Notification Send Successfully!');
            return $this->redirect(['notification']);
        } else {
            echo "error";
        }
    }

    public function actionSaveandroidkey()
    {
        $androidkey = $_GET['androidkey'];
        $siteSettingsModel = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        echo $siteSettingsModel->androidkey = $androidkey;
        $siteSettingsModel->save(false);
        // Yii::$app->session->setFlash('success',Yii::t('app','This option not available for Demo'));

        Yii::$app->session->setFlash('success', Yii::t('app', 'Android key saved!'));
    }
    public function actionProfile()
    {
        $guestModel = new Admin();
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $id = Yii::$app->user->id;
        $model = Admin::find()->where(['id' => $id])->one();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate(array('username', 'name'))) {
                $model->username = $_POST['Admin']['username'];
                $model->email = $_POST['Admin']['username'];
                $model->name = $_POST['Admin']['name'];
                $model->save(false);
//Yii::$app->session->setFlash('success',Yii::t('app','This option not available for Demo'));
                Yii::$app->session->setFlash('success', Yii::t('app', 'Profile Setting Updated!'));
                return $this->redirect(['profile']);
            } else {
                $errors = $model->errors;
            }
        }
        return $this->render('profile', [
            'model' => $model,
        ]);
    }
    public function actionRequestpasswordreset()
    {
        $model = new AdminPasswordResetRequestForm();
        $setting = Admin::find()->where(['id' => Yii::$app->user->id])->one();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $sitesetting = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
            $mailer = Yii::$app->mailer->setTransport([
                'class' => 'Swift_SmtpTransport',
                'host' => $sitesetting['smtpHost'],
                'username' => $sitesetting['smtpEmail'],
                'password' => $sitesetting['smtpPassword'],
                'port' => $sitesetting['smtpPort'],
                'encryption' => 'tls',
            ]);
            try
            {
                if ($model->sendEmail()) {
                    Yii::$app->user->logout();
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Check your email for further instructions'));
                    $guestModel = new Admin();
                    if (Yii::$app->user->isGuest) {
                        return $this->goHome();
                    }
                } else {
                    Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address');
                }
            } catch (\Swift_TransportException $exception) {
                Yii::$app->session->setFlash('error', 'Sorry, SMTP Connection error check email setting');
            }
        }
        return $this->render('requestpasswordreset', [
            'model' => $model, 'setting' => $setting,
        ]);
    }
    public function actionResetPassword($token)
    {
        $this->layout = "login";
        try {
            $model = new AdminResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'New password saved'));
            $guestModel = new Admin();
            if (Yii::$app->user->isGuest) {
                return $this->goHome();
            }
        }
        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
    public function actionLanguage()
    {
        $language = $_GET['language'];
        $session = Yii::$app->session;
        Yii::$app->session->set('language', $language);
        return "";
    }
    public function actionData()
    {
        $items = $_GET['items'];
        $session = Yii::$app->session;
        Yii::$app->session->set('reportItems', $items);
        return "";
    }
    public function actionDatapromotions()
    {
        $promotions = $_GET['promotions'];
        $session = Yii::$app->session;
        Yii::$app->session->set('reportpromotions', $promotions);
        return "";
    }
    public function actionDatauser()
    {
        $user = $_GET['user'];
        $session = Yii::$app->session;
        Yii::$app->session->set('reportuser', $user);
        return "";
    }
    public function actionDatarevenue()
    {
        $revenue = $_GET['revenue'];
        $session = Yii::$app->session;
        Yii::$app->session->set('reportRevenue', $revenue);
        return "";
    }
    public function actionDatapromotionrevenue()
    {
        $promotions = $_GET['promotions'];
        $session = Yii::$app->session;
        Yii::$app->session->set('promotionsRevenue', $promotions);
        return "";
    }
    public function actionDataincome()
    {
        $dailyincome = $_GET['dailyincome'];
        $session = Yii::$app->session;
        Yii::$app->session->set('reportIncome', $dailyincome);
        return "";
    }
    public function actionError()
    {
        $this->layout = "login";
        return $this->render('error');
    }
    public function actionChangepassword()
    {
        $id = Yii::$app->user->id;
        $user = Admin::find()->where(['id' => $id])->one();
        try {
            $model = new \backend\models\PasswordForm($id);
        } catch (InvalidParamException $e) {
            throw new \yii\web\BadRequestHttpException($e->getMessage());
        }
        if ($model->load(\Yii::$app->request->post()) && $model->validate() && $model->changePassword()) {
        \Yii::$app->session->setFlash('success', Yii::t('app', 'Password Changed!'));
        return $this->refresh();
        }
        // \Yii::$app->session->setFlash('success', Yii::t('app', 'This option not available for Demo!'));

        return $this->render('changepassword', ['model' => $model, 'user' => $user]);
    }
    public function actionNotification()
    {
        $guestModel = new Admin();
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $query = Logs::find()->where(['type' => 'admin']);
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $model = $query->offset($pages->offset)
        ->limit($pages->limit)
        ->orderBy(['id' => SORT_DESC])
        ->all();
        return $this->render('notification', ['model' => $model, 'pages' => $pages]);
    }
    public function actionDelete($id)
    {
        $getLog = Logs::findOne($id);
        if (!empty($getLog)) {
            Logs::findOne($id)->delete();
            Yii::$app->session->setFlash('success', Yii::t('app', 'Message logs deleted'));
            return $this->redirect(['notification']);
        } else {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Log details are invalid.'));
            return $this->redirect(['index']);
        }
    }
    public function actionRevenue()
    {
        $guestModel = new Admin();
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $order = new Orders();
        $bannerAmount = Banners::find()->where(['status' => "approved"])->all();
        foreach ($bannerAmount as $paidbanner) {
            $getAmt = $paidbanner->totalCost;
            $total = $total + $getAmt;
        }
        $OrderTotal = $total;
        $sitesettingsModel = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
//Promotions Amt
        $promotionAmt = Promotiontransaction::find()->sum('promotionPrice');
        $adspromotionAmt = Promotiontransaction::find()->where(['promotionName' => 'adds'])->sum('promotionPrice');
        $urgentpromotionAmt = Promotiontransaction::find()->where(['promotionName' => 'urgent'])->sum('promotionPrice');
        if ($sitesettingsModel->promotionStatus == 0 && $sitesettingsModel->paidbannerstatus == 0) {
            $totalRevenue = 0;
        } elseif ($sitesettingsModel->promotionStatus == 0) {
            $totalRevenue = $total;
        } elseif ($sitesettingsModel->paidbannerstatus == 0) {
            $totalRevenue = $promotionAmt;
        } else {
            $totalRevenue = $promotionAmt + $total;
        }
//Revenue Chart
        $label = array();
        $getTotalrevenue = array();
        if (Yii::$app->session->get('reportRevenue') == 'daily') {
            for ($i = 7; $i >= 0; $i--) {
                $label[] = date("d-m-Y", strtotime("-" . $i . "days"));
                $getTotalrevenue[] = yii::$app->Myclass->getrevenueTotal(date("d-m-Y", strtotime("-" . $i . "days")));
            }
        } else if (Yii::$app->session->get('reportRevenue') == 'monthly') {
            for ($i = 7; $i >= 0; $i--) {
                $label[] = date('Y-m', strtotime('-' . $i . ' month', time()));
                $getTotalrevenue[] = yii::$app->Myclass->getrevenueTotalMonthly(date('Y-m', strtotime('-' . $i . ' month', time())));
            }
        } else if (Yii::$app->session->get('reportRevenue') == 'year') {
            for ($i = 7; $i >= 0; $i--) {
                $label[] = date('Y', strtotime('-' . $i . ' year', time()));
                $getTotalrevenue[] = yii::$app->Myclass->getrevenueTotalYearly(date('Y', strtotime('-' . $i . ' year', time())));
            }
        } else {
            for ($i = 7; $i >= 0; $i--) {
                $label[] = date("d-m-Y", strtotime("-" . $i . "days"));
                $getTotalrevenue[] = yii::$app->Myclass->getrevenueTotal(date("d-m-Y", strtotime("-" . $i . "days")));
            }
        }
//Promotion Graph
        $currentLabel = array();
        $currentadPromotion = array();
        $currentUrgentPromotion = array();
        if (Yii::$app->session->get('promotionsRevenue') == 'daily') {
            for ($i = 0; $i <= 7; $i++) {
                $currentLabel[] = date('d-m-Y', strtotime('-' . $i . ' days', time()));
            }
            for ($i = 7; $i >= 0; $i--) {
                $currentadPromotion[] = yii::$app->Myclass->getPromotionRevDaily(date('d-m-Y', strtotime('-' . $i . ' days', time())));
                $currentUrgentPromotion[] = yii::$app->Myclass->getPromotionUrgent(date('d-m-Y', strtotime('-' . $i . ' days', time())));
            }
        } else if (Yii::$app->session->get('promotionsRevenue') == 'monthly') {
            for ($i = 7; $i >= 0; $i--) {
                $currentLabel[] = date('Y-m', strtotime('-' . $i . ' month', time()));
                $currentadPromotion[] = yii::$app->Myclass->getPromotionRevMonthly(date('Y-m', strtotime('-' . $i . ' month', time())));
                $currentUrgentPromotion[] = yii::$app->Myclass->getPromotionUrgentMonthly(date('Y-m', strtotime('-' . $i . ' month', time())));
            }
        } else if (Yii::$app->session->get('promotionsRevenue') == 'year') {
            for ($i = 7; $i >= 0; $i--) {
                $currentLabel[] = date('Y', strtotime('-' . $i . ' year', time()));
                $currentadPromotion[] = yii::$app->Myclass->getPromotionRevYearly(date('Y', strtotime('-' . $i . ' year', time())));
                $currentUrgentPromotion[] = yii::$app->Myclass->getPromotionUrgentYearly(date('Y', strtotime('-' . $i . ' year', time())));
            }
        } else {
            for ($i = 0; $i <= 7; $i++) {
                $currentLabel[] = date('d-m-Y', strtotime('-' . $i . ' days', time()));
            }
            for ($i = 7; $i >= 0; $i--) {
                $currentadPromotion[] = yii::$app->Myclass->getPromotionRevDaily(date('d-m-Y', strtotime('-' . $i . ' days', time())));
                $currentUrgentPromotion[] = yii::$app->Myclass->getPromotionUrgent(date('d-m-Y', strtotime('-' . $i . ' days', time())));
            }
        }
///Daily Income Chart
        $labeltoRevenue = array();
        $getDailyrevenue = array();
        if (Yii::$app->session->get('reportIncome') == 'daily') {
            for ($i = 7; $i >= 0; $i--) {
                $labeltoRevenue[] = date("d-m-Y", strtotime("-" . $i . "days"));
                $getDailyrevenue[] = yii::$app->Myclass->getDailyRevenue(date("d-m-Y", strtotime("-" . $i . "days")));
            }
        } else if (Yii::$app->session->get('reportIncome') == 'monthly') {
            for ($i = 7; $i >= 0; $i--) {
                $labeltoRevenue[] = date('Y-m', strtotime('-' . $i . ' month', time()));
                $getDailyrevenue[] = yii::$app->Myclass->getMonthlyRevenue(date('Y-m', strtotime('-' . $i . ' month', time())));
            }
        } else if (Yii::$app->session->get('reportIncome') == 'year') {
            for ($i = 7; $i >= 0; $i--) {
                $labeltoRevenue[] = date('Y', strtotime('-' . $i . ' year', time()));
                $getDailyrevenue[] = yii::$app->Myclass->getYearlyRevenue(date('Y', strtotime('-' . $i . ' year', time())));
            }
        } else {
            for ($i = 7; $i >= 0; $i--) {
                $labeltoRevenue[] = date("d-m-Y", strtotime("-" . $i . "days"));
                $getDailyrevenue[] = yii::$app->Myclass->getDailyRevenue(date("d-m-Y", strtotime("-" . $i . "days")));
            }
        }
//Top city products
        $product = Products::find()
        ->select(['COUNT(*) AS cty', trim('city')])
        ->where(['not', ['city' => null]])
        ->groupBy([trim('city')])->orderBy(['city' => SORT_DESC])
        ->all();
        foreach ($product as $pro) {
            $proo = $pro->city;
            $arr[] = trim($proo);
        }
        foreach (array_unique($arr) as $key => $value) {
            $products = Products::find()->where(['like', 'city', $value])->all();
            $pcounlist[$value] = count($products);
        }
        $temp = arsort($pcounlist);
        $productcoun = array_slice($pcounlist, 0, 7);
        return $this->render('revenue', ['paidbanner' => $OrderTotal, 'totalRevenue' => $totalRevenue, 'promotionAmt' => $promotionAmt,
            'sitesetting' => $sitesettingsModel,
            'adspromotionAmt' => $adspromotionAmt, 'urgentpromotionAmt' => $urgentpromotionAmt, 'getTotalrevenue' => $getTotalrevenue,
            'label' => $label, 'promotionlabel' => $promotionlabel, 'adsPromotion' => $adsPromotionRevenue, 'urgentPromotion' => $urgentPromotionRevenue,
            'currentLabel' => $currentLabel, 'currentadPromotion' => $currentadPromotion, 'currentUrgentPromotion' => $currentUrgentPromotion,
            'labeltoRevenue' => $labeltoRevenue, 'getDailyrevenue' => $getDailyrevenue, 'counlist' => $productcoun]);
    }
    public function actionRevenuelog()
    {
        $guestModel = new Admin();
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $this->layout = "page";
        $searchModel = new OrdersSearchlog();
        $data = Yii::$app->request->queryParams;
        if(isset($data['orderDate']) && ($data['orderDate'] == "" || $data['enddate'] == "")){
            return $this->redirect(['revenuelog']);
        }
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 25;
        return $this->render('revenuelog', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionPromotion()
    {
        $guestModel = new Admin();
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $this->layout = "page";
        $searchModel = new PromotiontransactionSearch();
        $data = Yii::$app->request->queryParams;
        if(isset($data['orderDate']) && ($data['orderDate'] == "" || $data['enddate'] == "")){
            return $this->redirect(['promotion']);
        }
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('promotion', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionPaidbanner()
    {
        $guestModel = new Admin();
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $this->layout = "page";
        $searchModel = new BannerapprovedSearch();
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $currencySymbols = explode("-", $siteSettings->bannerCurrency);
        $currencySymbol = trim($currencySymbols[0]);
        $data = Yii::$app->request->queryParams;
        if(isset($data['orderDate']) && ($data['orderDate'] == "" || $data['enddate'] == "")){
            return $this->redirect(['paidbannerlog']);
        }
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 10;
        return $this->render('paidbannerlog', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'sitesettings' => $siteSettings,
            'selectedcurrency' => $currencySymbol,
        ]);
    }
    public function actionExportexcel($start = null, $end = null)
    {
        if ($start != "") {
            $start = date("Y-m-d", strtotime($start));
        }
        if ($end != "") {
            $end = date("Y-m-d", strtotime($end));
        }
        $criteria = Orders::find()->where(['or', ['status' => 'delivered'], ['status' => 'paid']]);
        if (!empty($start) && !empty($end)) {
            $criteria->andWhere(['>=', "date_format(FROM_UNIXTIME(`orderDate`), '%Y-%m-%d')", $start]);
            $criteria->andWhere(['<=', "date_format(FROM_UNIXTIME(`orderDate`), '%Y-%m-%d')", $end]);
        }
        if (!empty($start) && empty($end)) {
            $criteria->andWhere(['>=', "date_format(FROM_UNIXTIME(`orderDate`), '%Y-%m-%d')", $start]);
        }
        if (empty($start) && !empty($end)) {
            $criteria->andWhere(['<=', "date_format(FROM_UNIXTIME(`orderDate`), '%Y-%m-%d')", $end]);
        }
        $Project = $criteria->orderBy(['orderId' => SORT_DESC])->all();
        $filename = 'Revenue_report.xls';
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=" . $filename);
        echo '<table width="100%" border="1">
        <thead>
        <tr>
        <th>Order Id</th>
        <th>Buyer</th>
        <th>Seller</th>
        <th>Commission</th>
        <th>Item Cost</th>
        <th>Order Date</th>
        </tr>
        </thead>';
        foreach ($Project as $prj) {
            $date = date_create(date('Y-m-d', $prj->orderDate));
            $shipping = (float) $prj->totalShipping;
            $totalcost = (float) $prj->totalCost;
            $itemcost = (float) $prj->totalCost - (float) $prj->totalShipping;
            $commission = (float) $prj->getCommission();
            $paymethod = (float) $prj->getItemcost();
            echo '
            <tr>
            <td>' . $prj->orderId . '</td>
            <td>' . yii::$app->Myclass->getUsername($prj->userId) . '</td>
            <td>' . yii::$app->Myclass->getUsername($prj->sellerId) . '</td>
            <td>' . $commission . '</td>
            <td>' . $paymethod . '</td>
            <td>' . Yii::t('app', date_format($date, "d-m-Y")) . '</td>
            </tr>
            ';
        }
        echo '</table>';
        die;
    }
    public function actionPaidbannerexcel($start = null, $end = null, $type = null)
    {
        if ($start != "") {
            $start = date("Y-m-d", strtotime($start));
        }
        if ($end != "") {
            $end = date("Y-m-d", strtotime($end));
        }
        $criteria = Banners::find()->where(['status' => "approved"]);
        if (!empty($start) && !empty($end)) {
            $criteria->andWhere(['>=', 'createdDate', $start]);
            $criteria->andWhere(['<=', 'createdDate', $end]);
        }
        if (!empty($start) && empty($end)) {
            $criteria->andWhere(['>=', 'createdDate', $start]);
        }
        if (empty($start) && !empty($end)) {
            $criteria->andWhere(['<=', 'createdDate', $end]);
        }
        $Project = $criteria->orderBy(['createdDate' => SORT_ASC])->all();
        $filename = 'Paidbanners_report.xls';
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=" . $filename);
        echo '<table width="100%" border="1">
        <thead>
        <tr>
        <th>Banner Id</th>
        <th>Total Cost</th>
        <th>Total Days</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>Posted On</th>
        </tr>
        </thead>';
        foreach ($Project as $prj) {
            echo '
            <tr>
            <td>' . $prj->id . '</td>
            <td>' . $prj->totalCost . '</td>
            <td>' . $prj->totaldays . '</td>
            <td>' . $prj->startdate . '</td>
            <td>' . $prj->enddate . '</td>
            <td>' . $prj->createdDate . '</td>
            </tr>
            ';
        }
        echo '</table>';
    }
    public function actionPromotionexcel($start = null, $end = null, $type = null)
    {
        if ($start != "") {
            $start = date("m-d-Y", strtotime($start));
        }
        if ($end != "") {
            $end = date("m-d-Y", strtotime($end));
        }
        if ($type != "" && $type == 'all') {
            $criteria = Promotiontransaction::find();
        } else {
            $criteria = Promotiontransaction::find()->where(['promotionName' => $type]);
        }
        if (!empty($start) && !empty($end)) {
            $criteria->andWhere(['>=', "date_format(FROM_UNIXTIME(`createdDate`), '%m-%d-%Y')", $start]);
            $criteria->andWhere(['<=', "date_format(FROM_UNIXTIME(`createdDate`), '%m-%d-%Y')", $end]);
        }
        if (!empty($start) && empty($end)) {
            $criteria->andWhere(['>=', "date_format(FROM_UNIXTIME(`createdDate`), '%m-%d-%Y')", $start]);
        }
        if (empty($start) && !empty($end)) {
            $criteria->andWhere(['<=', "date_format(FROM_UNIXTIME(`createdDate`), '%m-%d-%Y')", $end]);
        }
        $Project = $criteria->orderBy(['productId' => SORT_DESC])->all();
        $filename = 'Promotions_report.xls';
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=" . $filename);
        echo '<table width="100%" border="1">
        <thead>
        <tr>
        <th>Product Id</th>
        <th>User Name</th>
        <th>Promotion Price</th>
        <th>Promotion Type</th>
        <th>Created Date</th>
        </tr>
        </thead>';
        foreach ($Project as $prj) {
            $date = date_create(date('Y-m-d', $prj->createdDate));
            echo '
            <tr>
            <td>' . $prj->productId . '</td>
            <td>' . yii::$app->Myclass->getUserName($prj->userId) . '</td>
            <td>' . $prj->promotionPrice . '</td>
            <td>' . $prj->promotionName . '</td>
            <td>' . Yii::t('app', date_format($date, "d-m-Y")) . '</td>
            </tr>
            ';
        }
        echo '</table>';
    }
    public function actionGraph()
    {
        return $this->render('graph');
    }
    public function actionChangeregion()
    {
        $countrycode = Country::find()->where(['country' => $_GET['name']])->one();
        $session = Yii::$app->session;
        Yii::$app->session->set('countrycode', $countrycode->code);
        Yii::$app->session->set('countryname', $_GET['name']);
        if (isset($_SESSION['countrycode'])) {
            $subGetUser = Users::find()
            ->select(['COUNT(*) AS cty', trim('city')])
            ->where(['like', 'country', trim($_SESSION['countryname'])])
            ->groupBy([trim('city')])->orderBy(['cty' => SORT_DESC])
            ->all();
            foreach ($subGetUser as $users) {
                $proo = $users->city;
                $subarr[] = trim($proo);
            }
            foreach (array_unique($subarr) as $key => $value) {
                $products = Users::find()->where([trim('city') => $value])->all();
                $cityValue[$value] = count($products);
            }
        }
        echo json_encode($cityValue);
    }
    public function actionChangeproregion()
    {
        $countrycode = Country::find()->where(['country' => trim($_GET['name'])])->one();
        $session = Yii::$app->session;
        Yii::$app->session->set('product_countrycode', $countrycode->code);
        Yii::$app->session->set('product_countryname', trim($_GET['name']));
        if (isset($_SESSION['product_countrycode'])) {
            $query = new \yii\db\Query;
            $query->select(['count(hts_products.city) as counter', 'hts_products.city', 'hts_country.country as countryname'])->from('hts_products')
            ->leftJoin('hts_country', 'hts_country.code = hts_products.country')
            ->where(['!=', 'hts_products.country', 'NULL'])
            ->andWhere(['!=', 'hts_products.country', ''])
            ->andWhere(['!=', 'hts_products.city', ''])
            ->andWhere(['!=', 'hts_products.city', 'NULL'])
            ->andWhere(['=', 'hts_products.country', $_SESSION['product_countrycode']])
            ->groupBy('hts_products.city')
            ->orderBy('hts_products.city asc');
            $countQuery = clone $query;
            $procityValue = $countQuery->createCommand()->queryAll();
        }
        $subprotem = arsort($procityValue);
        echo json_encode($procityValue);
    }
    public function actionItemsdata()
    {
        $type = $_GET['items'];
        $mystring = array();
        $count = array();
        $leastDate = date("d-m-Y", strtotime("-7 days"));
        if ($type == 'daily') {
            for ($i = 7; $i >= 0; $i--) {
                $mystring[] = date("Y-m-d", strtotime("-" . $i . "days"));
                $count[] = yii::$app->Myclass->getItemsAdded(date("d-m-Y", strtotime("-" . $i . "days")));
            }
        } else if ($type == 'monthly') {
            for ($i = 7; $i >= 0; $i--) {
                $mystring[] = date('Y-m', strtotime('-' . $i . ' month', time()));
                $count[] = yii::$app->Myclass->getItemsAddedMonthly(date('Y-m', strtotime('-' . $i . ' month', time())));
            }
        } else if ($type == 'year') {
            for ($i = 7; $i >= 0; $i--) {
                $mystring[] = date('Y', strtotime('-' . $i . ' year', time()));
                $count[] = yii::$app->Myclass->getItemsAddedYearly(date('Y', strtotime('-' . $i . ' year', time())));
            }
        }
        echo json_encode($mystring) . '||' . json_encode($count);
    }
    public function actionPromotiondata()
    {
        $type = $_GET['promotions'];
        $promotionads = array();
        $promotionUrgent = array();
        $promotionLabel = array();
        $leastDate = date("d-m-Y", strtotime("-7 days"));
        if ($type == 'daily') {
            for ($i = 7; $i >= 0; $i--) {
                $promotionLabel[] = date('d-m-Y', strtotime('-' . $i . ' days', time()));
                $promotionads[] = yii::$app->Myclass->getPromotionsAdds(date("d-m-Y", strtotime('-' . $i . "days")));
                $promotionUrgent[] = yii::$app->Myclass->getPromotionsUrgent(date("d-m-Y", strtotime('-' . $i . "days")));
            }
        } else if ($type == 'monthly') {
            for ($i = 7; $i >= 0; $i--) {
                $promotionLabel[] = date('Y-m', strtotime('-' . $i . ' month', time()));
                $promotionads[] = yii::$app->Myclass->getPromotionsAddsMonthly(date('Y-m', strtotime('-' . $i . ' month', time())));
                $promotionUrgent[] = yii::$app->Myclass->getPromotionsUrgentMonthly(date('Y-m', strtotime('-' . $i . ' month', time())));
            }
        } else if ($type == 'year') {
            for ($i = 7; $i >= 0; $i--) {
                $promotionLabel[] = date('Y', strtotime('-' . $i . ' year', time()));
                $promotionads[] = yii::$app->Myclass->getPromotionsAddsYearly(date('Y', strtotime('-' . $i . ' year', time())));
                $promotionUrgent[] = yii::$app->Myclass->getPromotionsUrgentYearly(date('Y', strtotime('-' . $i . ' year', time())));
            }
        }
        echo json_encode($promotionLabel) . '||' . json_encode($promotionads) . '||' . json_encode($promotionUrgent);
    }
    public function actionUserdata()
    {
        $type = $_GET['user'];
        $userLabel = array();
        $getRegisteredUsers = array();
        $leastDate = date("d-m-Y", strtotime("-7 days"));
        $getLoggedUsers = array();
        if ($type == 'daily') {
            for ($i = 7; $i >= 0; $i--) {
                $userLabel[] = date("Y-m-d", strtotime("-" . $i . "days"));
            }
            for ($i = 0; $i <= 7; $i++) {
                $getRegisteredUsers[] = yii::$app->Myclass->getRegisteredUsers(date("d-m-Y", strtotime($leastDate . "+" . $i . "days")));
                $getLoggedUsers[] = yii::$app->Myclass->getLoggedUsers(date("d-m-Y", strtotime($leastDate . "+" . $i . "days")));
            }
        } else if ($type == 'monthly') {
            for ($i = 7; $i >= 0; $i--) {
                $userLabel[] = date('Y-m', strtotime('-' . $i . ' month', time()));
                $getRegisteredUsers[] = yii::$app->Myclass->getRegisteredUsersMonthly(date('Y-m', strtotime('-' . $i . ' month', time())));
                $getLoggedUsers[] = yii::$app->Myclass->getLoggedUsersMonthly(date('Y-m', strtotime('-' . $i . ' month', time())));
            }
        } else if ($type == 'year') {
            for ($i = 7; $i >= 0; $i--) {
                $userLabel[] = date('Y', strtotime('-' . $i . ' year', time()));
                $getRegisteredUsers[] = yii::$app->Myclass->getRegisteredUsersYearly(date('Y', strtotime('-' . $i . ' year', time())));
                $getLoggedUsers[] = yii::$app->Myclass->getLoggedUsersYearly(date('Y', strtotime('-' . $i . ' year', time())));
            }
        }
        echo json_encode($userLabel) . '||' . json_encode($getRegisteredUsers) . '||' . json_encode($getLoggedUsers);
    }
    public function actionIncomedata()
    {
        $type = $_GET['dailyincome'];
        $labeltoRevenue = array();
        $getDailyrevenue = array();
        if ($type == 'daily') {
            for ($i = 7; $i >= 0; $i--) {
                $labeltoRevenue[] = date("d-m-Y", strtotime("-" . $i . "days"));
                $getDailyrevenue[] = yii::$app->Myclass->getDailyRevenue(date("d-m-Y", strtotime("-" . $i . "days")));
            }
        } else if ($type == 'monthly') {
            for ($i = 7; $i >= 0; $i--) {
                $labeltoRevenue[] = date('Y-m', strtotime('-' . $i . ' month', time()));
                $getDailyrevenue[] = yii::$app->Myclass->getMonthlyRevenue(date('Y-m', strtotime('-' . $i . ' month', time())));
            }
        } else if ($type == 'year') {
            for ($i = 7; $i >= 0; $i--) {
                $labeltoRevenue[] = date('Y', strtotime('-' . $i . ' year', time()));
                $getDailyrevenue[] = yii::$app->Myclass->getYearlyRevenue(date('Y', strtotime('-' . $i . ' year', time())));
            }
        }
        $siteSetting = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $sitePaymentMode = json_decode($siteSetting->sitepaymentmodes);
        if ($sitePaymentMode->buynowPaymentMode == 1) {
            $itemlist[] = ['Date', 'Features', 'Ads', 'Orders', ['role' => 'annotation']];
        } else {
            $itemlist[] = ['Date', 'Features', 'Ads', ['role' => 'annotation']];
        }
        foreach ($getDailyrevenue as $key => $value) {
            $incometemp = explode(',', $value);
            $date[$key][] = $labeltoRevenue[$key];
            $incomearr[$key] = array_merge($date[$key], $incometemp);
        }
        $incomedata = array_merge($itemlist, $incomearr);
        echo json_encode($incomedata, JSON_NUMERIC_CHECK);
    }
}
