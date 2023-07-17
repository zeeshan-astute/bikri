<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Subscriptiontransaction;
use common\models\Users;
use common\models\Freelisting;


/**
 * FreelistingSearch represents the model behind the search form of `common\models\Freelisting`.
 */
class SubscriptionSearch extends Subscriptiontransaction
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'subscriptionId', 'userId','subscriptionPrice','createdDate'], 'integer'],
            [['subscriptionName','status'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Subscriptiontransaction::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
	    'sort'=> ['defaultOrder' => ['id'=>SORT_DESC]]
        ]);
        if (isset($params['createdDate'])) {
           $stt=Yii::$app->formatter->asDateTime($params['createdDate']);
            $dat=$params['createdDate'];
        }
        else
        {
            $dat=$this->createdDate;
        }
        // print_r($params); exit;
        $this->load($params);
        if (isset($params["SubscriptionSearch"]['userId'])) {
            $users =  Users::find()->select('userId')->where(['LIKE', 'name', $params["SubscriptionSearch"]['userId']])->all();
            if(count($users)>0 ){
             foreach ($users as $key => $value) {
                $userIds[] = $value->userId;
            }
               $query->andFilterWhere(['in','userId',$userIds]);
            }
           }
        if (isset($params["SubscriptionSearch"]['subscriptionId']) && $params["SubscriptionSearch"]['subscriptionId'] != "") {
            $freelisting =  Freelisting::find()->select('id')->where(['LIKE', 'name', $params["SubscriptionSearch"]['subscriptionId']])->all();
            if(count($freelisting)>0 ){
             foreach ($freelisting as $key => $value) {
                $Ids[] = $value->id;
            }
               $query->andFilterWhere(['in','subscriptionId',$Ids]);
            }
           }

        /*if (!$this->validate()) {
            return $dataProvider;
        }*/

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'subscriptionPrice' => $this->subscriptionPrice,
        ]);

        $query->andFilterWhere(['like', 'subscriptionName', $this->subscriptionName])
            ->andFilterWhere(['=', "date_format(FROM_UNIXTIME(`createdDate`), '%d-%m-%Y' )", $dat]);

        return $dataProvider;
    }
}
