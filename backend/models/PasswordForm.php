<?php
namespace backend\models;
use Yii;
use yii\base\InvalidParamException;
use yii\base\Model;
use common\models\Admin;
class PasswordForm extends Model
{
    public $id;
    public $password;
    public $confirm_password;
    public $oldpass;
    private $_user;
    public function __construct($id, $config = [])
    {
        $this->_user = Admin::findIdentity($id);
        if (!$this->_user) {
            throw new InvalidParamException('Unable to find user!');
        }
        $this->id = $this->_user->id;
        parent::__construct($config);
    }
   public function rules()
    {
        return [
            [['oldpass','password','confirm_password'], 'required'],
            [['password','confirm_password'], 'string', 'min' => 6],
            ['confirm_password', 'compare', 'compareAttribute' => 'password','message' => Yii::t('app','Confirm password should match with password')],
            [['oldpass'],'validateCurrentPassword'],
            [['password'],'existPassword'],
        ];
    } 
    public function changePassword()
    {
        $user = $this->_user;
        $user->setPassword($this->password);
        $user->password_encrypt = base64_encode($this->password);
        return $user->save(false);
    }
    public function existPassword($attribute) {
        if(!empty($this->password)) {
            $id = Yii::$app->user->id;
            $check = Admin::find()->where(['password_encrypt'=>base64_encode($this->password),'id'=>$id])->one();
            if(!empty($check)) {
                $this->addError('password',Yii::t('app',"Your new password should be different from your current password."));
            }
        }
    }
  public function validateCurrentPassword()
    {
        if (!$this->verifyPassword($this->oldpass)) {
            $this->addError("oldpass",  Yii::t('app','Current password Incorrect'));
        }
    }
    public function verifyPassword($password)
    {
        $dbpassword = Admin::findOne(['username'=>Yii::$app->user->identity->username])->password_hash;
        return Yii::$app->security->validatePassword($password,$dbpassword);
    }
    public function attributeLabels(){
        return [
            'oldpass'=>Yii::t('app','Current Password'),
            'password'=>Yii::t('app','New Password'),
            'confirm_password'=>Yii::t('app','Confirm Password'),
        ];
    }
}