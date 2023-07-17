<?php
namespace backend\models;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Promotions;
class PromotionsSearch extends Promotions
{
    public function rules()
    {
        return [
            [['id', 'days', 'price'], 'integer'],
            [['name'], 'safe'],
        ];
    }
    public function scenarios()
    {
        return Model::scenarios();
    }
    public function search($params)
    {
        $query = Promotions::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
	    'sort'=> ['defaultOrder' => ['id'=>SORT_DESC]]
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'id' => $this->id,
            'days' => $this->days,
            'price' => $this->price,
        ]);
        $query->andFilterWhere(['like', 'name', $this->name]);
        return $dataProvider;
    }
}