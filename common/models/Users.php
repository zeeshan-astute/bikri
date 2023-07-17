<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\web\IdentityInterface;

class Users extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;
    public $stripeprivatekey;
    public $stripepublickey;

    public $cty;

    public static function tableName()
    {
        return 'users';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
            ['name', 'trim'],
            ['name', 'required'],
            ['username', 'trim'],
            ['username', 'required'],
            ['name', 'match', 'not' => true, 'pattern' => '/([^\p{L}\p{M}0-9 ])/u',
                'message' => Yii::t('app', 'Special characters not allowed')],
            ['username', 'match', 'not' => true, 'pattern' => '/([^\p{L}\p{M}0-9])/u',
                'message' => Yii::t('app', 'Special characters or space not allowed')],
            ['username', 'unique', 'targetClass' => '\common\models\Users', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            [['city'], 'string', 'max' => 50],
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\common\models\Users', 'message' => 'This email address has already been taken.'],
            ['email', 'unique', 'targetClass' => '\common\models\Admin', 'message' => 'This email address has already been taken.'],
            ['email', 'checkEmail', 'on' => 'forgetpassword'],

        ];
    }

    public static function findIdentity($id)
    {
        return static::findOne(['userId' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    public function checkEmail($attribute)
    {
        $check = Users::find()->where(['email' => $this->email])->one();
        if (empty($check)) {
            $this->addError($this->attribute, Yii::t('app', "Email Not Found"));
        }
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findUserByEmail($email)
    {
        return static::findOne(['email' => $email]);
    }

    public function getReviewRating()
    {
        // return
    }

    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword($password)
    {
        return $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey()
    {
        return $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public function removePasswordResetToken()
    {
        return $this->password_reset_token = null;
    }

    public function generateFbdtails()
    {
        if (!empty($this->fbdetails)) {
            $details = Json::decode($this->fbdetails, true);
            $output = "";
            foreach ($details as $fbKey => $fbvalue) {
                $output .= $fbKey . ": " . $fbvalue . "</br>";
            }
            return $output;
        }
    }

    public function sendEmail($email, $verifyLink, $name)
    {
        /* @var $user User */
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'signup-html', 'text' => 'signup-text'],
                ['siteSettings' => $siteSettings, 'access_url' => $verifyLink, 'name' => $name]
            )
            ->setFrom($siteSettings->smtpEmail, $siteSettings->sitename)
            ->setTo($email)
            ->setSubject($siteSettings->sitename . Yii::t('app', ' Welcome Mail'))
            ->send();
    }

    public function sendForgotmail($email, $name, $uniquecode_pass)
    {
        /* @var $user User */
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'forget-html', 'text' => 'forget-text'],
                ['siteSettings' => $siteSettings, 'name' => $name, 'uniquecode_pass' => $uniquecode_pass]
            )
            ->setFrom($siteSettings->smtpEmail, $siteSettings->sitename)
            ->setTo($email)
            ->setSubject($siteSettings->sitename . Yii::t('app', 'Reset Password Mail'))
            ->send();
    }

    public function sendAdminEmail($email, $name, $password)
    {
        /* @var $user User */
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'adminsignup-html', 'text' => 'adminsignup-html'],
                ['siteSettings' => $siteSettings, 'name' => $name, 'useremail' => $email, 'password' => $password]
            )
            ->setFrom($siteSettings->smtpEmail, $siteSettings->sitename)
            ->setTo($email)
            ->setSubject($siteSettings->sitename . ' ' . Yii::t('app', 'Welcome Mail'))
            ->send();
    }

    public function sendForgetPasswordEmail($email, $name, $resetPasswordData)
    {

        $resetPasswordLink = Yii::$app->urlManager->createAbsoluteUrl(['/resetpassword?resetLink=' . $resetPasswordData]);
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'forget-html', 'text' => 'forget-text'],
                ['siteSettings' => $siteSettings, 'uniquecode_pass' => $resetPasswordLink,
                    'name' => $name]
            )
            ->setFrom($siteSettings->smtpEmail, $siteSettings->sitename)
            ->setTo($email)
            ->setSubject($siteSettings->sitename . ' ' . Yii::t('app', 'Forget Password Request'))
            ->send();
    }

    public function sendSellerOrderMail($sellerEmail, $orderId, $custom, $userModel, $sellerName, $keyarray, $tempShippingModel)
    {

        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'sellerorderintimation-html', 'text' => 'sellerorderintimation-text'],
                ['siteSettings' => $siteSettings, 'orderId' => $orderId, 'custom' => $custom,
                    'userModel' => $userModel, 'sellerName' => $sellerName, 'keyarray' => $keyarray,
                    'tempShippingModel' => $tempShippingModel]
            )
            ->setFrom($siteSettings->smtpEmail, $siteSettings->sitename)
            ->setTo($sellerEmail)
            ->setSubject($siteSettings->sitename . ' ' . Yii::t('app', 'Seller Order Information'))
            ->send();
    }

    public function sendbuyerorderintimation($email, $orderId, $custom, $userModel, $sellerName, $keyarray, $tempShippingModel)
    {

        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'buyerorderintimation-html', 'text' => 'buyerorderintimation-text'],
                ['siteSettings' => $siteSettings, 'orderId' => $orderId, 'custom' => $custom,
                    'userModel' => $userModel, 'sellerName' => $sellerName, 'keyarray' => $keyarray,
                    'tempShippingModel' => $tempShippingModel]
            )
            ->setFrom($siteSettings->smtpEmail, $siteSettings->sitename)
            ->setTo($email)
            ->setSubject($siteSettings->sitename . ' ' . Yii::t('app', 'Buyer Order Information'))
            ->send();
    }

    public function sendBuyerEmail($email, $shipping, $buyerModel, $name, $track, $orderModel)
    {

        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'trackdetailsmail-html', 'text' => 'trackdetailsmail-text'],
                ['siteSettings' => $siteSettings, 'tempShippingModel' => $shipping,
                    'userModel' => $buyerModel, 'sellerName' => $name, 'tracking' => $track,
                    'model' => $orderModel]
            )
            ->setFrom($siteSettings->smtpEmail, $siteSettings->sitename)
            ->setTo($email)
            ->setSubject($siteSettings->sitename . ' ' . Yii::t('app', 'Tracking Details Mail'))
            ->send();
    }

    public function verifyEmail($email, $verifyLink, $name)
    {
        /* @var $user User */
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'signup-html', 'text' => 'signup-text'],
                ['siteSettings' => $siteSettings, 'access_url' => $verifyLink, 'name' => $name]
            )
            ->setFrom($siteSettings->smtpEmail, $siteSettings->sitename)
            ->setTo($email)
            ->setSubject($siteSettings->sitename . ' ' . Yii::t('app', 'Signup Verification Mail'))
            ->send();
    }

    public function reverifyEmail($email, $verifyLink, $name)
    {
        /* @var $user User */
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'adminreverify-html', 'text' => 'adminreverify-text'],
                ['siteSettings' => $siteSettings, 'access_url' => $verifyLink, 'name' => $name]
            )
            ->setFrom($siteSettings->smtpEmail, $siteSettings->sitename)
            ->setTo($email)
            ->setSubject($siteSettings->sitename . ' ' . Yii::t('app', 'Reverification Mail'))
            ->send();
    }

    public function UserverifyEmail($email, $verifyLink, $name)
    {
        /* @var $user User */
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'reverify-html', 'text' => 'reverify-text'],
                ['siteSettings' => $siteSettings, 'access_url' => $verifyLink, 'name' => $name]
            )
            ->setFrom($siteSettings->smtpEmail, $siteSettings->sitename)
            ->setTo($email)
            ->setSubject($siteSettings->sitename . ' ' . Yii::t('app', 'Reverification Mail'))
            ->send();
    }
    public function AfterforgotEmail($email, $name)
    {
        $siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'afterforgot-html.php', 'text' => 'afterforgot-text.php'],
                ['siteSettings' => $siteSettings, 'name' => $name]
            )
            ->setFrom($siteSettings->smtpEmail, $siteSettings->sitename)
            ->setTo($email)
            ->setSubject($siteSettings->sitename . ' ' . Yii::t('app', 'Reset Password Mail'))
            ->send();
    }
}
