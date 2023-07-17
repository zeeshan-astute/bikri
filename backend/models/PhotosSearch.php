<?php
namespace backend\models;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Photos;
class PhotosSearch extends Photos
{
    public function rules()
    {
        return [
            [['photoId', 'productId', 'createdDate'], 'integer'],
            [['name'], 'safe'],
        ];
    }
    public function scenarios()
    {
        return Model::scenarios();
    }
    public function search($params)
    {
        $query = Photos::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'photoId' => $this->photoId,
            'productId' => $this->productId,
            'createdDate' => $this->createdDate,
        ]);
        $query->andFilterWhere(['like', 'name', $this->name]);
        return $dataProvider;
    }
}