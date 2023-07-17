<?php
namespace backend\models;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Help;
class HelpSearch extends Help
{
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['page', 'pageContent'], 'safe'],
        ];
    }
    public function scenarios()
    {
        return Model::scenarios();
    }
    public function search($params)
    {
        $query = Help::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'id' => $this->id,
        ]);
        $query->andFilterWhere(['like', 'page', $this->page])
            ->andFilterWhere(['like', 'pageContent', $this->pageContent]);
        return $dataProvider;
    }
}