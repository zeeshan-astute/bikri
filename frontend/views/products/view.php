<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\UrlManager;
use yii\helpers\ArrayHelper;
use conquer\toastr\ToastrWidget;
use common\models\Filter;
use common\models\Filtervalues;
use bigpaulie\social\share\Share;
use common\components\MyAws;
$sitePaymentModes = yii::$app->Myclass->getSitePaymentModes();
$sitesetting = yii::$app->Myclass->getSitesettings();
$logUserid = Yii::$app->user->id;
$sellerId = $userModel->userId;
$path1 = Yii::$app->urlManagerfrontEnd->baseUrl;
$path = $path1 . '/media' . '/';
?>
<?php if (!empty($photoModel[0]->name)) {
	$viewImageURL = Yii::$app->urlManager->createAbsoluteUrl("/media/item/" . $model->productId . "/" . $photoModel[0]->name);
	$resizedurl = Yii::$app->urlManager->createAbsoluteUrl ('resized.php?src='.$viewImageURL.'&w=200&h=200');
} else {
	$viewImageURL = Yii::$app->urlManager->createAbsoluteUrl("/media/item/" . 'default.jpeg');
}
$desc = html_entity_decode($model->description); ?>
<?= Html::csrfMetaTags() ?>
<?php  if(!empty($photoModel[0]->name)) {
	\Yii::$app->params['fbimg'] = $resizedurl;
} else {
	\Yii::$app->params['fbimg'] = Yii::$app->thumbnailer->get(Yii::$app->urlManager->createAbsoluteUrl("/media/item/".'default.jpeg'), 250,250);
}
\Yii::$app->params['fbdescription'] = $model->description; 
\Yii::$app->params['fbtitle'] = $model->name;?>
<?php
$soldData = '';
if ($model->quantity == 0 || $model->soldItem == 1) {
	$soldData = '<div class="sold-out item-view"><i class="fa fa-dollar"></i>' . Yii::t('app', 'Sold Out') . '</div>';
}
$userDet = yii::$app->Myclass->getUserDetailss($model->userId);
if (!empty($photoModel[0]->name)) {
	$viewImageURL = Yii::$app->urlManager->createAbsoluteUrl("/media/item/" . $model->productId . "/" . $photoModel[0]->name);
	$mediapath = realpath ( Yii::$app->basePath . "/web/media/item/".$model->productId . "/" . $photoModel[0]->name);
	if(file_exists($mediapath)) {
		$viewImageURL = $viewImageURL; // $awsimage
	} else {
		$viewImageURL =Yii::$app->urlManager->createAbsoluteUrl("/media/item/".$sitesetting->default_productimage);
	}
} else {
	$viewImageURL = Yii::$app->urlManager->createAbsoluteUrl("/media/item/" . $sitesetting->default_productimage);
} ?>
<?php  
$fbShareLink = "https://www.facebook.com/sharer.php?u=".Yii::$app->urlManager->createAbsoluteUrl("/products/view/" .yii::$app->Myclass->safe_b64encode($model->productId . '-' . rand(0, 999)). '/' . yii::$app->Myclass->productSlug($model->name)); 
$twitShareLink = "https://twitter.com/share/?status=".Yii::$app->urlManager->createAbsoluteUrl("/products/view/" .yii::$app->Myclass->safe_b64encode($model->productId . '-' . rand(0, 999)). '/' . yii::$app->Myclass->productSlug($model->name)); 
?>
<?php
$productcategoryname = yii::$app->Myclass->getProductCategory($model->category);
$subcategoryname =yii::$app->Myclass->getProductCategory($model->subCategory);
$subsubtcategoryname =yii::$app->Myclass->getProductCategory($model->sub_subCategory);
$slugproductcategoryname = yii::$app->Myclass->getProductCategoryslug($model->category);
$slugsubcategoryname =yii::$app->Myclass->getProductCategoryslug($model->subCategory);
$slugsubsubtcategoryname =yii::$app->Myclass->getProductCategoryslug($model->sub_subCategory);
?>

<div class="container">
	<div class="row">
		<div class="classified-breadcrumb add-product col-xs-12 col-sm-12 col-md-12 col-lg-12 ">
			<ol class="breadcrumb">
				<li>
					<a href="<?php echo Yii::$app->getUrlManager()->getBaseUrl().'/'; ?>"><?php echo Yii::t('app','Home');?></a>
				</li>
				<?php if(!empty($model->category)) {  ?>
					<li>
						<a href="<?php echo Yii::$app->getUrlManager()->getBaseUrl().'/category/'.$slugproductcategoryname; ?>"><?php echo Yii::t('app',$productcategoryname);?></a>
					</li>
					<?php if (!empty($model->subCategory)) {  
						$categoryname = yii::$app->Myclass->getCategoryBreadcrumName($slugsubcategoryname);
						$subcategoryUrll= Yii::$app->urlManager->createAbsoluteUrl('/category/'.$slugproductcategoryname.'/'.$slugsubcategoryname);
						?>
						<li>
							<a href="<?php echo $subcategoryUrll; ?>"><?php echo Yii::t('app',$categoryname); ?></a>
						</li>

						<?php if (!empty($model->sub_subCategory))  {
							$categoryname = yii::$app->Myclass->getCategoryBreadcrumName($slugsubcategoryname);
							$sub_subcategoryUrll= Yii::$app->urlManager->createAbsoluteUrl('/category/'.$slugproductcategoryname.'/'.$slugsubcategoryname.'/'.$slugsubsubtcategoryname);?>
							<li>
								<a href="<?php echo $sub_subcategoryUrll; ?>"><?php echo Yii::t('app',$subsubtcategoryname); ?></a>
							</li>
						<?php } 

					} 
				} else {  ?>
					<li><a href="<?php echo $categoryUrl; ?>"><?php echo Yii::t('app','All Categories')?></a></li>
				<?php } ?>
			</ol>
		</div>
	</div>
	<div class="row">
		<div class="product-left-container col-xs-12 col-sm-12 col-md-8 col-lg-8">
			<?php  $productID = $model->productId; ?>
			<?php if (Yii::$app->session->hasFlash('success')) : ?>
				<?= ToastrWidget::widget([
					'type' => 'success', 'message' => Yii::$app->session->getFlash('success'),
					"closeButton" => true,
					"debug" => false,
					"newestOnTop" => false,
					"progressBar" => false,
					"positionClass" => ToastrWidget::POSITION_TOP_RIGHT,
					"preventDuplicates" => false,
					"onclick" => null,
					"showDuration" => "300",
					"hideDuration" => "1000",
					"timeOut" => "5000",
					"extendedTimeOut" => "1000",
					"showEasing" => "swing",
					"hideEasing" => "linear",
					"showMethod" => "fadeIn",
					"hideMethod" => "fadeOut"
				]); ?>
			<?php endif; ?>
			<?php if (Yii::$app->session->hasFlash('error')) : ?>
				<?= ToastrWidget::widget([
					'type' => 'error', 'message' => Yii::$app->session->getFlash('error'),
					"closeButton" => true,
					"debug" => false,
					"newestOnTop" => false,
					"progressBar" => false,
					"positionClass" => ToastrWidget::POSITION_TOP_RIGHT,
					"preventDuplicates" => false,
					"onclick" => null,
					"showDuration" => "300",
					"hideDuration" => "1000",
					"timeOut" => "5000",
					"extendedTimeOut" => "1000",
					"showEasing" => "swing",
					"hideEasing" => "linear",
					"showMethod" => "fadeIn",
					"hideMethod" => "fadeOut"
				]); ?>
			<?php endif; ?>
			<?php if (Yii::$app->session->hasFlash('info')) : ?>
				<?= ToastrWidget::widget([
					'type' => 'info', 'message' => Yii::$app->session->getFlash('info'),
					"closeButton" => true,
					"debug" => false,
					"newestOnTop" => false,
					"progressBar" => false,
					"positionClass" => ToastrWidget::POSITION_TOP_RIGHT,
					"preventDuplicates" => false,
					"onclick" => null,
					"showDuration" => "300",
					"hideDuration" => "1000",
					"timeOut" => "5000",
					"extendedTimeOut" => "1000",
					"showEasing" => "swing",
					"hideEasing" => "linear",
					"showMethod" => "fadeIn",
					"hideMethod" => "fadeOut"
				]); ?>
			<?php endif; ?>
			<!----Mobile slider---->
			<div id="carousel-example-generic" class="mobile-product-slide carousel slide" data-ride="carousel" data-interval="false">
				<!-- Wrapper for slides -->
				<div class="carousel-inner" role="listbox">
					<div class="product-image item active" id="prod-img-1" data-thumb="0" style="background: rgba(0, 0, 0, 0) url('<?php echo $viewImageURL; ?>') no-repeat scroll center center / cover;  background-size: auto 100% !important;"></div>
					<?php $slide_photoModel = $photoModel;
					$mob_photoModel = array_splice($slide_photoModel, 1);
					foreach ($mob_photoModel as $phts) {
						$URL = $model->productId . "/" . $phts->name;
						$moreProductURL = Yii::$app->urlManager->createAbsoluteUrl("/media/item/" . $URL);
						?>
						<div class="product-image item" id="prod-img-1" data-thumb="0" style="background: rgba(0, 0, 0, 0) url('<?php echo $moreProductURL; ?>') no-repeat scroll center center / cover ;  background-size: auto 100% !important;"></div>
						<?php 
					} ?>
				</div>
				<?php if(!empty($model->videoUrl)) {
					?>
					<div class="play-btn">
						<a href="#" data-toggle="modal" data-target="#prodModal" class="ply-video">
							<span>Play Video</span>
						</a>
					</div>
				<?php } ?>
				<!-- Controls -->
				<?php if (count($photoModel) > 1) { ?>
					<a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
						<span class="thmb-slider-arrow-left"></span>
						<span class="sr-only"><?php echo Yii::t('app', 'Previous'); ?></span>
					</a>
					<a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
						<span class="thmb-slider-arrow-right"></span>
						<span class="sr-only"><?php echo Yii::t('app', 'Next'); ?></span>
					</a>
					<?php 
				} ?>
			</div>
			<!----E O mobile slider---->
			<div id="carousel" class="non-mobile-product-slide carousel slide" data-ride="carousel" data-interval="false">
				<div class="carousel-inner product-img">
					<div class="product-image item active" id="prod-img-1" data-thumb="0" style="background: rgba(0, 0, 0, 0) url('<?php echo $viewImageURL; ?>') no-repeat scroll center center;  background-size: auto 100% !important; margin-top:10px; margin-bottom:10px;"></div>
					<?php foreach ($photoModel as $phts) {
						$URL = $model->productId . "/" . $phts->name;
						$moreProductURL = Yii::$app->urlManager->createAbsoluteUrl("/media/item/" . $URL);
						$mediapath = realpath ( Yii::$app->basePath . "/web/media/item/".$URL);
						if(file_exists($mediapath)) {
							$moreProductURL = $moreProductURL;
						} else {
							$moreProductURL = Yii::$app->urlManager->createAbsoluteUrl("/media/item/" . $sitesetting->default_productimage); 
						}
						?>
						<div class="product-image item" id="prod-img-1" data-thumb="0" style="background: rgba(0, 0, 0, 0) url('<?php echo $moreProductURL; ?>') no-repeat scroll center center;  background-size: auto 100% !important; margin-top:10px;"></div>
						<?php 
					} ?>
				</div>
				<?php if(!empty($model->videoUrl)) {
					?>
					<div class="play-btn">
						<a href="#" data-toggle="modal" data-target="#prodModal" class="ply-video">
							<span>Play Video</span>
						</a>
					</div>
				<?php } ?>
			</div>
			<?php if (count($photoModel) > 1) { ?>
				<div class="product-thmb-slider clearfix">
					<div id="thumbcarousel" class="carousel slide" data-interval="false">
						<div class="slider-fix-left"></div>
						<div class="slider-fix-right"></div>
						<div class="carousel-inner">
							<div class="item active">
								<?php foreach ($photoModel as $key => $phts) {
									$key = $key + 1;
									$URL = $phts->productId . "/" . $phts->name;
									$moreProductURL = Yii::$app->thumbnailer->get(Yii::$app->urlManager->createAbsoluteUrl("/media/item/" . $URL), 125,125);
									$mediapath = realpath ( Yii::$app->basePath . "/web/media/item/".$URL);
									if(file_exists($mediapath)) {
										$moreProductURL = $moreProductURL;
									} else {
										$moreProductURL = Yii::$app->thumbnailer->get(Yii::$app->urlManager->createAbsoluteUrl("/media/item/" . $sitesetting->default_productimage), 125,125);
									}
									?>
									<div data-target="#carousel" data-slide-to="<?php echo $key; ?>" class="col-sm-2 col-lg-2 thumb-cnt thumb ">
										<div class="product-image" id="prod-thmb-img-1" style="background: rgba(0, 0, 0, 0) url('<?php echo $moreProductURL; ?>') no-repeat scroll center center / cover ;"></div>
									</div>
									<?php 
								} ?>
							</div><!-- /item -->
							<?php if (count($photoModel) > 6) { 
								$totalmodel = (ceil(count($photoModel) / 6)) - 1;
								$i = 0;
								for ($a = 0; $a < $totalmodel; $a++) {
									?>
									<div class="item">
										<?php
										$next_slide_photoModel = $photoModel;
										$sec_photoModel = array_slice($next_slide_photoModel, $i, 6, true);
										$itmcount = $i + 5;
										for ($j = $i; $j <= $itmcount; $j++) {
											$URL = $sec_photoModel[$j]->productId . '/' . $sec_photoModel[$j]->name;
											$moreProductURL = Yii::$app->thumbnailer->get(Yii::$app->urlManager->createAbsoluteUrl("/media/item/" . $URL), 125,125);
											$mediapath = realpath ( Yii::$app->basePath . "/web/media/item/".$URL);
											if(file_exists($mediapath)) {
												$moreProductURL = $moreProductURL;
											} else {
												$moreProductURL = Yii::$app->thumbnailer->get(Yii::$app->urlManager->createAbsoluteUrl("/media/item/" . $sitesetting->default_productimage), 125,125);
											}
											$k = $j + 1;
											?>
											<div data-target="#carousel" data-slide-to="<?php echo $k; ?>" class="col-sm-2 col-lg-2 thumb-cnt thumb "><div class="product-image" id="prod-thmb-img-1" style="background: rgba(0, 0, 0, 0) url('<?php echo $moreProductURL; ?>') no-repeat scroll center center / cover ;"></div></div>
											<?php
										}
										$i = $i + 6;
										?>
									</div><!-- /item -->
									<?php 
								} ?>
								<?php 
							} ?>
						</div><!-- /carousel-inner -->
						<?php if (count($photoModel) > 6) { ?>
							<a class="thmb-slider-bg-left left carousel-control" href="#thumbcarousel" role="button" data-slide="prev">
								<span class="thmb-slider-arrow-left"></span>
							</a>
							<a class="thmb-slider-bg-right right carousel-control" href="#thumbcarousel" role="button" data-slide="next">
								<span class="thmb-slider-arrow-right"></span>
							</a>
							<?php 
						} ?>
					</div> <!-- /thumbcarousel -->
				</div><!-- /clearfix -->
				<?php 
			} ?>
			<div class="mobile-product-details col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
				<div class="product-page-right-top-container col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
						<div class="product-details col-xs-10 col-sm-10 col-md-10 col-lg-10 no-hor-padding">
							<div class="product-name col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><?php echo $model->name; ?></div>
							<div class="product-price col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
								<?php
								if ($model->price == 0 && $model->instantBuy == 0) {
									echo '<span class="label_ga">' . Yii::t('app', 'Giving Away') . '</span>';
								} else {
									if (isset($_SESSION['language']) && $_SESSION['language'] == 'ar')
										echo yii::$app->Myclass->getArabicFormattingCurrency($model->currency,$model->price); 
									else{
										echo yii::$app->Myclass->getFormattingCurrency($model->currency,$model->price); 
									}
								}
								?>
							</div>
						</div>
						<?php if($logUserid != $sellerId){?>
							<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2 no-hor-padding">
								<div id="mobile_favs">
									<?php
									if (empty($fav))
										echo Html::a('<div class="product-like col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding pull-right"></div>', 'javascript:void(0)', array('onclick' => 'mobile_like(' . $model->productId . ')'));
									else
										echo Html::a('<div class="product-like col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding pull-right" id="liked"></div>', 'javascript:void(0)', array('onclick' => 'mobile_dislike(' . $model->productId . ')'));
									?>
								</div>
								<div class="like-counter col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><span id="mobile_like_counter"><?php echo $model->likes . '</span> ' . Yii::t('app', 'likes'); ?></div>
								</div><?php } ?>
							</div>
							<div class="used-status-row col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
								<?php if (!empty($model->productCondition)) { 
									$conditionName = yii::$app->Myclass->getproductConditionName($model->productCondition);
									?>
									<div class="used-status"><?php echo Yii::t('app',$conditionName); ?></div>
									<?php 
								} ?>
								<div class="days-reviews-counter"><?php echo yii::$app->Myclass->getElapsedTime($model->createdDate) . ' ' . Yii::t('app', 'ago'); ?> <span>|</span> <span><?php echo $model->views; ?><?php echo Yii::t('app', ' views'); ?></span></div>
							</div>
							<?php if ($model->instantBuy != '0' && $sitePaymentModes['buynowPaymentMode'] == 1) { ?>
								<div class="shipping-cost-cnt col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
									<div class="shipping-icon"></div>
									<div class="shipping-cost product-location">
										<?php if ($model->shippingCost != 0) {
											if (isset($_SESSION['language']) && $_SESSION['language'] == 'ar')
												echo Yii::t('app', 'Shipping Cost') . " : " .yii::$app->Myclass->getArabicFormattingCurrency($model->currency,$model->shippingCost); 
											else
												echo Yii::t('app', 'Shipping Cost') . " : " .yii::$app->Myclass->getFormattingCurrency($model->currency,$model->shippingCost); 
										} else {
											echo Yii::t('app', 'Shipping Cost') . " : " . Yii::t('app', 'Free&nbsp;Shipping');
										} ?>
									</div>
								</div>
								<?php 
							} ?>
							<div class="social-buttons-link col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
								<span>
									<a href="#" onclick="return sharePopup('<?= $fbShareLink; ?>');"> 
										<img src="<?php echo Yii::$app->urlManager->createAbsoluteUrl('images/fb.jpg'); ?>" alt="">
									</a>
								</span> 
								<span>
									<a href="#" onclick="return sharePopup('<?= $twitShareLink; ?>');">   
										<img src="<?php echo Yii::$app->urlManager->createAbsoluteUrl('images/twitt.jpg'); ?>" alt="">
									</a>
								</span>  
							</div>
							<div class="product-location-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
								<div class="location-grey"></div>
								<div class="product-location"><?php echo $model->location; ?></div>
							</div>
							<input type="hidden" value="<?php echo $model->userId; ?>"	class="product-user-id" /> 
							<input type="hidden" value="<?php echo $model->location; ?>" class="product-location-name" />
							<input type="hidden" value="<?php echo $model->latitude; ?>" class="product-location-lat" /> 
							<input type="hidden" value="<?php echo $model->longitude; ?>" class="product-location-long" /> 
							<input type="hidden" class="price-option-hidden" value="<?php echo yii::$app->Myclass->getCurrency($model->currency); ?>" /> 
							<input type="hidden" class="product-price-hidden" value="<?php echo $model->price; ?>" />
							<div class="product-address-map col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
								<div id="mobile_map_canvas" class="map-location" style="width: 600; height: 450"></div>
							</div>
							<?php
							$logUserid = Yii::$app->user->id;
							$sellerId = $userModel->userId;
							?>
							<?php if ($logUserid != $sellerId) {
								if (empty($logUserid)) { ?>
									<div class="belowBtns" >
										<div  style="flex:1"><a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('site/login/'); ?>"><div class="chat-seller-btn btn-chat-with-seller primary-bg-color col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding" style="margin-top:0;padding-top: 10px;padding-bottom: 10px;border-radius:0">
											<div class="chat-with-seller-text col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><img src="<?php echo Yii::$app->urlManager->createAbsoluteUrl('images/chat-with-seller-icon.png'); ?>" alt="chat-icon"><span><?php echo Yii::t('app', 'Chat with seller'); ?></span></div>
										</div></a></div>
										<?php if ( ($model->myoffer == '0') && ($model->instantBuy != '1' && $sitePaymentModes['buynowPaymentMode'] != 1)) { ?>
											<div style="flex:1;margin-top:0">
												<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('site/login/'); ?>"><div class="make-offer-btn btn-make-an-offer col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding" style="margin-top:0;padding-top: 10px;padding-bottom: 10px;border-radius:0">
													<?php echo Yii::t('app', 'Make an offer'); ?>
												</div></a>
											</div>
										<?php } ?>
										<?php if ($model->instantBuy == '1' && $sitePaymentModes['buynowPaymentMode'] == 1) { ?>
											<div style="flex:1;margin-top:0">
												<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('site/login/'); ?>"><div class="buy-now-btn btn-make-an-offer col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding" style="margin-top:0;padding-top: 10px;padding-bottom: 10px;border-radius:0">
													<?php echo Yii::t('app', 'Buy now'); ?>
												</div></a>
											</div>
											<?php 
										} 
										?>
									</div>
									<?php if ($model->exchangeToBuy == '1' && $sitePaymentModes['exchangePaymentMode'] == 1 && $model->soldItem == 0) { ?>
										<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('site/login/'); ?>" ><div class="exchange-buy-btn btn-make-an-offer col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
											<?php echo Yii::t('app', 'Exchange to buy'); ?>
										</div></a>
										<?php 
									} ?>
									<?php if ( ($model->myoffer == '0') && ($model->instantBuy == '1' && $sitePaymentModes['buynowPaymentMode'] == 1)) { ?>
										<div style="flex:1;margin-top:0">
											<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('site/login/'); ?>"><div class="make-offer-btn btn-make-an-offer col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding" style="margin-top:0;padding-top: 10px;padding-bottom: 10px;border-radius:0">
												<?php echo Yii::t('app', 'Make an offer'); ?>
											</div></a>
										</div>
									<?php } ?>
									<?php 
								} else { ?>
									<div class="belowBtns">
										<div  style="flex:1">
											<a href="" data-dismiss="modal" data-toggle="modal" data-target="#chat-with-seller-modal">
												<div class="chat-seller-btn btn-chat-with-seller primary-bg-color col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding" style="margin-top:0;padding-top: 15px;padding-bottom: 16px;border-radius:0">
													<div class="chat-with-seller-text col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><img src="<?php echo Yii::$app->urlManager->createAbsoluteUrl('images/chat-with-seller-icon.png'); ?>" alt="chat-icon"><span><?php echo Yii::t('app', 'Chat with seller'); ?></span> </div>
												</div></a>
											</div> 
											<?php if ($model->instantBuy == '1' && $sitePaymentModes['buynowPaymentMode'] == 1) { ?>
												<div style="flex:1;margin-top:0">
													<?php $cartDataURL = yii::$app->Myclass->cart_encrypt($model->productId . "-0", 'joy*ccart'); ?>
													<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('revieworder/' . $cartDataURL); ?>"><div class="buy-now-btn btn-make-an-offer col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding" style="margin-top:0;padding-top: 15px;padding-bottom: 15px;border-radius:0">
														<?php echo Yii::t('app', 'Buy now'); ?>
													</div></a>
												</div>
												<?php 
											} 
											?>
										</div>
										<?php if ($model->myoffer == '0') { ?>
											<a href="" data-dismiss="modal" data-toggle="modal" data-target="#offer-modal"><div class="make-offer-btn  btn-make-an-offer col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
												<?php echo Yii::t('app', 'Make an offer'); ?>
											</div></a>
											<?php 
										} ?>
										<?php if ($model->exchangeToBuy == 1 && $sitePaymentModes['exchangePaymentMode'] == 1) {
											?>
											<a href="" data-dismiss="modal" data-toggle="modal" data-target="#exchange-modal"><div class="exchange-buy-btn btn-make-an-offer col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
												<?php echo Yii::t('app', 'Exchange to buy'); ?>
											</div></a>
											<?php 
										} ?>
										<?php 
									} ?>
									<?php 
								} else { 
									$lin =yii::$app->Myclass->safe_b64encode($model->productId . '-' . rand(100, 999));
									?>
									<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('products/update') . '/'.$lin; ?>">
										<div class="btn-chat-with-seller primary-bg-color col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
											<div class="chat-with-seller-text col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
												<?php echo Yii::t('app', 'Edit Item'); ?>
											</div>
										</div>
									</a>
									<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('products/insights?id=').$lin; ?>" >
										<div class="in-sights-btn  btn-make-an-offer col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
											<?php echo Yii::t('app', 'Insights'); ?>
										</div>
									</a>
									<?php 
								} ?>
							</div>
						</div>
						<!-- adsense start1 -->
						<?php if($sitesetting->google_ads_product == 1) 
						{?>
						<div style="display: none;" class="tab-section col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">		
						<div class="adscontents">		
						<script type="text/javascript" src="//pagead2.googlesyndication.com/pagead/show_ads.js">
						</script>
						<script type="text/javascript">
						google_ad_client = "<?php echo $sitesetting->google_ad_client_product; ?>";
						google_ad_slot = "<?php echo $sitesetting->google_ad_slot_product; ?>";
						google_ad_width = 780;
						google_ad_height = 90;
						</script>
						</div>
						</div>
						<div style="margin-top: 10px;" class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">		
						<div style="text-align:center;" class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding adscontents">		
						<script type="text/javascript">
						var width = window.innerWidth || document.documentElement.clientWidth;
						google_ad_client = "<?php echo $sitesetting->google_ad_client_product; ?>";
						if (width > 800) {
						google_ad_slot = "<?php echo $sitesetting->google_ad_slot_product; ?>";
						google_ad_width = 728;
						google_ad_height = 90;
						}
						else if ((width <= 800) && (width > 400)) { 
						google_ad_slot = "<?php echo $sitesetting->google_ad_slot_product; ?>";
						google_ad_width = 768; 
						google_ad_height = 90;
						}
						else
						{
						google_ad_slot = "<?php echo $sitesetting->google_ad_slot_product; ?>";
						google_ad_width = 250; 
						google_ad_height = 90;
						}
						</script>
						<script type="text/javascript" src="//pagead2.googlesyndication.com/pagead/show_ads.js">
						</script>
						</div>
						</div>
						<?php } ?>
						<!-- adsense1 end -->

						<!--tab section-->
						<div class="tab-section col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
							<ul class="tab-section-tabs nav nav-tabs">
								<li class="active"><a data-toggle="tab" href="#desc"><?php echo Yii::t('app', 'Description'); ?></a></li>
								<?php if(count($filterValues)!=0){ ?>
									<li><a data-toggle="tab" href="#details"><?php echo Yii::t('app', 'Details'); ?></a></li><?php } ?>
									<li><a data-toggle="tab" href="#comment"><?php echo Yii::t('app', 'Comments'); ?></a></li>
									<?php if (Yii::$app->user->id != $model->userId) { ?>
										<li class="pull-right">
											<?php
											$reportedUsers = json_decode($model->reports, true);
											if (in_array(Yii::$app->user->id, $reportedUsers)) { ?>
												<a id = "unreportflag" data-dismiss="modal" data-toggle="modal" data-target="#undoreportconfirm_popup_container" onclick = "reportflag()" class="exclamatory-cnt item-report" href="javascript:;"><span class="report-active exclamatory-icon-active"></span><span class="report-text"><?php echo Yii::t('app', 'Undo reporting'); ?></span></a>
						<?php //}
					} else { ?>
						<a id = "reportflag"  data-dismiss="modal" data-toggle="modal" data-target="#reportconfirm_popup_container" class="exclamatory-cnt item-report" href="javascript:;"><span class="report-active exclamatory-icon"></span><span class="report-text"><?php echo Yii::t('app', 'Report inappropriate'); ?></span></a>
						<?php 
					} ?>
				</li>
				<?php 
			} ?>
		</ul>
		<div class="tab-section-content tab-content" style="word-break:break-word;">
			<div id="desc" class="tab-pane active">
				<div class="comment-text-cnt more" id="prod_description">
					<?php echo $desc; ?>
				</div>
			</div>
			<div id="details" class="tab-pane">
				<div>
					<?php
					foreach($filterValues as $fkey=>$fval){
						$getFilter = Filter::find()->where(['id'=>$fval->filter_id])->one();
						$filterName = (isset($getFilter->name)) ? $getFilter->name : '';
						if($getFilter->type == 'multilevel')
						{
							$getparentFilterval = Filtervalues::find()->where(['id'=>$fval->level_two])->one();
							$getFilterval = Filtervalues::find()->where(['id'=>$fval->level_three])->one();
							$getparentname = (isset($getparentFilterval->name)) ? $getparentFilterval->name : '';
							$getchildvalname = (isset($getFilterval->name)) ? $getFilterval->name : '';

							if($getFilter->name && $getparentname && $getchildvalname){
								echo '<div><span style="font-weight:bold;">'.Yii::t('app',$getFilter->name).'</span>: '.Yii::t('app',$getparentname).'</span> ('.Yii::t('app',$getchildvalname).')</div>';
							}
							
						}
						else if ($getFilter->type == 'dropdown') {
							$getparentFilterval = Filtervalues::find()->where(['id'=>$fval->level_two])->one();
							$getparentname = (isset($getparentFilterval->name)) ? $getparentFilterval->name : '';
							if($getparentname != ''){
								echo '<div><span style="font-weight:bold;">'.Yii::t('app',$filterName).'</span>: '.Yii::t('app',$getparentname).'</div>';
							}
						}
						else{
							echo '<div><span style="font-weight:bold;">'.Yii::t('app',$filterName).'</span>: '.Yii::t('app',$fval->filter_values).'</div>';
						}
					}
					?>
				</div>
			</div>
			<div id="comment" class="tab-pane col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
				<div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
					<textarea class="comment-text-area form-control commenter-text" rows="5" id="comment" onkeyup="limitText(120);" placeholder="<?php echo Yii::t('app', 'Type your message') ?>" maxlength="120"></textarea>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
					<div class="character-left"><p> <?php echo Yii::t('app', 'You have'); ?> <span id="countdown">120</span> <?php echo Yii::t('app', 'character left.'); ?></p></div>
					<div class="post-comment col-xs-12 pull-right"><a class="upload-btn" id="post-comment" href="javascript: void(0)" onClick="postcomment()" style="color:#fff;"><?= Yii::t('app', 'Post Comment') ?></a></div>
					<input type="hidden" name="logindetails" class="logindetails" value="<?php echo $user; ?>" />
					<?php $commentCount = count($commentModel); ?>
					<input type="hidden" id="commentCount" value="<?php echo $commentCount; ?>">
				</div>
				<div class="comment-error" style="float:right;color: red;font-size: 12px; "></div>
				<div class="comment-section col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
					<?php if (!empty($commentModel)) {
						$min_commentModel = array_slice($commentModel, 0, 3);
						foreach ($min_commentModel as $comment) {
							$userDetails = yii::$app->Myclass->getUserDetailss($comment->userId); ?>
							<div class="comment col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding" id="cmt-<?php echo $comment->commentId; ?>">
								<?php
								if (!empty($userDetails->userImage)) {
									$user_profile = Yii::$app->urlManager->createAbsoluteUrl('profile/' . $userDetails->userImage);
								} else {
									$user_profile = Yii::$app->urlManager->createAbsoluteUrl('media/logo/' . yii::$app->Myclass->getDefaultUser());
								}
								?>
								<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl(['user/profiles', 'id' => yii::$app->Myclass->safe_b64encode($userDetails->userId . '-' . rand(100, 999))]); ?>"><div class="comment-profile-default icon col-xs-2 col-sm-2 col-md-1 col-lg-1 no-hor-padding" style="background: rgba(0, 0, 0, 0) url('<?php echo $user_profile; ?>') no-repeat scroll center center / cover; border-radius:20px; "></div></a>
								<div class="comment-content icon col-xs-10 col-sm-10 col-md-11 col-lg-11 no-hor-padding">
									<div class="comment-user-name col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl(['user/profiles', 'id' => yii::$app->Myclass->safe_b64encode($userDetails->userId . '-' . rand(100, 999))]); ?>"><?php echo $userDetails->name; ?></a>
										<?php
										if (isset($logUserid) && $logUserid == $userDetails->userId) {
											?>
											<a class="pull-right" href="javascript:void(0);" onclick="deletecomment(<?php echo $comment->commentId; ?>,<?php echo $model->productId; ?>);"><?php echo Yii::t('app', 'Delete'); ?></a>
											<?php
										}
										?>
									</div>
									<div class="comment-text col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
										<p><?php echo $comment->comment; ?></p>
										<div class="comment-timing-detail col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
											<p><?php echo yii::$app->Myclass->getElapsedTime($comment->createdDate) . '' . Yii::t('app', 'ago'); ?></p>
										</div>
									</div>
								</div>
							</div>
							<?php 
						}
					} else { ?>
						<div class="empty-comment">
							<?php echo Yii::t('app', 'Be the first one to comment on this product.') ?>
						</div>
						<?php 
					} ?>
					<?php if (count($commentModel) > 3) { ?>
						<?php
						$max_commentModel = array_slice($commentModel, 3);
						foreach ($max_commentModel as $comment) {
							$userDetails = yii::$app->Myclass->getUserDetailss($comment->userId); ?>
							<div class="comment view_more_comnts col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding" id="cmt-<?php echo $comment->commentId; ?>" style="display:none;">
								<?php
								if (!empty($userDetails->userImage)) {
									$user_profile = Yii::$app->thumbnailer->get(Yii::$app->urlManager->createAbsoluteUrl("/media/user/" . $userDetails->userImage), 40,40);
								} else {
									$user_profile = Yii::$app->thumbnailer->get(Yii::$app->urlManager->createAbsoluteUrl("/media/user/" . yii::$app->Myclass->getDefaultUser()), 40,40);
								}
								?>
								<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl(['user/profiles', 'id' => yii::$app->Myclass->safe_b64encode($userDetails->userId . '-' . rand(100, 999))]); ?>"><div class="comment-profile-default icon col-xs-2 col-sm-2 col-md-1 col-lg-1 no-hor-padding" style="background: rgba(0, 0, 0, 0) url('<?php echo $user_profile; ?>') no-repeat scroll center center / cover; border-radius:20px; "></div></a>
								<div class="comment-content icon col-xs-10 col-sm-10 col-md-11 col-lg-11 no-hor-padding">
									<div class="comment-user-name col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl(['user/profiles', 'id' => yii::$app->Myclass->safe_b64encode($userDetails->userId . '-' . rand(100, 999))]); ?>"><?php echo $userDetails->name; ?></a>
										<?php
										if (isset($logUserid) && $logUserid == $userDetails->userId) {
											?>
											<a class="pull-right" href="javascript:void(0);" onclick="deletecomment(<?php echo $comment->commentId; ?>,<?php echo $model->productId; ?>);"><?php echo Yii::t('app', 'Delete'); ?></a>
											<?php
										}
										?>
									</div>
									<div class="comment-text col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
										<p><?php echo $comment->comment; ?></p>
										<div class="comment-timing-detail col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
											<p><?php echo yii::$app->Myclass->getElapsedTime($comment->createdDate) . ' ' . Yii::t('app', 'ago'); ?></p>
										</div>
									</div>
								</div>
							</div>
							<?php 
						} ?>
						<?php 
					} ?>
				</div>
				<?php if (count($commentModel) > 3) { ?>
					<div class="view-all-comment view-more-comnt col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><a href="javascript:;" onClick="showmorecomment()"><?php echo Yii::t('app', 'View all comments'); ?></a></div>
					<div class="view-all-comment hide-more-comnt col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding" style="display:none;"><a href="javascript:;" onClick="hidemorecomment()"><?php echo Yii::t('app', 'Hide the comments'); ?></a></div>
					<?php 
				} ?>
			</div>
		</div>
		<?php if (Yii::$app->user->id != $model->userId) {
			$reportedUsers = json_decode($model->reports, true); ?>
			<div class="mobile-report-div col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
				<?php
				if (in_array(Yii::$app->user->id, $reportedUsers)) { ?>
					<a href="javascript:;" data-toggle="modal" data-target="#mobundoreportconfirm_popup_container" id = "mob_unreportflag" onclick = "reportflag()" class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding mob-item-report">
						<span class="col-xs-4 col-sm-4 no-hor-padding"><span class="report-mob-active exclamatory-icon-active pull-right"></span></span>
						<span class="mob-report-text col-xs-8 col-sm-8 no-hor-padding"><?php echo Yii::t('app', 'Undo reporting'); ?></span>
					</a>
					<?php //} 
				} else { ?>
					<a data-toggle="modal" data-target="#mobreportconfirm_popup_container" href="javascript:;" id = "mob_reportflag" class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding mob-item-report">
						<span class="col-xs-4 col-sm-4 no-hor-padding"><span class="report-mob-active exclamatory-icon pull-right"></span></span>
						<span class="mob-report-text col-xs-8 col-sm-8 no-hor-padding"><?php echo Yii::t('app', 'Report inappropriate'); ?></span>
					</a>
					<?php 
				} ?>
			</div>
			<?php 
		} ?>
	</div>
</div>
<div class="product-right-container col-xs-12 col-sm-12 col-md-4 col-lg-4 pad_bottom">
	<div class="product-page-right-top-container col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
			<div class="product-details col-xs-10 col-sm-10 col-md-10 col-lg-10 no-hor-padding">
				<div class="product-name col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><?php echo $model->name; ?></div>
				<div class="product-price col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
					<?php
					if ($model->price == 0 && $model->instantBuy == 0) {
						echo '<span class="label_ga">' . Yii::t('app', 'Giving Away') . '</span>';
					} else {
						if (isset($_SESSION['language']) && $_SESSION['language'] == 'ar')
							echo yii::$app->Myclass->getArabicFormattingCurrency($model->currency,$model->price); 
						else{
							echo yii::$app->Myclass->getFormattingCurrency($model->currency,$model->price); 
						}
					}
					?>
				</div>
			</div>
			<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2 no-hor-padding">
				<div id="favs">
					<?php
					if (Yii::$app->user->id != "") {
						if($logUserid != $sellerId){
							if (empty($fav))
								echo Html::a('<div class="product-like col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding pull-right"></div>', 'javascript:void(0)', ['onclick' => 'like(' . $model->productId . ')']);
							else
								echo Html::a('<div class="product-like col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding pull-right" id="liked"></div>', 'javascript:void(0)', array('onclick' => 'dislike(' . $model->productId . ')'));
						}else{
							echo '';
						}
					} else {
						$a = Yii::$app->urlManager->createAbsoluteUrl('site/login/');
						echo '<a href="' . $a . '"><div class="product-like col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding pull-right"></div></a>';
					}
					?>
				</div>
				<?php
				if (Yii::$app->user->id != "") {
					if($logUserid != $sellerId){
						?>
						<div class="like-counter col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><span id="like_counter"><?php echo $model->likes . '</span> ' . Yii::t('app', 'likes'); ?></div>
					<?php } }?>
					<?php  ?>
				</div>
			</div>
			<div class="	 col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
				<?php if (!empty($model->productCondition)) { 
					$conditionName = yii::$app->Myclass->getproductConditionName($model->productCondition);
					?>
					<div class="used-status"><?php echo Yii::t('app', $conditionName); ?></div>
					<?php 
				} ?>
				<div class="days-reviews-counter"><?php 
				if (Yii::$app->language == 'ar'){
					echo Yii::t('app', 'ago') . ' ' . yii::$app->Myclass->getElapsedTime($model->createdDate);}
					else
						echo yii::$app->Myclass->getElapsedTime($model->createdDate) . ' ' . Yii::t('app', 'ago');
					?> <span>|</span> <span><?php echo $model->views; ?> <?php echo Yii::t('app', 'views'); ?></span>
					<?php
					if (Yii::$app->user->id != "") {
						if($logUserid == $sellerId){

							?>
							<span>|</span> <span><?php echo $model->likes . '</span> ' . Yii::t('app', 'likes'); ?></span>
						<?php } }?>

					</div>
				</div>
				<?php if ($model->instantBuy != '0' && $sitePaymentModes['buynowPaymentMode'] == 1) { ?>
					<div class="shipping-cost-cnt col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
						<div class="shipping-icon"></div>
						<div class="shipping-cost product-location">
							<?php if ($model->shippingCost != 0) {
								if (isset($_SESSION['language']) && $_SESSION['language'] == 'ar')
									echo Yii::t('app', 'Shipping Cost') . " : " .yii::$app->Myclass->getArabicFormattingCurrency($model->currency,$model->shippingCost); 
								else
									echo Yii::t('app', 'Shipping Cost') . " : " .yii::$app->Myclass->getFormattingCurrency($model->currency,$model->shippingCost); 
							} else {
								echo Yii::t('app', 'Shipping Cost') . " : " . Yii::t('app', 'Free&nbsp;Shipping');
							} ?>
						</div>
					</div>
					<?php 
				} 
				?>
				<div class="social-buttons-link col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
					<span>
						<a href="#" onclick="return sharePopup('<?= $fbShareLink; ?>');"> 
							<img src="<?php echo Yii::$app->urlManager->createAbsoluteUrl('images/fb.jpg'); ?>" alt="">
						</a>
					</span> 
					<span>
						<a href="#" onclick="return sharePopup('<?= $twitShareLink; ?>');">   
							<img src="<?php echo Yii::$app->urlManager->createAbsoluteUrl('images/twitt.jpg'); ?>" alt="">
						</a>
					</span>  
				</div> 
				<div class="product-location-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
					<div class="location-grey"></div>
					<div class="product-location"><?php echo $model->location; ?></div>
				</div>
				<div class="product-address-map col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
					<div id="map_canvas" class="map-location" style="width: 600; height: 450"></div>
				</div>
				<?php
				$logUserid = Yii::$app->user->id;
				$sellerId = $userModel->userId;
				?>
				<?php if ($logUserid != $sellerId) {
					if (empty($logUserid)) { ?>
						<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('site/login/'); ?>"><div class="chat-seller-btn btn-chat-with-seller primary-bg-color col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
							<div class="chat-with-seller-text col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><img src="<?php echo Yii::$app->urlManager->createAbsoluteUrl('images/chat-with-seller-icon.png'); ?>" alt="chat-icon"><span><?php echo Yii::t('app', 'Chat with seller'); ?></span></div>
						</div></a>
						<?php if ($model->instantBuy == '1' && $sitePaymentModes['buynowPaymentMode'] == 1 && $model->quantity > 0 && $model->soldItem != 1) { ?>
							<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('site/login/'); ?>">
								<div class="buy-now-btn btn-make-an-offer col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
									<?php echo Yii::t('app', 'Buy now'); ?>
								</div>
							</a>
							<?php 
						} ?>
						<?php if ($model->myoffer == '0') { ?>
							<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('site/login/'); ?>" ><div class="make-offer-btn  btn-make-an-offer col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
								<?php echo Yii::t('app', 'Make an offer'); ?>
							</div></a>
							<?php 
						} ?>
						<?php if ($model->exchangeToBuy == '1' && $sitePaymentModes['exchangePaymentMode'] == 1) { ?>
							<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('site/login/'); ?>"><div class="exchange-buy-btn btn-make-an-offer col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
								<?php echo Yii::t('app', 'Exchange to buy'); ?>
							</div></a>
							<?php 
						} ?>
						<?php 
					} else { ?>
						<a href="" data-dismiss="modal" data-toggle="modal" data-target="#chat-with-seller-modal"><div class="chat-seller-btn btn-chat-with-seller primary-bg-color col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
							<div class="chat-with-seller-text col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><img src="<?php echo Yii::$app->urlManager->createAbsoluteUrl('images/chat-with-seller-icon.png'); ?>" alt="chat-icon"><span><?php echo Yii::t('app', 'Chat with seller'); ?></span></div>
						</div>
					</a>

					<?php if ($model->instantBuy != '0' && $sitePaymentModes['buynowPaymentMode'] == 1 ) { ?>
						<?php $cartDataURL = yii::$app->Myclass->cart_encrypt($model->productId . "-0", 'joy*ccart'); ?>
						<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('revieworder/' . $cartDataURL); ?>">
							<div class="buy-now-btn btn-make-an-offer col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
								<?php echo Yii::t('app', 'Buy now'); ?>
							</div>
						</a>
						<?php 
					} ?>
					<?php if ($model->myoffer == '0') { ?>
						<a href="" data-dismiss="modal" data-toggle="modal" data-target="#offer-modal"><div class="make-offer-btn  btn-make-an-offer col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
							<?php echo Yii::t('app', 'Make an offer'); ?>
						</div></a>
						<?php 
					} ?>
					<?php if ($model->exchangeToBuy == '1' && $sitePaymentModes['exchangePaymentMode'] == 1) { ?>
						<a href="" data-dismiss="modal" data-toggle="modal" data-target="#exchange-modal"><div class="exchange-buy-btn btn-make-an-offer col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
							<?php echo Yii::t('app', 'Exchange to buy'); ?>
						</div></a>
						<?php 
					} ?>
					<?php 
				} ?>
				<?php 
			} else {
				$lin = yii::$app->Myclass->safe_b64encode($model->productId . '-' . rand(100, 999));
				?>
				<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('products/update') . '/'.$lin; ?>" >
					<div class="btn-chat-with-seller primary-bg-color col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
						<div class="chat-with-seller-text col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
							<?php echo Yii::t('app', 'Edit Item'); ?>
						</div>
					</div>
				</a>
				<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('products/insights?id=').$lin; ?>" >
					<div class="in-sights-btn  btn-make-an-offer col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
						<?php echo Yii::t('app', 'Insights'); ?>
					</div>
				</a>
				<?php 
			} ?>
		</div>
		<!-- adsense start -->
		<?php if($sitesetting->google_ads_productright == 1) 
		{?>
		<div style="display: none;" class="tab-section col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">		
		<div class="adscontents">		
		<script type="text/javascript" src="//pagead2.googlesyndication.com/pagead/show_ads.js">
		</script>
		<script type="text/javascript">
		google_ad_client = "<?php echo $sitesetting->google_ad_client_productright; ?>";
		google_ad_slot = "<?php echo $sitesetting->google_ad_slot_productright; ?>";
		google_ad_width = 336;
		google_ad_height = 280;
		</script>
		</div>
		</div>
		<div style="margin-top: 10px; margin-left: 15px;" class="container col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">		
		<div style="text-align:center;" class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding adscontents">		
		<script type="text/javascript">
		var width = window.innerWidth || document.documentElement.clientWidth;
		google_ad_client = "<?php echo $sitesetting->google_ad_client_productright; ?>";
		if (width > 800) {
		google_ad_slot = "<?php echo $sitesetting->google_ad_slot_productright; ?>";
		google_ad_width = 336;
		google_ad_height = 280;
		}
		else if ((width <= 800) && (width > 400)) { 
		google_ad_slot = "<?php echo $sitesetting->google_ad_slot_productright; ?>";
		google_ad_width = 300; 
		google_ad_height = 250;
		}
		else
		{
		google_ad_slot = "<?php echo $sitesetting->google_ad_slot_productright; ?>";
		google_ad_width = 250; 
		google_ad_height = 250;
		}
		</script>
		<script type="text/javascript" src="//pagead2.googlesyndication.com/pagead/show_ads.js">
		</script>
		</div>
		</div>
		<?php } ?>
		<!-- adsense end -->

		<div class="product-page-right-bottom-container col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
			<div class="about-the-seller col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
				<?php echo Yii::t('app', 'About the seller'); ?>
			</div>
			<div class="seller-details col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
					<div class="col-xs-5 col-sm-2 col-md-4 col-lg-4 no-hor-padding">
						<?php
						if (!empty($userModel->userImage)) {
							$seller_profile = Yii::$app->urlManager->createAbsoluteUrl('profile/' . $userModel->userImage);
						} else {
							$seller_profile = Yii::$app->urlManager->createAbsoluteUrl('media/logo/' . yii::$app->Myclass->getDefaultUser());
						}
						?>
						<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl([
						'user/profiles',
						'id' => yii::$app->Myclass->safe_b64encode($userModel->userId . '-' . rand(100, 999))
						]); ?>"><div class="seller-prof-pic" style="background: rgba(0, 0, 0, 0) url('<?php echo $seller_profile; ?>') no-repeat scroll center center / cover ;">
							<?php if ($userModel->mobile_status == '1' && !empty($userModel->facebookId)) { ?>
								<div class="seller-verified-icon" title="<?php echo Yii::t('app', 'Seller Verified!'); ?>"><i class="fa fa-check"></i></div>
								<?php 
							} ?>
						</div></a>
					</div>
					<div class="col-xs-7 col-sm-10 col-md-8 col-lg-8">
						<div class="seller-name col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl(['user/profiles', 'id' => yii::$app->Myclass->safe_b64encode($userModel->userId . '-' . rand(100, 999))]); ?>"><?php echo $userModel->name; ?></a></div>
						<div class="seller-verification col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
							<?php if ($userModel->mobile_status == '1') { ?>
								<div class="mobile-verification" id="verified" data-toggle="tooltip" title="<?php echo Yii::t('app', 'Mobile number Verified!'); ?>"></div>
								<?php 
							} else { ?>
								<div class="mobile-verification" data-toggle="tooltip" title="<?php echo Yii::t('app', 'Mobile number is not Verified!'); ?>"></div>
								<?php 
							} ?>
							<?php if (!empty($userModel->facebookId)) { ?>
								<div class="fb-verification" id="verified" data-toggle="tooltip" title="<?php echo Yii::t('app', 'Facebook account Verified!'); ?>"></div>
								<?php 
							} else { ?>
								<div class="fb-verification" data-toggle="tooltip" title="<?php echo Yii::t('app', 'Facebook account is not Verified!'); ?>"></div>
								<?php 
							} ?>
							
							<?php if($sitesetting->signup_active === "yes") { ?>

								<div class="mail-verification" id="verified" data-toggle="tooltip" title="<?php echo Yii::t('app', 'Mail Id Verified!'); ?>"></div>
							<?php } ?>
						</div>
						<?php
						if (isset($userModel->phonevisible) && $userModel->phonevisible == "1" && $userModel->mobile_status == 1) {
							if($userModel->sms_country_code != 0){
								$mob_code =  explode($userModel->sms_country_code,$userModel->phone);
								echo '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><span class="country-code">+' . $userModel->sms_country_code . '</span>' . $mob_code[1] . '</div>';
							}
							else
							{
								echo '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><span class="country-code">' . $userModel->phone . '</span></div>';
							}
						}
						?>
					</div>
				</div>
				<div class="seller-joysale-detail col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
					<div class="product-counter-container col-xs-4 col-sm-4 col-md-4 col-lg-4">
						<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl(['user/profiles' . '/' . yii::$app->Myclass->safe_b64encode($userModel->userId . '-' . rand(100, 999))]); ?>"><div class="btn-product-counter">
							<div class="counter"><?php echo $itemCount; ?></div>
							<div class="counter-label"><?php echo Yii::t('app', 'Item(s)'); ?></div>
						</div></a>
					</div>
					<div class="product-counter-container col-xs-4 col-sm-4 col-md-4 col-lg-4">
						<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl(['user/following', 'id' => yii::$app->Myclass->safe_b64encode($userModel->userId . '-' . rand(100, 999))]); ?>"><div class="btn-product-counter">
							<div class="counter following-count"><?php echo $followingCount; ?></div>
							<div class="counter-label"><?php echo Yii::t('app', 'Following(s)'); ?></div>
						</div></a>
					</div>
					<div class="product-counter-container col-xs-4 col-sm-4 col-md-4 col-lg-4">
						<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl(['user/follower', 'id' => yii::$app->Myclass->safe_b64encode($userModel->userId . '-' . rand(100, 999))]); ?>"><div class="btn-product-counter">
							<div class="counter follower-count"><?php echo $followerCount; ?></div>
							<div class="counter-label"><?php echo Yii::t('app', 'Follower(s)'); ?></div>
						</div></a>
					</div>
				</div>
				<?php
				if (empty($logUserid)) { ?>
					<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('site/login/'); ?>" ><div class="btn-make-an-offer col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
						<div class="chat-with-seller-text col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><span id="sellerfollow<?php echo $sellerId; ?>" style="color:#515e6a;"><?php echo Yii::t('app', 'Follow'); ?></span></div>
					</div>
				</a>
				<?php 
			} else {
				if ($logUserid != $sellerId) {
					echo '<input type="hidden" value="1" id="seller_follow">';
					if (empty($checkFollow)) { ?>
						<a href="javascript:;" onclick="getfollows(<?php echo $sellerId; ?>)" id="follow<?php echo $sellerId; ?>" class="followlink btn-make-an-offer col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding" style="margin-top:20px;">
							<div class="chat-with-seller-text col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><span id="sellerfollow<?php echo $sellerId; ?>" style="color:#515e6a;"><?php echo Yii::t('app', 'Follow'); ?></span></div>
						</a>
						<?php 
					} else { ?>
						<a href="javascript:;" onclick="deletefollows(<?php echo $sellerId; ?>)" id="follow<?php echo $sellerId; ?>" class="unfollowlink btn-chat-with-seller primary-bg-color col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
							<div class="chat-with-seller-text col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><span id="sellerfollow<?php echo $sellerId; ?>"><?php echo Yii::t('app', 'Following') ?></span></div>
						</a>
						<?php 
					} ?>
					<?php 
				} else { ?>
					<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('user/editprofile'); ?>" ><div class="btn-chat-with-seller primary-bg-color col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
						<div class="chat-with-seller-text col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><span><?php echo Yii::t('app', 'Edit Profile'); ?></span></div>
					</div></a>
					<?php 
				}
			} ?>
		</div>
	</div>
</div> 
</div> 
<div class="modal fade" id="chat-with-seller-modal" role="dialog" tabindex='-1'>
	<div class="modal-dialog modal-dialog-width">
		<div class="chat-seller-modal-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
			<div class="to-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
				<button data-dismiss="modal" class="close chat-with-seller-close" type="button">×</button>
				<?php
				if (!empty($userModel->userImage)) {
					$seller_profile = $path1 . '/profile/' . $userModel->userImage;
				} else {
					$seller_profile = $path . 'logo/' . yii::$app->Myclass->getDefaultUser();
				}
				?>
				<?php
				?>
				<div class="to-prof-pic-cnt col-xs-3 col-sm-2 col-md-2 col-lg-2 no-hor-padding"><a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl(['user/profiles', 'id' => yii::$app->Myclass->safe_b64encode($userModel->userId . '-' . rand(100, 999))]); ?>"><div class="to-prof-pic" style="background: rgba(0, 0, 0, 0) url('<?php echo $seller_profile; ?>') no-repeat scroll center center / cover ;"></div></a>
			</div>
			<div class="to-prof-content col-xs-9 col-sm-10 col-md-10 col-lg-10 no-hor-padding">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><?php echo Yii::t('app', 'To:'); ?></div>
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl(['user/profiles', 'id' => yii::$app->Myclass->safe_b64encode($userModel->userId . '-' . rand(100, 999))]); ?>"><?php echo $userModel->username; ?></a></div>
			</div>
		</div>
		<div class="messgage-container col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
			<?php 
			if (isset($chatModel->blockedUser) && $chatModel->blockedUser === $user) {
							echo Yii::t('app', 'conversation blocked.'); // Your conversation has been blocked
						} elseif (isset($chatModel->blockedUser) && $chatModel->blockedUser === $userModel->userId) {
							echo Yii::t('app', 'conversation blocked.');// You have blocked this user.
						} else { ?>
							<textarea class="contact-textarea chat-modal-text-area form-control" maxlength="500" rows="5" placeholder="<?php echo Yii::t('app', 'Type your message') ?>" id="contact-textarea" ></textarea>
							<input type="hidden" class="contact-sender" value="<?php echo Yii::$app->user->id; ?>" />
							<input type="hidden" class="contact-receiver" value="<?php echo $userModel->userId; ?>" />
							<div class="option-error contactme-error" style="color:red;"></div>
							<div class="send-btn-cnt col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
								<div class="help-text col-xs-12 col-sm-9 col-md-9 col-lg-9 no-hor-padding"><span><i class="info-icon"></i></span><?php echo Yii::t('app', 'You and other person takes the complete responsibility of what is discussed here.'); ?></div>
								<div class="send-btn-container col-xs-12 col-sm-3 col-md-3 col-lg-3 no-hor-padding"><a href="javascript:;" onclick="contactMePopup()" >
									<div class="send-btn primary-bg-color txt-white-color text-align-center seller-chat-btn"><?php echo Yii::t('app', 'Send') ?></div></a></div>
								</div>
								<?php 
							} ?>
						</div>
					</div>
				</div>
			</div>
			<!--E O chat with seller modal-->	
			<!--chat with seller success modal-->
			<div class="modal fade" id="chat-with-seller-success-modal" role="dialog" tabindex='-1'>
				<div class="modal-dialog modal-dialog-width">
					<div class="success-modal col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
						<button onclick="close_popup();" class="close success-modal-close" type="button">×</button>
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
								<div class="success-icon"><i class="fa fa-check"></i></div></div>
								<div class="sent-text"><?php echo Yii::t('app', 'Message sent'); ?></div>
							</div>
						</div>
					</div>
				</div>
				<?php
				$userDetails = yii::$app->Myclass->getUserDetailss(Yii::$app->user->id);
				?>
				<!--E O chat with seller success modal-->
				<input type="hidden" value="<?php if (isset($userDetails->username)) echo $userDetails->username ?>" id="MyOfferForm_name">
				<input type="hidden" value="<?php if (isset($userDetails->email)) echo $userDetails->email; ?>" id="MyOfferForm_email">
				<input type="hidden" value="<?php if (isset($userDetails->phone)) echo $userDetails->phone; ?>" id="MyOfferForm_phone">
				<!--offer modal-->
				<div class="modal fade" id="offer-modal" role="dialog" tabindex='-1'>
					<div class="modal-dialog modal-dialog-width">
						<div class="login-modal-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
							<div class="login-modal-header col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
								<h2 class="login-header-text"><?php echo Yii::t('app', 'Make an offer'); ?></h2>
								<button data-dismiss="modal" class="close login-close" type="button">×</button>
							</div>
							<div class="login-line col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"></div>
							<div class="login-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding ">
								<div class="ask-price-text col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
									<?php echo Yii::t('app', 'Ask price'); ?><span>
										<?php
										if ($model->price == 0 && $model->instantBuy == 0) {
											echo '<span class="label_ga">' . Yii::t('app', 'Giving Away') . '</span>';
										} else {
											if (isset($_SESSION['language']) && $_SESSION['language'] == 'ar')
												echo '<div>'.yii::$app->Myclass->getArabicFormattingCurrency($model->currency,$model->price).'</div>'; 
											else{
												echo yii::$app->Myclass->getFormattingCurrency($model->currency,$model->price); 
											}
										}
										?>
									</span>
								</div>
								<div class="offer-price-section col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
									<div class="offer-price-text col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"> <?php echo Yii::t('app', 'Your offer Price :'); ?></div>
									<div class="offer-price-txt-field-cnt col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
										<?php
										$currency = explode('-', $model->currency);
										?>
										<div class="offer-text-field-label col-xs-1 col-sm-1 col-md-1 col-lg-1 no-hor-padding"><?php echo $currency[0]; ?></div>
										<div class="offer-text-field col-xs-11 col-sm-11 col-md-11 col-lg-11 no-hor-padding">
											<input type="text" class="my-offer-rate" maxlength="9" id="MyOfferForm_offer_rate" placeholder="<?php echo Yii::t('app', 'Enter price') ?>" autofocus=""  onkeypress="return isNumberrKey(event)" onkeyup="myOfferRate()" type="tel" pattern="[0-9]+([\.,][0-9]+)?">
											<div class="message-error" style="color: red;position:relative;text-align:left;"></div>
										</div>
									</div>
									<textarea class="offer-modal-text-area form-control" rows="5" id="MyOfferForm_message" placeholder="<?php echo Yii::t('app', 'Type your message') ?>" maxlength="500"></textarea>
									<div class="send-btn-container col-xs-12 col-sm-3 col-md-3 col-lg-3 no-hor-padding pull-right"><a href="javascript:;" onClick="myoffer()"><div class="send-btn primary-bg-color txt-white-color text-align-center offer-send-btn"><?php echo Yii::t('app', 'Send'); ?></div></a></div>
									<div id="errorMessage" style="color: red"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!--E O offer modal-->	
				<!--offer success modal-->
				<div class="modal fade" id="offer-success-modal" role="dialog" tabindex='-1'>
					<div class="modal-dialog modal-dialog-width">
						<div class="success-modal col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
							<button onClick="close_popup()" class="close success-modal-close" type="button">×</button>
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
								<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
									<div class="success-icon"><i class="fa fa-check"></i></div></div>
									<div class="sent-text"><?php echo Yii::t('app', 'Your Offer sent'); ?></div>
								</div>
							</div>
						</div>
					</div>
					<!--E O offer success modal-->
					<!--Exchange modal-->
					<div class="modal fade" id="exchange-modal" role="dialog" tabindex='-1'>
						<div class="modal-dialog exchange-modal-dialog-width modal-dialoType your messageType your messageg-width">
							<div class="login-modal-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
								<div class="login-modal-header col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
									<h2 class="login-header-text"><?php echo Yii::t('app', 'Exchange to buy'); ?></h2>
									<button data-dismiss="modal" class="close login-close" type="button">×</button>
								</div>
								<div class="login-line col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"></div>
								<div class="login-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
									<div class="exchange-product-grid-container col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
										<?php if (!empty($ownItems)) {
											foreach ($ownItems as $product) :
												$image = yii::$app->Myclass->getProductImage($product->productId);
												if (!empty($image)) {
													$img = Yii::$app->thumbnailer->get(Yii::$app->urlManager->createAbsoluteUrl("/media/item/" . $product->productId . '/' . $image), 155,155);
													$mediapath = realpath ( Yii::$app->basePath . "/web/media/item/".$product->productId . "/" . $image);
													if(file_exists($mediapath)) {
														$img = $img;
													} else {
														$img =Yii::$app->urlManager->createAbsoluteUrl("/media/item/".$sitesetting->default_productimage);
													}
												}
												else
												{
													$img =Yii::$app->urlManager->createAbsoluteUrl("/media/item/".$sitesetting->default_productimage);
												}
												?>
												<div class="exchange-product-grid <?php echo "exchange" . $product->productId; ?> col-xs-6 col-sm-4 col-d-4 col-lg-4">
													<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
														<div class="exchange-product" style="background: rgba(0, 0, 0, 0) url('<?php echo $img; ?>') no-repeat scroll center center / cover ; cursor:pointer; border-right: 1px solid #d0dbe5; border-top: 1px solid #d0dbe5; border-left: 1px solid #d0dbe5;" onClick="selectExchangeproduct(<?php echo $product->productId; ?>)">
															<input type="hidden" value="" id="exchange_product_id">
														</div>
														<div class="exchange-product-name"><?php echo $product->name; ?></div>
													</div>
												</div>
												<?php
											endforeach;
										} else {
											echo Yii::t('app', 'You haven’t added any products yet.');
										}
										?>
									</div>
									<?php if (!empty($ownItems)) { ?>
										<div class="create-exchange-btn send-btn-container col-xs-12 col-sm-3 col-md-3 col-lg-3 no-hor-padding"><a id="exchange-btn" href="javascript:;" onclick="createExchange(<?php echo $model->productId; ?>,<?php echo $model->userId; ?>)"><div class="send-btn primary-bg-color txt-white-color text-align-center"><?php echo Yii::t('app', 'Create Exchange'); ?></div></a></div>
										<?php 
									} ?>
									<div class="option-error" style="display:none;"></div>
								</div>
							</div>
						</div>
					</div>
					<!--E O Exchange modal-->
					<!--Recently viewed products-->
					<?php if (!empty($logUserid) && !empty($recentlyprodcts)) { ?>
						<div class="product-row row recently-viewed-container">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
								<div class="recently-viewed-heading col-xs-12 col-sm-12 col-md-12 col-lg-12"><?php echo Yii::t('app', 'Recently Viewed Products'); ?></div>
								<div class="recently-viewed col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
									<?php foreach ($recentlyprodcts as $recents) {
										$image = yii::$app->Myclass->getProductImage($recents->productId);
										$pdtURL = Yii::$app->urlManager->createAbsoluteUrl("/media/item/resized/" . $recents->productId . "/" . $image);
										$mediapath = realpath ( Yii::$app->basePath . "/web/media/item/resized/".$recents->productId . "/" . $image);
										if(isset($mediapath)) {
											$pdtURL = $pdtURL;
										} else {
											$pdtURL =Yii::$app->urlManager->createAbsoluteUrl("/media/item/".$sitesetting->default_productimage);
										}
										?>
										<div class="product-padding col-xs-12 col-sm-6 col-md-3 col-lg-3">
											<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl("/products/view/" .yii::$app->Myclass->safe_b64encode($recents->productId. '-' .rand(100, 999)) . '/' . yii::$app->Myclass->productSlug(strtolower($recents->name))); ?>">
												<?php
												if (isset($sitesetting->promotionStatus) && $sitesetting->promotionStatus == "1") {
													if ($recents->promotionType == '1' && $recents->soldItem == '0') { ?>
														<span class="item-ad"><?php echo Yii::t('app', 'Ad'); ?></span>
														<?php 
													} else if ($recents->promotionType == '2' && $recents->soldItem == '0') { ?>
														<span class="item-urgent"><?php echo Yii::t('app', 'Urgent'); ?></span>
														<?php 
													}
												}
												if ($recents->soldItem == '1') { ?>
													<span class="sold-out"><?php echo Yii::t('app', 'Sold Out'); ?></span>
													<?php 
												} ?>
												<div class="image-container col-xs-12 col-sm-12 col-md-12 col-lg-12" id="prod-5" style="background: rgba(0, 0, 0, 0) url('<?php echo $pdtURL; ?>') no-repeat scroll center center / cover ;">
												</div>
												<span class="day-count">
												<?php if (Yii::$app->language == 'ar'){
													echo Yii::t('app', 'ago') . ' ' . yii::$app->Myclass->getElapsedTime($recents->createdDate);}
													else
														echo yii::$app->Myclass->getElapsedTime($recents->createdDate) . ' ' . Yii::t('app', 'ago'); ?>
												</span>
											</a>
											<div class="rate-section-2 col-xs-12 col-sm-12 no-hor-padding">
												<div class="price bold col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
													<?php
													if ($recents->price == 0 && $recents->instantBuy == 0) {
														echo '<span class="label_ga">' . Yii::t('app', 'Giving Away') . '</span>';
													} else {
														if (isset($_SESSION['language']) && $_SESSION['language'] == 'ar')
															echo yii::$app->Myclass->getArabicFormattingCurrency($recents->currency,$recents->price); 
														else{
															echo yii::$app->Myclass->getFormattingCurrency($recents->currency,$recents->price); 
														}
													}
													?>
												</div>
												<div class="item-name col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
													<?php	$count = strlen($recents->name);
													if ($count > 30) {
														$itmName = substr($recents->name, 0, 30) . '...';
													} else {
														$itmName = $recents->name;
													}
													?>
													<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl("/products/view/" . yii::$app->Myclass->safe_b64encode($recents->productId . '-' . rand(100, 999)) . '/' . yii::$app->Myclass->productSlug(strtolower($recents->name))); ?>"><?php echo $itmName; ?></a>
												</div>
												<?php
												$location = $recents->location;
												?>
												<span class="item-location secondary-txt-color col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><?php echo $location; ?></span>
											</div>
										</div>
										<?php 
									} ?>
								</div>
							</div>
						</div>
						<?php 
					} ?>
					<?php  
					$recentlyprodcts = $userinterested;
					if ( !empty($recentlyprodcts)) { 
						?>
						<div class="product-row row user-interet-prod recently-viewed-container">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
								<div class="recently-viewed-heading col-xs-12 col-sm-12 col-md-12 col-lg-12"><?php echo Yii::t('app', 'Related products'); ?></div>
								<div class="recently-viewed col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
									<?php foreach ($recentlyprodcts as $recents) {
										$image = yii::$app->Myclass->getProductImage($recents->productId);
										$pdtURL = Yii::$app->urlManager->createAbsoluteUrl("/media/item/resized/" . $recents->productId . "/" . $image);
										$mediapath = realpath ( Yii::$app->basePath . "/web/media/item/resized/".$recents->productId . "/" . $image);
										if(isset($mediapath)) {
											$pdtURL = $pdtURL;
										} else {
											$pdtURL =Yii::$app->urlManager->createAbsoluteUrl("/media/item/".$sitesetting->default_productimage);
										}
										?>
										<div class="product-padding col-xs-12 col-sm-6 col-md-3 col-lg-3">
											<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl("/products/view/" .yii::$app->Myclass->safe_b64encode($recents->productId. '-' .rand(100, 999)) . '/' . yii::$app->Myclass->productSlug(strtolower($recents->name))); ?>">
												<?php
												if (isset($sitesetting->promotionStatus) && $sitesetting->promotionStatus == "1") {
													if ($recents->promotionType == '1' && $recents->soldItem == '0') { ?>
														<span class="item-ad"><?php echo Yii::t('app', 'Ad'); ?></span>
														<?php 
													} else if ($recents->promotionType == '2' && $recents->soldItem == '0') { ?>
														<span class="item-urgent"><?php echo Yii::t('app', 'Urgent'); ?></span>
														<?php 
													}
												}
												if ($recents->soldItem == '1') { ?>
													<span class="sold-out"><?php echo Yii::t('app', 'Sold Out'); ?></span>
													<?php 
												} ?>
												<div class="image-container col-xs-12 col-sm-12 col-md-12 col-lg-12" id="prod-5" style="background: rgba(0, 0, 0, 0) url('<?php echo $pdtURL; ?>') no-repeat scroll center center / cover ;">
												</div>
												<span class="day-count">
													<?php if (Yii::$app->language == 'ar'){
													echo Yii::t('app', 'ago') . ' ' . yii::$app->Myclass->getElapsedTime($recents->createdDate);}
													else
														echo yii::$app->Myclass->getElapsedTime($recents->createdDate) . ' ' . Yii::t('app', 'ago'); ?>					
												</span>
											</a>
											<div class="rate-section-2 col-xs-12 col-sm-12 no-hor-padding">
												<div class="price bold col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
													<?php
													if ($recents->price == 0 && $recents->instantBuy == 0) {
														echo '<span class="label_ga">' . Yii::t('app', 'Giving Away') . '</span>';
													} else {
														if (isset($_SESSION['language']) && $_SESSION['language'] == 'ar')
															echo yii::$app->Myclass->getArabicFormattingCurrency($recents->currency,$recents->price); 
														else{
															echo yii::$app->Myclass->getFormattingCurrency($recents->currency,$recents->price); 
														}
													}
													?>
												</div>
												<div class="item-name col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
													<?php	$count = strlen($recents->name);
													if ($count > 30) {
														$itmName = substr($recents->name, 0, 30) . '...';
													} else {
														$itmName = $recents->name;
													}
													?>
													<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl("/products/" . yii::$app->Myclass->safe_b64encode($recents->productId . '-' . rand(100, 999)) . '/' . yii::$app->Myclass->productSlug($recents->name)); ?>"><?php echo $itmName; ?></a>
												</div>
												<?php
												$location = $recents->location;
												?>
												<span class="item-location secondary-txt-color col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><?php echo $location; ?></span>
											</div>
										</div>
										<?php 
									} ?>
								</div>
							</div>
						</div>
					<?php }
//} ?>
<?php if (!empty($relateditems) || !empty($relatedaditems)) { ?>
	<!--Related products-->
	<div class="product-row row popular-product-container">
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
			<div class="recently-viewed-heading col-xs-12 col-sm-12 col-md-12 col-lg-12"><?php echo Yii::t('app','Related Products'); ?></div>
			<div class="recently-viewed col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
				<?php 
				if($count_relatedaditems > 1) {
					$related_position[] = RAND(0,1);
					$related_position[] = RAND(2,3);
				} else if($count_relatedaditems == '0') {
				}else {
					$related_position[] = RAND(0,3);
				}
				?>
				<?php
				if (empty($relateditems)) {
					$relateditems = $relatedaditems;
				}
				foreach($relateditems as $key=>$related) {
					if(isset($related_position))
					{
						if(in_array($key,$related_position)) {
							if($count_relatedaditems > 1) {
								$relatedaditems = array_slice($relatedaditems,0,2);
								$relatedadd[$related_position[0]] = $relatedaditems[0];
								$relatedadd[$related_position[1]] = $relatedaditems[1];
							} else {
								$relatedaditems = $relatedaditems;
								$relatedadd[$related_position[0]] = $relatedaditems[0];
							}
							$image = yii::$app->Myclass->getProductImage($relatedadd[$key]->productId);
							$pdtURL = Yii::$app->thumbnailer->get(Yii::$app->urlManager->createAbsoluteUrl("/media/item/resized/".$relatedadd[$key]->productId."/".$image), 300,300); ?>
							<div class="product-padding col-xs-12 col-sm-6 col-md-3 col-lg-3">
								<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl("/products/".yii::$app->Myclass->safe_b64encode($relatedadd[$key]->productId.'-'.rand(100,999)).'/'.yii::$app->Myclass->productSlug($relatedadd[$key]->name)); ?>">
									<?php
									if(isset($sitesetting->promotionStatus) && $sitesetting->promotionStatus == "1"){
										if($relatedadd[$key]->promotionType == '1' && $relatedadd[$key]->soldItem == '0') { ?>
											<span class="item-ad"><?php echo Yii::t('app','Ad'); ?></span>
										<?php }else if($relatedadd[$key]->promotionType == '2' && $relatedadd[$key]->soldItem == '0') { ?>
											<span class="item-urgent"><?php echo Yii::t('app','Urgent'); ?></span>
										<?php }
									}
									if($relatedadd[$key]->soldItem == '1') { ?>
										<span class="sold-out"><?php echo Yii::t('app','Sold Out'); ?></span>
									<?php }?>
									<div class="image-container col-xs-12 col-sm-12 col-md-12 col-lg-12" id="prod-5" style="background: rgba(0, 0, 0, 0) url('<?php echo $pdtURL; ?>') no-repeat scroll center center / cover ;">
									</div>
									<span class="day-count">
									<?php if (Yii::$app->language == 'ar'){
											echo Yii::t('app', 'ago') . ' ' . yii::$app->Myclass->getElapsedTime($relatedadd[$key]->createdDate);}
										else
											echo yii::$app->Myclass->getElapsedTime($relatedadd[$key]->createdDate).' '.Yii::t('app','ago'); ?>	
									</span>
								</a>
								<div class="rate-section-2 col-xs-12 col-sm-12 no-hor-padding">
									<div class="price bold col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
										<?php
										if($relatedadd[$key]->price == 0 && $relatedadd[$key]->instantBuy == 0) {
											echo '<span class="label_ga">'.Yii::t('app','Giving Away').'</span>';
										} else {
											if(isset($_SESSION['_lang']) == 'ar')
												echo '<span>'.$relatedadd[$key]->price.'</span> <span>'.yii::$app->Myclass->getCurrency($relatedadd[$key]->currency).'</span>';
											else
												echo '<span>'.yii::$app->Myclass->getCurrency($relatedadd[$key]->currency).'</span> <span>'. $relatedadd[$key]->price.'</span>';
										}
										?>
									</div>
									<div class="item-name col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
										<?php	$count = strlen($relatedadd[$key]->name);
										if($count > 30){
											$itmName = substr($relatedadd[$key]->name,0,30).'...';
										} else {
											$itmName = $relatedadd[$key]->name;
										}
										?>
										<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl("/products/".yii::$app->Myclass->safe_b64encode($relatedadd[$key]->productId.'-'.rand(0,999)).'/'.yii::$app->Myclass->productSlug($relatedadd[$key]->name)); ?>"><?php echo $itmName; ?></a>
									</div>
									<?php
									$location = $relatedadd[$key]->location;
									?>
									<span class="item-location secondary-txt-color col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><?php echo $location; ?></span>
								</div>
							</div>
						<?php   //}
					} else {
						$image = yii::$app->Myclass->getProductImage($related->productId);
						$pdtURL = Yii::$app->thumbnailer->get(Yii::$app->urlManager->createAbsoluteUrl("/media/item/resized/".$related->productId."/".$image), 300,300); ?>
						<div class="product-padding col-xs-12 col-sm-6 col-md-3 col-lg-3">
							<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl("/products/".yii::$app->Myclass->safe_b64encode($related->productId.'-'.rand(100,999)).'/'.yii::$app->Myclass->productSlug($related->name)); ?>">
								<?php
								if(isset($sitesetting->promotionStatus) && $sitesetting->promotionStatus == "1"){
									if($related->promotionType == '1' && $related->soldItem == '0') { ?>
										<span class="item-ad"><?php echo Yii::t('app','Ad'); ?></span>
									<?php }else if($related->promotionType == '2' && $related->soldItem == '0') { ?>
										<span class="item-urgent"><?php echo Yii::t('app','Urgent'); ?></span>
									<?php }
								}
								if($related->soldItem == '1') { ?>
									<span class="sold-out"><?php echo Yii::t('app','Sold Out'); ?></span>
								<?php }?>
								<div class="image-container col-xs-12 col-sm-12 col-md-12 col-lg-12" id="prod-5" style="background: rgba(0, 0, 0, 0) url('<?php echo $pdtURL; ?>') no-repeat scroll center center / cover ;">
								</div>
								<span class="day-count">
								<?php if (Yii::$app->language == 'ar'){
									echo Yii::t('app', 'ago') . ' ' . yii::$app->Myclass->getElapsedTime($related->createdDate);}
								else
									echo yii::$app->Myclass->getElapsedTime($related->createdDate).' '.Yii::t('app','ago'); ?>			
								</span>
							</a>
							<div class="rate-section-2 col-xs-12 col-sm-12 no-hor-padding">
								<div class="price bold col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
									<?php
									if($related->price == 0 && $related->instantBuy == 0) {
										echo '<span class="label_ga">'.Yii::t('app','Giving Away').'</span>';
									} else {
										if(isset($_SESSION['_lang']) == 'ar')
											echo '<span>'.$related->price.'</span> <span>'.yii::$app->Myclass->getCurrency($related->currency).'</span>';
										else
											echo '<span>'.yii::$app->Myclass->getCurrency($related->currency).'</span> <span>'. $related->price.'</span>';
									}
									?>
								</div>
								<div class="item-name col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
									<?php	$count = strlen($related->name);
									if($count > 30){
										$itmName = substr($related->name,0,30).'...';
									} else {
										$itmName = $related->name;
									}
									?>
									<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl("/products/".yii::$app->Myclass->safe_b64encode($related->productId.'-'.rand(0,999)).'/'.yii::$app->Myclass->productSlug($related->name)); ?>"><?php echo $itmName; ?></a>
								</div>
								<?php
								$location = $related->location;
								?>
								<span class="item-location secondary-txt-color col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><?php echo $location; ?></span>
							</div>
						</div>
					<?php } }
					else
					{ 
						$image = yii::$app->Myclass->getProductImage($related->productId);
						$pdtURL = Yii::$app->thumbnailer->get(Yii::$app->urlManager->createAbsoluteUrl("/media/item/resized/".$related->productId."/".$image), 300,300);
						?>
						<div class="product-padding col-xs-12 col-sm-6 col-md-3 col-lg-3">
							<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl("/products/".yii::$app->Myclass->safe_b64encode($related->productId.'-'.rand(0,999)).'/'.yii::$app->Myclass->productSlug($related->name)); ?>">
								<?php
								if(isset($sitesetting->promotionStatus) && $sitesetting->promotionStatus == "1"){
									if($related->promotionType == '1' && $related->soldItem == '0') { ?>
										<span class="item-ad"><?php echo Yii::t('app','Ad'); ?></span>
									<?php }else if($related->promotionType == '2' && $related->soldItem == '0') { ?>
										<span class="item-urgent"><?php echo Yii::t('app','Urgent'); ?></span>
									<?php }
								}
								if($related->soldItem == '1') { ?>
									<span class="sold-out"><?php echo Yii::t('app','Sold Out'); ?></span>
								<?php }?>
								<div class="image-container col-xs-12 col-sm-12 col-md-12 col-lg-12" id="prod-5" style="background: rgba(0, 0, 0, 0) url('<?php echo $pdtURL; ?>') no-repeat scroll center center / cover ;">
								</div>
								<span class="day-count">
								<?php if (Yii::$app->language == 'ar'){
									echo Yii::t('app', 'ago') . ' ' . yii::$app->Myclass->getElapsedTime($related->createdDate);}
								else
									echo yii::$app->Myclass->getElapsedTime($related->createdDate).' '.Yii::t('app','ago'); ?>			
								</span>
							</a>
							<div class="rate-section-2 col-xs-12 col-sm-12 no-hor-padding">
								<div class="price bold col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
									<?php
									if($related->price == 0 && $related->instantBuy == 0) {
										echo '<span class="label_ga">'.Yii::t('app','Giving Away').'</span>';
									} else {
										if(isset($_SESSION['_lang']) == 'ar')
											echo '<span>'.$related->price.'</span> <span>'.yii::$app->Myclass->getCurrency($related->currency).'</span>';
										else
											echo '<span>'.yii::$app->Myclass->getCurrency($related->currency).'</span> <span>'. $related->price.'</span>';
									}
									?>
								</div>
								<div class="item-name col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
									<?php	$count = strlen($related->name);
									if($count > 30){
										$itmName = substr($related->name,0,30).'...';
									} else {
										$itmName = $related->name;
									}
									?>
									<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl("/products/".yii::$app->Myclass->safe_b64encode($related->productId.'-'.rand(0,999)).'/'.yii::$app->Myclass->productSlug($related->name)); ?>"><?php echo $itmName; ?></a>
								</div>
								<?php
								$location = $related->location;
								?>
								<span class="item-location secondary-txt-color col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><?php echo $location; ?></span>
							</div>
						</div>
					<?php		}
				} ?>
			</div>
		</div>
	</div>
<?php } ?>
<?php if (!empty($sameUserItems)) { ?>
	<!--More items from seller-->
	<div class="product-row row popular-product-container">
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
			<div class="recently-viewed-heading col-xs-12 col-sm-12 col-md-12 col-lg-12"><?php echo Yii::t('app','More Items from').' '.$userModel->name; ?></div>
			<div class="recently-viewed col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
				<?php
				foreach($sameUserItems as $key=>$populars) {
					$image = yii::$app->Myclass->getProductImage($populars->productId);
					$pdtURL = Yii::$app->thumbnailer->get(Yii::$app->urlManager->createAbsoluteUrl("/media/item/resized/".$populars->productId."/".$image), 300,300); ?>
					<div class="product-padding col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl("/products/".yii::$app->Myclass->safe_b64encode($populars->productId.'-'.rand(0,999)).'/'.yii::$app->Myclass->productSlug($populars->name)); ?>">
							<?php
							if(isset($sitesetting->promotionStatus) && $sitesetting->promotionStatus == "1"){
								if($popularadd[$key]->promotionType == '1' && $populars->soldItem == '0') { ?>
									<span class="item-ad"><?php echo Yii::t('app','Ad'); ?></span>
								<?php }else if($populars->promotionType == '2' && $populars->soldItem == '0') { ?>
									<span class="item-urgent"><?php echo Yii::t('app','Urgent'); ?></span>
								<?php }
							}
							if($populars->soldItem == '1') { ?>
								<span class="sold-out"><?php echo Yii::t('app','Sold Out'); ?></span>
							<?php }?>
							<div class="image-container col-xs-12 col-sm-12 col-md-12 col-lg-12" id="prod-5" style="background: rgba(0, 0, 0, 0) url('<?php echo $pdtURL; ?>') no-repeat scroll center center / cover ;">
							</div>
							<span class="day-count">
							<?php if (Yii::$app->language == 'ar'){
								echo Yii::t('app', 'ago') . ' ' . yii::$app->Myclass->getElapsedTime($populars->createdDate);}
							else
								echo yii::$app->Myclass->getElapsedTime($populars->createdDate).' '.Yii::t('app','ago'); ?>		
							</span>
						</a>
						<div class="rate-section-2 col-xs-12 col-sm-12 no-hor-padding">
							<div class="price bold col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
								<?php
								if($populars->price == 0 && $populars->instantBuy == 0) {
									echo '<span class="label_ga">'.Yii::t('app','Giving Away').'</span>';
								} else {
									if(isset($_SESSION['_lang']) == 'ar')
										echo '<span>'.$populars->price.'</span> <span>'.yii::$app->Myclass->getCurrency($populars->currency).'</span>';
									else
										echo '<span>'.yii::$app->Myclass->getCurrency($populars->currency).'</span> <span>'. $populars->price.'</span>';
								}
								?>
							</div>
							<div class="item-name col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
								<?php	$count = strlen($populars->name);
								if($count > 30){
									$itmName = substr($populars->name,0,30).'...';
								} else {
									$itmName = $populars->name;
								}
								?>
								<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl("/products/".yii::$app->Myclass->safe_b64encode($populars->productId.'-'.rand(0,999)).'/'.yii::$app->Myclass->productSlug($populars->name)); ?>"><?php echo $itmName; ?></a>
							</div>
							<?php
							$location = $populars->location;
							?>
							<span class="item-location secondary-txt-color col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"><?php echo $location; ?></span>
						</div>
					</div>
					<?php 
				}
				?>
			</div>
		</div>
	</div>
<?php } ?>
<div class="product-container col-md-6">
	<div class="purchase-details">
		<?php if($model->sizeOptions != ''){
			echo '<input type="hidden" value="1" class="item-option"/>';
		}else{
			echo '<input type="hidden" value="0" class="item-option"/>';
			$cartDataURL = yii::$app->Myclass->cart_encrypt($model->productId."-0", 'joy*ccart');
			echo '<input type="hidden" value="'.$cartDataURL.'" class="item-cartdata"/>';
		}?>
		<div class="carterror" style="color: red"></div>
	</div>
	<input type="hidden" value="<?php echo $model->productId; ?>"
	class="item-id" />
</div>
<div id="popup_container">
	<?php if ($model->chatAndBuy == 1) { ?>
		<div id="contact-me-popup" style="display: none;"
		class="popup ly-title update contact-me-popup">
		<p class="ltit">
			<?php echo Yii::t('app','Contact me'); ?>
		</p>
		<button type="button" class="ly-close" id="btn-browses">x</button>
		<div class="contact-me-content">
			<div class="contact-top">
				<div class="contact-top-left">
					<div class="contact-user-image">
						<?php $userImage = yii::$app->Myclass->getUserDetailss($model->userId);
						if(!empty($userImage->userImage)) {
							echo Html::img(Yii::$app->thumbnailer->get(Yii::$app->urlManager->createAbsoluteUrl("/media/item/".$userImage->userImage), 40,40),$userImage->username,array('height'=>40,'width' => 40));
						} else {
							echo Html::img(Yii::$app->thumbnailer->get(Yii::$app->urlManager->createAbsoluteUrl("/media/item/".yii::$app->Myclass->getDefaultUser()), 40,40),$userImage->username,array('height'=>40,'width' => 40));
						} ?>
					</div>
					<div class="contact-to-details">
						<?php echo Yii::t('app','To'); ?>
						:<br> <span class="to-user"><a class="userNameLink"
							href="<?php echo Yii::$app->urlManager->createAbsoluteUrl(['user/profiles','id' => yii::$app->Myclass->safe_b64encode($userImage->userId.'-'.rand(0,999))]); ?>"><?php echo $userImage->name; ?>
						</a> </span>
					</div>
				</div>
			</div>
			<div class="contact-message">
				<input type=hidden name=lastkey id=lastkey>
				<textarea class="contact-textarea" rows="4" cols="12"
				id="contact-textarea" onkeyup="keyban(event)"
				onkeydown="keyHandler(event)"></textarea>
				<input type="hidden" class="contact-sender"
				value="<?php echo Yii::$app->urlManager->user->id; ?>" /> <input
				type="hidden" class="contact-receiver"
				value="<?php echo $userImage->userId; ?>" />
				<div class="option-error contactme-error"></div>
			</div>
			<div class="contact-buttons-area">
				<div class="cancel-button close-contact">
					<?php echo Yii::t('app','Cancel'); ?>
				</div>
				<div class="send-button" onclick="contactMePopup()">
					<?php echo Yii::t('app','Send'); ?>
				</div>
			</div>
		</div>
	</div>
<?php } ?>
<div id="show-coupon-popup" style="display: none;"
class="popup ly-title update show-exchange-popup">
<p class="ltit">
	<?php echo Yii::t('app','Generate Coupon'); ?>
</p>
<button type="button" class="ly-close" id="btn-browses">x</button>
<div class="coupon-popup">
	<div class='generate-coupon-container'>
		<label class="control-label"><?php echo Yii::t('app','Coupon Value'); ?>
		<?php echo '('.yii::$app->Myclass->getCurrency($model->currency).')'; ?>
	: </label> <input type="text" class="couponValue" id="couponValue"
	onkeypress="return isNumber(event)" maxlength=9 />
	<div class="option-error"></div>
	<div class="btn-area">
		<button type="button" class="btn-choose-option btn-done"
		id="btn-doneid"
		onclick="generateCoupon('<?php echo $model->productId; ?>','<?php echo $model->userId; ?>','<?php echo $model->price; ?>','<?php echo yii::$app->Myclass->getCurrency($model->currency); ?>')">
		<?php echo Yii::t('app','Generate Coupon'); ?>
	</button>
</div>
</div>
<div class="coupon-code"></div>
<p class="new-coupon-link">
	<?php echo Yii::t('app','Generate New Coupon'); ?>
	?
</p>
</div>
</div>
<?php if($model->exchangeToBuy == 0) { ?>
	<div id="show-exchange-popup" style="display: none;"
	class="popup ly-title update show-exchange-popup">
	<p class="ltit">
		<?php echo Yii::t('app','Exchange to Buy'); ?>
	</p>
	<button type="button" class="ly-close" id="btn-browses">x</button>
	<?php $this->render('requestExchange',array('mainProductId' => $model->productId,'ownItems'=> $ownItems,'requestTo'=>$model->userId),false,true); ?>
</div>
<?php } ?>
<?php if ($model->instantBuy == 1 && $model->sizeOptions != ''){ ?>
	<div id="choose-option-popup" style="display: none;"
	class="popup ly-title update choose-option-popup">
	<p class="ltit">
		<?php echo Yii::t('app','Choose a option'); ?>
	</p>
	<button type="button" class="ly-close" id="btn-browses">x</button>
	<div class='choose-option-content'>
		<?php $options = json_decode($model->sizeOptions, true);
		echo '<div class="quantity" style="display: inline-block; width: 200px;">'.Yii::t('app','Options').':
		<select class="item-qty" onchange="selectedOptionPrice();">';
		$i = 0;
		foreach ($options as $optionkey => $option){
			if ($option['quantity'] != 0){
				$value = yii::$app->Myclass->cart_encrypt($model->productId."-".$option['option'], 'joy*ccart');
				if ($option['price'] != 0){
					if($i == 0) {
						$price = $option['price'];
						$i++;
					}
					echo '<option value="'.$value.'" pricetag="'.$option['price'].'">'.$option['option'].'</option>';
				}else{
					$price = $model->price;
					echo '<option value="'.$value.'" pricetag="'.$model->price.'">'.$option['option'].'</option>';
				}
			}
		}
		echo '</select></div>';
		?>
		<div class="option-price">
			<?php echo Yii::t('app','Price'); ?>
			:
			<?php
			if($model->price == 0 && $model->instantBuy == 0) {
				echo '<span class="label_ga">'.Yii::t('app','Giving Away').'</span>';
			} else {
				if($_SESSION['_lang'] == 'ar')
					echo '<span class="option-price-value">'.$price.'</span> <span>'.yii::$app->Myclass->getCurrency($model->currency).'</span>';
				else
					echo '<span>'.yii::$app->Myclass->getCurrency($model->currency).'</span> <span class="option-price-value">'. $price.'</span>';
			}
			?>
		</div>
	</div>
	<div class="btn-area">
		<button type="button" class="btn-choose-option btn-done"
		id="btn-doneid" onclick="optionCheck();">
		<?php echo Yii::t('app','Proceed to Checkout'); ?>
	</button>
	<div class="option-error"></div>
</div>
</div>
<?php } ?>
</div>
</div>
<div id="prodModal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-body">
			<div class="embed-responsive embed-responsive-16by9">
				<iframe class="embed-responsive-item" src="<?php echo convertYoutube($model->videoUrl); ?>"></iframe>
			</div>
		</div>
		<div>
			<button onclick = "return skip_video();" type="button" class="skip-button" data-dismiss="modal">Skip</button>
		</div>
	</div>
</div>
<script type="text/javascript">
	$("#prodModal").on('hidden.bs.modal', function (e) {
		$("#prodModal iframe").attr("src", $("#prodModal iframe").attr("src"));
	});
</script>
<?php
$url = Yii::$app->request->url;
$splitURL = explode('/', $url);
$action = end($splitURL);
if ($sitesetting->promotionStatus == '0' && $sitesetting->product_autoapprove == '0' && $action == 'create') {
	?>
	<script>
		$(document).ready(function() {
			$('.container-fluid').append('<div class="flashes message-floating-div-cnt col-xs-12 col-sm-4 col-md-3 col-lg-3 no-hor-padding"><div class="flash-0 floating-div no-hor-padding pull-right" style="width:auto;"><div class="message-user-info-cnt no-hor-padding" style="width:auto;"><div class="message-user-info">Your product is submitted & waiting for admin approval</div></div></div></div>');
			setTimeout(function() {
				$(".container-fluid").fadeOut();
			}, 3000);
		});

	</script>
	<?php 
} ?>
<script>
	var offset = 13;
	var limit = 12;
	var totalMoreImage = <?php echo count($photoModel) - 7; ?>;
	var currentRightClick = 0;
	var currentLeftClick =  <?php echo count($photoModel) - 7; ?>;
	var currentPosition = 0;
</script>
<style>
	.sightView {
		margin-top: 10px;
	}
	.message-error {
		background-color: #FFFFFF;
		color: #DF2525;
		display: none;
		padding: 5px;
		position: absolute;
		z-index: 20;
		text-align: center;
		width: 328px;
		font-size: 13px;
	}
	body {
		padding-right:0px !important;
	}
	.modal-open .modal {
		overflow:hidden !important;
	}
	.footer {
		margin-top: 0px !important;
	}
	@media (min-width: 320px) and (max-width: 640px) {
		.footer {
			margin-bottom: 45px !important;
		}
	}
	@media (min-width: 320px) and (max-width: 767px) {
		.modal-open .modal {
			overflow:auto !important;
		}
	}
	.tab-section ul
	{
		list-style-type: disc;
		margin-block-start: 1em;
		margin-inline-end: 0px;
	}
	.pad_bottom {
		padding-bottom: 30px;
	}
</style>
<?php
$siteSettings = yii::$app->Myclass->getSitesettings();
if (!empty($siteSettings) && isset($siteSettings->googleapikey) && $siteSettings->googleapikey != "")
	$googleapikey = "&key=" . $siteSettings->googleapikey;
else
	$googleapikey = "";

function convertYoutube($url) {
	$shortUrlRegex = '/youtu.be\/([a-zA-Z0-9_]+)\??/i';
	$longUrlRegex = '/^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/';
	if (preg_match($longUrlRegex, $url, $matches)) {
		$youtube_id = $matches[count($matches) - 1];
	}
	// if (preg_match($shortUrlRegex, $url, $matches)) {
	// 	$youtube_id = $matches[count($matches) - 1];
	// }
	return 'https://www.youtube.com/embed/' . $youtube_id ;
}

?>
<script>
	function process() {
		document.getElementById("prod_description").innerHTML = anchorme({
			input: document.getElementById("prod_description").innerHTML,
    // use some options
    options: {
    	attributes: {
    		target: "_blank",
    		class: "detected"
    	}
    },
    // and extensions
    extensions: [
      // an extension for hashtag search
      {
      	test: /#(\w|_)+/gi,
      	transform: string =>
      	`<a href="https://a.b?s=${string.substr(1)}">${string}</a>`
      },
      // an extension for mentions
      {
      	test: /@(\w|_)+/gi,
      	transform: string =>
      	`<a href="https://a.b/${string.substr(1)}">${string}</a>`
      }
      ]
  });
	}
	
	process();

	function skip_video(){
		$('.embed-responsive-item').each(function(){
			var el_src = $(this).attr("src");
			$(this).attr("src",el_src);
			$('#close_videos').trigger("click");
		});
	}
</script>
