<?php
use yii\helpers\Html;
use frontend\assets\AppAsset;
use frontend\models\SignupForm;
use common\models\Filtervalues;
use common\models\Filter;
use yii\helpers\Url;
use conquer\toastr\ToastrWidget;
$signupModel = new SignupForm();
$siteSettings = yii::$app->Myclass->getSitesettings();
$socialLoginSettings = json_decode($siteSettings->socialLoginDetails, true);
$fbappid = $socialLoginSettings['facebook']['appid'];
Yii::$app->i18nJs;
AppAsset::register($this);
$baseUrl = Yii::$app->request->baseUrl;
$urll = Yii::$app->getUrlManager()->getBaseUrl();
?>
<?php $this->beginPage() ?>
<?php
$app_language = Yii::$app->language;
if(Yii::$app->language=="")
	$app_language = 'en';
?>
<!DOCTYPE html>
<html lang="<?= $app_language; ?>">
<head>
	<meta charset="<?= Yii::$app->charset ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="robots" content="index, follow">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<?php $metaInformation = Yii::$app->Myclass->getMetaData(); ?>
	<!-- For Facebook meta values -->
	<meta property="og:site_name" content="<?php echo $metaInformation['sitename']; ?>"/>
	<meta property="og:title" content="<?php echo isset(\Yii::$app->params['fbtitle']) ? \Yii::$app->params['fbtitle'] : $metaInformation['title']; ?>" />
	<meta property="og:type" content="product" />
	<meta property="og:url" content="<?php echo Yii::$app->request->hostInfo . Yii::$app->request->url; ?>" />
	<meta property="fb:app_id" content="<?php echo $fbappid; ?>">
	<?php if(isset(\Yii::$app->params['fbimg'])) { ?>
		<meta property="og:image" content="<?php echo Yii::$app->request->hostInfo . Yii::$app->params['fbimg']; ?>" />
		<meta property="og:image:width" content="400" />
		<meta property="og:image:height" content="300" />
		<meta name="twitter:image" content="<?php echo Yii::$app->request->hostInfo . Yii::$app->params['fbimg']; ?>">
		<meta itemprop="image" content="<?php echo Yii::$app->request->hostInfo . Yii::$app->params['fbimg']; ?>">
	<?php } ?>
	<meta property="og:description" content="<?php echo isset(\Yii::$app->params['fbdescription']) ? strip_tags(htmlspecialchars_decode(\Yii::$app->params['fbdescription'])) : $metaInformation['description']; ?>" />
	<!-- For Twitter meta values -->
	<meta name="twitter:title" content="<?php echo $metaInformation['sitename']; ?>">
	<meta name="twitter:description" content="<?php echo \Yii::$app->params['fbdescription']!='' ? strip_tags(htmlspecialchars_decode(\Yii::$app->params['fbdescription'])) : strip_tags($metaInformation['description']); ?>">
	<meta name="twitter:card" content="summary">
	<meta name="twitter:site" content="<?php echo $metaInformation['sitename']; ?>">
	<!-- For Google+ meta values -->
	<?= Html::csrfMetaTags() ?>
	<link rel="icon" href="<?php echo Yii::$app->request->baseUrl; ?>/media/logo/<?=$siteSettings['favicon']?>">
	<!--meta data title and description start-->
	<?php 
	$uri = Yii::$app->request->pathInfo;
	$uriArray = explode('/', $uri);
	//echo $uriArray[0];exit;
	$slash=' | '; 
	$sitename=$slash.$metaInformation['sitename'];
	$page_url2 = isset($uriArray[0]);
	if ($uriArray[0] === "category") {
	$page_url = (isset($uriArray[0])) ? $uriArray[0] : "";  //category
	$sub_url = (isset($uriArray[1])) ? $uriArray[1] : "";   // vehicle
	$sub_sub_url = (isset($uriArray[2])) ? $uriArray[2] : "";  // car
	$sub_sub_sub_url = (isset($uriArray[3])) ? $uriArray[3] : "";  // bmw
	$sub_cat = "";
	$sub_subcat = "";
	$subsub_cat = "";
	$categorydesc="";
	$sub_categorydesc="";
	$subsub_categorydesc="";
		/*category name with meta title*/
		if(!empty($page_url) && $sub_url==''){
			$categoryname = yii::$app->Myclass->getMetaCategoryName($page_url);
			if($categoryname==''){
				$categoryname=$page_url;
			}
			$categorydesc = yii::$app->Myclass->getMetaDesCategoryName($page_url);
			if($categorydesc==''){
				$categorydesc= $page_url;
			}
		}
		/*subcategory name with meta title*/
		else if(!empty($sub_url) && $sub_sub_url==''){
			/*category name with subcategory name with meta title*/
			$categoryname = yii::$app->Myclass->getMetaCategoryName($page_url);
			if($categoryname==''){
				$categoryname=$page_url;
			}
			$subcategoryname = yii::$app->Myclass->getMetaCategoryName($sub_url);	
			$sub_cat=$slash.$subcategoryname;
			if($subcategoryname==''){
				$sub_cat=$slash.$sub_url;
			}else{
				$sub_cat=$slash.$subcategoryname;
			}
			$categorydesc = yii::$app->Myclass->getMetaDesCategoryName($page_url);
			if($categorydesc==''){
				$categorydesc=$page_url;
			}
			$sub_categorydesc = yii::$app->Myclass->getMetadesCategoryName($sub_url);
			if($sub_categorydesc==''){
				$sub_categorydesc=$slash.$sub_url;
			}else{
				$sub_categorydesc=$slash.$sub_categorydesc;
			}
		}		
		/*subsub category name with meta title*/
		else if(!empty($sub_url)){
			/*category name with subcategory and subsub category name with meta title*/
			$categoryname = yii::$app->Myclass->getMetaCategoryName($page_url);
			if($categoryname==''){
				$categoryname=$page_url;
			}
			$subcategoryname = yii::$app->Myclass->getMetaCategoryName($sub_url);
			if($subcategoryname==''){
				$sub_cat=$slash.$sub_url;
			}else{
				$sub_cat=$slash.$subcategoryname;
			}
			$subsubcategoryname = yii::$app->Myclass->getMetaCategoryName($sub_sub_url);
			if($subsubcategoryname==''){
				$subsub_cat=$slash.$sub_sub_url;
			}else{
				$subsub_cat=$slash.$subsubcategoryname;
			}
			$categorydesc        = yii::$app->Myclass->getMetaDesCategoryName($page_url);
			$sub_categorydesc    = yii::$app->Myclass->getMetadesCategoryName($sub_url);
			$subsub_categorydesc = yii::$app->Myclass->getMetadesCategoryName($sub_sub_url);
			if($categorydesc==''){
				$categorydesc=$page_url;
			}
			if($sub_categorydesc==''){
				$sub_categorydesc=$slash.$sub_url;
			}else{
				$sub_categorydesc=$slash.$sub_categorydesc;
			}
			if($subsub_categorydesc==''){
				$subsub_categorydesc=$slash.$sub_sub_url;
			}else{
				$subsub_categorydesc=$slash.$subsub_categorydesc;
			}
		}
		if(!empty($categoryname) || !empty($sub_cat) || !empty($sub_subcat)){
			if($sub_sub_sub_url != ""){ 
			$subsubsub_title = yii::$app->Myclass->getMetaCategoryName($sub_sub_sub_url); 
			$subsubsub_desc = yii::$app->Myclass->getMetaDesCategoryName($sub_sub_sub_url); 
			if($subsubsub_title == "") $subsubsub_title = $sub_sub_sub_url; ?>
			<title><?php echo ucwords($subsubsub_title); ?></title>
			<meta name="description" content="<?php echo ucwords($subsubsub_desc); ?>">
		<?php } else 
			if($sub_sub_url != ""){ 
			$subsub_title = yii::$app->Myclass->getMetaCategoryName($sub_sub_url); 
			$subsub_desc = yii::$app->Myclass->getMetaDesCategoryName($sub_sub_url); 
			if($subsub_title == "") $subsub_title = $sub_sub_url; ?>
			<title><?php echo ucwords($subsub_title); ?></title>
			<meta name="description" content="<?php echo ucwords($subsub_desc); ?>">
		<?php } else if ($sub_url != "" && $sub_url != "allcategories") {
			$sub_title = yii::$app->Myclass->getMetaCategoryName($sub_url); 
			$sub_desc = yii::$app->Myclass->getMetaDesCategoryName($sub_url); 
			if($sub_title == "") $sub_title = $sub_url; ?>
			<title><?php echo ucwords($sub_title); ?></title>
			<meta name="description" content="<?php echo ucwords($sub_desc); ?>">
		<?php } else {?> 
			<title><?php echo ucwords($categoryname).ucwords($sub_cat).ucwords($subsub_cat).$sitename; ?></title>
			<meta name="description" content="<?php echo ucwords($categorydesc).' '.ucwords($sub_categorydesc).' '.ucwords($subsub_categorydesc).$sitename; ?>">
		<?php }
		}else{
			$category=$page_url;
			if($sub_url!=''){
				$sub_cat=$slash.$sub_url;
			}
			if($sub_sub_url!=''){
				$sub_subcat=$slash.$sub_sub_url;
			}
			?>
			<!--category with name only show-->
			<title><?php echo ucwords($category).ucwords($sub_cat).ucwords($sub_subcat).$sitename; ?></title>
			<meta name="description" content="<?php echo ucwords($page_url)."  ". ucwords($sub_url)."  ". ucwords($sub_sub_url)."  ".Html::encode(\Yii::$app->params['fbdescription']!='' ? $metaInformation['sitename']."  ".\Yii::$app->params['fbtitle'] : $metaInformation['sitename']."  ".$metaInformation['title']); ?>">
			<?php
		}
		?>
	<?php } 
//category end
//product start
	else if($uriArray[0]=='products' ){	
		if($uriArray[1]=='success' ){
			?>
			<title><?php echo Html::encode(\Yii::$app->params['fbdescription']!='' ? $metaInformation['sitename']."  ".\Yii::$app->params['fbtitle'] : $metaInformation['sitename']."  ".$metaInformation['title']); ?></title>
			<?php
		}else{
			/*$uriArray = explode('/', $uri);
			$page_url = $uriArray[4];
			$decid = yii::$app->Myclass->safe_b64decode($page_url);
			$spl = explode('-',$decid);
			$prodid = $spl[0];*/

			$uriArray = explode('/', $uri);
			$page_url = (isset($uriArray[2])) ? $uriArray[2] : ""; 
			$decid = yii::$app->Myclass->safe_b64decode($page_url);
			$spl = explode('-',$decid);
			$prodid = $spl[0];
			if($prodid!=''){
				$productname = yii::$app->Myclass->getProductDetails($prodid);
				$productmetacategoryname = yii::$app->Myclass->getProductMetaCategoryName($productname->category);
				$productmetasubcategoryname = yii::$app->Myclass->getProductMetaCategoryName($productname->subCategory);
				$productsubsubcategoryname = yii::$app->Myclass->getProductMetaCategoryName($productname->sub_subCategory);
				if($productmetacategoryname==''){
					$productcategoryname =yii::$app->Myclass->getProductCategory($productname->category);
				}else{
					$productcategoryname = yii::$app->Myclass->getProductMetaCategoryName($productname->category);
				}if($productmetasubcategoryname==''){
					$subcategoryname = yii::$app->Myclass->getProductMetaCategoryName($productname->subCategory);
					if($subcategoryname!=''){
						$sub_cat=$subcategoryname ;
					}
				}else{
					$subcategoryname = yii::$app->Myclass->getProductMetaCategoryName($productname->subCategory);
					if($subcategoryname!=''){
						$sub_cat=$subcategoryname ;
					}
				}if(($productsubsubcategoryname=='')){
					$subsubtcategoryname =yii::$app->Myclass->getProductMetaCategoryName($productname->sub_subCategory);
					if($subsubtcategoryname!=''){
						$subsub_cat=$subsubtcategoryname ;
					}
				}else{
					$subsubtcategoryname = yii::$app->Myclass->getProductCategory($productname->sub_subCategory);
					if($subsubtcategoryname!=''){
						$subsub_cat=$subsubtcategoryname;
					}
				}
			}

			if(!empty($prodid)){
				?>
				<!-- <title><?php //echo $productname->name.' '." - ".' '.$productcategoryname.' '."in".' '.$productname->location;?></title> -->
				<title><?php echo $productname->name; ?></title>
				<meta name="description" content="<?php echo "Buy".' '.$productname->name.' '."under".' '.$productcategoryname.$sub_cat.$subsub_cat.' '."from".' '.$productname->city.' '.$productname->country.' - '.$metaInformation['sitename'];?>">
				<?php
			}
		}
	}
	// "Buy".' '. $productcategoryname.$sub_cat.$subsub_cat
//end product
	else {
		$data = isset($uriArray[2]);  
		$arr = explode("=", $data, 2);
		$first = $arr[0];
		if($first=='category?search'){
			$search = $_GET['search'];
			$searchname = yii::$app->Myclass->getProductsearchname($search);
			$productcategoryname = yii::$app->Myclass->getProductCategory($searchname->category);
			$subcategoryname = yii::$app->Myclass->getProductCategory($searchname->subCategory);
			$subsubtcategoryname = yii::$app->Myclass->getProductCategory($searchname->sub_subCategory);
			if(isset($searchname->name)){
				if($search==$searchname->name){
					?>
					<title><?php echo "Buy".' '.$searchname->name.' '."-".' '.$productcategoryname.' '.$subcategoryname.$subsubtcategoryname. ' '."in".' '.$searchname->city.' '.$searchname->country;?></title>
					<meta name="description" content="<?php echo "Buy".' '.$productname->name.' '."under".' '.$productcategoryname.' '.$subcategoryname.$subsubtcategoryname. ' '."from".' '.$searchname->city.' '.$searchname->country.' - '.$metaInformation['sitename'];?>">
					<?php
				} 
			}
		}else {
			?>
			<!-- <title><?php echo Html::encode(\Yii::$app->params['fbdescription']!='' ? $metaInformation['sitename']."  ".\Yii::$app->params['fbtitle'] : $metaInformation['sitename']."  ".$metaInformation['title']); ?></title> -->
			<title><?php echo Html::encode($metaInformation['title']); ?></title>
			<meta name="description" content="<?php if($metaInformation['description']) { echo $metaInformation['description']; } ?>">
			<?php
		} 
	}
	?>
	<!--meta data title and description end-->
	<script src="https://apis.google.com/js/platform.js"></script>
	<script src="<?php echo Yii::$app->request->baseUrl; ?>/js/jquery.js"></script> 
	<script src="<?php echo Yii::$app->request->baseUrl; ?>/js/anchorme.min.js"></script> 
	<script src="<?php echo Yii::$app->request->baseUrl; ?>/js/jquery.slimscroll.min.js"></script>
	<link rel="stylesheet" href="<?php echo Yii::$app->request->baseUrl; ?>/css/jquery/1.12.1/jquery-ui.css">
	<link rel="stylesheet" href="<?php echo Yii::$app->request->baseUrl; ?>/css//braintreeformresp.css">
	<?= Html::csrfMetaTags() ?>
	<?php $this->head() ?>
</head>
<body>
	<?php $this->beginBody() ?>
	<?php $sitePaymentModes = yii::$app->Myclass->getSitePaymentModes(); ?>
	<div id="wrapper">
		<aside data-pushbar-id="left" class="pushbar from_left">
			<div class="examples">
				<div id="testDiv">
					<div class="sideMenu">
						<div class="classified-mobile-Category-catgr">Category
							<button class="close push_right margin-right-25" type="button" data-pushbar-close>×</button>
						</div>
						<script>
//This baseUrl is used in front.js and nodeClient.js
var baseUrl = "<?php echo $urll; ?>";
</script>
<?php if (!empty(Yii::$app->user->id)) { ?>
	<div id="sidebar-wrapper" class="sidebar-wraper">
	<?php } else { ?>
		<div id="sidebar-wrapper">
		<?php } ?>
		<div class="sidebar-menu-listng">
			<ul class="nav navbar-nav sidebar-nav d-flex flex-column">
				<!-- Hot fixes -->
				<?php $categorypriority = yii::$app->Myclass->getCategoryPriority(); ?>
				<?php foreach ($categorypriority as $key => $category) :
					if ($category != "empty") {
						$getcatdet = yii::$app->Myclass->getCatDetails($category);
						$getcatimage = yii::$app->Myclass->getCatImage($category);
						$subCategory = yii::$app->Myclass->getSubCategory($category);
						?> 
						<!-- mobile category view-->
						<li class="dropdown  main-mobile-menu">
							<?php if (!empty($subCategory)) {  ?>
								<a class="dropdown-toggle" data-toggle="dropdown" href="#"  style="background:url(<?php echo Yii::$app->urlManagerBackEnd->baseUrl . '/uploads/' . $getcatimage; ?>) no-repeat scroll 18px 17px / 32px auto; " >
									<?php 
								} else { 
									?>
									<a class="dropdown-toggle" href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('/category/' . $getcatdet->slug); ?>" style="background:url(<?php echo Yii::$app->urlManagerBackEnd->baseUrl . '/uploads/' . $getcatimage; ?>) no-repeat scroll 18px 17px / 32px auto; " >
										<?php 
									}
									?>
									<span><?php echo Yii::t('app', $getcatdet->name); ?></span>
									<?php if (!empty($subCategory)) { ?>
										<i class="angle-down"></i>
									<?php } ?>
								</a>
								<?php if (!empty($subCategory)) { ?>
									<ul class="dropdown-menu mainmenu">
										<?php foreach ($subCategory as $key => $subCategory) :
											$subCatdet = yii::$app->Myclass->getCatDetails($key);
											$childCategory = Yii::$app->Myclass->getSubCategory($subCatdet->categoryId);  
											?>
											<?php if(count($childCategory) > 0){ ?>
												<li class="below_menu">
													<a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('/category/' . $getcatdet->slug . '/' . $subCatdet->slug); ?>"><div class="downarrow">
														<span><?php echo Yii::t('app', $subCategory); ?> </span></div></a>
														<input type="checkbox" class="sub-menu-checkbox" class="settings" />
														<ul class="sub-menu-dropdown">
															<?php
															foreach ($childCategory as $key => $childCategory) :
																$childCatdet = Yii::$app->Myclass->getCatDetails($key);
																?>
																<li><a  href="<?php echo $baseUrl . '/category/' . $getcatdet->slug . '/' . $subCatdet->slug. '/'.$childCatdet->slug; ?>"><?php echo Yii::t('app', $childCategory); ?></a></li>
															<?php endforeach; ?>
															<li><a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('/category/' . $getcatdet->slug . '/' . $subCatdet->slug); ?>"><?php echo Yii::t('app','View all');?></a></li>
														</ul>
													</li>
												<?php } else{ ?>
													<li><a
														href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('/category/' . $getcatdet->slug . '/' . $subCatdet->slug); ?>"><?php echo Yii::t('app', $subCategory); ?> </a></li>
													<?php } ?>
												<?php endforeach; ?>
												<li><a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('/category/' . $getcatdet->slug); ?>"><?php echo Yii::t('app','View all');?></a></li>
											</ul>
										<?php } ?>
									</li>
								<?php } 
							endforeach; ?>
						</ul>
					</div>
					<?php if (!empty(Yii::$app->user->id)) { ?>
						<div class="mobile-user-area flex-column">
							<?= Html::a(Yii::t('app', 'Sell your stuff'), ['products/create'], ['class' => 'classified-stuff-mob']) ?>
							<?= Html::a(Yii::t('app', 'Profile'), ['/user/profiles'], ['class' => 'classified-account']) ?>
							<?php if ($siteSettings['promotionStatus'] == 1) { ?>
								<?= Html::a(Yii::t('app', 'My Promotions'), ['user/promotions', 'type' => 'urgent'], ['class' => 'classified-exchange']) ?>
								<?php 
							} ?>
							<?php if ($sitePaymentModes['exchangePaymentMode'] == 1) { ?>
								<?= Html::a(Yii::t('app', 'My Exchanges'), ['user/exchanges', 'type' => 'incoming'], ['class' => 'classified-exchange']) ?>
								<?php 
							} ?>
							<?php if ($sitePaymentModes['buynowPaymentMode'] == 1) { ?>
								<?= Html::a(Yii::t('app', 'My Orders and Sales'), ['buynow/orders', 'type' => 'orders'], ['class' => 'classified-exchange']) ?>
								<?php 
							} ?>
							<?= Html::a(Yii::t('app', 'Logout'), ['site/logout'], ['class' => 'classified-logout']) ?>
						</div>
					<?php } else { ?>
						<div class="mobile-user-area">
							<div data-toggle="modal" data-target="#login-modal" class="primary-bg-color txt-white-color border-radius-5"><?php echo Yii::t('app', 'Login'); ?></div>
							<div data-toggle="modal" data-target="#signup-modal" class="classified-signup  txt-white-color border-radius-5"><?php echo Yii::t('app', 'Sign up'); ?></div>
						</div> 
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>
</aside>
</div>
<?= $this->render('/site/header', ['signupModel' => $signupModel]) ?> 
<!--Login modal-->
<!--E O signup modal-->
<!--Forgot password--> 
<div class="modal fade" id="forgot-password-modal" role="dialog">
	<div class="modal-dialog modal-dialog-width">
		<div class="login-modal-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
			<div class="login-modal-header col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
				<h2 class="forgot-header-text">Forgot Password</h2>
				<button data-dismiss="modal" class="close login-close" type="button">×</button>
				<p class="forgot-sub-header-text">Enter your email address and we'll send you a link to reset your password.</p>
			</div>
			<div class="forgot-line col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"></div>
			<div class="forgot-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding ">
				<div class="forgot-box col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
					<div class="forgot-text-box col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
						<form onsubmit="return validforgot()" id="forgetpassword-form" action="/forgotpassword" method="post">									<input class="forgetpasswords popup-input forget-input" placeholder="Enter your email address" name="Users[email]" id="Users_email" type="text" maxlength="150" />									<div class="errorMessage" id="Users_emails_em_" style="display:none"></div>									<input class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding forgot-btn" style="margin-top:10px;" type="submit" name="yt3" value="Reset Password" />									</form>									
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="classified-search-bar-bg">
	<div class="container-fluid">
		<div class="app-responsive-adjust"></div>
		<div class="classified-search-bar-mobile col-xs-12 col-sm-12 col-md-12 no-hor-padding form-group search-input-container">
			<form role="form" class="navbar-form- navbar-left- search-form" style="padding-left: 0;" action="<?= Yii::$app->getUrlManager()->getBaseUrl(); ?>"
				method="get">
				<a href="" class="classified-icon-mobile input-search" data-toggle="modal" data-target="#search-mobile-cal">Search products</a>
			</form>
		</div>
	</div>
</div>
<div class="classified-menu" >
	<div class="container-fluid">
		<div class="row" style="height: 64px;"></div>
		<div class="row">
			<div class="gnbar" style="display: none;">
				<button id="gnbar__toggle--nav" class="gnbar__toggle gnbar__toggle--nav animated-hamburger animated-hamburger--skewed"
				aria-label="Toggle sitewide navigation">
				<span class="animated-hamburger__child"></span>
			</button>
			<button id="gnbar__toggle--search" class="gnbar__toggle gnbar__toggle--search animated-search" aria-label="Toggle sitewide search"></button>
		</div>
		<div class="gnbar__placeholder hide-on-desktop"></div>
		<nav id="gn" class="gn global-dropdown">
			<div class="gn__group gn__group--secondary">
				<button id="gn__label--search"></button>
			</div>
		</nav>
		<div class="global-dropdown__overlay"></div>
		<div class="gs global-dropdown"></div>
		<nav id="sn-bar" class="sn-bar sn-bar--transparent sn-bar--scrollable">
			<div id="sn-bar__outer-wrap" class="sn-bar__outer-wrap">
				<div id="sn-bar__heading" class="sn-bar__heading"></div>
				<ul id="sn-bar__inner-wrap" class="sn-bar__inner-wrap">
					<li class="sn-bar__item sn-bar__menu-item sn-bar__menu-item--has-children" tabindex="0">
						<span class="sn-bar__link sn-bar__link--has-dropdown">
							<a class="dropdown-toggle bold classified-for-sale disabled" data-toggle="dropdown"
							href="<?php echo $baseUrl . '/category/allcategories'; ?>" style=" no-repeat scroll left center / 32px auto; ">
							<?php echo Yii::t('app', 'All Categories'); ?></a>
						</span>
					</li>
					<?php $categorypriority = Yii::$app->Myclass->getCategoryPriority(); ?>
					<?php 
					foreach ($categorypriority as $key => $category) :
						if ($category != "empty") {
							$getcatdet = Yii::$app->Myclass->getCatDetails($category);
							$getcatimage = Yii::$app->Myclass->getCatImage($category);
							$subCategory = Yii::$app->Myclass->getSubCategory($category); 
							?> 
							<li class="sn-bar__item sn-bar__menu-item sn-bar__menu-item--has-children" tabindex="0">
								<span class="sn-bar__link sn-bar__link--has-dropdown">
									<a class="dropdown-toggle bold classified-for-sale disabled" data-toggle="dropdown"
									href="<?php echo $baseUrl . '/category/' .strtolower($getcatdet->slug); ?>" style="background:url(<?php echo Yii::$app->urlManagerBackEnd->createUrl('/uploads/' . $getcatimage); ?>) no-repeat scroll left center / 32px auto; "><?php echo Yii::t('app', $getcatdet->name); ?></a>
								</span>
								<?php if (!empty($subCategory)) { ?>
									<ul class="sn-bar__dropdown dropdown-menu classified-dropdown-submenu">
										<?php foreach ($subCategory as $key => $subCategory) :
											$subCatdet = Yii::$app->Myclass->getCatDetails($key);
											$childCategory = Yii::$app->Myclass->getSubCategory($subCatdet->categoryId);  
											if(count($childCategory) > 0)
												{ ?>
													<li class="listsubmenu">
														<a class="sn-bar__link bold" href="<?php echo $baseUrl . '/category/' . strtolower($getcatdet->slug) . '/' . strtolower($subCatdet->slug); ?>">
															<div class="subcate">
																<span><?php echo Yii::t('app', $subCategory); ?></span>
																<?php if (isset($_SESSION['language']) && ($_SESSION['language'] == 'ar')){?>
																	<i class="fa fa-caret-left arrow_cart"></i> 
																<?php } else {?>
																	<i class="fa fa-caret-right arrow_cart"></i> 
																<?php } ?>
															</div>
														</a>
														<ul class="dropdown-menu"
														aria-labelledby="navbarDropdownMenuLink">
														<li class="dropdown-submenu">
															<?php
															foreach ($childCategory as $key => $childCategory) :
																$childCatdet = Yii::$app->Myclass->getCatDetails($key);
																?>
																<a class="dropdown-item dropdown-toggle bold" href="<?php echo $baseUrl . '/category/' .  strtolower($getcatdet->slug ). '/' .  strtolower($subCatdet->slug ). '/'. strtolower($childCatdet->slug); ?>"><?php echo Yii::t('app', $childCategory); ?></a>
															<?php endforeach; ?>
														</li>
													</ul>
												</li>
											<?php	}else{
												?>
												<li>
													<a class="sn-bar__link bold" href="<?php echo $baseUrl . '/category/' . strtolower($getcatdet->slug). '/' . strtolower($subCatdet->slug); ?>"><?php echo Yii::t('app', $subCategory); ?></a>
												</li>
											<?php } ?>
										<?php endforeach; ?>
									</ul>
								<?php } ?>
							</li>
						<?php } 
					endforeach;
					?>
				</ul>
			</div>
			<button id="sn-bar__scroll--left" class="sn-bar__scroll sn-bar__scroll--left hide"
			style="left: 106px;">‹</button>
			<button id="sn-bar__scroll--right" class="sn-bar__scroll sn-bar__scroll--right ">›</button>
		</nav>
		<div class="sn-bar__placeholder"></div>
	</div>
</div>
</div>
<div class="modal fade" id="confirm_popup_container" role="dialog" aria-hidden="true">
	<div id="confirm-popup" class="modal-dialog modal-dialog-width confirm-popup">
		<div class="login-modal-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
			<div class="login-modal-header col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
				<h2 class="login-header-text"><?= Yii::t('app', 'Confirm') ?></h2>
			</div>
			<div class="login-line col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"></div>
			<div class="login-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding ">
				<span class="delete-sub-text col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
					<?= Yii::t('app', 'Are you sure you want to delete ?'); ?>	
				</span>
				<a class="margin-bottom-0 delete-btn margin-10" href="#" onclick="closeConfirm()">
					<?= Yii::t('app', 'cancel') ?></a>
					<span class="confirm-btn">
						<a class="margin-bottom-0 post-btn" href="#" onclick="closeConfirm()">
							<?= Yii::t('app', 'ok') ?></a>
						</span>
					</div>
				</div>
			</div>
		</div> 
		<div class="modal fade" id="reportconfirm_popup_container" role="dialog" aria-hidden="true">
			<div id="reportconfirm-popup" class="modal-dialog modal-dialog-width confirm-popup">
				<div class="login-modal-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
					<div class="login-modal-header col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
						<h2 class="login-header-text"><?= Yii::t('app', 'Confirm') ?></h2>
					</div>
					<div class="login-line col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"></div>
					<div class="login-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding ">
						<span class="delete-sub-text col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
							<?= Yii::t('app', 'Report this as inappropriate or broke ?'); ?>	
						</span>
						<a class="margin-bottom-0 delete-btn margin-10" href="#" id="reportflagcancel" data-dismiss="modal" >
							<?= Yii::t('app', 'cancel') ?></a>
							<span class="confirm-btn">
								<a class="margin-bottom-0 post-btn" href="#" id="reportflagok" data-dismiss="modal">
									<?= Yii::t('app', 'ok') ?></a>
								</span>
							</div>
						</div>
					</div>
				</div> 
				<div class="modal fade" id="undoreportconfirm_popup_container" role="dialog" aria-hidden="true">
					<div id="undoreportconfirm-popup" class="modal-dialog modal-dialog-width confirm-popup">
						<div class="login-modal-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
							<div class="login-modal-header col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
								<h2 class="login-header-text"><?= Yii::t('app', 'Confirm') ?></h2>
							</div>
							<div class="login-line col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"></div>
							<div class="login-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding ">
								<span class="delete-sub-text col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
									<?= Yii::t('app', 'Cancel Report this ?'); ?>	
								</span>
								<a class="margin-bottom-0 delete-btn margin-10" href="#" id="undoreportflagcancel" data-dismiss="modal" >
									<?= Yii::t('app', 'cancel') ?></a>
									<span class="confirm-btn">
										<a class="margin-bottom-0 post-btn" href="#" id="undoreportflagok" data-dismiss="modal">
											<?= Yii::t('app', 'ok') ?></a>
										</span>
									</div>
								</div>
							</div>
						</div> 
						<div class="modal fade" id="cancelorderconfirm_popup_container" role="dialog" aria-hidden="true">
							<div id="cancelorderconfirm-popup" class="modal-dialog modal-dialog-width confirm-popup">
								<div class="login-modal-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
									<div class="login-modal-header col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
										<h2 class="login-header-text"><?= Yii::t('app', 'Confirm') ?></h2>
									</div>
									<div class="login-line col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"></div>
									<div class="login-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding ">
										<span class="delete-sub-text col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
											<?= Yii::t('app', 'Cancel the order ?'); ?>	
										</span>
										<a class="margin-bottom-0 delete-btn margin-10" href="#" id="cancelorderflagcancel" data-dismiss="modal" >
											<?= Yii::t('app', 'cancel') ?></a>
											<span class="confirm-btn">
												<a class="margin-bottom-0 post-btn" href="#" id="cancelorderflagok" data-dismiss="modal">
													<?= Yii::t('app', 'ok') ?></a>
												</span>
											</div>
										</div>
									</div>
								</div> 
								<div class="modal fade" id="mobundoreportconfirm_popup_container" role="dialog" aria-hidden="true">
									<div id="mobundoreportconfirm-popup" class="modal-dialog modal-dialog-width confirm-popup">
										<div class="login-modal-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
											<div class="login-modal-header col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
												<h2 class="login-header-text"><?= Yii::t('app', 'Confirm') ?></h2>
											</div>
											<div class="login-line col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"></div>
											<div class="login-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding ">
												<span class="delete-sub-text col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
													<?= Yii::t('app', 'Cancel Report this ?'); ?>	
												</span>
												<a class="margin-bottom-0 delete-btn margin-10" href="#" id="mobundoreportflagcancel" data-dismiss="modal" >
													<?= Yii::t('app', 'cancel') ?></a>
													<span class="confirm-btn">
														<a class="margin-bottom-0 post-btn" href="#" id="mobundoreportflagok" data-dismiss="modal">
															<?= Yii::t('app', 'ok') ?></a>
														</span>
													</div>
												</div>
											</div>
										</div> 
										<div class="modal fade" id="mobreportconfirm_popup_container" role="dialog" aria-hidden="true">
											<div id="mobreportconfirm-popup" class="modal-dialog modal-dialog-width confirm-popup">
												<div class="login-modal-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
													<div class="login-modal-header col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
														<h2 class="login-header-text"><?= Yii::t('app', 'Confirm') ?></h2>
													</div>
													<div class="login-line col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"></div>
													<div class="login-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding ">
														<span class="delete-sub-text col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
															<?= Yii::t('app', 'Report this as inappropriate or broke ?'); ?>	
														</span>
														<a class="margin-bottom-0 delete-btn margin-10" href="#" id="mobreportflagcancel" data-dismiss="modal" >
															<?= Yii::t('app', 'cancel') ?></a>
															<span class="confirm-btn">
																<a class="margin-bottom-0 post-btn" href="#" id="mobreportflagok" data-dismiss="modal">
																	<?= Yii::t('app', 'ok') ?></a>
																</span>
															</div>
														</div>
													</div>
												</div> 
												<?= $content ?>
												<input type="hidden" name="app_language" id="app_language" value="<?= $app_language ?>">
												<input type="hidden" name="formatted_address" id="formatted_address" >
												<input type="hidden" name="latitude" id="latitude" >
												<input type="hidden" name="longitude" id="longitude" >
												<?php if (Yii::$app->session->hasFlash('warning')) : ?>
													<?= ToastrWidget::widget([
														'type' => 'warning', 'message' => Yii::$app->session->getFlash('warning'),
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
												<div class="backtop">
													<a id="back2Top" title="<?= Yii::t('app', 'Back to top') ?>" href="#"><span><?= Yii::t('app', 'Back to top') ?></span></a>
												</div>
												<!-- End back to top -->
												<?= $this->render('//site/footer') ?> 
												<?php $this->endBody() ?>
												<?php $this->endPage() ?>
												<script>
													$("#menu-toggle").click(function(e) {
														e.preventDefault();
														$("#wrapper").toggleClass("toggled");
														$("#wrapper.toggled").css("display", "block");
														$("body").toggleClass("scroll-hidden");
													});
												</script>
												<style>
													.scroll-hidden{
														overflow:hidden;
													}
												</style>
												<!-- Sticky menu --> 
												<script>
													$(window).scroll(function() {
														var scroll = $(window).scrollTop();
														var headerHeightTrack = ($('.classified-menu').height() - 64);

														if (scroll >= headerHeightTrack) {
															$(".classified-header").addClass("affix");
														} else {
															$(".classified-header").removeClass("affix");
														}
													});
												</script>
												<script type="text/javascript">
													/*Scroll to top when arrow up clicked BEGIN*/
													$(window).scroll(function() {
														var height = $(window).scrollTop();
														if (height > 500) {
															$('#back2Top').fadeIn();
														} else {
															$('#back2Top').fadeOut();
														}
													});
													$("#back2Top").click(function(event) {
														event.preventDefault();
														$("html, body").animate({ scrollTop: 0 }, "slow");
														return false;
													});
// });
/*Scroll to top when arrow up clicked END*/
</script>
<!-- page -->
<style type="text/css">
	.flashes{
		-webkit-transition: all 3s ease-out;
		-moz-transition: all 3s ease-out;
		-ms-transition: all 3s ease-out;
		-o-transition: all 3s ease-out;
		transition: all 3s ease-out;
	}
	.move{
		position: absolute;
		-webkit-transition: all 3s ease-out;
		-moz-transition: all 3s ease-out;
		-ms-transition: all 3s ease-out;
		-o-transition: all 3s ease-out;
		transition: all 3s ease-out;
	}
</style>
<script>
	$(document).keyup(function(e) {
		if (e.keyCode === 27){
			if ($('#login-modal').css('display') == 'block'){
				$('#login-modal').modal('hide');
			}
			if ($('#signup-modal').css('display') == 'block'){
				$('#signup-modal').modal('hide');
			}
			if ($('#forgot-password-modal').css('display') == 'block'){
				$('#forgot-password-modal').modal('hide');
			}
			if ($('#nearmemodals').css('display') == 'block'){
				$('#nearmemodals').modal('hide');
			}
			if ($('#post-your-list').css('display') == 'block'){
				$('#post-your-list').modal('hide');
			}
			if ($('#mobile-otp').css('display') == 'block'){
				$('#mobile-otp').modal('hide');
			}
			if ($('#chat-with-seller-success-modal').css('display') == 'block'){
				$('.modal').modal('hide');
				$('#chat-with-seller-success-modal').css('display','none');
			}
			if ($('#offer-success-modal').css('display') == 'block'){
				$('.modal').modal('hide');
				$('#offer-success-modal').css('display','none');
			}
		}
	});
	var loginSession = readCookie('PHPSESSID');
	setTimeout(function() {
		$('.flashes').fadeOut('fast');
	}, 3000);
	function readCookie(name) {
		var nameEQ = escape(name) + "=";
		var ca = document.cookie.split(';');
		for (var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) === ' ') c = c.substring(1, c.length);
			if (c.indexOf(nameEQ) === 0) return unescape(c.substring(nameEQ.length, c.length));
		}
		return null;
	}
	if (typeof timerId != 'undefined'){
		clearInterval(timerId);
	}
	var timerId = setInterval(function() {
		var currentSession = readCookie('PHPSESSID');
		if(loginSession != currentSession) {
			window.location = '<?= Yii::$app->getUrlManager()->getBaseUrl(); ?>';
			clearInterval(timerId);
		}
	},1000);
</script>
<?php 
$lat = Yii::$app->session['latitude'];
$lon = Yii::$app->session['longitude'];
?>
<script> 
	function mapinitialize()
	{

		var input = document.getElementById('pac-input');
		var autocomplete = new google.maps.places.Autocomplete(input);

		autocomplete.addListener('place_changed', function() {
			var place = autocomplete.getPlace();
			var address = place.formatted_address;
			var latitude = place.geometry.location.lat();
			var longitude = place.geometry.location.lng();
			document.getElementById("map-latitude").value = latitude;
			document.getElementById("map-longitude").value = longitude;
		});
	}

	function mapinitialize1()
	{
		var input = document.getElementById('pac-input2');
		var autocomplete = new google.maps.places.Autocomplete(input);
		autocomplete.bindTo('bounds', map);
		autocomplete.addListener('place_changed', function() {
			var place = autocomplete.getPlace();
			var address = place.formatted_address;
			var latitude = place.geometry.location.lat();
			var longitude = place.geometry.location.lng();
			document.getElementById("map-latitude").value = latitude;
			document.getElementById("map-longitude").value = longitude;
		});
	}

	var mapStickyTrack;
	var bannerdetails = 0;
	var userdetails = 0;
	$(document).ready(function(){
		$(window).on('load resize', function () {
			if($(window).width() >= 1024){
				if(userdetails == 0)
					mapStickyTrack = $('.classified-menu').height() + $('.classified-app-download').height();
				else
					mapStickyTrack = $('.classified-menu').height();
			}else{
				if(userdetails == 0)
					mapStickyTrack = $('.classified-app-download').height();
				else
					mapStickyTrack = $('.classified-header').height();
			}
			if(bannerdetails == 1)
			{
				mapStickyTrack = $('.classified-header').height() + $("#myCarousel").height() + 50;
			}
			mapStickyTrack -= 56;
		});

		$(window).on('scroll', function () {
			if ($(window).scrollTop() >= mapStickyTrack) {
				$('.find-near-you').addClass('map-menu-fixed');
			} else {
				$('.find-near-you').removeClass('map-menu-fixed');
			}
		});
	});
</script>
<script type="text/javascript">
	var loadflag = 0;
</script> 
<script type="text/javascript" charset="utf-8">
	distancelimit = '<?php echo $siteSettings->searchList; ?>'/2;
	jQuery("#Sliders2").slider({ from: 1, to: <?php echo $siteSettings->searchList; ?>, step: 1, dimension: '&nbsp;<?php
		if($siteSettings->searchType=="kilometer") echo "Km"; else echo "Mi"; ?>&nbsp;',
		callback: function (value) {
			getLocationData();
		}, });
	</script>
	<script type="text/javascript" charset="utf-8">
		jQuery("#Sliders3").slider({
			from: 1, 
			to: <?php echo $siteSettings->searchList;?>, 
			step: 1, 
			dimension: '&nbsp;<?php echo $siteSettings->searchType;?>',
		});
	</script>
	<script type="text/javascript">  	
		$( document ).ready(function() {
			$("#SliderPrice").val("0;10000");
			$("#SliderPriceSM").val("0;10000");
		});  
	</script>
	<script type="text/javascript" charset="utf-8">
		if($('body').css("direction") == "rtl") { 
			jQuery("#SliderPrice").slider({ 
				from: 0, to: 5000, step: 10, dimension: '&nbsp;',
				callback: function (value) {
					$("#SliderPrice").val(value);
					var value = value.split(";");
					if (value[0]== 0 && value[1]>=5000) {
						$(".SliderPriceCol .SliderPrice_min_range").html('0 - 5000+');	
					}
					else
					{
						$(".SliderPriceCol .SliderPrice_min_range").html(value[0] + '-' + value[1]);
// $(".SliderPriceCol .SliderPrice_max_range").html(value[1]);	
}
getLocationData();
}, 
});
		}else
		{
			jQuery("#SliderPrice").slider({ 
				from: 0, to: 5000, step: 1, dimension: '&nbsp;',
				callback: function (value) {
					$("#SliderPrice").val(value);
					var value = value.split(";");
					if (value[0]== 0 && value[1]>=5000) {
						$(".SliderPriceCol .SliderPrice_min_range").html('0 - 5000+');	
					}
					else
					{
						$(".SliderPriceCol .SliderPrice_min_range").html(value[0] + '-' + value[1]);	
					}
					getLocationData();
				}, 
			});
		}
	</script>

	<?php
	if(isset($_SESSION['rangevaluesarray']) && trim($_SESSION['rangevaluesarray'])!="") 
	{
		$filterValues = explode(',', $_SESSION['rangevaluesarray']);
		foreach($filterValues as $key => $val)
		{ 
			$filterVals = Filtervalues::find()->where(['id'=>$val])->one();
			if($filterVals){
				$filterRangevalue = Filter::find()->where(['id'=>$filterVals->filter_id])->one();
				$rangeVal_id = $filterVals->filter_id;
				$range_id = $filterVals->id;
				$rangeVal_name = $filterRangevalue->name;
				$rangeVal_range = $filterRangevalue->value;
				$splitVals = explode(';', $rangeVal_range);
				$filterIdwithname = strtolower(str_replace(' ', '_', $rangeVal_name)).'_'.$range_id;
				?>
				<script type="text/javascript">
					var jsonArg2 = new Object();
					jsonArg2.id = "<?= $rangeVal_id; ?>";
					jsonArg2.value = "<?= $rangeVal_range; ?>";
					var json2Array = JSON.stringify(jsonArg2);
					$('#sliderhiddenattribute_<?= $range_id; ?>').val(json2Array);
					jQuery("#<?= $filterIdwithname; ?>").slider({ from: <?= $splitVals[0]; ?>, to: <?= $splitVals[1]; ?>,format: { format: "###0.##" }, step: 1, dimension: '&nbsp;',callback: function (value) {
						jQuery("#<?= $filterIdwithname; ?>").val(value);
						var jsonArg1 = new Object();
						jsonArg1.id = <?= $rangeVal_id; ?>;
						jsonArg1.value = value;
						var value = value.split(";");
						var jsonArray = JSON.stringify(jsonArg1);
						$("#sliderhiddenattribute_min_range_<?= $filterIdwithname; ?>").html(value[0]);
						$("#sliderhiddenattribute_max_range_<?= $filterIdwithname; ?>").html(value[1]);	
						$('#sliderhiddenattribute_<?= $range_id; ?>').val(jsonArray);
					}, 
				}); 
			</script>
			<?php
		}
	}
}
?>
<script type="text/javascript" charset="utf-8">
	var gethiddenval = $('#rangevaluesarray').val();
	jQuery.ajax({
		url: baseUrl+'/products/getrangefilter/',
		type: "POST",
		dataType: "html",
		data: { 'rangevalues': gethiddenval },
		success: function (responce) {
			jQuery.each(JSON.parse(responce), function(id, obj) {
				var rangval = obj.value;
				if(typeof rangval != "undefined")
				{
					var value = rangval.split(";");
					jQuery("#"+obj.name).slider({ from: value[0], to: value[1], step: 1, dimension: '&nbsp;',callback: function (value) {
						console.log('success');
						var value = value.split(";");
						$("#sliderhiddenattribute_"+obj.name).val(value);
						$("#rangevalues").val(value[1]);
					}, });
				}
			});
		}
	});
	jQuery("#SliderPriceSM").slider({ from: 0, to: 5000, step: 1, dimension: '&nbsp;',
		callback: function (value) {
			$("#SliderPriceSM").val(value);
			var value = value.split(";");
			if (value[1]>4999) {
				$(".SliderPriceSM .jslider-value-to > span").html('5,000+');
			}
			getLocationDatamobile();
		}, 
	});
</script>
<script type="text/javascript" charset="utf-8">
	$('#flat-slider').slider({
		orientation: 'horizontal',
		range:       true,
		values:      [17,67]
	});
</script>
<script type="text/javascript">
	jQuery(function($) {
		jQuery('body').on('click','#yt0',function(){jQuery.ajax({'beforeSend':function(){$(".more-listing").hide();$(".classified-loader").show();$("#ugnt_load").html("");},'data':{"limit": limit, "offset": offset, "loadData": 1,"adsOffset": adsoffset,"urgent": urgent,"ads": ads, "PriceValue": PriceValue},'success':function(response){
			var grid = document.querySelector("#fh5co-board");
			$(".more-listing").show();$(".classified-loader").hide();
			var output = response.trim();
			var contentData = eval($.trim(output));
			loadflag++;
			if (output) {
				offset = offset + limit;
				adsoffset = adsoffset + 10;
				for(var i = 0; i < contentData.length; i++){
					var item = document.createElement("div");
					salvattore["append_elements"](grid, [item]);
					item.outerHTML = contentData[i];
				}
				$("#ugnt_load").html(" ");
				$(".imgcls").load(function () {
					$(".imgcls").addClass("hgtremoved");
				});
			} else {
				$(".classified-loader").hide();
				$(".more-listing").hide();
			}
		},'url':'/site/loadresults/?search=&category=&subcategory=&lat=&lon=','cache':false});return false;});
	});
</script>
<script> 
	function initMap() {
		var lat = "<?php echo Yii::$app->session['latitude']; ?>";
		var lon = "<?php echo Yii::$app->session['longitude']; ?>";
		document.getElementById('pac-input').onkeyup = function(){
			var local=document.getElementById('pac-input').value;
			if(local.length >=2)
			{
				$local_val=document.getElementById('pac-input');
				var autocomplete = new google.maps.places.Autocomplete(($local_val), {
					types : [ 'geocode' ]
				});
				autocomplete.addListener('place_changed', function() {
					var place = autocomplete.getPlace();
					var latitude = place.geometry.location.lat();
					var longitude = place.geometry.location.lng();
					var placeDetails = place.address_components;
					var count = placeDetails.length;
					var country = "";
					while(count != 0 && country == ""){
						if(placeDetails[count-1].types[0] == "country"){
							country = placeDetails[count-1].short_name;
							$('#shippingcountry').val(country);
						}
						count--;
					}
					$("#map-latitude").val(latitude);
					$("#map-longitude").val(longitude);
				});
			}
			else{
				google.maps.event.clearInstanceListeners($local_val);
				$(".pac-container").remove();
			}
		}
		document.getElementById('pac-input2').onkeyup = function(){
			var local2=document.getElementById('pac-input2').value;
			if(local2.length >=3)
			{
				$local_val2=document.getElementById('pac-input2');
				var autocomplete = new google.maps.places.Autocomplete(($local_val2), {
					types : [ 'geocode' ]
				});
				autocomplete.addListener('place_changed', function() {
					var place = autocomplete.getPlace();
					var latitude = place.geometry.location.lat();
					var longitude = place.geometry.location.lng();
					var placeDetails = place.address_components;
					var count = placeDetails.length;
					var country = "";
					while(count != 0 && country == ""){
						if(placeDetails[count-1].types[0] == "country"){
							country = placeDetails[count-1].short_name;
							$('#shippingcountry').val(country);
						}
						count--;
					}
					$("#map-latitude").val(latitude);
					$("#map-longitude").val(longitude);
				});
			}
			else{
				google.maps.event.clearInstanceListeners($local_val2);
				$(".pac-container").remove();
			}
		}			
	}
</script>
<?php
if (!empty($siteSettings) && isset($siteSettings->googleapikey) && $siteSettings->googleapikey != "")
	$googleapikey = $siteSettings->googleapikey;
else
	$googleapikey = "";
?>
<?php if (isset($_SESSION['language']) && ($_SESSION['language'] == 'ar')){?>
	<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $googleapikey; ?>&libraries=places&callback=initMap&language=ar"></script>
<?php }else {?>
	<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $googleapikey; ?>&libraries=places&callback=initMap&language=en"></script>
<?php } ?>
<!-- for autocomplete -->
<script src="<?php echo Yii::$app->getUrlManager()->getBaseUrl(); ?>/js/jquery/1.12.1/jquery-ui.js"></script>
<script>
</script>
<script>
	$.ajaxSetup({
		data: <?= \yii\helpers\Json::encode([
			\yii::$app->request->csrfParam => \yii::$app->request->csrfToken,
		]) ?>
	});
</script>
<script>
	$(".SliderPriceCol .jslider-value-to > span").html('5,000+');
</script>
<style type="text/css">
	.toast{
		opacity: 1!important;
	}
	.toast-success{
		background-color: #fff!important;
	}
</style>
<?php  if(Yii::$app->controller->id=='site' && Yii::$app->controller->action->id=='index') { ?>
	<script>
		$(document).ready(function(){	
			initialLoad = typeof initialLoad !== 'undefined' ? initialLoad : 0;
			var baseurl = baseUrl;
			var grid = document.querySelector('#fh5co-board');
			var geocoder = new google.maps.Geocoder();
			var kilometer = 25;
			var lat;
			var lon;
			$('.search-location').hide();
			$('.btn-worldwide').hide();
			$('.loading-btn').show();
			$('.loader-front').hide();
			$('.loader-back').show();
			if(reload == 0) {
				if(navigator.geolocation) 
				{
					navigator.geolocation.getCurrentPosition(function(position) {
						var pos = new google.maps.LatLng(position.coords.latitude,
							position.coords.longitude);
						lat = pos.lat();
						lon = pos.lng();
						if (initialLoad == 0) {
							var geocoder = new google.maps.Geocoder();
							var infowindow = new google.maps.InfoWindow;
							var latlngs = new google.maps.LatLng(lat, lon);	
							var latlng = {lat: parseFloat(lat), lng: parseFloat(lon)};
							let formatted_address= "";
							geocoder.geocode({'latLng': latlng}, function(results, status) {
								if (status == google.maps.GeocoderStatus.OK) {
									if (results) {

										var area_array = results[0].address_components.filter(function(address_component){
											return address_component.types.includes("administrative_area_level_2");
										}); 

										formatted_address += area_array.length ? area_array[0].long_name: "" ;


										var locality_array = results[0].address_components.filter(function(address_component){
											return address_component.types.includes("locality");
										}); 

										if(area_array.length === 0){
											formatted_address += locality_array.length ? locality_array[0].long_name: "" ;
										}

										var area_level_array = results[0].address_components.filter(function(address_component){
											return address_component.types.includes("administrative_area_level_1");
										}); 

										formatted_address += ", "; 
										formatted_address += area_level_array.length ? area_level_array[0].long_name: "" ;

										var country_array = results[0].address_components.filter(function(address_component){
											return address_component.types.includes("country");
										}); 

										formatted_address += ", ";  
										formatted_address += country_array.length ? country_array[0].long_name: "";

										document.getElementById("pac-input").value = formatted_address;
										document.getElementById("formatted_address").value = formatted_address;
										document.getElementById("latitude").value = lat;
										document.getElementById("longitude").value = lon;
										document.getElementById("pac-input2").value = formatted_address;
										document.getElementById("map-latitude").value = lat;
										document.getElementById("map-longitude").value = lon;
									} else {
										//alert("No results found");
									}
								} else {
									//alert("Geocoder failed due to: " + status);
								}
							});

							return false;
						} else{
							$('.loader-front').show();
							$('.loader-back').hide();
							var latlng = new google.maps.LatLng(lat, lon);
							document.getElementById("map-latitude").value = lat;
							document.getElementById("map-longitude").value = lon;
						}
					}, function (error) {
						if (error.code == error.PERMISSION_DENIED)
						{	
							$('.loader-front').show();
							$('.loader-back').hide();
							console.log("you denied me :-(");
						}
					});
				} else {
					console.log('Browser not support Geo Location');
				}
			}
		}); 
	</script>
<?php } ?>
<?php
if(isset($_SESSION['reload'])) { ?>
	<script>
		var reload = <?php echo $_SESSION['reload']; ?>; 
	</script>
<?php } 
?>
<script>
	/******sidemenu smooth scrolling script*******/
// Add active class to the current button (highlight it)
var header = document.getElementById("nav");
if(typeof header != "undefined" && header != null)
{
	var btns = header.getElementsByClassName("nav-link");
	for (var i = 0; i < btns.length; i++) {
		btns[i].addEventListener("click", function() {
			var current = document.getElementsByClassName("active");
			current[0].className = current[0].className.replace(" active", "");
			this.className += " active";
		});
	}
}
</script>
<style>
	#testDiv {
		height: 100vh !important;
	}
	.slimScrollDiv {
		height: 95vh !important;
	}
	.sideMenu::-moz-scrollbar {
		-moz-appearance: none;
		width: 6px;
		background: transparent;
	}
	.sideMenu::-moz-scrollbar-thumb {
		background-color: rgba(228,0,70,0.9);
		-moz-box-shadow: 0 0 1px #cdcdcd;
		border-radius:100px;
	}
</style>
<script>
	var reload = 0;
	$(document).ready(function(){
		$('[data-toggle="tooltip"]').tooltip();
	});
</script>
<script>
	$(window).scroll(function(){
		var sticky = $('.sticky'),
		scroll = $(window).scrollTop();
		if (scroll >= 145) sticky.addClass('fixed');
		else sticky.removeClass('fixed');
	});
</script>
<script>
	/*******responsive sidemenu toggle*******/
	$('.sidemenu-btn').click(function(){
		$('#sideshow').toggleClass('add-class');
	});
</script>
<script>
	/******sidemenu smooth scrolling script*******/
	$("#nav li a[href^='#']").on('click', function(e) {
		e.preventDefault();
		$('html, body').animate({
			// scrollTop: $(this.hash).offset().top
			scrollTop: $(this.hash).offset().top - 150
		}, 500, function(){
			window.location.hash = this.hash;
		});
	});
</script>
<script type="text/javascript">
	$(function(){
		if($('body').css("direction") == "rtl") { 
			$('#testDiv').slimScroll({
				position: 'left',
				start: 'top'
			});
		}
		else
		{
			$('#testDiv').slimScroll({
				start: 'top'
			});
		}
	});
</script>
<script type="text/javascript">
	$(document).ready(function($){
		var app_language = $('#app_language').val();
		if(app_language == 'ar')
		{
			$('#sn-bar__scroll--right').css('display', 'none');
			$('#sn-bar__scroll--left').click(function(){
				$('#sn-bar__scroll--right').css('display', 'block');
			});
		}
	});
</script>
</body>
</html> 