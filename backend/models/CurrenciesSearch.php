<?php
namespace backend\models;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Currencies;
class CurrenciesSearch extends Currencies
{
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['currency_name', 'currency_shortcode', 'currency_image', 'currency_symbol','currency_mode','currency_position'], 'safe'],
        ];
    }
    public function scenarios()
    {
        return Model::scenarios();
    }
    public function search($params)
    {
        $query = Currencies::find();
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
        $query->andFilterWhere(['like', 'currency_name', $this->currency_name])
            ->andFilterWhere(['like', 'currency_shortcode', $this->currency_shortcode])
            ->andFilterWhere(['like', 'currency_image', $this->currency_image])
            ->andFilterWhere(['like', 'currency_symbol', $this->currency_symbol]);
        return $dataProvider;
    }
}