<?php
namespace backend\models;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Users;
class UsersSearch extends Users
{
    public function rules()
    {
        return [
            [['userId', 'phonevisible', 'facebookId', 'twitterId', 'defaultshipping', 'createdDate', 'lastLoginDate', 'averageRating', 'mobile_verificationcode', 'mobile_status', 'unreadNotification', 'sms_country_code', 'country_code'], 'integer'],
            [['username', 'name', 'password', 'email', 'phone', 'country', 'city', 'state', 'postalcode', 'geolocationDetails', 'userImage', 'userstatus', 'activationStatus', 'gender', 'fbdetails', 'facebook_session', 'googleId', 'notificationSettings', 'recently_view_product', 'braintree_cid'], 'safe'],
        ];
    }
    public function scenarios()
    {
        return Model::scenarios();
    }
    public function search($params)
    {
        $query = Users::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
	     'sort'=> ['defaultOrder' => ['userId'=>SORT_DESC]]
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        // grid filtering conditions
        $query->andFilterWhere([
            'userId' => $this->userId,
            'phonevisible' => $this->phonevisible,
            'facebookId' => $this->facebookId,
            'twitterId' => $this->twitterId,
            'defaultshipping' => $this->defaultshipping,
            'createdDate' => $this->createdDate,
            'lastLoginDate' => $this->lastLoginDate,
            'averageRating' => $this->averageRating,
            'mobile_verificationcode' => $this->mobile_verificationcode,
            'mobile_status' => $this->mobile_status,
            'unreadNotification' => $this->unreadNotification,
            'sms_country_code' => $this->sms_country_code,
            'country_code' => $this->country_code,
        ]);
        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'country', $this->country])
            ->andFilterWhere(['like', 'city', $this->city])
            ->andFilterWhere(['like', 'state', $this->state])
            ->andFilterWhere(['like', 'postalcode', $this->postalcode])
            ->andFilterWhere(['like', 'geolocationDetails', $this->geolocationDetails])
            ->andFilterWhere(['like', 'userImage', $this->userImage])
            ->andFilterWhere(['like', 'userstatus', $this->userstatus])
            ->andFilterWhere(['like', 'activationStatus', $this->activationStatus])
            ->andFilterWhere(['like', 'gender', $this->gender])
            ->andFilterWhere(['like', 'fbdetails', $this->fbdetails])
            ->andFilterWhere(['like', 'facebook_session', $this->facebook_session])
            ->andFilterWhere(['like', 'googleId', $this->googleId])
            ->andFilterWhere(['like', 'notificationSettings', $this->notificationSettings])
            ->andFilterWhere(['like', 'recently_view_product', $this->recently_view_product])
            ->andFilterWhere(['like', 'braintree_cid', $this->braintree_cid]);
        return $dataProvider;
    }
}