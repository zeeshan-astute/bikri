<?php
namespace backend\models;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Banners;
class BannercancelledSearch extends Banners
{
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['bannerimage', 'appbannerimage', 'bannerurl','startdate', 'enddate', 'totaldays', 'amount', 'paidstatus', 'status','tranxId','createdDate','paymentMethod','currency','trackPayment','status'], 'safe'],
        ];
    }
    public function scenarios()
    {
        return Model::scenarios();
    }
 public function search($params)
    {
        $query = Banners::find()->where(['status'=>"cancelled"]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['id' => SORT_DESC]]
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere(['>=', 'startdate', $this->startdate])
            ->andFilterWhere(['<=', 'enddate', $this->enddate]);
        return $dataProvider;
    }
}