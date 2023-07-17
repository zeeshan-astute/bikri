<?php
namespace backend\models;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Banners;
class BannerapprovedSearch extends Banners
{
    public $showdate;
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
        $query = Banners::find()->where(['status'=>"approved"]);
      if (isset($params['createdDate']) && $params['createdDate']!="") {
            $dat= date("Y-m-d", strtotime($params['createdDate']));
              $query->andFilterWhere(['>=', 'startdate', $dat]);
        }
        else
        {
            $dat= "";
        }
        if (isset($params['enddate']) && $params['enddate']!="") {
           $enddate= date("Y-m-d", strtotime($params['enddate']));
            $query->andFilterWhere(['<=', 'enddate', $enddate]);
        }
        else
        {
            $enddate ="";
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['id' => SORT_DESC]]
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        if($startdate=="" || $enddate==""){
        $query->andFilterWhere(['>=', 'startdate', $this->startdate])
            ->andFilterWhere(['<=', 'enddate', $this->enddate]);
        }
        return $dataProvider;
    }
}