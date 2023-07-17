<?php
namespace backend\models;

use common\models\Users;
use yii\base\Model;

class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $name;
    public $userstatus;
    public $phone; //phone number addons
    public $activationStatus;
    public function rules()
    {
        return [
            ['name', 'trim'],
            ['name', 'required'],
            ['name', 'string', 'min' => 3, 'max' => 30],
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['name', 'match', 'not' => true, 'pattern' => '/([^\p{L}\p{M}0-9 ])/u',
                'message' => 'Special characters not allowed.',
            ],
            ['username', 'match', 'not' => true, 'pattern' => '/([^\p{L}\p{M}0-9])/u',
                'message' => 'Special characters or space not allowed.',
            ],
            ['username', 'unique', 'targetClass' => '\common\models\Users', 'message' => 'This username has already been taken.'],
            /*Mobile OTP addons*/
            ['phone', 'required'],
            [['phone'], 'number'],
            [['phone'], 'string', 'max' => 15, 'min' => 12],
            ['phone', 'unique', 'targetClass' => '\common\models\Users', 'message' => 'Phone number already exists.'],
            /*Mobile OTP addons*/
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\common\models\Users', 'message' => 'This email address has already been taken.'],
            ['password', 'required'],
            ['password', 'string', 'min' => 6],
            [['userstatus', 'activationStatus'], 'integer'],
        ];
    }

    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }
        $user = new Users();
        $user->userstatus = 1;
        $user->activationStatus = 1;
        $user->username = $this->username;
        $user->email = $this->email;
        $user->name = $this->name;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        return $user->save() ? $user : null;
    }
}
