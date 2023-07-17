<?php
namespace backend\models;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Banners;
class BannersSearch extends Banners
{
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['bannerimage', 'appbannerimage', 'bannerurl'], 'safe'],
        ];
    }
    public function scenarios()
    {
        return Model::scenarios();
    }
    public function search($params)
    {
        $query = Banners::find()->where(['status'=>""]);
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
        $query->andFilterWhere(['like', 'bannerimage', $this->bannerimage])
            ->andFilterWhere(['like', 'appbannerimage', $this->appbannerimage])
            ->andFilterWhere(['like', 'bannerurl', $this->bannerurl]);
        return $dataProvider;
    }
}