<?php
namespace backend\models;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Filter;
class FilterSearch extends Filter
{
    public function rules()
    {
        return [
            [['id', 'categoryID', 'subcategoryID', 'isRequired', 'status'], 'integer'],
            [['name', 'type', 'value'], 'safe'],
        ];
    }
    public function scenarios()
    {
        return Model::scenarios();
    }    
    public function search($params)
    {
        $query = Filter::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([ 
            'id' => $this->id,
            'categoryID' => $this->categoryID,
            'subcategoryID' => $this->subcategoryID,
            'isRequired' => $this->isRequired,
            'status' => $this->status,
        ]);
        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'type', $this->type])
            ->andFilterWhere(['like', 'value', $this->value]);
        return $dataProvider;
    }
}