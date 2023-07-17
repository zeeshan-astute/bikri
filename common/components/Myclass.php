<?php
namespace common\components;
use Yii;
use yii\base\Component;
use common\models\Sitesettings;
use common\models\Currencies;
use common\models\Country;
use common\models\Userviews;
use yii\helpers\Json;
use common\models\Users;
use common\models\Filter;
use common\models\Categories;
use common\models\Followers;
use common\models\Banners;
use yii\helpers\ArrayHelper;
use common\models\Reviews;
use yii\helpers\Url;
use common\models\Helppages;
use common\models\Products;
use common\models\Logs;
use common\models\Promotiontransaction;
use common\models\Exchanges;
use common\models\Messages;
use common\models\Invoices;
use common\models\Photos;
use common\models\Chats;
use common\models\Userdevices;
use common\models\Orderitems;
use common\models\Orders;
use common\models\Records;
use common\models\Productconditions;
use common\models\Freecount;
use common\models\Freelisting;
class Myclass extends Component {
	public static function encrypt($string) {
		return substr(hash('sha256',$string),0,8);
	}
	public static function getLogo() {
		$id = 1;
		$setting = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
		return $setting->logo;
	}
	public static function getSitePaymentModes() {
		$id = 1;
		$setting = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
		$paymentModes = "";
		if(!empty($setting->sitepaymentmodes))
			$paymentModes = Json::decode($setting->sitepaymentmodes, true);
		return $paymentModes;
	}
	public static function getLogoDarkVersion() {
		$id = 1;
		$setting = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
		return $setting->logoDarkVersion;
	}
	public static function getWatermark() {
		$id = 1;
		$setting = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
		return $setting->watermark;
	}
	public static function getDefaultUser() {
		$id = 1;
		$setting = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
		return $setting->default_userimage;
	}
	public static function getFooterLinks() {
		$footerLinksModel = Helppages::find()->all();
		return $footerLinksModel;
	}
	public static function getOrderItemDetails($id) {
		$orderItem =  Orderitems::find()->where(['orderId' => $id])->one();
		if(!empty($orderItem))
			return $orderItem;
	}
	public static function getBanners() {
		$all_banners = Banners::find()->all();
		return $all_banners;
	}
	public static function getTermsSlug() {
		$footerLinksModel = Helppages::find()->where(['id'=>1])->one();
		return $footerLinksModel['slug'];
	}
	public static function getcurrentUserdetail() {
		$userId = Yii::$app->user->id;
		$userdetail = Users::find()->where(['userId' => $userId]);
		return $userdetail;
	}
	public static function getReviewcount($id) {
		$reviewcount =  Reviews::find()->where(['receiverId' => $id])->all();
		return count($reviewcount);
	}
	public static function filterCount($id){
		$filterCount = Categories::find()->where(['categoryId'=>$id])->one();
		$array[]=json::decode($filterCount->filters,true);
		return count($array[0]);
	}
	public static function getUserbyemail($email) {
		$userdetail = Users::find()->where(['email' => $email])->one();
		return $userdetail;
	}
	public static function getInvoiceDetails($id) {
		$invoiceItem =  Invoices::find()->where(['orderId' => $id])->one();
		if(!empty($invoiceItem))
			return $invoiceItem;
	}
	public static function getMetaData(){
		$siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
		$metaData = json_decode($siteSettings->metaData, true);
		if(!empty($metaData)){
			$metaContent['title'] = $metaData['metaTitle'];
			$metaContent['description'] = $metaData['metaDescription'];
			$metaContent['metaKeywords'] = $metaData['metaKeywords'];
		}
		$metaContent['sitename'] = $siteSettings->sitename;
		return $metaContent;
	}
	public static function getFooterSettings() {
		$id = 1;
		$setting = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
		$details = array();
		if(!empty($setting->footer_settings)){
			$footerSettings = json_decode($setting->footer_settings, true);
			$footerSettings = $footerSettings['footerDetails'];
			$details['socialLinks'] = array();$details['appLinks'] = array();
			if(!empty($footerSettings['facebooklink'])){
				$details['socialLinks']['facebook'] = $footerSettings['facebooklink'];
			}
			if(!empty($footerSettings['googlelink'])){
				$details['socialLinks']['google'] = $footerSettings['googlelink'];
			}
			if(!empty($footerSettings['twitterlink'])){
				$details['socialLinks']['twitter'] = $footerSettings['twitterlink'];
			}
			if(!empty($footerSettings['tiktoklink'])){
				$details['socialLinks']['tiktok'] = $footerSettings['tiktoklink'];
			}
			if(!empty($footerSettings['androidlink'])){
				$details['appLinks']['android'] = $footerSettings['androidlink'];
			}
			if(!empty($footerSettings['ioslink'])){
				$details['appLinks']['ios'] = $footerSettings['ioslink'];
			}
			$details['footerCopyRightsDetails'] = $footerSettings['footerCopyRightsDetails'];
			$details['socialloginheading'] = $footerSettings['socialloginheading'];
			$details['applinkheading'] = $footerSettings['applinkheading'];
			$details['generaltextguest'] = $footerSettings['generaltextguest'];
			$details['generaltextuser'] = $footerSettings['generaltextuser'];
		}
		$details['analytics'] = $setting->tracking_code;
		return $details;
	}
	///start new changes
	public static function SubCategoryCount($id){
		$subcategory = Categories::find()->where(['parentCategory'=>$id])->all();
		return count($subcategory);
	}
	public static function getProductcondition() {
		$setting = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
		$productModes = "";
		if(!empty($setting->productCondition))
			$productModes = Json::decode($setting->productCondition, true);
		return $productModes;
	}
	public static function getproductConditionName($id){
		$conditionModel = Productconditions::findOne($id);
		return $conditionModel->condition;
	}
	//revenue
	public static function getPromotionRevDaily($date) {	
		$sql = "SELECT * FROM `hts_promotiontransaction` where from_unixtime(`createdDate`, '%d-%m-%Y')='$date' and `promotionName`='adds'";
		$promotions = Promotiontransaction::findBySql($sql)->all();	
		foreach($promotions as $promotion)	{
			$total = $total +   $promotion->promotionPrice;		
		}      
		$data=$total;	
		return $data;
	}
	public static function getPromotionRevMonthly($date) {	
		$sql = "SELECT * FROM `hts_promotiontransaction` where from_unixtime(`createdDate`, '%Y-%m')='$date' and `promotionName`='adds'";
		$promotions = Promotiontransaction::findBySql($sql)->all();	
		foreach($promotions as $promotion)	{
			$total = $total +   $promotion->promotionPrice;		
		}      
		$data=$total;	
		return $data;
	}
	public static function getPromotionRevYearly($date) {	
		$sql = "SELECT * FROM `hts_promotiontransaction` where from_unixtime(`createdDate`, '%Y')='$date' and `promotionName`='adds'";
		$promotions = Promotiontransaction::findBySql($sql)->all();	
		foreach($promotions as $promotion)	{
			$total = $total +   $promotion->promotionPrice;		
		}      
		$data=$total;	
		return $data;
	}
  //Urgent Promotion Graph
	public static function getPromotionUrgent($date) {	
		$sql = "SELECT * FROM `hts_promotiontransaction` where from_unixtime(`createdDate`, '%d-%m-%Y')='$date' and `promotionName`='urgent'";
		$promotions = Promotiontransaction::findBySql($sql)->all();	
		foreach($promotions as $promotion)	{
			$total = $total +   $promotion->promotionPrice;		
		}      
		$data=$total;	
		return $data;
	}
	public static function getPromotionUrgentMonthly($date) {	
		$sql = "SELECT * FROM `hts_promotiontransaction` where from_unixtime(`createdDate`, '%Y-%m')='$date' and `promotionName`='urgent'";
		$promotions = Promotiontransaction::findBySql($sql)->all();	
		foreach($promotions as $promotion)	{
			$total = $total +   $promotion->promotionPrice;		
		}      
		$data=$total;	
		return $data;
	}
	public static function getPromotionUrgentYearly($date) {	
		$sql = "SELECT * FROM `hts_promotiontransaction` where from_unixtime(`createdDate`, '%Y')='$date' and `promotionName`='urgent'";
		$promotions = Promotiontransaction::findBySql($sql)->all();	
		foreach($promotions as $promotion)	{
			$total = $total +   $promotion->promotionPrice;		
		}      
		$data=$total;	
		return $data;
	}
	public static function getDailyRevenue($date) {
//buynow
		$order = new Orders();
		$sql = "SELECT * FROM `hts_orders` where from_unixtime(`orderDate`, '%d-%m-%Y')='$date' and (`status`='delivered' or `status`='paid')";
		$getRevenue = Orders::findBySql($sql)->all();
		foreach($getRevenue as $order) {
			$getAmt = $order->getCommissionOrder($order->orderId);
			$total = $total + $getAmt;
		}
	//ad promotion
		$asql = "SELECT * FROM `hts_promotiontransaction` where from_unixtime(`createdDate`, '%d-%m-%Y')='$date' AND `promotionName` = 'adds'";
		$adspromotion = Promotiontransaction::findBySql($asql)->all();	
		foreach($adspromotion as $promotion)	{
			$adtotal = $adtotal +   $promotion->promotionPrice;		
		}   
   //urgent promotion
		$usql = "SELECT * FROM `hts_promotiontransaction` where from_unixtime(`createdDate`, '%d-%m-%Y')='$date' AND `promotionName` = 'urgent'";
		$urgpromotion = Promotiontransaction::findBySql($usql)->all();	
		foreach($urgpromotion as $promotion)	{
			$urtotal = $urtotal +   $promotion->promotionPrice;		
		}   
		if ($total == '') {
			$total = 0;
		}
		if ($adtotal == '') {
			$adtotal = 0;
		}
		if ($urtotal == '') {
			$urtotal = 0;
		}
		$final_total = $total+$adtotal+$urtotal;
		$siteSetting = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
		$sitePaymentMode = json_decode($siteSetting->sitepaymentmodes);
		if($sitePaymentMode->buynowPaymentMode == 1) 
			return $urtotal.','.$adtotal.','.$total.','.$final_total;
		else
			return $urtotal.','.$adtotal.','.$final_total;
	}
	public static function setUserlanguage($userId){
		$user = Users::findOne($userId);
		$user->user_lang = $_SESSION['language'];
		$user->save(false);
	}
	public static function getBuynowDaily($date)
	{
		$order = new Orders();
		$sql = "SELECT * FROM `hts_orders` where from_unixtime(`orderDate`, '%d-%m-%Y')='$date' and (`status`='delivered' or `status`='paid')";
		$getRevenue = Orders::findBySql($sql)->all();
		foreach($getRevenue as $order) {
			$getAmt = $order->getCommissionOrder($order->orderId);
			$total = $total + $getAmt;
		}
		return $total;
	}
	public static function getMonthlyRevenue($date) {
//buynow
		$order = new Orders();
		$sql = "SELECT * FROM `hts_orders` where from_unixtime(`orderDate`, '%Y-%m')='$date' and (`status`='delivered' or `status`='paid')";
		$getRevenue = Orders::findBySql($sql)->all();
		foreach($getRevenue as $order) {
			$getAmt = $order->getCommissionOrder($order->orderId);
			$total = $total + $getAmt;
		}
	//promotion
		$adsql = "SELECT * FROM `hts_promotiontransaction` where from_unixtime(`createdDate`, '%Y-%m')='$date' AND `promotionName` = 'adds'";
		$adpromotions = Promotiontransaction::findBySql($adsql)->all();	
		foreach($adpromotions as $promotion)	{
			$adtotal = $adtotal +   $promotion->promotionPrice;		
		}   
		$ursql = "SELECT * FROM `hts_promotiontransaction` where from_unixtime(`createdDate`, '%Y-%m')='$date' AND `promotionName` = 'urgent'";
		$promotions = Promotiontransaction::findBySql($ursql)->all();	
		foreach($promotions as $promotion)	{
			$urtotal = $urtotal +   $promotion->promotionPrice;		
		}   
		if ($total == '') {
			$total = 0;
		}
		if ($adtotal == '') {
			$adtotal = 0;
		}
		if ($urtotal == '') {
			$urtotal = 0;
		}
		$final_total = $total+$adtotal+$urtotal;
		$siteSetting = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
		$sitePaymentMode = json_decode($siteSetting->sitepaymentmodes);
		if($sitePaymentMode->buynowPaymentMode == 1) 
			return $urtotal.','.$adtotal.','.$total.','.$final_total;
		else
			return $urtotal.','.$adtotal.','.$final_total;
	}
	public static function getYearlyRevenue($date) {
//buynow
		$order = new Orders();
		$sql = "SELECT * FROM `hts_orders` where from_unixtime(`orderDate`, '%Y')='$date' and (`status`='delivered' or `status`='paid')";
		$getRevenue = Orders::findBySql($sql)->all();
		foreach($getRevenue as $order) {
			$getAmt = $order->getCommissionOrder($order->orderId);
			$total = $total + $getAmt;
		}
	//promotion
		$adsql = "SELECT * FROM `hts_promotiontransaction` where from_unixtime(`createdDate`, '%Y')='$date' AND `promotionName` = 'adds'";
		$adpromotions = Promotiontransaction::findBySql($adsql)->all();	
		foreach($adpromotions as $promotion)	{
			$adtotal = $adtotal +   $promotion->promotionPrice;		
		}   
		$ursql = "SELECT * FROM `hts_promotiontransaction` where from_unixtime(`createdDate`, '%Y')='$date' AND `promotionName` = 'urgent'";
		$promotions = Promotiontransaction::findBySql($ursql)->all();	
		foreach($promotions as $promotion)	{
			$urtotal = $urtotal +   $promotion->promotionPrice;		
		}   
		if ($total == '') {
			$total = 0;
		}
		if ($adtotal == '') {
			$adtotal = 0;
		}
		if ($urtotal == '') {
			$urtotal = 0;
		}
		$final_total = $total+$adtotal+$urtotal;
		$siteSetting = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
		$sitePaymentMode = json_decode($siteSetting->sitepaymentmodes);
		if($sitePaymentMode->buynowPaymentMode == 1) 
			return $urtotal.','.$adtotal.','.$total.','.$final_total;
		else
			return $urtotal.','.$adtotal.','.$final_total;
	}
	public static function getrevenueTotal($date) {
		$order = new Orders();
		$sql = "SELECT * FROM `hts_orders` where from_unixtime(`statusDate`, '%d-%m-%Y')='$date' and (`status`='delivered' or `status`='paid')";
		$getRevenue = Orders::findBySql($sql)->all();
		foreach($getRevenue as $order) {
			$getAmt = $order->getCommissionOrder($order->orderId);
			$total = $total + $getAmt;
		}
		return $total;
	}
	public static function getItemsAddedMonthly($month) {
		$sql = "SELECT * FROM `hts_products` where from_unixtime(`createdDate`, '%Y-%m')='$month'";
		$count = Products::findBySql($sql)->count();		return $count;	}
		public static function getItemsAddedYearly($year) {
			$sql = "SELECT * FROM `hts_products` where from_unixtime(`createdDate`, '%Y')='$year'";
			$count = Products::findBySql($sql)->count();		return $count;	}
			public static function getrevenueTotalMonthly($date) {
				$order = new Orders();
				$sql = "SELECT * FROM `hts_orders` where from_unixtime(`orderDate`, '%Y-%m')='$date' and (`status`='delivered' or `status`='paid')";
				$getRevenue = Orders::findBySql($sql)->all();
				foreach($getRevenue as $order) {
					$getAmt = $order->getCommissionOrder($order->orderId);
					$total = $total + $getAmt;
				}
				return $total;
			}
			public static function getrevenueTotalYearly($date) {
				$sql = "SELECT * FROM `hts_orders` where from_unixtime(`orderDate`, '%Y')='$date' and (`status`='delivered' or `status`='paid')";
				$getRevenue = Orders::findBySql($sql)->all();
				foreach($getRevenue as $order) {
					$getAmt = $order->getCommissionOrder($order->orderId);
					$total = $total + $getAmt;
				}
				return $total;
			}
			public static function getProductImage($id) {
				if($id != null)
					$images = Photos::find()->where(["productId" => $id])->one();
				if(!empty($images)){
					return $images->name;
				}
			}
			
			public static function getCountryId($countryCode){
				$countryModel = Country::find()->where(['code' => $countryCode])->one();
				return $countryModel['countryId'];
			}

			public static function getCountryCode($countryId){
				$countryModel = Country::find()->where(['countryId' => $countryId])->one();
				return $countryModel['code'];
			}

			public static function getProductDetails($id) {
				$product =  Products::find()->where(['productId' => $id])->one();
				if(!empty($product))
					return $product;
			}public static function getProductsearchname($id) {
				$product =  Products::find()->where(['name' => $id])->one();
				if(!empty($product))
					return $product;
			}
			public static function getProductURL($productModel){
				$productURL = Yii::app()->createAbsoluteUrl('products',array('id' => $this->safe_b64encode(
					$productModel->productId.'-'.rand(0,999)))).'/'.$this->productSlug($productModel->name);
				return $productURL;
			}
			public static function getUserProductDetails($id,$limit) {
				$product =  Products::find()->where(['userId' => $id])->limit($limit)->all();
				if(!empty($product))
					return $product;
			}
			public static function getUserDetails($id) {
				$user =  Users::find()->where(['userId' => $id])->all();
				if(!empty($user))
					return $user;
			}
			public static function getUserDetailss($id) {
				$user =  Users::find()->where(['userId' => $id])->one();
				if(!empty($user))
					return $user;
			}
			public static function getUsername($id) {
				$user =  Users::find()->where(['userId' => $id])->one();
				if(!empty($user))
					return $user->username;
			}
			public static function getCategory() {
				$category = Categories::find()->where(['parentCategory' => 0])->all();
				return $category;
			}
			public static function getCategoryName($categorySlug) {
				$category = Categories::find()->where(['slug'=>strtolower($categorySlug)])->one();
				if(!empty($category))
					return $category->name;
				else
					return "";
			}
			public static function getCategoryBreadcrumName($categorySlug) {
				 $category = Categories::find()->where(['slug'=>$categorySlug])->one();
				if(!empty($category))
					return $category->name;
				else
					return "";
			}
			public static function getProductCategory($catid) {
				$category = Categories::find()->where(['categoryId'=>$catid])->one();
				if(!empty($category))
					return $category->name;
				else
					return "";
			}
			public static function getProductCategoryslug($catid) {
				$category = Categories::find()->where(['categoryId'=>$catid])->one();
				if(!empty($category))
					return $category->slug;
				else
					return "";
			}
			public static function getProductMetaCategoryName($catid) {
				$category = Categories::find()->where(['categoryId'=>$catid])->one();
				if(!empty($category))
					return $category->meta_Title;
				else
					return "";
			}
			public static function getMetaCategoryName($categorySlug) {
				$category = Categories::find()->where(['slug'=>$categorySlug])->one();
				if(!empty($category))
					return $category->meta_Title;
				else
					return ;
			}public static function getMetaDesCategoryName($categorySlug) {
				$category = Categories::find()->where(['slug'=>$categorySlug])->one();
				if(!empty($category))
					return $category->meta_Description;
				else
					return "";
			}
			public static function getCategoryId($categorySlug) {
				$category = Categories::find()->where(['slug'=>strtolower($categorySlug)])->one();
				if(!empty($category))
					return $category->categoryId;
				else
					return "";
			}
			public static function getCategorydetbyslug($categorySlug) {
				$category = Categories::find()->where(['slug'=>strtolower($categorySlug)])->one();
				if(!empty($category))
					return $category;
				else
					return "";
			}
			public static function getCategoryDet($id) {
				$category = Categories::find()->where(['categoryId'=>$id])->one();
				if(!empty($category))
					return $category;
				else
					return "";
			}
	public static function slug($str) {
		if(preg_match("/[a-z]/i", $str)){
			$str = strtolower(trim($str));
			$str = preg_replace('/[^a-z0-9-]/', '-', $str);
			$str = preg_replace('/-+/', "-", $str);
			return $str;
		}
		else
		{
			return $str;
		}
	}
	public static function trimSpace($str) {
		$str = str_replace(' ', '-',$str);
		return $str;
	}
	public static function productSlug($str) {
		$old = $str;
		$slugname = preg_replace("/[\s\&.,]+/", "-", $old);
	 $slug = preg_replace('/[^A-Za-z0-9\-]/', '', $slugname);
	 //$slug=htmlspecialchars($slugname);
		if(!empty($slug))
			return strtolower($slug);
		else 
	 $str = base64_encode($old);
	 $str = strtolower(trim($str));
	 $slugname = preg_replace('/[^A-Za-z0-9\-]/', '', $str);
		return trim(strtolower($slugname));
	}
	
	public static function getMessageCount($id){
		$chatModel = Chats::find()->where(['lastToRead'=>$id])->all();
		return count($chatModel);
	}
	public static function getNotificationCount($id){
		$userModel = Users::findOne($id);
		return $userModel->unreadNotification;
	}
	public static function getElapsedTime($timestamp) {
		$time = time() - $timestamp;
		$tokens = array (
			31536000 => 'year',
			2592000 => 'month',
			604800 => 'week',
			86400 => 'day',
			3600 => 'hour',
			60 => 'minute',
			1 => 'second'
		);
		foreach ($tokens as $unit => $text) {
			if ($time < $unit) continue;
			$numberOfUnits = floor($time / $unit);
			if($numberOfUnits>1) {
				$text = $text.'s';
			}
			$text = Yii::t('app',$text);
			return $numberOfUnits.' '.$text;
		}
	}
	public static function cart_encrypt($text, $salt)
	{
		return trim(yii::$app->Myclass->safe_b64encode($text));
	}
	public static function cart_decrypt($text, $salt)
	{
		return trim(yii::$app->Myclass->safe_b64decode($text));
	}
	public static function safe_b64encode($string) {
		$data = base64_encode($string);
		$data = str_replace(array('+','/','='),array('-','_',''),$data);
		return $data;
	}
	public static function safe_b64decode($string) {
		$data = str_replace(array('-','_'),array('+','/'),$string);
		$mod4 = strlen($data) % 4;
		if ($mod4) {
			$data .= substr('====', $mod4);
		}
		return base64_decode($data);
	}
	public static function getCatName($id) {
		$category =   Categories::find()->where(['categoryId' => $id])->one();
		if(!empty($category))
			return $category->name;
		else
			return Yii::t('app','NIL');
	}
	public static function getCatDetails($id) {
		$category = Categories::find()->where(['categoryId' => $id])->one();
		if(!empty($category))
			return $category;
	}
	public static function getCatDetailsval($id) {
		$category = Categories::find()->where(['categoryId' => $id])->one();
		if(!empty($category))
			return $category->slug;
		else
			return $category->name;
		
	}
	public static function getDefaultShippingAddress($userId){
		$userAddress = Users::find()->where(['userId' => $userId])->one();
		if(!empty($userAddress))
			return $userAddress->defaultshipping;
	}
	public static function getRandomString($length) {
		$charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$charshuffle = str_shuffle($charset);
		return substr($charshuffle,0,$length);
		return $randomString;
	}
	public static function checkSoldOut($id) {
		$productCriteria = new CDbCriteria;
		$productCriteria->addCondition("productId = '$id'");
		$productCriteria->addCondition("quantity != '0'");
		$products = Products::model()->find($productCriteria);
		return $products;
	}
	public static function getImagefromURL($imageUrl, $type = 'user'){
		if ($type == "item"){
			$user_image_path = "media/items/";
		}else{
			$user_image_path = "profile/";
		}
		$newname = time().".jpg";
		$finalPath = $user_image_path;
		$imageUrl = urldecode($imageUrl);
		$raw = file_get_contents($imageUrl);
		if ($raw == false){
			$ch = curl_init ($imageUrl);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
			$raw=curl_exec($ch);
			curl_close ($ch);
		}
			$fori = fopen($finalPath.$newname,'wb');
			fwrite($fori,$raw);
			fclose($fori);
			chmod($finalPath.$newname, 0666);
			return $newname;
		}
		public static function getShippingCost($pid,$cid) {
			$criteria = new CDbCriteria;
			$criteria->addCondition("productId = $pid");
			$criteria->addCondition("countryId = $cid");
			$shippingCost = Shipping::model()->find($criteria);
			if(!empty($shippingCost))
				return $shippingCost->shippingCost;
			else {
				return '0';
			}
		}
		public static function getLastProductPaypalId($userId){
			$productModel = Products::find()->where(['userId' => $userId])->andWhere(['<>','paypalid',''])->orderBy(['productId' => SORT_DESC])->one();
			if(!empty($productModel)){
				return $productModel->paypalid;
			}else{
				return "";
			}
		}
		public static function getLastinsertId(){
			$categoryModel = categories::find()->orderBy(['categoryId' => SORT_DESC])->one();
			if(!empty($categoryModel)){
				return $categoryModel->categoryId;
			}else{
				return "";
			}
		}
		public static function allproducts() {
			$products=Products::find()->where(['approvedStatus' => 1])->orderBy(['productId' => SORT_DESC])->limit(32)->all();
			return $products;
		}
		public static function searchproducts($name) {
			$product =  Products::find()->where(['like','name',$name])->all();
			if(!empty($product))
				return $product;
		}
		public static function getCurrency($str) {
			$str = explode("-",$str);
			return $str[1];
		}
		public static function getCurrencySymbol($str)
		{
			$currencycode = trim($str);
			$currencies = Currencies::find()->where(['currency_shortcode' => $currencycode])->one();
			$currency_symbol = $currencies->currency_symbol;
			return $currency_symbol;
		}
		public static function getFormattingCurrency($str,$amt) {
			$str = explode("-",$str);
			$currencies = Currencies::find()->where(['currency_shortcode' => $str[1]])->one();
			//print_r($currencies).'asdfas33232';exit;
			//echo $str[1]; exit;
			if($currencies->currency_mode == "symbol")
				$currency_format = $str[0];
			else
				$currency_format = $str[1];
			if($currencies->currency_position == "postfix")
				return '<span style="margin-right:5px;">'.$amt.'</span><span>'.$currency_format.'</span>';
			else
				return '<span style="margin-right:5px;">'.$currency_format.'</span><span>'.$amt.'</span>';
		}
		public static function getArabicFormattingCurrency($str,$amt) {
			$str = explode("-",$str);
			$currencies = Currencies::find()->where(['currency_shortcode' => $str[1]])->one();
			if($currencies->currency_mode == "symbol")
				$currency_format = $str[0];
			else
				$currency_format = $str[1];
			if($currencies->currency_position == "postfix")
				return '<span style="margin-right:5px;">'.$currency_format.'</span><span>'.$amt.'</span>';
			else
				return '<div style="text-align:right; direction:ltr;"><span style="margin-right:5px;">'.$amt.'</span><span style="direction:ltr !important;">'.$currency_format.'</span></div>';
		}
		public static function getCurrencyFormats($str) {
			$str = explode("-",$str);
			$currencies = Currencies::find()->where(['currency_shortcode' => $str[1]])->one();
			$currencyformats = [$currencies->currency_mode,$currencies->currency_position];
			return $currencyformats;
		}
		public static function getCurrencyFormat($str) {
			$currencycode = trim($str);
			$currencies = Currencies::find()->where(['currency_shortcode' => $currencycode])->one();
			$currencyformats = [$currencies->currency_mode,$currencies->currency_position];
			return $currencyformats;
		}
		public static function getFormattingCurrencyapi($str,$amt) {
			$str = explode("-",$str);
			$currencies = Currencies::find()->where(['currency_shortcode' => $str[1]])->one();
			if($currencies->currency_mode == "symbol")
				$currency_format = $str[0];
			else
				$currency_format = $str[1];
			if($currencies->currency_position == "postfix")
				return $amt.' '.$currency_format;
			else
				return $currency_format.' '.$amt;
		}
		public static function convertFormattingCurrencyapi($str,$amt) {
			$str = trim($str);
			$currencies = Currencies::find()->where(['currency_shortcode' => $str])->one();
			if($currencies->currency_mode == "symbol")
				$currency_format = $currencies->currency_symbol;
			else
				$currency_format =  $currencies->currency_shortcode;
			if($currencies->currency_position == "postfix")
				return $amt.' '.$currency_format;
			else
				return $currency_format.' '.$amt;
		}

		public static function arabicgetFormattingCurrencyapi($str,$amt) {
			$str = explode("-",$str);
			$currencies = Currencies::find()->where(['currency_shortcode' => $str[1]])->one();
			if($currencies->currency_mode == "symbol")
				$currency_format = $str[0];
			else
				$currency_format = $str[1];
			if($currencies->currency_position == "postfix")
				
				return $currency_format.' '.$amt;
			else
				return $amt.' '.$currency_format;
		}

		public static function arabicconvertFormattingCurrencyapi($str,$amt) {
			$str = trim($str);
			$currencies = Currencies::find()->where(['currency_shortcode' => $str])->one();
			if($currencies->currency_mode == "symbol")
				$currency_format = $currencies->currency_symbol;
			else
				$currency_format =  $currencies->currency_shortcode;
			if($currencies->currency_position == "postfix")
				return $currency_format.' '.$amt;
			else
				return $amt.' '.$currency_format;
		}
		public static function convertFormattingCurrency($str,$amt) {
			$str = trim($str);
			$currencies = Currencies::find()->where(['currency_shortcode' => $str])->one();
			$currency_format = "";
			if($currencies->currency_mode == "symbol"){
				$currency_format = $currencies->currency_symbol;
			}
			else{
				$currency_format =  $currencies->currency_shortcode;
			}
			if($currencies->currency_position == "postfix"){
				return '<span style="margin-right:5px;">'.$amt.'</span><span>'.$currency_format.'</span>';	
			}
			else
				return '<span style="margin-right:5px;">'.$currency_format.'</span><span>'.$amt.'</span>';
		}
		public static function convertArabicFormattingCurrency($str,$amt) {
			$str = trim($str);
			$currencies = Currencies::find()->where(['currency_shortcode' => $str])->one();
			if($currencies->currency_mode == "symbol")
				$currency_format = $currencies->currency_symbol;
			else
				$currency_format =  $currencies->currency_shortcode;
			if($currencies->currency_position == "postfix")
				return '<span style="margin-right:5px;">'.$currency_format.'</span><span>'.$amt.'</span>';
			else
				return '<div style="text-align:right; direction:ltr;"><span style="margin-right:5px;">'.$amt.'</span><span style="direction:ltr !important;">'.$currency_format.'</span></div>';


		}
		public static function convertArabicPopupFormattingCurrency($str,$amt) 
		{
			$str = trim($str);
			$currencies = Currencies::find()->where(['currency_shortcode' => $str])->one();
			if($currencies->currency_mode == "symbol")
				$currency_format = $currencies->currency_symbol;
			else
				$currency_format =  $currencies->currency_shortcode;
			if($currencies->currency_position == "postfix")
				return '<span style="margin-right:5px;">'.$currency_format.'</span><span>'.$amt.'</span>';
			else
				return '<p style="text-align:center; direction:ltr;"><span style="margin-right:5px;">'.$amt.'</span><span style="direction:ltr !important;">'.$currency_format.'</span></p>';
		}
		public static function getPromotionCurrency() {
			$siteSetting = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
			$str = explode("-",$siteSetting->promotionCurrency);
			return $str[1];
		}
		public static function getCurrencyData(){
			$currencyList = Currencies::find()->all();
			return $currencyList;
		}
		public static function getNewUsers() {
			$criteria = new CDbCriteria;
			$date = date("d-m-Y",time());
			$criteria->condition = "from_unixtime(`createdDate`, '%d-%m-%Y') = '$date'";
			return Users::model()->count($criteria);
		}
		public static function getNewItems() {
			$criteria = new CDbCriteria;
			$date = date("d-m-Y",time());
			$criteria->condition = "from_unixtime(`createdDate`, '%d-%m-%Y') = '$date'";
			return Products::find()->count($criteria);
		}
		public static function getTotalOrders() {
			return Orders::find()->count();
		}
		public static function getTotalPromotions() {
			return Promotiontransaction::find()->count();
		}
		public static function getTotalExchanges() {
			return Exchanges::find()->count();
		}
		public static function getTotalUsers() {
			return  Users::find()->count();
		}
		public static function getTotalItems() {
			return Products::find()->count();
		}
		public static function getSoldTotalItems() {
			return Products::find()->where(['soldItem'=>1])->count();
		}
		public static function getGivingAwayCount() {
			return Products::find()->where(['price'=>0])->count();
		}
		public static function getChatBuyCount() {
			$messages = Messages::find()
			->select('chatId')
			->andWhere(['messageType' => 'normal'])
			->andWhere(['sourceId' => !0])
			->distinct()->count();
			return $messages;
		}
		public static function getExchangeBuyCount() {
			$date = date("d-m-Y",time());
			$messages = Exchanges::find()
			->andWhere([from_unixtime(`date`, '%d-%m-%Y') => $date])
			->andWhere(['status' => 4])
			->count();
			return $messages;
		}
		public static function getInstantBuyCount() {
			$date = date("d-m-Y",time());
			$messages = Invoices::find()
			->andWhere([from_unixtime(`invoiceDate`, '%d-%m-%Y') => $date])
			->andWhere(['status' => 4])
			->count();
			return $messages;
		}
		public static function getExchangeBuyLog($date) {
			$criteria = new CDbCriteria;
			$criteria->condition = "from_unixtime(`date`, '%d-%m-%Y') = '$date'";
			$criteria->addCondition("status = 4");
			return Exchanges::model()->count($criteria);
		}
		public static function getPromotionsAddsCount() {
			$date = date("d-m-Y",time());
			$sql = "SELECT * FROM `hts_promotiontransaction` where from_unixtime(`createdDate`, '%d-%m-%Y')='$date' and `promotionName`='adds'";
			$messages = Promotiontransaction::findBySql($sql)->count();
			return $messages;
		}
		public static function getPromotionsUrgentCount() {
			$date = date("d-m-Y",time());
			$sql = "SELECT * FROM `hts_promotiontransaction` where from_unixtime(`createdDate`, '%d-%m-%Y')='$date' and `promotionName`='urgent'";
			$messages = Promotiontransaction::findBySql($sql)->count();
			return $messages;
		}
		public static function getPromotionsAdds($date) {
			$sql = "SELECT * FROM `hts_promotiontransaction` where from_unixtime(`createdDate`, '%d-%m-%Y')='$date' and `promotionName`='adds'";
			$messages = Promotiontransaction::findBySql($sql)->count();
			return $messages;
		}
		public static function getPromotionsUrgent($date) { 
			$sql = "SELECT * FROM `hts_promotiontransaction` where from_unixtime(`createdDate`, '%d-%m-%Y')='$date' and `promotionName`='urgent'";
			$messages = Promotiontransaction::findBySql($sql)->count();
			return $messages;
		}
		public static function getPromotionsAddsMonthly($date) {
			$sql = "SELECT * FROM `hts_promotiontransaction` where from_unixtime(`createdDate`, '%Y-%m')='$date' and `promotionName`='adds'";
			$messages = Promotiontransaction::findBySql($sql)->count();
			return $messages;
		}
		public static function getPromotionsUrgentMonthly($date) { 
			$sql = "SELECT * FROM `hts_promotiontransaction` where from_unixtime(`createdDate`, '%Y-%m')='$date' and `promotionName`='urgent'";
			$messages = Promotiontransaction::findBySql($sql)->count();
			return $messages;
		}
		public static function getPromotionsAddsYearly($date) {
			$sql = "SELECT * FROM `hts_promotiontransaction` where from_unixtime(`createdDate`, '%Y')='$date' and `promotionName`='adds'";
			$messages = Promotiontransaction::findBySql($sql)->count();
			return $messages;
		}
		public static function getPromotionsUrgentYearly($date) { 
			$sql = "SELECT * FROM `hts_promotiontransaction` where from_unixtime(`createdDate`, '%Y')='$date' and `promotionName`='urgent'";
			$messages = Promotiontransaction::findBySql($sql)->count();
			return $messages;
		}
		public static function getInstantBuyLog($date) {
			$criteria = new CDbCriteria;
			$criteria->condition = "from_unixtime(`invoiceDate`, '%d-%m-%Y') = '$date'";
			return Invoices::model()->count($criteria);
		}
		public static function getRegisteredUsers($date = null) {
			if(empty($date)) {
				$date = date("d-m-Y",time());
			}
			$sql = "SELECT * FROM `users` where from_unixtime(`created_at`, '%d-%m-%Y')='$date'";
			$messages = Users::findBySql($sql)->count();
			return $messages;
		}
		public static function getLoggedUsers($date = null) {
			if(empty($date)) {
				$date = date("d-m-Y",time());
			}
			$sql = "SELECT * FROM `users` where from_unixtime(`lastLoginDate`, '%d-%m-%Y')='$date'";
			$messages = Users::findBySql($sql)->count();
			return $messages;
		}
		public static function getActiveUsers() {
			if(empty($date)) {
				$date = date("d-m-Y",time());
			}
			$sql = "SELECT * FROM `users` where userstatus =1 and activationStatus =1";
			$messages = Users::findBySql($sql)->count();
			return $messages;
		}
		public static function getRegisteredUsersMonthly($date) {
			$sql = "SELECT * FROM `users` where from_unixtime(`created_at`, '%Y-%m')='$date'";
			$messages = Users::findBySql($sql)->count();
			return $messages;
		}
		public static function getLoggedUsersMonthly($date) {
			$sql = "SELECT * FROM `users` where from_unixtime(`lastLoginDate`, '%Y-%m')='$date'";
			$messages = Users::findBySql($sql)->count();
			return $messages;
		}
		public static function getRegisteredUsersYearly($date) {
			$sql = "SELECT * FROM `users` where from_unixtime(`created_at`, '%Y')='$date'";
			$messages = Users::findBySql($sql)->count();
			return $messages;
		}
		public static function getLoggedUsersYearly($date) {
			$sql = "SELECT * FROM `users` where from_unixtime(`lastLoginDate`, '%Y')='$date'";
			$messages = Users::findBySql($sql)->count();
			return $messages;
		}
		public static function getItemsAdded($date) {
			$sql = "SELECT * FROM `hts_products` where from_unixtime(`createdDate`, '%d-%m-%Y')='$date'	";
			$messages = Products::findBySql($sql)->count();
			return $messages;
		}
		public static function getUserId($username) {
			$user = Users::model()->findByAttributes(array('username'=>$username));
			if(!empty($user))
			{
				return $user->userId;
			}
		}
		public static function exchangeProductExist($mid,$exid,$fromUser,$toUser) {
				$sql = "SELECT * FROM `hts_exchanges` where (`mainProductId` = '$mid' AND `exchangeProductId` = '$exid' OR `mainProductId` = '$exid' AND `exchangeProductId` = '$mid') AND (`requestFrom` = '$fromUser' AND `requestTo` = '$toUser' OR `requestFrom` = '$toUser' AND `requestTo` = '$fromUser')";
			$exCheck = Exchanges::findBySql($sql)->one();
			if(!empty($exCheck)) {
				return $exCheck;
			}
		}
		public static function getCurrencyList($cur = null) {
			$currency =  array('$-Australian Dollar' => 'AUD', 'R$-Brazilian Rea' => 'BRL', 'C$-Canadian Dollar' => 'CAD', 'Kč-Czech Koruna' => 'CZK', 'kr.-Danish Krone' => 'DKK', '€-Euro' => 'EUR', 'HK$-Hong Kong Dollar' => 'HKD', 'Ft-Hungarian Forint' => 'HUF', '₪-Israeli New Sheqel' => 'ILS', '₹-Indian Rupee' => 'INR', '¥-Japanese Yen' => 'JPY', 'RM-Malaysian Ringgit' => 'MYR', 'Mex$-Mexican Peso' => 'MXN', 'kr-Norwegian Krone' => 'NOK', '$-New Zealand Dollar' => 'NZD', '₱-Philippine Peso' => 'PHP', 'zł-Polish Zloty' => 'PLN', '£-Pound Sterling' => 'GBP', 'руб-Russian Ruble' => 'RUB', 'S$-Singapore Dollar' => 'SGD', 'kr-Swedish Krona' => 'SEK', '₣-Swiss Franc' => 'CHF', 'NT$-Taiwan New Dolla' => 'TWD', '฿-Thai Baht' => 'THB', '₺-Turkish Lira' => 'TRY', '$-U.S. Dollar' => 'USD','CFA-West African CFA franc'=>'XOF', 'रु-Nepalese rupee' => 'NPR' ); 
			if(!empty($cur)) {
				return $currency[$cur];
			} else {
				return $currency;
			}
		}
		public static function getDbCurrencyList($cur = null) {
			$currencyModel =  Currencies::find()->all(); 
			foreach($currencyModel as $value):
				$key = trim($value->currency_symbol)."-".trim($value->currency_name);
				$currency[$key] =  $value->currency_shortcode;
			endforeach;
			if(!empty($cur)) {
				return $currency[$cur];
			} else {
				return $currency;
			}
		}
		public static function checkWhetherProductSold($productId) {
			$product = Products::find()->where(['productId' => $productId])->one();
			if(($product['soldItem'] == 1) || ($product['quantity'] == 0)) {
				return $product;
			} else {
				return 0;
			}
		}
		public static function checkChatExists($user1,$user2) {
			$sql = "SELECT * FROM `hts_chats` where `user1` = '$user1' AND `user2` = '$user2' OR `user1` = '$user2' AND `user2` = '$user1'";
			$chatCheck = Chats::findBySql($sql)->one();
			if(!empty($chatCheck)) {
				return $chatCheck->chatId;
			}
		}
		public static function getSiteName() {
			$siteSetting = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
			return $siteSetting->sitename;
		}
	public static function addLogs($type, $userid, $notifyto = 0, $sourceid = 0, $itemid = 0,$notifymessage = null, $notificationId = 0, $message = null){
		if($notifyto || $notifyto == 0 || $userid == 0){
			$logsModel = new Logs();
			$logsModel->type = $type;
			$logsModel->userid = $userid;
			$logsModel->notifyto = $notifyto;
			$logsModel->sourceid = $sourceid;
			$logsModel->itemid = $itemid;
			$logsModel->notifymessage = $notifymessage;
			$logsModel->notification_id = $notificationId;
			$logsModel->message = $message;
			$logsModel->createddate = time();
			$logsModel->save(false);
			if($notifyto != 0){
				$userModel = Users::find()->where(['userId' => $notifyto])->one();
				if(!empty($userModel)){
					$userModel->unreadNotification += 1;
					$userModel->save(false);
				}
			}
			else if($notifyto == 0 && $type == "admin")
			{
				if ($userid!=0) {
					$followersModel = Followers::find()->where(['follow_userId' => $userid])->all();
					foreach ($followersModel as $follower){
						$followerId = $follower->userId;
						$userModel = Users::find()->where(['userId' => $notifyto])->one();
						if(!empty($userModel)){
							$userModel->unreadNotification += 1;
							$userModel->save(false);
						}
					}
				}
				else
				{
					$userModel = Users::find()->all();
					foreach ($userModel as $user){
						$user->unreadNotification += 1;
						$user->save(false);
					}
				}
			}
		}
	}
	public static function removeItemLogs($itemId){
		Logs::deleteAll(['itemid' => $itemId]);
		return true;
	}
	/*public static function pushnot($deviceToken = NULL, $message = NULL, $badge = NULL, $notifytype="notification")
	{
		$userdevicedatas = Userdevices::find()->where(['deviceToken' => $deviceToken])->one();
		if($userdevicedatas->type == 0) {
			yii::$app->Myclass->sendall_push_notification($deviceToken,$message,$notifytype,$userdevicedatas->type);
		} else {
			yii::$app->Myclass->sendall_push_notification($deviceToken,$message,$notifytype,$userdevicedatas->type);
		}
	}*/

	public static function pushnot($deviceToken = null, $message = null, $badge = null, $notifytype = "notification",$platform=null) 
	{
		if($platform == "ios"){
			$userdevicedatas = Userdevices::find()->where(['voip_token' => $deviceToken])->one();
		} else{
			$userdevicedatas = Userdevices::find()->where(['deviceToken' => $deviceToken])->one();
		}
		$siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
		$passphrase = $siteSettings->pem_passphrase;
		if ($userdevicedatas->type == 0) {
			$messageDetails = json_decode($message, true);	
			if ($notifytype == "audio" || $notifytype == "video" || $notifytype == "bye") {
				$messages = $messageDetails;
				try {
					if ($userdevicedatas->mode == 0) {
						$certifcUrl = 'certificate/ck.pem'; 
						$url = 'https://api.development.push.apple.com/3/device/'.$deviceToken;
					} else {
						$certifcUrl = 'certificate/ck.pem';
						$url = 'https://api.push.apple.com/3/device/'.$deviceToken;
					}
					$headers = array(
						"User-Agent: My Sender"
					);
					if($notifytype == "bye") {
						$alertMsg = '{"aps":{"type":"'.$notifytype.'","alert":{"from_id":"'.$messages['from_id'].'","room_id":"'.$messages['room_id'].'"}}}';
					} else {
						$alertMsg = '{"aps":{"type":"'.$notifytype.'","alert":{"user_id":"'.$messages['user_id'].'","chat_id":"'.$messages['chat_id'].'","room_id":"'.$messages['room_id'].'","platform":"'.$messages['platform'].'","time_stamp":"'.$messages['time_stamp'].'","user_name":"'.$messages['user_name'].'","user_image":"'.$messages['user_image'].'"}}}';
					}
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_HEADER, 1);
					curl_setopt($ch, CURLOPT_SSLKEYPASSWD, $passphrase);	// 'Joysale'
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_SSLCERT, $certifcUrl);
					curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $alertMsg);
					$result = curl_exec($ch);
					$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
					if ($status === 400) {
						$errormsg = curl_error($ch);
						print_r($errormsg);
						die;
					}
					curl_close($ch);
				} catch (Exception $e) {
					echo "problem with the device token: " . $deviceToken . " Exception caused: " . $e->getMessage(); 
				} 
			}
			else {
				// $fcmtoken = $userdevicedatas->ios_fcm_token;
				yii::$app->Myclass->send_ios_push_notification($deviceToken, $message, $badge, $notifytype);
			}
		} else {
			yii::$app->Myclass->sendall_push_notification($deviceToken, $message, $notifytype,$userdevicedatas->type);
		}
	}

	public static function send_ios_push_notification($registration_ids, $message, $badge, $notifytype = "notification")
	{

		$siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
		// $siteSettings = Sitesettings::first();
		$url = 'https://fcm.googleapis.com/fcm/send';
		
		$deviceIDS = array($registration_ids);
		$messageToBeSent = array();
		$notification = array();
		$notification_message = json_decode($message, true);
		// echo "<pre>"; print_r($message); echo "</pre>"; die;
		$notification['body'] =  $notification_message;	//$notification_message['message'];
		if($notifytype == "gif") {
			$notification['title'] = $notification_message['user_name']. " send a Gif";
		} else if($notifytype == "audio_msg") {
			$notification['title'] = $notification_message['user_name']. " send a Audio";
		} else if($notifytype == "image") {
			$notification['title'] = $notification_message['user_name']." send a Image";
		} else {
			$notification['title'] = $notification_message;	//$notification_message['user_name'].' send a message';
		}

		//$notification['title'] = ($notification_message['type'] == "gif") ? $notification_message['user_name'].' send a message' : "Testing";   
		// $notification['badge'] = $badge;
		$notification['sound'] = "default";

		$messageToBeSent['data']['title'] = "Joysale"; //"Howzu";   
		if($notifytype == 'audio' || $notifytype == 'video' || $notifytype == 'bye')
			$messageToBeSent['data']['message'] = json_decode($message,true);
		else
			$messageToBeSent['data']['message'] = json_encode($message, JSON_UNESCAPED_UNICODE,true);
		$messageToBeSent['data']['type'] = $notifytype;
		$fcmMsg = array(
			'body' => $message,
			'sound' => 'default',
			"type"=>$notifytype
		);
		$fields = array(
			'registration_ids' => $deviceIDS,
			'content_available' => true,
			'notification' => $fcmMsg,
			// 'data' => $messageToBeSent,
			'time_to_live' => 30,
			'sound' => 'default'
			// 'badge' => int($badge)
		);

		$id = 1;
		$headers = array(
			'Authorization:  key='.$siteSettings->androidkey,
			'Content-Type: application/json'
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		$result = curl_exec($ch);
		
		if ($result === false) { 

		}
		$errormsg = curl_error($ch);
		curl_close($ch);
	}

	public static function send_push_notification($registatoin_ids, $message,$notifytype)
	{
		$siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
		// $siteSettings = Sitesettings::first();
		$url = 'https://fcm.googleapis.com/fcm/send';
		//$url = 'https://android.googleapis.com/gcm/send';
		$deviceIDS = $registatoin_ids;
		$registatoin_ids = array($registatoin_ids);
		$messageToBeSent = array();
		$notification = array();
		$notification_message = json_decode($message, true);
		$messageToBeSent['data']['title'] = $notifytype;	//$notification_message['type'];
		$messageToBeSent['data']['message'] = json_decode($message, true);
		
		if ($notifytype == "audio" || $notifytype == "video" || $notifytype== "bye") {
			
					$fields = array(
					'to' => $deviceIDS,
					 // 'notification' => $notification,
					'data' => $messageToBeSent,
					'priority'=>'high',
					'time_to_live' => 30
				);
		} 
		else
		{
				$notification['body'] =  $notification_message['message'];
				$notification['title'] = $notification_message['user_name'].' send a message';
					$fields = array(
					'to' => $deviceIDS,
					 // 'notification' => $notification,
					'data' => $messageToBeSent,
					'priority'=>'high',
					'time_to_live' => 30
				);
		}
		$headers = array(
			'Authorization:  key='.$siteSettings->androidkey,
			'Content-Type: application/json'
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		$result = curl_exec($ch);
 
		if ($result === FALSE) {

		}
		$errormsg = curl_error($ch);
		curl_close($ch);
	}

	public static function sendall_push_notification($registatoin_ids, $message, $notifytype, $device_type) {
		$fcm_url = 'https://fcm.googleapis.com/fcm/send';
		$id = 1;
		$setting = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
		$registatoin_ids = array($registatoin_ids);
		if($device_type == 0) {
			$fcmMsg = array(
				'body' => $message,
				'sound' => "default",
				"type"=>$notifytype
			);
			$fcmFields = array(
				'registration_ids' => $registatoin_ids,
				'priority' => 'high',
				'notification' => $fcmMsg,	
			);
		} elseif ($device_type == 1) {
			$messageToBeSent = array();
			if($notifytype == 'audio' || $notifytype == 'video' || $notifytype == 'bye')
				$messageToBeSent['data']['message'] = json_decode($message,true);
			else
				$messageToBeSent['data']['message'] = json_encode($message, JSON_UNESCAPED_UNICODE,true);
			$messageToBeSent['data']['type'] = $notifytype;
			$fcmFields = array(
				'registration_ids' => $registatoin_ids,
				'data' => $messageToBeSent
			);
		}
		$headers = array(
			'Authorization: key=' . $setting->androidkey,
			'Content-Type: application/json'
		);
		$ch = curl_init();
		curl_setopt( $ch,CURLOPT_URL, $fcm_url );
		curl_setopt( $ch,CURLOPT_POST, true );
		curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fcmFields ) );
		$result = json_decode(curl_exec($ch));
		curl_close( $ch );
	}

	/*public static function sendall_push_notification($registatoin_ids, $message, $notifytype, $device_type) {

		$fcm_url = 'https://fcm.googleapis.com/fcm/send';
		$id = 1;
		$setting = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
		$userdevicedatas = Userdevices::find()->where(['deviceToken'=>$registatoin_ids])->one();
		$registatoin_ids = array($registatoin_ids);
		if($device_type == 0) {
			$callmessage = json_decode($message,true);
			if (is_array($callmessage) && array_key_exists('type', $callmessage) && ($callmessage['type'] == 'video' || $callmessage['type'] == 'audio' || $callmessage['type'] == 'bye'))
			{ 
				
				try {
					if ($userdevicedatas->mode == 0) {	
						$certifcUrl = 'certificate/ck.pem';
						$push = new PushNotification("sandbox", $certifcUrl);
					} else {
						$certifcUrl = 'certificate/ck.pem';
						$push = new PushNotification("production", $certifcUrl);
					}
					$deviceToken = $userdevicedatas->ios_voip_token;
					$push->setDeviceToken($deviceToken);
					$push->setPassPhrase("Joysale");  // THAHAB
					$push->setBadge($badge);
					$notifytype = $callmessage['type'];
					$push->setNotifytype($notifytype);
					// latest update
					$CustomMessage = json_decode($message,true);
					$push->setMessageBody($CustomMessage);
					$push->sendNotification();
				} catch (Exception $e) {
					echo "problem with the device token: " . $deviceToken . " Exception caused: " . $e->getMessage(); 
				}

			} else {
				$fcmMsg = array(
					'body' => $message,
					'sound' => "default",
					"type"=>$notifytype
				);
				$fcmFields = array(
					'registration_ids' => $registatoin_ids,
					'priority' => 'high',
					'notification' => $fcmMsg,	
				);
			}
			
		} elseif ($device_type == 1) {
			$messageToBeSent = array();
			$callmessage = json_decode($message,true);
			if (is_array($callmessage) && array_key_exists('type', $callmessage) && ($callmessage['type'] == 'video' || $callmessage['type'] == 'audio' || $callmessage['type'] == 'bye'))
			{ 
				$messageToBeSent['data']['message'] =  json_decode($message,true);
				$notifytype = $callmessage['type'];
			}
			else
			{
				$messageToBeSent['data']['message'] = json_encode($message, JSON_UNESCAPED_UNICODE,true);
			}
			$messageToBeSent['data']['type'] = $notifytype;
			$fcmFields = array(
				'registration_ids' => $registatoin_ids,
				'data' => $messageToBeSent
			);
		}
		$headers = array(
			'Authorization: key=' . $setting->androidkey,
			'Content-Type: application/json'
		);
		$ch = curl_init();
		curl_setopt( $ch,CURLOPT_URL, $fcm_url );
		curl_setopt( $ch,CURLOPT_POST, true );
		curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fcmFields ) );
		$result = json_decode(curl_exec($ch));
		curl_close( $ch );
		return $result;
	}*/
	public static function push_lang($lang){
		Yii::$app->language = $lang;
		return;
	}
		public static function getCategoryPriority() {
			$id = 1;
			$setting = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
			$details = array();
			if(!empty($setting->category_priority)){
				$categorypriority = Json::decode($setting->category_priority, true);
		}
		return $categorypriority;
	}
	public static function getCategoryfull() {
		$id = 1;
		$setting = Categories::find()->where(['parentCategory' => '0'])->all();
		$details = array();
		if(!empty($setting->category_priority)){
			$categorypriority = Json::decode($setting->category_priority, true);
		}
		return $setting;
	}
	public static function getSubCategory($id) {
		$subCategory =  Categories::find()->where(['parentCategory'=>$id])->all();
		$subCategory =  ArrayHelper::map($subCategory, 'categoryId', 'name');
		return $subCategory;
	}
	public static function getCatImage($id) {
		$category = Categories::find()->where(['categoryId' => $id])->one();
		if(!empty($category))
			return $category->image;
		else
			return Yii::t('app','NIL');
	}
	public static function getsocialLoginDetails() {
		$id = 1;
		$siteSettingsModel = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
		$socialLogin = Json::decode($siteSettingsModel->socialLoginDetails, true);
		return $socialLogin;
	}
	public static function getSitesettings()
	{
		$id = 1;
		$siteSettingsModel = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
		return $siteSettingsModel;
	}
	public static function checkPostvalue($val)
	{
		if (preg_match('/[\'\"^£$%&*()}{@#~?><>;":,.|=_+¬-]/', $val))
		{
			throw new CHttpException(500,'Malicious Activity');
		}
		else
		{
			return true;
		}
	}
	public static function Change_chatUser_status($callValue, $currentID, $ChatUserID)
	{
		$ChatID = yii::$app->Myclass->checkChatExists($currentID, $ChatUserID);
		if($ChatID) {
			$blockedUserValue = yii::$app->Myclass->getChatBlockValue($ChatID);
			if($callValue == "unblock" && $blockedUserValue == $ChatUserID){
				$default = Chats::findOne($ChatID);
				$default->blockedUser = 0;
				if($default->save(false)){
					$blockedUserValue = base64_encode($default->blockedUser);
					return "unblocked~#~".$blockedUserValue;
				}
			} elseif ($callValue == "block" && $blockedUserValue == 0) {
				$default = Chats::findOne($ChatID);
				$default->blockedUser = $ChatUserID;
				if($default->save(false)){
					$blockedUserValue = base64_encode($default->blockedUser);
					return "blocked~#~".$blockedUserValue;
				}
			} elseif ($callValue == "block" && $blockedUserValue == $currentID) {
				$blockedUserValue = base64_encode($blockedUserValue);
				return "currentblocked~#~".$blockedUserValue;
			}
		}
		return "false";
	}
	public static function getChatBlockValue($ChatID) {
		$chatCheck = Chats::find()->where(['chatId'=>$ChatID])->one();
		if(!empty($chatCheck)) {
			return $chatCheck->blockedUser;
		}
	}
	public static function getbraintreemerchantid($paycurrency) {
		$site_datas = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
		$sitepaystatus = Json::decode($site_datas->braintree_merchant_ids,true);
		if($sitepaystatus!="")
		{
			if (array_key_exists($paycurrency, $sitepaystatus)) {
				foreach ($sitepaystatus as $key => $value) {
					if($key == $paycurrency) {
						$data =  $value['merchant_account_id'];
					}
				}
			}
			else
			{
				$data="";
			}
		}else
		{
			$data="";
		}
		return $data;         
	}
	public static function getDeviceName() {
		$iPod = strpos($_SERVER['HTTP_USER_AGENT'],"iPod");
		$iPhone = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone");
		$iPad = strpos($_SERVER['HTTP_USER_AGENT'],"iPad");
		$android = strpos($_SERVER['HTTP_USER_AGENT'],"Android");
		if($iPad||$iPhone||$iPod){
			return 'ios';
		} else if($android) {
			return 'android';
		} else {
			return 'pc';
		}
	}
	public static function getWhosBlock($currentId,$receiverId) {
		$ChatID = yii::$app->Myclass->checkChatExists($currentId, $receiverId);
		if(!empty($ChatID)){
			$blockedUserValue = yii::$app->Myclass->getChatBlockValue($ChatID);
		if($blockedUserValue==0) { return 0; /* No block found */ }
		else{
		if($currentId==$blockedUserValue) { return 1; /* current user is blocked by receiver */ }
	else { return 2;  }
}
}
else { return 0;  }
}
public static function getDaterecordsWeekly($date,$id) {
	$getViews = Userviews::find()
	->select('created_at')
	->where(['product_id'=>$id])
	->all();
	$arrayCount =  array();
	foreach ($getViews as $key=>$value) {
		$data = date("Y-m-d", strtotime($value->created_at));
		$cdate = date("Y-m-d", strtotime($date));
		if(strtotime($data) == strtotime($cdate))
		{
			$arrayCount[] = $value;
		}
	}
	return count($arrayCount);		
	}
	public static function getDaterecordsMontly($month,$id) {
		$getViews = Userviews::find()
		->select('created_at')
		->where(['product_id'=>$id])
		->all();
		$arrayCount =  array();
		foreach ($getViews as $key=>$value) {
			$data = date("Y/m", strtotime($value->created_at));
			if($data == $month)
			{
				$arrayCount[] = $value;
			}
		}
		return count($arrayCount);
	}
	public static function getDaterecordsYearly($year,$id) {
		$getViews = Userviews::find()
		->select('created_at')
		->where(['product_id'=>$id])
		->all();
		$arrayCount =  array();
		foreach ($getViews as $key=>$value) {
			$data = date("Y", strtotime($value->created_at));
			if($data == $year)
			{
				$arrayCount[] = $value;
			}
		}
		return count($arrayCount);
	}
	public static function getFilterdata($filterId) {
		$getFilters = Filter::find()->where(['id'=>$filterId])->one();
		return $getFilters;
	}
	public static function checkproductexist($id) {
		$product =  Products::find()->where(['category'=>$id])->count();
		if(!empty($product))
			return $product;
	}
	public static function getSubscriptionCurrency() {
			$siteSetting = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
			$str = explode("-",$siteSetting->subscriptionCurrency);
			if(isset($siteSetting->subscriptionCurrency) && $siteSetting->subscriptionCurrency != "")
			return $str[0];
	}
	// User Limitation Start
	/*public static function getFreeListing() {
		$freelistingdetails =  Freelisting::find()->all();
		if(!empty($freelistingdetails))
			return $freelistingdetails;
		}
	public static function getFreelistingName($subscriptionid) {
		$freelistingdetails =  Freelisting::find()->where(['id' => $subscriptionid])->one();
		if(!empty($freelistingdetails))
			return $freelistingdetails->name;
	}*/
	// User Limitation End
	public static function getSubcriptionStatus($id) {
		$user =  Users::find()->where(['userId' => $id])->one();
		if(!empty($user))
			return $user->subscription_enable;
	}

	public function getFreeCount($userId)
	{
		$products = Users::find()->where(['userId' => $userId])->one();
		$freecount = $products->remaining_list_count;
		return $freecount;
	}
	public function getFreeStatus($userId)
	{
		$user = Users::find()->where(['userId' => $userId])->one();
		$freestatus = $user->free_listing_enable;
		return $freestatus;
	}
	public function getSubscriptionStatus($userId)
	{
		$user = Users::find()->where(['userId' => $userId])->one();
		$substatus = $user->subscription_enable;
		return $substatus;
	}
	public function getProductCnt($userId)
	{
		$products = Products::find()->where(['userId' => $userId])->all();
		$procount = count($products);
		return $procount;
	}
	public function getRemainingPosts($userId)
	{
		$user = Users::find()->where(['userId' => $userId])->one();
		$remaining_list_count = $user->remaining_posts;
		return $remaining_list_count;
	}

	public static function getReviewDetails($id) {
		$review =  Reviews::find()->where(['logId' => $id])->one();
		if(!empty($review))
			return $review;
	}
	public static function getLogDetails($id) {
		$logDetails =  Logs::find()->where(['id' => $id])->one();
		if(!empty($logDetails))
			return $logDetails;
	}
}
?>