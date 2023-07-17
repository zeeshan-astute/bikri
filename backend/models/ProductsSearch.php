<?php
namespace backend\models;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Products;
use common\models\Users;
class ProductsSearch extends Products
{
    public function rules()
    {
        return [
            [['productId', 'category', 'subCategory', 'quantity', 'createdDate', 'likeCount', 'commentCount', 'chatAndBuy', 'exchangeToBuy', 'instantBuy', 'myoffer', 'shippingcountry', 'soldItem', 'likes', 'views', 'reportCount', 'approvedStatus', 'Initial_approve'], 'integer'],
            [['name', 'description', 'currency', 'sizeOptions', 'productCondition', 'paypalid', 'shippingTime', 'location', 'reports', 'promotionType'], 'safe'],
            [['price', 'shippingCost', 'latitude', 'longitude'], 'number'],
             [['userId'], 'default'],

        ];
    }
    public function scenarios()
    {
        return Model::scenarios();
    }
    public function search($params)
    {
        $query = Products::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
	    'sort'=> ['defaultOrder' => ['productId'=>SORT_DESC]]
        ]);
        if (isset($params['createdDate'])) {
           $stt=Yii::$app->formatter->asDateTime($params['createdDate']);
            $dat=$params['createdDate'];
        }
        else
        {
            $dat=$this->createdDate;
        }
        $this->load($params);
         if (isset($params['ProductsSearch']['userId'])) {
            $users =  Users::find()->select('userId')->where(['LIKE', 'name', $params['ProductsSearch']['userId']])->all();
            if(count($users)>0 ){
             foreach ($users as $key => $value) {
                $userIds[] = $value->userId;
            }
               $query->andFilterWhere(['in','userId',$userIds]);
            }
           }
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'productId' => $this->productId,
            'category' => $this->category,
            'subCategory' => $this->subCategory,
            'price' => $this->price, 
            'quantity' => $this->quantity,
            'likeCount' => $this->likeCount,
            'commentCount' => $this->commentCount,
            'chatAndBuy' => $this->chatAndBuy,
            'exchangeToBuy' => $this->exchangeToBuy,
            'instantBuy' => $this->instantBuy,
            'myoffer' => $this->myoffer,
            'shippingcountry' => $this->shippingcountry,
            'shippingCost' => $this->shippingCost,
            'soldItem' => $this->soldItem,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'likes' => $this->likes,
            'views' => $this->views,
            'reportCount' => $this->reportCount,
            'approvedStatus' => $this->approvedStatus,
            'Initial_approve' => $this->Initial_approve,
        ]);
        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'currency', $this->currency])
            ->andFilterWhere(['like', 'sizeOptions', $this->sizeOptions])
            ->andFilterWhere(['like', 'productCondition', $this->productCondition])
            ->andFilterWhere(['like', 'paypalid', $this->paypalid])
            ->andFilterWhere(['like', 'shippingTime', $this->shippingTime])
            ->andFilterWhere(['like', 'location', $this->location])
            ->andFilterWhere(['like', 'reports', $this->reports])
            ->andFilterWhere(['like', 'promotionType', $this->promotionType])
            ->andFilterWhere(['=', "date_format(FROM_UNIXTIME(`createdDate`), '%d-%m-%Y' )", $dat]);
        return $dataProvider;
    }
}