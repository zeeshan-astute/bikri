<?php
namespace backend\models;
use Yii;
class Roles extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'hts_roles';
    }
    public function rules()
    {
        return [
            [['name', 'comments'], 'required'],
            [['priviliges'], 'string'],
            [['created_date'], 'safe'],
            [['name', 'comments'], 'string', 'max' => 100],
        ];
    }
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => Yii::t('app','Role'),
            'comments' => Yii::t('app','Description'),
            'priviliges' => 'Priviliges',
            'created_date' => Yii::t('app','Date')
        ];
    }
}