<?php
namespace backend\models;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Categories;
class CategoriesSearch extends Categories
{
    public function rules()
    {
        return [
            [['categoryId', 'parentCategory', 'subcategoryVisible'], 'integer'],
            [['name', 'image', 'categoryProperty', 'slug', 'createdDate'], 'safe'],
        ];
    }
    public function scenarios()
    {
        return Model::scenarios();
    }
    public function search($params)
    {
        $query = Categories::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
	     'sort'=> ['defaultOrder' => ['categoryId'=>SORT_DESC]]
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
         if (isset($params['createdDate'])) {
            $dat=$params['createdDate'];
        }
        else
        {
            $dat=$this->createdDate;
        }
        $query->andFilterWhere([
            'categoryId' => $this->categoryId,
            'parentCategory' => $this->parentCategory,
            'subcategoryVisible' => $this->subcategoryVisible,
        ]);
        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'image', $this->image])
            ->andFilterWhere(['like', 'categoryProperty', $this->categoryProperty])
            ->andFilterWhere(['like', 'slug', $this->slug])
            ->andFilterWhere(['=', "DATE_FORMAT(`createdDate`, '%d-%m-%Y' )", $dat]);
        return $dataProvider;
    }
}