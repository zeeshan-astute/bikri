<?php
namespace backend\models;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Roles;
class RolesSearch extends Roles
{
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name', 'comments', 'priviliges', 'created_date'], 'safe'],
        ];
    }
    public function scenarios()
    {
        return Model::scenarios();
    }
    public function search($params)
    {
        $query = Roles::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'id' => $this->id,
            'created_date' => $this->created_date,
        ]);
        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'comments', $this->comments])
            ->andFilterWhere(['like', 'priviliges', $this->priviliges]);
        return $dataProvider;
    }
}