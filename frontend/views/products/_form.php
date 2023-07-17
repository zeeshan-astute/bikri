<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="-1">

<?php
	use yii\helpers\Html;
	use yii\widgets\ActiveForm;
	use yii\helpers\Json;
	use dosamigos\ckeditor\CKEditor;
	use common\models\Filter;
	error_reporting(0);
	$baseUrl = Yii::$app->request->baseUrl;
?> 
<?php
	if (isset($_SESSION['language'])) 
	{
		if($_SESSION['language'] == 'ar')
		{
			$lang='rtl';
		}
	} else
	{
		$lang='ltr';
	}
?>
<div class="form product-form-container">
	<div id="page-container" class="product-new-update">
		<div class="container-small container">
			<div class="row">
				<div class="classified-breadcrumb add-product col-xs-12 col-sm-12 col-md-12 col-lg-12">
					<ol class="breadcrumb">
						<li><a href="<?php echo Yii::$app->getUrlManager()->getBaseUrl().'/'; ?>"><?php echo Yii::t('app','Home'); ?></a></li>
						<li><a href="#"><?php echo Yii::t('app','Sell your stuff'); ?></a></li>
					</ol>
				</div>
			</div>
			<?php 
				$form = ActiveForm::begin(['id'=>'products-form','options' => ['enctype' => 'multipart/form-data']]); 
			?>
			<?php
			if (isset($plen)) {?>
				<input type="hidden" name="count" id="count" value="<?php echo $plen; ?>">
			<?php }else{ ?>
				<input type="hidden" name="count" id="count" value="0">
			<?php } ?>
			<?php if(isset($pricerange)){?>
				<input type="hidden" name="before_decimal" id="before_decimal" value="<?php echo $pricerange->before_decimal_notation; ?>">
				<input type="hidden" name="after_decimal" id="after_decimal" value="<?php echo $pricerange->after_decimal_notation; ?>"> 
			<?php } ?>
			<div class="row">
				<div class="full-horizontal-line col-xs-12 col-sm-12 col-md-12 col-lg-12"></div>
			</div>
			<?php if(!$model->isNewRecord) { 
				?>
				<div class="row">
					<div class="add-product-heading col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<h2 class="top-heading-text"><?=Yii::t('app','Post your list free')?></h2>
						<p class="top-heading-sub-text">
							<?=Yii::t('app','Provide more information about your item and upload good quality photos')?>
						</p>
					</div>
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="padding:0px; !important">
						<div class="edit-btn">
							<?php if($model->soldItem == 1){ ?>
									<a href="#" data-loading-text="Posting..." id="load" data-toggle="modal" class="sold-btn sale-btn" onclick="soldItems('<?php echo yii::$app->Myclass->safe_b64encode($model->productId.'-0') ?>', '0')">
										<?php echo Yii::t('app','Back to sale'); ?>
									</a>
							<?php }else if($model->soldItem != 1 && $model->quantity != 0){ ?>
								<a href="#" data-loading-text="Posting..." id="load" data-toggle="modal" class="sold-btn" onclick="soldItems('<?php echo yii::$app->Myclass->safe_b64encode($model->productId.'-0') ?>', '1')">
									<?php echo Yii::t('app','Mark as sold'); ?>
								</a> 
							<?php } ?>
							<a data-target="#" data-toggle="modal" href="#" class="delete-btn" onclick="confirmModal('method', 'deleteItem', '<?php echo yii::$app->Myclass->safe_b64encode($model->productId.'-0') ?>')">
								<?php echo Yii::t('app','Delete Sale'); ?>
							</a>
						</div>
					</div>
				</div>
			<?php }else{  ?>
				<div class="row">
					<div class="add-product-heading col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<h2 class="top-heading-text"><?php echo Yii::t('app','Post your list free'); ?></h2>
						<p class="top-heading-sub-text">
							<?php echo Yii::t('app','Provide more information about your item and upload good quality photos'); ?>
						</p>
					</div>
				</div>
			<?php } ?>
			<div class="row">
				<div class="add-photos col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"> 
					<div class="add-photos-heading">
						<span><?php echo Yii::t('app','Add photos of your stuff'); ?> <span class="required">*</span>
						<!-- <p class="text-muted"><?php echo Yii::t('app','(Offensive,violent or inappropriate images are not allowed)'); ?></p> --></span>
					</div>
					<div class="input-file-container dis_inline_block align_middle">
						<input class="input-file dis_none" type="file" id="image_file" multiple="true" name="XUploadForm[file]" accept=".png, .jpg, .jpeg" onchange="start_image_upload();">
						<div class="errorMessage" id="Sitesettings_logo_em_" ></div>	
						<div class="blog_images margin_bottom20"> <label tabindex="0" for="image_file" class="input-file-trigger direcc"><div class="img_browse"><div class="add_img"></div></div></label> 
						<?php
						if(!empty($photos)):
							foreach ($photos as $photo) { 
								echo '<div class="uploaded_img align_middle margin_left10" style="float: inherit;"><img src="'.$baseUrl.'/media/item/'.$model->productId.'/'.$photo->name.'"" class="img-responsive"><button type="button" class="close post_img_cls" data-dismiss="modal" aria-label="Close" onclick="remove_images1(this,\''.$photo->name.'\',\''.$model->productId.'\')"><span aria-hidden="true">×</span></button></div>';
							} 
						endif;?>
					</div>
				</div>
				<?php
					echo '<img id="loadingimg" src="'.$baseUrl.'/images/load.gif" class="loading" style="display:none;">';
				?>
				<input type="hidden" value='' name="uploadedfiles" id="uploadedfiles">
				<input type="hidden" name="removefiles" id="removefiles">
				<input type="hidden" value='<?php echo Yii::$app->request->referrer; ?>' name="previousurl" id="previousurl">
				<div id="image_error" class="errorMessage"></div>
			</div>
	<div class="add-stuff-Category-section col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
		<div class="add-stuff-Category-heading">
			<span>
				<?php echo Yii::t('app','What is your listing based on'); ?>
			</span>
		</div>
		<div class="Category-select-box-row col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
			<div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
				<input type="hidden" name="productId" id="productId" value="">
				<label class="Category-select-box-heading required" for="Products_category">
					<?=Yii::t('app','Select Category')?> 
					<span class="required">*</span>
				</label>	
				<?php if (!empty($parentCategory)){
					echo '<select class="form-control select-box-down-arrow" name="Products[category]" id="Products_category">
					<option value="">'.Yii::t('app','Select Category').'</option>';
					foreach ($parentCategory as $key => $value) {
						if($model->category == $key)
							echo '<option value="'.$key.'" selected>'.Yii::t('app',$value).'</option>';
						else
							echo '<option value="'.$key.'">'.Yii::t('app',$value).'</option>';
					}
					echo '</select>';
				}
				else
				{
					echo $form->dropDownList(Yii::t('app',$model), 'category', array('prompt'=>Yii::t('admin','Select Parent category'), 'class' => 'form-control select-box-down-arrow'));
				}
				?>
				<div id="Products_category_em_" class="errorMessage"></div>
			</div>
		</div>
		<div class="Category-select-box-row col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding" id="subcategoryhide">
			<div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
				<label class="Category-select-box-heading" for="Products_subCategory"><?=Yii::t('app','Select Subcategory')?><span class="required">*</span></label>		
				<?php if (!empty($subCategory)){
					echo $form->field($model, 'subCategory')->dropDownList( 
						$subCategory,
						['prompt'=>Yii::t('app','Select Subcategory'),'class'=>'subcatid form-control select-box-down-arrow','id'=>'Products_subCategory'])->label(false);
				}else{
					echo $form->field($model, 'subCategory')->dropDownList( 
						$subCategory,
						['prompt'=>Yii::t('app','Select Subcategory'),'class'=>'subcatid form-control select-box-down-arrow','id'=>'Products_subCategory'])->label(false);
				}
				?>
				<div id="Products_subcategory_em_" class="errorMessage"></div>
			</div>
		</div>
		<div id="showField">
			<div class="Category-select-box-row col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
				<div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
					<label class="Category-select-box-heading" for="Products_sub_subCategory" id="Products_sub_subCategory_head"><?=Yii::t('app','Select child category')?></label>		
					<?php echo $form->field($model, 'sub_subCategory')->dropDownList( 
						$sub_subCategory,
						['prompt'=>Yii::t('app','Select child category'),'class'=>'form-control select-box-down-arrow','id'=>'Products_sub_subCategory'])->label(false);
						?>
						<div id="Products_sub_subCategory_em_" class="errorMessage"></div>
					</div>
				</div>
			</div>
			<div id="showsubfield">
			</div>
			<div id="childField">
			</div>
			<?php
			if(!empty($filtermodel))
			{
				foreach($filtermodel as $key=>$val)
				{
					$getFilterDatas = Filter::find()->where(['id'=>$val])->one();
					if($getFilterDatas->type == 'dropdown')
					{
						$splitvalues = explode(',', $getFilterDatas->value);
						?>
						<div>
							<label><?php echo $getFilterDatas->name; ?></label>
							<select id="filter" class="form-control select-box-down-arrow productattributes" name="<?php echo $getFilterDatas->name; ?>">
								<?php
								foreach($splitvalues as $valnews)
								{
									echo '<option>'.$valnews.'</option>';
								}
								?>
							</select>
						</div>
						<?php	
					}elseif($getFilterDatas->type == 'range')
					{
						?>
						<div>
							<label><?php echo $getFilterDatas->name; ?></label>
							<input type="text" class="form-control productattributerange" name="range" value=""/>
						</div>
						<?php
					}elseif($getFilterDatas->type == 'multilevel')
					{
						?>
						<div>
							<label><?php echo 's'; ?></label>
						</div>
						<?php
					}
					?>
					<?php
				}
				?>
				<?php
			}
			?>
			<div class="Category-input-box-row col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
				<label><?=Yii::t('app','Name')?> <span class="required">*</span></label>		
				<?= $form->field($model, 'name')->textInput(['maxlength' => true,'id' => 'Products_name','placeholder' => Yii::t('app','Stuff title')])->label(false); ?>
				<div id="Products_name_em_" class="errorMessage"></div>
			</div>
			<div class="Category-input-box-row col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
				<label><?=Yii::t('app','Description')?> <span class="required">*</span></label>
				<?= $form->field($model, 'description')->widget(CKEditor::className(), [
					'options' => ['rows' => 6, 'id' => 'Products_description'],
					'preset' => 'basic',
					'clientOptions' => ['contentsLangDirection'=>$lang,'target'=>'_blank']
				])->label(false) ?>
				<div id="Products_description_em_" class="errorMessage"></div>
			</div>
		</br>
		<?php if($model->productId !=0 && $model->productId!="") { 
			if ($givingaway == "yes") { ?>
				<div class="Category-give-away-box-row col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
					<div class="Category-input-box-row col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
						<label class="Category-input-box-heading  col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><?php echo Yii::t('app','Giving Away'); ?></label>
						<div class="switch-box col-xs-6 col-sm-2 col-md-2 col-lg-2 no-hor-padding" style="width:50%;">
							<div class="switch-1 col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
								<?php if( $model->price == 0)
								{ ?>
									<input id="giving_away" class="cmn-toggle cmn-toggle-round" name="giving_away" type="checkbox" value="1" checked="checked">
								<?php } else { ?>  
									<input id="giving_away" class="cmn-toggle-1 cmn-toggle-round-1" name="giving_away" type="checkbox" value="0">
								<?php } ?>
								<label for="giving_away"></label>
							</div>
						</div>
					</div>
				</div>
			<?php } else if($model->price == 0 && $givingaway == "no")
			{ ?>
				<div class="Category-give-away-box-row col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
					<div class="Category-input-box-row col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
						<label class="Category-input-box-heading  col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding" for="Products_giving_away"><?php echo Yii::t('app','Giving Away'); ?></label>
						<div class="switch-box col-xs-6 col-sm-2 col-md-2 col-lg-2 no-hor-padding" style="width:50%;">
							<div class="switch-1 col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
								<input id="giving_away" class="cmn-toggle cmn-toggle-round" name="giving_away" type="checkbox" value="1" checked="checked">
								<label for="giving_away"></label>
							</div>
						</div>
					</div>
				</div>
			<?php } else { ?>
				<input id="giving_away" type="hidden" value="0">
			<?php }  } elseif ($givingaway == "yes") { ?>
				<div class="Category-give-away-box-row col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
					<div class="Category-input-box-row col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
						<label class="Category-input-box-heading  col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><?php echo Yii::t('app','Giving Away'); ?></label>
						<div class="switch-box col-xs-6 col-sm-2 col-md-2 col-lg-2 no-hor-padding" style="width:50%;">
							<div class="switch-1 col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
								<?php if($model->price != "" && $model->price == 0)
								{ ?>
									<input id="giving_away" class="cmn-toggle cmn-toggle-round" name="giving_away" type="checkbox" value="1" checked="checked">
								<?php } else { ?>  
									<input id="giving_away" class="cmn-toggle-1 cmn-toggle-round-1" name="giving_away" type="checkbox" value="0">
								<?php } ?>
								<label for="giving_away"></label>
							</div>
						</div>
					</div>
				</div>
			<?php } else { ?>
				<input id="giving_away" type="hidden" value="0">
			<?php } ?>
		</br>
		<?php if(isset($model->price) && $model->price == 0) { 
			$price_box = "display: none;";
		} else {
			$price_box = "display: block;";
		}
		?>
		<div class="Category-price-box-row col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding" style="<?php echo $price_box; ?> ">
			<div class="Category-input-box-row col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding" style="margin-top: auto;">
				<label><?=Yii::t('app','Price')?> <span class="required">*</span></label>
				<?= $form->field($model, 'price')->textInput(['maxlength' => true,'class' => 'col-xs-12 col-sm-10 col-md-9 col-lg-10 no-hor-padding', 'placeholder'=>Yii::t('app','Stuff price'),'id' => 'Products_price'])->label(false); ?>
				<div class="currency-select-box-row col-xs-12 col-sm-2 col-md-3 col-lg-2 no-hor-padding">
					<div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
						<?php $currencyList = yii::$app->Myclass->getCurrencyData();$hideCurrencyFlag = 0; ?>
						<select class="form-control select-box-down-arrow" id="sel1" name="Products[currency]">
							<?php foreach ($currencyList as $currency){
								$currencySelect = "";
								$currencyDetails = $currency->currency_symbol."-".$currency->currency_shortcode;
								if($model->currency == $currencyDetails)
									$currencySelect = "selected";
								echo "<option $currencySelect value='$currencyDetails'>$currency->currency_shortcode</option>";
							}?>
						</select>
					</div>
				</div>
				<div id="Products_price_em_" class="errorMessage"></div>
			</div>
		</div>
	</div>
	<div class="add-stuff-Category-section col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding dynamic-section" style="display: none;">
		<div class="add-stuff-Category-heading col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
			<span><?=Yii::t('app','What is your expectation and other details'); ?></span>
		</div>
		<div class="dynamicProperty"></div>
		<div id="Products_productCondition_em_" class="errorMessage"></div>
	</div>
	<?php
	$sitesetting = yii::$app->Myclass->getSitesettings();
	$paymentmode = Json::decode($sitesetting->sitepaymentmodes,true);
	if($paymentmode['buynowPaymentMode'] == 1)
	{
		$cate = yii::$app->Myclass->getCategoryDet($model->category);
		$buynow = Json::decode($cate->categoryProperty,true);
		if(!$model->isNewRecord){
			$instantBuyDetails = "";
			if($model->instantBuy == 1 && $buynow['buyNow'] == 'enable'){
				$instantBuyDetails = "style='display:block;'";
			}
		}else{
			$userId = Yii::$app->user->id;
			$instantBuyDetails = "style='display:none;'";
			$model->paypalid = yii::$app->Myclass->getLastProductPaypalId($userId);
		}
		?>
		<div class="add-stuff-Category-section col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding instant-buy-details"<?php echo $instantBuyDetails; ?>>
			<div class="add-stuff-Category-heading col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
				<span><?=Yii::t('app','Instant buy details')?></span>
			</div>
			<?php
			if($paymentmode['buynowPaymentMode'] == 1) {
				if($model->shippingCost == "" || $model->shippingCost == '0')
					?>
				<div class="Category-input-box-row col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
					<?=Yii::t('app','Shipping Cost')?>	
					<?php echo $form->field($model,'shippingCost')->textInput(['class' => 'col-xs-12 col-sm-10 col-md-9 col-lg-10 no-hor-padding', 'placeholder'=> Yii::t('app','Shipping Cost'),'id' => 'Products_shippingCost'])->label(false); ?>
					<div id="Products_shippingCost_em_" class="errorMessage"></div>
				</div>
			<?php } ?>
		</div>
	<?php } ?>
	<div class="add-stuff-Category-section col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
		<div class="add-stuff-Category-heading col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
			<span><?php echo Yii::t('app','Where the item is located?'); ?></span>
		</div>
		<div class="Category-input-box-row col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding location-container">
			<!--here we add label tag-->
			<label>
				<?php echo Yii::t('app','Listing’s location'); ?> <span class="required">*</span></label>
				<input id="Products_location" class="form-control" type="text"
				placeholder="<?php echo Yii::t('app','Tell where you sell the item'); ?>"
				name="Products[location]" onchange="return resetLatLong()"
				value="<?php echo $model->location; ?>">
				<input id="latitude"  value="1" type="hidden" name="Products[latitude]"
				value="3.0827"> 
				<input id="longitude" type="hidden" name="Products[longitude]" value="80.2707">
				<input id="Productscountry" type="hidden" name="Products[country]" value="">
				<input id="Productstate" type="hidden" name="Products[city]" value="">
				<p>
					<?php echo Yii::t('app',"Note: Choose a location from the dropdown list. Avoid entering manually"); ?>		</p>
					<div class="errorMessage" id="Products_location_em_"></div>
				</div></div>
				<?php
				if($paymentmode['buynowPaymentMode'] == 1) {
					if(isset($shipping_country_code) && $shipping_country_code != "") {
						?>
						<input id="shippingcountry" type="hidden" name="Products[shippingcountry]" value="<?php echo $shipping_country_code; ?>">
					<?php }else {?>
						<input id="shippingcountry" type="hidden" name="Products[shippingcountry]" value="<?php echo $model->shippingcountry; ?>">
					<?php } }?>
				</div>
				<div class="add-stuff-Category-section col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
					<div class="add-stuff-Category-heading col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
						<span><?=Yii::t('app',"Youtube Video Embed URL")?></span>
					</div>
					<div class="Category-input-box-row col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding location-container">
						<label><?=Yii::t('app','Product Video')?></label>
						<input id="videoUrl" class="form-control" placeholder="https://www.youtube.com/watch?v=embed%2FSMcFn" name="Products[videoUrl]"  value="<?php echo $model->videoUrl; ?>" type="text">
						<div id="Products_videourl_" class="errorMessage"></div>
					</div></div>
					<?php 
						echo Html::submitButton($model->isNewRecord ? Yii::t('app','Save') : Yii::t('app','Update'),
						array('id'=>'addProduct','class'=>'post-btn btnUpdate btn','disabled'=>'disabled','onclick'=>'return validateProduct()')); 
					?>
					<?php if(!$model->isNewRecord){ ?>
						<?= Html::a(Yii::t('app','Cancel'), ['/user/profiles'], ['class'=>'delete-btn margin-10']) ?>
					<?php }?>
						<input type="hidden" name="Products[promotion][type]" value="" id="promotion-type">
						<input type="hidden" name="Products[promotion][addtype]" value="" id="promotion-addtype">
						<input type="hidden" name="Products[uploadSessionId]" value="<?php // $sessionId; ?>" id="promotion-addtype">
						<?php if($model->isNewRecord){
							echo "<input type='hidden' value='0' class='product-update-flag' />";
						}else{
							echo "<input type='hidden' value='1' class='product-update-flag' />";
						}?>
					</div>
				</div>
			</div>
			<?php ActiveForm::end(); ?>
			<div class="paypal-form-container"></div>
			<!--Add popup modal-->
			<div class="modal fade" id="post-your-list" role="dialog" data-backdrop="static" data-keyboard="false">
				<div class="modal-dialog post-list-modal-width">
					<div class="post-list-modal-content login-modal-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
						<div class="post-list-header promoteListing login-modal-header col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
							<div class="modal-header-text"><p class="login-header-text"><?php $autoApporveDetails = yii::$app->Myclass->getSitesettings();
							$autoApprove = $autoApporveDetails->product_autoapprove;
							if($autoApprove==1)
							{
								echo Yii::t('app','Your stuff successfully posted!');
							}
							else
							{
								echo Yii::t('app','Your product is waiting for admin approval');
							} ?></p></div>
						</div>
						<div class="login-line col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"></div>
						<div class="post-list-cnt login-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding ">
							<div class="login-box col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
								<div class="login-text-box col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
									<div class="post-list-modal-heading"><?php echo Yii::t('app','Highlight your listing?'); ?></div>
									<div class="post-list-content">
										<?php echo $sitesetting->sitename." ".Yii::t('app','allows you to highlight your listing with two different options to reach more number of buyers. You can choose the appropriate option for your listings. Urgent listings gets more leads from buyers and featured listings shows at various places of the website to reach more buyers.'); ?>
									</div>
								</div>
								<div class="post-list-tab-cnt">
									<ul class="post-list-modal-tab nav nav-tabs">
										<li class="active"><a data-toggle="tab" href="#urgent"><?php echo Yii::t('app','Urgent'); ?></a></li>
										<li><a data-toggle="tab" href="#promote"><?php echo Yii::t('app','Ad'); ?></a></li>
									</ul>
								</div>
							</div>
						</div>
						<div class="post-list-tab-content  tab-content">
							<div id="urgent" class="tab-pane fade in active">
								<p> <?php echo Yii::t('app','To make your ads instantly viewable you can go for Urgent ads, which gets highlighted at the top.'); ?> </p>
								<p align='center'>
									<?php $promoteCurrency = explode("-", $promotionCurrency);	
									if($bannerpaymenttype == "stripe"){
										$stripe_currency = ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'];
										if(in_array(strtoupper(trim($promoteCurrency[0])),$stripe_currency)){
											$urgentPrice = round($urgentPrice); 
										}
									}
									if (isset($_SESSION['language'])  && $_SESSION['language'] == 'ar'){
										echo yii::$app->Myclass->convertArabicPopupFormattingCurrency(str_replace(" ", "", $promoteCurrency[0]),$urgentPrice); 
									}
									else{
										echo yii::$app->Myclass->convertFormattingCurrency(str_replace(" ", "", $promoteCurrency[0]),$urgentPrice); 
									} ?></p>
									<div class="urgent-tab-left col-xs-12 col-sm-8 col-md-8 col-lg-8 no-hor-padding">
										<ul><div class="urgent-tab-heading"><?php echo Yii::t('app','Urgent tag Features')?>:</div>
											<li><i class="tick-icon fa fa-check"></i><span class="urgent-tab-left-list"><?php echo Yii::t('app','More opportunities for your buyers to see your product'); ?></span></li>
											<li><i class="tick-icon fa fa-check"></i><span class="urgent-tab-left-list"><?php echo Yii::t('app','Higher frequency of listing placements'); ?></span></li>
											<li><i class="tick-icon fa fa-check"></i><span class="urgent-tab-left-list"><?php echo Yii::t('app','Highlight your listing to stand out'); ?></span></li>
											<li><i class="tick-icon fa fa-check"></i><span class="urgent-tab-left-list"><?php echo Yii::t('app','Use for Make fast sale for seller and Make buyer to do purchase as Urgent'); ?></span></li>
											<li class="stuff-post">
												<?php
												$paymenttype = json_decode($sitesetting->sitepaymentmodes, true); 
												$bannerpaymenttype =  $paymenttype['bannerPaymenttype'];
												if($bannerpaymenttype == "stripe"){
													$form = ActiveForm::begin(['id'=>'promotionstripeform',
													'action'  => Yii::$app->urlManager->createAbsoluteUrl('/products/promotionstripepaymentprocess'),'options' => ['enctype' => 'multipart/form-data']]); ?>
													<input type="hidden" name="BPromotionType" value='urgent' />
													<input type="hidden" name="BPromotionProductid" id="UPromotionProductid" value="">
													<button class="btn post-btn brainTree" id="customButton"><?php echo Yii::t('app','Highlight with stripe'); ?></button>
													<input type="hidden" id="itemids" name="itemids">
													<?php 
														$userId = Yii::$app->user->id;
														$sitesetting = yii::$app->Myclass->getSitesettings();
														$stripeSetting = json_decode($sitesetting->stripe_settings, true); 
														$stripe_key = $stripeSetting['stripePublicKey'];
														$total_price = ($sitesetting->urgentPrice)*100;
														$currency =  explode('-', $sitesetting->promotionCurrency);	
														$promotionType = "urgent";
														$customField = $promotionType."-_-".$currency[0]."-_-0-_-".$total_price."-_-".$userId;
														$customField = yii::$app->Myclass->cart_encrypt($customField, "pr0m0tion-det@ils");
													?>
													<input type="hidden" value="<?php echo $total_price; ?>" id="price" >
													<input type="hidden" value="<?php echo $promoteCurrency[1]; ?>" id="displaycurrency" >
													<input type="hidden" value="" id="promotiontype" name="promotiontype">
													<input type="hidden" value="<?php echo $stripe_key; ?>" id="stripekey" >
													<input type="hidden" value="" id="totalprice" name="totalPrice">
													<div id="shiperr-stripe" style="font-size:13px;color:red;margin-left: 20px;"></div>
													<input type="hidden" value="<?php echo $customField; ?>" id="customField1" name="customField1">
													<input type="hidden" value="" id="customField" name="customField">
													<input type="hidden" name="BPromotionType" value='urgent' />
													<input type="hidden" name="BPromotionProductid" id="UPromotionProductid" value="">
													<input type="hidden" name="currency" value="<?php echo $currency[0]; ?>"/>
													<a class="delete-btn promotion-cancel" href=""><?php echo Yii::t('app','Cancel'); ?></a>
													<div class="urgent-promote-error delete-btn"></div>
													<?php ActiveForm::end(); } else { ?>
														<form name="promotionbraintreeform" method="post" action="<?php echo $baseUrl.'/products/promotionpaymentprocess/'; ?>" onsubmit="return promotionUpdate('urgent')">
															<input type="hidden" name="BPromotionType" value='urgent' />
															<input type="hidden" name="BPromotionProductid" id="UPromotionProductid" value="">
															<button class="btn post-btn brainTree" href="javascript:void(0);" onclick="" type="submit"><?php echo Yii::t('app','Highlight with braintree'); ?></button>
															<a class="delete-btn promotion-cancel" href=""><?php echo Yii::t('app','Cancel'); ?></a>
															<div class="urgent-promote-error delete-btn"></div>
														</form>
													<?php } ?>
												</li>
											</ul>
										</div>
										<div class="urgent-tab-right col-xs-12 col-sm-4 col-md-4 col-lg-4 no-hor-padding">
											<div class="urgent-right-circle-icon"><span class="item-urgent-1"><?php echo Yii::t('app','Urgent');?></span></div>
										</div>
									</div>
									<div id="promote" class="tab-pane fade">
										<p><?php echo Yii::t('app','Promote your listings to reach more users than normal listings. The promoted listings will be shown at various places to attract the buyers easily.'); ?></p>
										<div class="tab-radio-button-cnt col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
											<?php foreach ($promotionDetails as $promotion){ ?>
												<div class="tab-radio-button col-xs-12 col-sm-6 col-md-3 col-lg-3 no-hor-padding">
													<div class="tab-radio-content">
														<label class="required">
															
															<input type="radio" class="radioselect" value="aaa" name="optradio" onclick="updatePromotion('<?php echo $promotion->id; ?>')">
														</label>
														<div class="radio-tab-period"><?php echo $promotion->name; ?></div>
														<div class="radio-tab-price packPrice col-xs-offset-3 col-sm-offset-5 col-md-offset-4 col-lg-offset-4">
															<?php
															$promotionCurrencyDetails = explode('-', $promotionCurrency);
															if($bannerpaymenttype == "stripe"){
															$stripe_currency = ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'];
			        										if(in_array(strtoupper(trim($promotionCurrencyDetails[0])),$stripe_currency)){
			        											$promotion->price = round($promotion->price); 
			        										}
			        										}
															if (isset($_SESSION['language']) && ($_SESSION['language'] == 'ar'))
																echo yii::$app->Myclass->convertArabicFormattingCurrency($promotionCurrencyDetails[0],$promotion->price); 
															else{
																echo yii::$app->Myclass->convertFormattingCurrency($promotionCurrencyDetails[0],$promotion->price);
															} ?>
														</div>
														<div class="radio-tab-days"><?php echo $promotion->days; ?> <?php echo Yii::t('app','days'); ?></div>
													</div>
												</div>
											<?php } ?>
											
										</div>
										<div class="promote-tab-left col-xs-12 col-sm-8 col-md-8 col-lg-8 no-hor-padding">
											<ul>
												<div class="promote-tab-heading">
													<?php echo Yii::t('app','promote tag Features:'); ?>
												</div>
												<li><i class="tick-icon fa fa-check"></i><span class="promote-tab-left-list"><?php echo Yii::t('app','View-able with highlight for all users on desktop and mobile'); ?></span></li>
												<li><i class="tick-icon fa fa-check"></i><span class="promote-tab-left-list"><?php echo Yii::t('app','Displayed at the top of the page in search results'); ?></span></li>
												<li><i class="tick-icon fa fa-check"></i><span class="promote-tab-left-list"><?php echo Yii::t('app','Higher visibility in search results means more buyers'); ?></span></li>
												<li><i class="tick-icon fa fa-check"></i><span class="promote-tab-left-list"><?php echo Yii::t('app','Listing stands out from the regular posts'); ?></span></li>
												<li class="stuff-post">
													<?php 
													$userId = Yii::$app->user->id; 
													$sitesetting = yii::$app->Myclass->getSitesettings();
													$stripeSetting = json_decode($sitesetting->stripe_settings, true); 
													$stripe_key = $stripeSetting['stripePublicKey'];
													$currency =  explode('-', $sitesetting->promotionCurrency);
													$promotionTypes = "adds";
													$total_pricess = $promotion->price;
													$customFieldd = $promotionTypes."-_-".$currency[0]."-_-0-_-".$total_pricess."-_-".$userId;
													$customFieldd = yii::$app->Myclass->cart_encrypt($customFieldd, "pr0m0tion-det@ils"); 
													?>
													<?php
													if($bannerpaymenttype == "stripe"){
														$form = ActiveForm::begin(['id'=>'adpromotionstripeform',
														'action'  => Yii::$app->urlManager->createAbsoluteUrl('/products/promotionstripepaymentprocess'),'options' => ['enctype' => 'multipart/form-data']]); ?>
														<div id="radioerror" class="errorMessage"></div>
														
														<button class="btn post-btn brainTree" id="customButton1"><?php echo Yii::t('app','Promote with Stripe');?></button>
														
														
														
														
														<input type="hidden" id="promotionids" name="promotionids">
														<input type="hidden" value="<?php echo $promotionTypes; ?>" id="promotiontypee" name="promotiontypee">
														<input type="hidden" value="" id="itemide" name="itemide" >
														<input type="hidden" value="<?php echo $stripe_key; ?>" id="stripekeyy" >
														<input type="hidden"  value="<?php echo $total_pricess; ?>" id="totalpricee" >
														<input type="hidden"  value="<?php echo $total_pricess; ?>" name="totalPrice" id="totalprice_promotionstripeform">
														<div id="shiperr-stripe" style="font-size:13px;color:red;margin-left: 20px;display:none;"></div>
														<input type="hidden" value="<?php echo $customFieldd; ?>" id="customFieldd" name="customFieldd">
														<input type="hidden" id ="currencyy" name="currencyy" value="<?php echo $currency[0]; ?>"/>
														<a class="delete-btn promotion-cancel" href=""><?php echo Yii::t('app','Cancel'); ?></a>
														
														<?php ActiveForm::end(); }else{ ?>
															<form name="promotionbraintreeform" method="post" action="<?php echo $baseUrl.'/products/promotionpaymentprocess/'; ?>" onsubmit="return promotionUpdate('adds')">
																<input type="hidden" name="BPromotionType" value='adds' />
																<input type="hidden" name="BPromotionProductid" id="ADPromotionProductid" value="">
																<input type="hidden" name="BPromotionid" id="ADPromotionid" value="">
																<button class="post-btn btn brainTree" href="javascript:void(0);" onclick=""><?php echo Yii::t('app','Promote with braintree'); ?></button>
																<a class="delete-btn promotion-cancel" href="javascript:void(0);"><?php echo Yii::t('app','Cancel'); ?></a>
																<div class="adds-promote-error delete-btn"></div> 
															</form>
													<?php } ?>
													</li>
												</ul>
											</div>
											<div class="promote-tab-right col-xs-12 col-sm-4 col-md-4 col-lg-4 no-hor-padding">
												<div class="promote-right-circle-icon"><span class="item-ad-1"><?=Yii::t('app','Ad')?></span></div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<input type="hidden" class="promotion-product-id" value="">
						</div>
					</div>
				</div>
			</div>
			<script>
				var shippingArray = new Array();
				<?php
				if (isset($_SESSION['language']) && $_SESSION['language'] == 'ar') { ?>
					$('#cke_1_toolbox').addClass('rtl_float');
				<?php	}
				if (isset($jsShippingDetails) && $jsShippingDetails != ''){ ?>
					shippingArray = [<?php echo $jsShippingDetails; ?>];
				<?php } ?>
				<?php if (!$model->isNewRecord){ 
					?>
					window.onload = function() {
						productId = "<?php echo $model->productId; ?>";
						$.getJSON('<?php echo Yii::$app->urlManager->createUrl("upload", array("_method" => "list", "id" => $model->productId)); ?>', function (result) {
							var objForm = $('#products-form');
							if (result && result.length) {
								objForm.fileupload('option', 'done').call(objForm, null, {result: result});
								productImage = parseInt(result.length);
								console.log("In product append: "+productImage);
							}
						});
						var selectedCategory = $('#Products_category').val();
						var giving_away = $("#giving_away").val();

					    $.ajax({
							url: "<?=Yii::$app->getUrlManager()->getBaseUrl()?>/products/productproperty/",
							type: "post",
							data: {'selectedCategory':selectedCategory, 'givingAway':giving_away, 'productId': productId},
							dataType: "html",
							success: function (responce) {
								responce = responce.trim();
								propertyData = eval(responce);
								if (propertyData[0] == 0) {
									$('.dynamicProperty').html(propertyData[1]);
									$('.dynamic-section').hide();
								} else {
									$('.dynamicProperty').html(propertyData[1]);
									$('.dynamic-section').show();
								}
							}
						});
					}
				<?php } ?>
			</script> 
			<script type="text/javascript">
				function initMap() {
					document.getElementById('Products_location').onkeyup = function(){
						var local=document.getElementById('Products_location').value;
						if(local.length >=2)
						{
							$local_val=document.getElementById('Products_location');
							var autocomplete = new google.maps.places.Autocomplete(($local_val), {
								types: ['(cities)']
							});
							autocomplete.addListener('place_changed', function() {
								var place = autocomplete.getPlace();
								var latitude = place.geometry.location.lat();
								var longitude = place.geometry.location.lng();
								$('#Productstate').val('');  
								var placeDetails = place.address_components;
								var count = placeDetails.length;
								var country = "";
								var state = "";
								for (var i = count; i >= 1; i--) { 
									if(placeDetails[i-1].types[0] == "country") { 
										country = placeDetails[i-1].short_name;
										$('#shippingcountry').val(country);
										$('#Productscountry').val(country); 
									} else if(placeDetails[i-1].types[0] == "administrative_area_level_1") {
										state = placeDetails[i-1].long_name; 
										$('#Productstate').val(state);   
									}   
								}
								$("#latitude").val(latitude);
								$("#longitude").val(longitude);
							});	
						}else{
							google.maps.event.clearInstanceListeners(document.getElementById('Products_location'));
							$(".pac-container").remove();
						}
					}
				}
				function readURL(input) {
					if (input.files && input.files[0]) {
						var reader = new FileReader();
						reader.onload = function (e) {
							$('#blah').attr('src', e.target.result);
						}
						reader.readAsDataURL(input.files[0]);
					}
				}
				function preview_image() 
				{
					var total_file=document.getElementById("image_file").files.length;
					for(var i=0;i<total_file;i++)
					{
						$('.add_img').append("<div class='column'><img src='"+URL.createObjectURL(event.target.files[i])+"' style='width:100%'></div><br>");
					}
				}
			</script>
			</script>
			<?php
				$siteSettings = yii::$app->Myclass->getSitesettings();
				if(!empty($siteSettings) && isset($siteSettings->googleapikey) && $siteSettings->googleapikey!="")
					$googleapikey = $siteSettings->googleapikey;
				else
					$googleapikey = "";
			?>
			<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $googleapikey; ?>&libraries=places&callback=initMap&language=en"></script>
			<style type="text/css">
				.form-group {
					margin-bottom: 0px;
				}
				label[for=Products_price]
				{
					float: left;
				}
			</style>
			<?php
				if (isset($_SESSION['language']) && $_SESSION['language'] == 'ar') { ?>
					<style type="text/css">
						.cke_toolbox {
							float: right!important;
						}
					</style>
					<?php
				}
			?>
			<script src="https://js.stripe.com/v3/"></script>
			<script src="https://code.jquery.com/jquery-latest.min.js"></script> 
			<script>
				
				$(document).ready(function() {
					$('#subcategoryhide').hide();
					$("#showField").hide();
				});

				$('#customButton').click(function(){
					if(this.disabled = true){
					var stripekey = $('#stripekey').val();//alert(stripekey);
					var x='<?php echo $promoteCurrency[1]; ?>';
					var promotionType = $('#promotiontype').val('urgent');//alert(promotionType);
					//var promotionType = $('#promotiontype').val();alert(promotionType);
					var totalprice = $('#price').val();
					var customField1 = $('#customField1').val();
					$('#customField').val(customField1);
					$('#totalprice').val(totalprice);
					$('#customButtonn').attr('disabled', 'disabled');
					$id = $('.promotion-product-id').val();//alert($id);
					$('#itemids').val($id);

					var stripe = Stripe(stripekey);
					$.ajax({
					url: '<?=Yii::$app->getUrlManager()->getBaseUrl()?>/products/stripesessioncreation/',
					type: "POST",
					dataType : "json",
					data:$('#promotionstripeform').serialize(),
						success: function (res) {
							if(res){
								if(res.session_id){
									return stripe.redirectToCheckout({ sessionId: res.session_id });
								}
							}
						},
					});
					return false;
					}
				});
				$('#customButton1').click(function(){
					var stripekey = $('#stripekeyy').val();
					//var promotionTypes = $('#promotiontypee').val('adds');alert(promotionTypes);
					var promotionTypes = $('#promotiontypee').val();//alert(promotiontypes);
					$id = $('.promotion-product-id').val();//alert($id);
					$('#itemide').val($id);
					var totalpricee = $('#totalpricee').val();
					var  totalprice =  totalpricee * 100;
					var errorSelector = ".adds-promote-error";	//alert(errorSelector);
					var promotionId = $('#promotion-addtype').val();
					$('#customButtonn1').attr('disabled', 'disabled');
					if(promotionId == ""){
						$(errorSelector).html(yii.t('app', 'Select a Promotion'));
						$(errorSelector).show();
						setTimeout(function() {
							$(errorSelector).html('');
							$(errorSelector).hide();
						}, 1500);
						return false;
					}
					else {
						if(this.disabled = true){
							var stripe = Stripe(stripekey);
							$.ajax({
							url: '<?=Yii::$app->getUrlManager()->getBaseUrl()?>/products/stripesessioncreation/',
							type: "POST",
							dataType : "json",
							data:$('#adpromotionstripeform').serialize(),
								success: function (res) {
									if(res){
										if(res.session_id){
											return stripe.redirectToCheckout({ sessionId: res.session_id });
										}
									}
								},
							});
						}
					}
					return false;
				});
			</script>
