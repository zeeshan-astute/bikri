<?php
$this->params['breadcrumbs'][]=[
	'Products'=>['index'],
	$model->name=>['view','id'=>$model->productId],
	'Update',
];
?>
<?php
echo $this->render('_updateform', array('model'=>$model,
	'attributes'=>$attributes,
	'parentCategory'=>$parentCategory,'subCategory'=>$subCategory,
	'sub_subCategory' => $sub_subCategory,'photos' => $photos,'options'=>$options, 
	'shippingTime' => $shippingTime, 'countryModel' => $countryModel, 'topCurs' => $topCurs,
	'currencies' => $currencies, 'givingaway'=>$givingaway, 'promotionCurrency'=>$promotionCurrency,
	'urgentPrice'=>$urgentPrice, 'promotionDetails'=>$promotionDetails,'plen' => $plen,
	'Filtermodel'=>$Filtermodel,'shipping_country_code'=>$shipping_country_code,
	'sub_cat_name' => $sub_cat_name,'pricerange'=>$pricerange,'chatuserModel'=>$chatuserModel)); ?>