<?php
namespace frontend\models;
use common\models\Country;
use common\models\Users;
use Yii;
use yii\base\Model;
use common\models\Sitesettings;

class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $name;
    public $password_repeat;
    public $userstatus;
    public $activationStatus;
    public $facebookId;
    public $fbdetails;
    public $googleId;
    public $userImage;
    public $phone;
    public function rules()
    {
        return [
            [['name', 'username', 'email', 'password', 'password_repeat','phone'], 'required', 'on' => 'signup'],
            ['name', 'trim'],
            ['name', 'string', 'min' => 3, 'max' => 30, 'on' => 'signup'],
            ['name', 'match', 'not' => true, 'pattern' => '/([^\p{L}\p{M}0-9 ])/u',
                'message' => Yii::t('app', 'Special characters not allowed')],
            ['username', 'match', 'not' => true, 'pattern' => '/([^\p{L}\p{M}0-9])/u', 'message' => Yii::t('app', 'Special characters or space not allowed')],
            ['username', 'trim'],
            ['username', 'unique', 'targetClass' => '\common\models\Users', 'message' => Yii::t('app', 'This username has already been taken'), 'on' => 'signup'],
            ['username', 'string', 'min' => 2, 'max' => 255, 'on' => 'signup'],
            ['email', 'trim'],
            ['email', 'email', 'on' => 'signup'],
            ['email', 'unique', 'targetClass' => '\common\models\Users', 'message' => Yii::t('app', 'This email address has already been taken'), 'on' => 'signup'],
            ['email', 'unique', 'targetClass' => '\common\models\Admin', 'message' => Yii::t('app', 'This email address has already been taken'), 'on' => 'signup'],
            ['password', 'string', 'min' => 6, 'on' => 'signup'],
            ['password_repeat', 'string', 'min' => 6, 'on' => 'signup'],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => Yii::t('app', "Passwords don't match"), 'on' => 'signup'],
        ];
    }

    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            //ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            //ip pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
        if (!empty($details) && !isset($_SESSION['place1'])) {
            $splitLocation = explode(',', $details->loc);
            $countrycode = strtoupper($details->country);
            $countryData = Country::find(['code' => $countrycode])->one();
            $locationArray = array (
            'latitude' => $splitLocation[0],
            'longitude' => $splitLocation[1],
            'place' => $details->city . ', ' . $details->region . ',' . $countryData['country'],
            );
            $city = $details->city;
            $state = $details->region;
            $country = $countryData['country'];
            $geolocationDetails = json_encode($locationArray);
        } elseif (isset($_SESSION['curr_place1']) && $_SESSION['curr_place1'] != '') {
            $getPlace = base64_decode($_SESSION['curr_place1']);
            $getgeolocation = explode(',', $getPlace);
            $city = (isset($getgeolocation[2])) ? $getgeolocation[2] : '';
            $state = (isset($getgeolocation[3])) ? $getgeolocation[3] : '';
            $country = (isset($getgeolocation[4])) ? $getgeolocation[4] : '';
            $result = array('latitude' => base64_decode($_SESSION['curr_latitude']), 'longitude' => base64_decode($_SESSION['curr_longitude']), 'place' => $city . ', ' . $state . ', ' . $country);
            $geolocationDetails = json_encode($result);
        } else {
            $city = '';
            $state = '';
            $country = '';
            $geolocationDetails = '';
        }
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $default_list_count  = !empty($siteSettings->default_list_count ) ? $siteSettings->default_list_count  : 0;
        $user = new Users();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->name = $this->name;
        $user->userstatus = $this->userstatus;
        $user->userImage = $this->userImage;
        $user->facebookId = $this->facebookId;
        $user->fbdetails = $this->fbdetails;
        $user->googleId = $this->googleId;
        /*Mobile OTP addon start*/
        $user->phone = preg_replace("/[^0-9]/", "", $this->phone);
        $user->mobile_status = 1;
        /*Mobile OTP addon end*/
        $user->city = $city;
        $user->state = $state;
        $user->country = $country;
        $user->geolocationDetails = $geolocationDetails;
        $user->activationStatus = $this->activationStatus;
        $user->password_encrypt = base64_encode($this->password);
        $user->setPassword($this->password);
        $user->generateAuthKey();
        if($default_list_count == 0)
            $user->subscription_enable = 1; 
        else
            $user->remaining_free_posts = $default_list_count;
        return $user->save() ? $user : null;
    }
    
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('app', 'Name'),
            'username' => Yii::t('app', 'Username'),
            'email' => Yii::t('app', 'Email'),
            'password' => Yii::t('app', 'Password'),
            'password_repeat' => Yii::t('app', 'Confirm Password'),
        ];
    }
}
