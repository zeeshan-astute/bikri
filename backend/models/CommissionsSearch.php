<?php
namespace backend\models;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Commissions;
class CommissionsSearch extends Commissions
{
    public function rules()
    {
        return [
            [['id', 'status', 'date'], 'integer'],
            [['percentage', 'minRate', 'maxRate'], 'safe'],
        ];
    }
    public function scenarios()
    {
        return Model::scenarios();
    }
    public function search($params)
    {
        $query = Commissions::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
            'date' => $this->date,
        ]);
        $query->andFilterWhere(['like', 'percentage', $this->percentage])
            ->andFilterWhere(['like', 'minRate', $this->minRate])
            ->andFilterWhere(['like', 'maxRate', $this->maxRate])
            ->orderBY(['date' => SORT_DESC]);
        return $dataProvider;
    }
}