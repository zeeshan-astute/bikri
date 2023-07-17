<?php
namespace backend\models;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Invoices;
class InvoicesSearch extends Invoices
{
    public function rules()
    {
        return [
            [['invoiceId', 'orderId', 'invoiceDate'], 'integer'],
            [['invoiceNo', 'invoiceStatus', 'paymentMethod', 'paymentTranxid'], 'safe'],
        ];
    }
    public function scenarios()
    {
        return Model::scenarios();
    }
    public function search($params)
    {
        $query = Invoices::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
             'sort'=> ['defaultOrder' => ['invoiceId'=>SORT_DESC]]
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
          if (isset($params['invoiceDate'])) {
            $dat=$params['invoiceDate'];
        }
        else
        {
            $dat=$this->invoiceDate;
        }
        $query->andFilterWhere([
            'invoiceId' => $this->invoiceId,
            'orderId' => $this->orderId,
            'invoiceDate' => $this->invoiceDate,
        ]);
        $query->andFilterWhere(['like', 'invoiceNo', $this->invoiceNo])
            ->andFilterWhere(['like', 'invoiceStatus', $this->invoiceStatus])
            ->andFilterWhere(['like', 'paymentMethod', $this->paymentMethod])
            ->andFilterWhere(['like', 'paymentTranxid', $this->paymentTranxid])
             ->andFilterWhere(['=', "date_format(FROM_UNIXTIME(`invoiceDate`), '%d-%m-%Y' )", $dat]);
        return $dataProvider;
    }
}