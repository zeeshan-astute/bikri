<?php
namespace backend\models;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Orders;
class OrdersSearch extends Orders
{
    public function rules()
    {
        return [
            [['orderId', 'userId', 'sellerId', 'orderDate', 'shippingAddress', 'statusDate', 'reviewFlag'], 'integer'],
            [['totalCost', 'totalShipping', 'admincommission', 'discount', 'discountSource', 'currency', 'sellerPaypalId', 'status', 'trackPayment'], 'safe'],
        ];
    }
    public function scenarios()
    {
        return Model::scenarios();
    }
     public function search($params)
    {
        $query = Orders::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['orderId'=>SORT_DESC]]
        ]);
        $this->load($params);
        if (isset($params['orderDate'])) {
            $dat=$params['orderDate'];
        }
        else
        {
            $dat=$this->orderDate;
        }
        if (isset($params['status'])) {
            $status=$params['status'];
            $status1='';
            if ($status == 'approved') {
               $status='paid';
            }
            elseif ($status == 'refunded') {
               $status='cancelled';
               $status1='refunded';
            }
             elseif ($status == 'cancelled') {
               $status='cancelled';
               $status1='pending';
            }
            else
            {
            }
        }
        else
        {
            $status=$this->status;
             $status1=$this->trackPayment;
        }
     if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'orderId' => $this->orderId,
            'userId' => $this->userId,
            'sellerId' => $this->sellerId,
            'shippingAddress' => $this->shippingAddress,
            'statusDate' => $this->statusDate,
            'reviewFlag' => $this->reviewFlag,
        ]);
        $query->andFilterWhere(['like', 'totalCost', $this->totalCost])
            ->andFilterWhere(['like', 'totalShipping', $this->totalShipping])
            ->andFilterWhere(['like', 'admincommission', $this->admincommission])
            ->andFilterWhere(['like', 'discount', $this->discount])
            ->andFilterWhere(['like', 'discountSource', $this->discountSource])
            ->andFilterWhere(['like', 'currency', $this->currency])
            ->andFilterWhere(['like', 'sellerPaypalId', $this->sellerPaypalId])
            ->andFilterWhere(['like','status', $status])
            ->andFilterWhere(['like', 'trackPayment', $status1])
            ->andFilterWhere(['=', "date_format(FROM_UNIXTIME(`orderDate`), '%d-%m-%Y')", $dat]);
      return $dataProvider;
    }
}