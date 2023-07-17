<?php
namespace backend\controllers;
use Yii;
use yii\helpers\Json;
use common\models\Products;
use common\models\Promotiontransaction;
use common\models\Productconditions;
use common\models\Filter;
use common\models\Filtervalues;
use common\models\Productfilters;
use backend\models\ProductsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\Sitesettings;
use common\models\Categories;
use common\models\Country;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use common\models\Shipping;
use common\models\Currencies;
use common\models\Users;
use common\models\Userdevices;
use common\models\Followers;
use common\models\Photos;
use yii\web\UploadedFile;
use yii\web\HttpException;
use yii\imagine\Image;
use Imagine\Image\Box;

use vendor\sightengine;
use vendor\sightengine\src\SightengineClient;
use common\components\MyAws;

class ProductsController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
	}
	public function actions()
    {
         $model = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
         if(isset($model->sitename)) {
         Yii::$app->view->title =  $model->sitename;     
        }
        else
        {
                     Yii::$app->view->title =  "Classifieds";     
        }
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
	}
  public function actionDelete($id)
    {
    	if (Yii::$app->user->isGuest) {            
    		return $this->goHome();          
    	}
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success',Yii::t('app','Items Deleted'));
        return $this->redirect(['index']);
    }
     public function beforeAction($action) {
            if (Yii::$app->user->isGuest) {            
                return $this->goHome();          
            }
            return true;
    }
    public function actionIndex()
    {  
    		if (Yii::$app->user->isGuest) {            
    		return $this->goHome();          
    	}
    	$this->layout="page";
        $searchModel = new ProductsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['approvedStatus'=>1]);
        $dataProvider->pagination->pageSize=10;
        $total = $dataProvider->query->count();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'total'=>$total
        ]);
    }
    public function actionPending()
    {  $this->layout="page";
        $searchModel = new ProductsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['approvedStatus'=>0]);
        $total = $dataProvider->query->count();
        return $this->render('pending', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'total'=>$total
        ]);
    }
    public function actionView($id)
    {
    		if (Yii::$app->user->isGuest) {            
    		return $this->goHome();          
    	}
    		$models = new Photos();
    		$getPhotos = Photos::find()->where(['productId'=>$id])->all();
        return $this->render('view', [
            'model' => $this->findModel($id),
            'getPhotos' => $getPhotos,  
        ]);
    }
    public function actionCreate()
    {
    		if (Yii::$app->user->isGuest) {            
    		return $this->goHome();          
    	}
        $model = new Products();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->productId]);
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }
	public function actionUpdate($id)
    {	
		$ProductModel  = Products::find()->where(['productId' => $id])->one();
        $model = $this->findModel($id);
        $models = new Photos();
        $photos = Photos::find()->where(['productId' => $id])->all();
       	$plen = count($photos);
		$parentCategory = array();
		$parentCategory = Categories::find()->where(['parentCategory' => 0])->all();
		if (!empty($parentCategory)){
			$parentCategory = ArrayHelper::map($parentCategory, 'categoryId', 'name');
		}
		$subCategory = Categories::find()->where(['parentCategory' => $model->category])->all();
		$subCategory = ArrayHelper::map($subCategory, 'categoryId', 'name');
		$sub_subCategory = Categories::find()->where(['parentCategory'=>$model->subCategory])->all();
		if (!empty($sub_subCategory)){
            $sub_subCategory = ArrayHelper::map($sub_subCategory, 'categoryId', 'name');
		}
		$shippingTime['1 business day'] = '1 business day';
		$shippingTime['1-2 business day'] = '1-2 business day';
		$shippingTime['2-3 business day'] = '2-3 business day';
		$shippingTime['3-5 business day'] = '3-5 business day';
		$shippingTime['1-2 weeks'] = '1-2 weeks';
		$shippingTime['2-4 weeks'] = '2-4 weeks';
		$shippingTime['5-8 weeks'] = '5-8 weeks';
		$countryModel = array();
		$countryList = Country::find()->where(['<>','countryId','0'])->all();
		if (!empty($countryList)){
			foreach ($countryList as $country){
				$countryKey = $country->countryId."-".$country->country;
				$countryModel[$countryKey] = $country->country;
				$shippingCountry[$country->countryId] = $country->country;
			}
		}
		$shipping_country_code = "";
		if($model->instantBuy == "1" && isset($model->shippingcountry) && $model->shippingcountry!="" && $model->shippingcountry != 0)
		{
		 	$shipping_country_code = yii::$app->Myclass->getCountryCode($model->shippingcountry);
		}

		$options = array();
		if (!empty($model->sizeOptions)){
			$options = json_decode($model->sizeOptions, true);
		}
		$shippingModel = $model->shipping;
		$jsShippingDetails = '';
		$itemShipping = array();
		foreach ($shippingModel as $shippingDetail){
			$itemShipping[$shippingDetail->countryId] = $shippingDetail->shippingCost;
			if(empty($jsShippingDetails)){
				$jsShippingDetails .= '"'.$shippingDetail->countryId.'"';
			}else{
				$jsShippingDetails .= ',"'.$shippingDetail->countryId.'"';
			}
		}

		if(isset($_POST['Products']))
		{	
			$productData = $_POST['Products'];
			$model->attributes=$_POST['Products'];
			$model->name = htmlentities($model->name);
			$model->description = htmlentities($model->description);
			$model->createdDate = time();
			if(isset($_POST['Products']['giving_away'])) {
				$model->price = 0;
			}
			else
			{
				$model->price=$_POST['Products']['price'];
			}
		if(isset($_POST['Products']['sub_subCategory'])){
			$model->sub_subCategory  = $_POST['Products']['sub_subCategory'];
		}
			$model->exchangeToBuy = 0;
			if(isset($_POST['Products']['exchangeToBuy']))
			{
				$model->exchangeToBuy = $_POST['Products']['exchangeToBuy'];
			}
			$model->instantBuy = 0;
			if(isset($_POST['Products']['instantBuy']) && (isset($_POST['giving_away'])=="" || isset($_POST['giving_away']) == '0')) {
				if (isset($_POST['Products']['instantBuy'])) {
				$model->instantBuy = $_POST['Products']['instantBuy'];
				}
                if(isset($_POST['Products']['shippingcountry'])) {
					$model->shippingcountry = $_POST['Products']['shippingcountry'];
				}
				if(isset($_POST['Products']['shippingCost'])){
					$model->shippingCost = $_POST['Products']['shippingCost'];
				}
			}
			$model->myoffer = 0;
			if(isset($_POST['Products']['myoffer']))
			{
				$model->myoffer = $_POST['Products']['myoffer'];
			}
			if (isset($_POST['Products']['shippingTime'])) {
				$model->shippingTime = $_POST['Products']['shippingTime'];
			}
			$model->currency  = $_POST['Products']['currency'];
			$model->subCategory = $_POST['Products']['subCategory'];
			$model->videoUrl = $_POST['Products']['videoUrl'];
			if (isset($productData['productOptions'])){
				$model->sizeOptions = json_encode($productData['productOptions']);
				$quantity = 0;
				$optionPrice = 0;
				foreach($productData['productOptions'] as $options){
					$quantity += $options['quantity'];
					$optionPrice = $optionPrice == 0 && !empty($options['price']) ? $options['price'] : $optionPrice;
				}
				$model->quantity = $quantity;
				$model->price = $optionPrice != 0 ? $optionPrice : $model->price;
			} else {
				$model->sizeOptions = '';
			}
			Shipping::deleteAll(['productId'=>$model->productId]);
			if(isset($productData['shipping'])) {
				foreach ($productData['shipping'] as $key => $shipping){
					if($shipping != ""){
						$shippingModel = new Shipping();
						$shippingModel->productId = $model->productId;
						$shippingModel->countryId = $key;
						$shippingModel->shippingCost = $shipping;
						$shippingModel->createdDate = time();
						$shippingModel->save();
					}
				}
			}
			//removing files
			$rmfilenames = $_POST['removefiles'];
			$rmtemp = explode(',', $rmfilenames);
			foreach ($rmtemp as $value) {
				$photosModel = Photos::find()->where(['name' =>$value])->one();
				$path = Yii::$app->basePath ."/web/media/item/".$model->productId."/"."/";
				$file = $path.$value;
				if( is_file( $file ) ) {
					unlink( $file );
				}
				if(!empty($photosModel))
				$photosModel->delete();
			}
			//Uploading images
				$filenames = json_decode($_POST['uploadedfiles'],true);
				if(isset($filenames)){
						for($i=0;$i<count($filenames);$i++)
						{
							$photoss = new Photos();
							$photodata = $photoss::find()->where(['name'=>$filenames[$i]])->one();
							if(!$photodata)	{						
								$path1 = realpath(Yii::$app->basePath.'/../');
								$path = realpath($path1.'/frontend/web/media/item').'/'.$model->productId.'/';
								$frontend_path = realpath($path1.'/frontend/web');
								$tmp_path =realpath($path1.'/frontend/web/media/item/tmp').'/'.$filenames[$i];
								if( !is_dir($path) ) {
									FileHelper::createDirectory($path);
									chmod( $path, 0777 );
								}
								if( is_file( $tmp_path ) ) {
									//chmod( $tmp_path, 0777 );
									if( rename( $tmp_path, $path.$filenames[$i] ) ) {
										$info = getimagesize($path.$filenames[$i]);	
										$watermark = yii::$app->Myclass->getWatermark();
										$frontendUrl = str_replace('/admin', '', Yii::$app->urlManager->createAbsoluteUrl("/")); 
										$watermarkImage = $frontendUrl."media/logo/".$watermark;
										$image = $frontendUrl."media/item/".$model->productId.'/'.$filenames[$i];
										list($widthh,$heightt) = getimagesize($image);
										$imagine = Image::getImagine();
										$imagine = $imagine->open($frontendUrl."media/logo/".$watermark);
										$sizes = getimagesize($frontendUrl."media/logo/".$watermark); 
										
										if ($sizes[0] > $sizes[1])
		                                {
		                                    if ($heightt > $widthh)
		                                    {
		                                        $ratioBrand = $sizes[1] / $sizes[0];
		                                        $width = $widthh * 0.3;
		                                        $height = $width / 4;
		                                    }
		                                    else
		                                    {
		                                        $ratioBrand = $sizes[1] / $sizes[0];
		                                        $height = $heightt * 0.07;
		                                        $width = $height / 0.25;
		                                    }
		                                }
		                                else
		                                {
		                                    if ($heightt > $widthh)
		                                    {
		                                        $ratioBrand = $sizes[1] / $sizes[0];
		                                        $width = $widthh * 0.2;
		                                        $height = $width / $ratioBrand;
		                                    }
		                                    else
		                                    {
		                                        $ratioBrand = $sizes[1] / $sizes[0];
		                                        $height = $heightt * 0.15;
		                                        $width = $height / $ratioBrand;
		                                    }
		                                }
		                                $imagine = $imagine->resize(new Box($width, $height))->save($frontend_path.'/media/item/'.$model->productId.'/watermark.png', ['quality' => 60]);
										$watermarkfile = $frontend_path.'/media/item/'.$model->productId.'/watermark.png'; 
										list($watermark_width,$watermark_height) = getimagesize($watermarkfile);
		                                $dest_x = intval($widthh - $width - 25);
		                                $dest_y = intval($heightt - $height - 25);
		                                $position = array(
		                                    $dest_x,
		                                    $dest_y
		                                );


										$resizedpath = $frontend_path . "/media/item/resized/{$model->productId}/";								
											//resize images
											if (!is_dir($resizedpath)) {
												mkdir($resizedpath);
												chmod($resizedpath, 0777);
											}
											$image = $frontend_path ."/media/item/".$model->productId.'/'.$filenames[$i];  

											$resizeimagineObj = Image::getImagine();
											$resizeimageObj = $resizeimagineObj->open($image);
											$resizeimageObj->resize(new Box(350, 350))->save(Yii::getAlias($frontend_path.'/media/item/resized/'.$model->productId.'/'.$filenames[$i], ['quality' => 60]));
										//end resize

										$newImage = Image::watermark($image, $watermarkfile, $position);
										$newImage->save($frontend_path.'/media/item/'.$model->productId.'/'.$filenames[$i], ['quality' => 60]);
										unlink($watermarkfile);
										chmod( $path.$filenames[$i], 0777 ); 
										$photoss->productId = $model->productId;
										$photoss->name = $filenames[$i]; 
										$photoss->createdDate = time();
										$photoss->save(false);
									}
								}
							}
						}
					}
					$model->location = $_POST['Products']['location'];
					$model->latitude = $_POST['Products']['latitude'];
					$model->longitude = $_POST['Products']['longitude'];
					if($model->save(false)) {
						Productfilters::deleteAll(['product_id' => $model->productId]);
						if(isset($_POST['Products']['attributes']) && $_POST['Products']['attributes']!=""){
						$getPostattributes = $_POST['Products']['attributes'];
								foreach($getPostattributes as $attrKey=>$attrVal)
								{
									if(empty($attrKey))
										continue;
									if($attrKey != 'multilevel')
									{
										$filterGet = Filter::find()->where(['id'=>$attrKey])->one();
										$productvals = Filtervalues::find()->where(['id'=>$attrVal])->one();
										if($filterGet->type == 'dropdown')
										{
											$levelOne = $attrKey;
											$levelTwo = $attrVal;
											$levelThree = 0;
											$pro_value = $productvals->name;
										}elseif($filterGet->type == 'range')
										{
											$levelOne = $attrKey;
											$levelTwo = $attrVal;
											$levelThree = 0;
											$pro_value = $attrVal;
										}elseif($filterGet->type == 'multilevel')
										{
											$levelOne = $attrKey;
											$levelTwo = $attrVal;
											$levelThree = ($getPostattributes['multilevel'][$levelTwo] == '') ? 0 : $getPostattributes['multilevel'][$levelTwo];
											$pro_value = 0;
										}
										$productAttribute = new Productfilters;
										$productAttribute->product_id = $model->productId;
										$productAttribute->category_id = $_POST['Products']['category'];
										$productAttribute->subcategory_id = ($_POST['Products']['subCategory'] == '') ? '0' : $_POST['Products']['subCategory'];
										$productAttribute->filter_id = $attrKey;
										$productAttribute->level_one = $levelOne;
										$productAttribute->level_two = $levelTwo;
										$productAttribute->level_three = $levelThree;
										$productAttribute->filter_name = $filterGet->name;
										$productAttribute->filter_type = $filterGet->type;
										$productAttribute->filter_values = $pro_value;
										$productAttribute->save(false);
									}
								}
							}
						Yii::$app->session->setFlash('success',Yii::t('app','Item updated'));
						return $this->redirect(['index']);
					}
				}
		// Ends here
		$currencies = Currencies::find()->all();
		$topFiveCur1 = Sitesettings::find()->orderBy(['id' => SORT_DESC])->all();
		$topFiveCur=$topFiveCur1[0]['currency_priority'];
		$topFive = json_decode($topFiveCur);
		//print_R($topFive) ; exit;
		$topCurs=0;
		if(isset($topFive))
        {
		foreach($topFive as $top):
		$topCurs[] = Currencies::find()->where(['id' => $top])->all();
		endforeach;
		}
		$model->name = html_entity_decode($model->name);
		$model->description = html_entity_decode($model->description);
		$parentfieldOption = Categories::find()->where(['categoryId' => $model->category])->one();
		$subcatfieldOption = Categories::find()->where(['categoryId' => $model->subCategory])->one();
		$sub_subcatfieldOption = Categories::find()->where(['categoryId' => $model->sub_subCategory])->one();
		$sub_cat_name = '';
		if(!empty($subcatfieldOption)){
			$sub_cat_name = $subcatfieldOption->name;
		}
		$getcateAttributes = array();
		if(!empty($parentfieldOption)){
			$getcateAttributes = explode(',', $parentfieldOption->categoryAttributes);
		}
		$getsubcateAttributes = array();
		if(!empty($subcatfieldOption)){
			$getsubcateAttributes = explode(',', $subcatfieldOption->categoryAttributes);
		}
		$getsub_subcateAttributes = array();
		if(!empty($sub_subcatfieldOption)){
			$getsub_subcateAttributes = explode(',', $sub_subcatfieldOption->categoryAttributes);
		}
		$filterValues = array_unique(array_filter(array_merge($getcateAttributes, $getsubcateAttributes, $getsub_subcateAttributes)));	
        return $this->render('update', [
            'model'=>$model,'attributes'=>$filterValues, 'parentCategory'=>$parentCategory,'subCategory'=>$subCategory,'sub_subCategory' => $sub_subCategory,'options'=>$options, 'shippingTime' => $shippingTime,
				'countryModel' => $countryModel, 'itemShipping' => $itemShipping,
				'shippingCountry'=>$shippingCountry, 'jsShippingDetails' => $jsShippingDetails,
				'topCurs' => $topCurs,'currencies' => $currencies,'models' => $models,'photos' => $photos,'shipping_country_code'=>$shipping_country_code,'plen' => $plen,
				'sub_cat_name' => $sub_cat_name
        ]);
    }
    public function actionGetchildlevel($id)
	{
		$loadFilter = Filtervalues::find()->where(['parentid'=>$_GET['id'],'parentlevel'=>'4'])->all();
		if(empty($loadFilter))
			return false;
		$options = '<div class="Category-select-box-row  no-hor-padding childlevelattr '.$_GET['id'].'">
							<div class="form-group no-hor-padding '.$_GET['id'].'">';
		$options.=' <select id="childattribute_'.$_GET['id'].'" class="form-control productattributes" name="Products[attributes][multilevel]['.$_GET['id'].']">';
		$options.= '<option value="">Select Child value</option>';
		foreach( $loadFilter as $key=>$value )
		{
			$options.= '<option value="'.$value->id.'">'.$value->name.'</option>';
		}
		$options.= '</select>';
		$options .= '<div class="text-danger childattribute_'.$_GET['id'].' errorMessage"></div>';
		$options.= '</div>';
		$options.= '</div>';
		return $options; 
	}
	public function actionGetrangefilter()
	{
		$rangevalues = $_POST['rangevalues'];
		$getRange = explode(',', $rangevalues);
		$rangeVal = array();
		foreach($getRange as $key=>$val)
		{
			$filterVals = Filtervalues::find()->where(['id'=>$val])->one();
			$filterRangevalue = Filter::find()->where(['id'=>$filterVals->filter_id])->one();
			$rangeVal[$key]['id'] = $filterVals->filter_id;
			$rangeVal[$key]['name'] = str_replace(' ', '_', $filterVals->name);
			$rangeVal[$key]['range'] = $filterRangevalue->value;
		}
		return json_encode($rangeVal);
	}
 	
 	public function actionGetsubcategory()
    {
        $subcat = $_POST['subcat'];
        $subcatfieldOption = Categories::find()->where(['categoryId' => $subcat])->one();
        $sub_subcatfieldOption = Categories::find()->where(['parentCategory' => $subcat])->all();

        $options = '<div class="Category-select-box-row no-hor-padding"><div class="form-group no-hor-padding">';
        if (!empty($sub_subcatfieldOption))
        {
            $options .= '<label class="Category-select-box-heading required" for="Products_category" id="Products_sub_subCategory_head"> ' . Yii::t('app', 'Select child category for') . ' ' . Yii::t('app', ucfirst($subcatfieldOption->name)) . ' <span class="required">*</span></label>';
            $options .= ' <select id="Products_sub_subCategory" class="form-control select-box-down-arrow productattributes" name="Products[sub_subCategory]" >';
            $options .= '<option value="">' . Yii::t('app', 'Select child category for') . ' ' . Yii::t('app', ucfirst($subcatfieldOption->name)) . '</option>';
            $sub_sub_cat = Categories::find()->where(['parentCategory' => $subcat])->all();
            foreach ($sub_sub_cat as $sub_data)
            {
                $selectedDrop =  '';
                $options .= '<option value="' . $sub_data->categoryId . '" ' . $selectedDrop . '>' . Yii::t('app', $sub_data->name) . '</option>';
            }
            $options .= '</select>';
            $options .= '</div>';
            $options .= '<div id="Products_sub_subCategory_em_" class="errorMessage text-danger"></div>';
            
        }
        $options .= '</div>';
        return $options;
        exit;
    }

	public function actionGetfilter()
    {
        $category = $_POST['cat'];
        $getAttributes = array();
        $ownAttributes = array();
        $productId = (isset($_POST['productId'])) ? $_POST['productId'] : '0';
        
        $parentfieldOption = Categories::find()->where(['categoryId' => $category])->one();

        if ($parentfieldOption->parentCategory != '' && $parentfieldOption->parentCategory != 0)
        {   
            $parentfilters = Categories::find()->where(['categoryId' => $parentfieldOption->parentCategory])->one();
            $getAttributes = explode(',', $parentfilters->categoryAttributes);
        }

        if ($parentfieldOption->categoryAttributes != '')
        {   
            $ownAttributes = explode(',', $parentfieldOption->categoryAttributes);
            $getAttributes = array_merge($getAttributes,$ownAttributes);
        }

        if(count($getAttributes) > 0) {
          $options = '<div class="no-hor-padding">';
          $multilevelvalues = array();
          foreach ($getAttributes as $key => $val)
          {
            $filterModel = Filter::find()->where(['id' => $val])->one();
            if ($filterModel->type == 'dropdown')
            {
                $filtervalueModel = Filtervalues::find()->where(['filter_id' => $filterModel
                    ->id])
                ->one();
                $getProductfilters = Productfilters::find()->where(['product_id' => $productId, 'filter_id' => $filterModel
                    ->id])
                ->one();
                if (isset($getProductfilters)) $filterVal = $getProductfilters->level_two;
                else $filterVal = '';
                $options .= '<div class="Category-select-box-row  no-hor-padding">
                <div class="form-group  no-hor-padding">';
                $options .= '<label class="Category-select-box-heading required" for="Products_category">' . Yii::t('app', ucfirst($filterModel->name)) . '</label>';
                $options .= ' <select id="product_attributes_' . $filterModel->id . '" class="form-control select-box-down-arrow productattributes" name="Products[attributes][' . $filterModel->id . ']" >';
                $options .= '<option value="">' . Yii::t('app', 'Select') . ' ' . Yii::t('app', ucfirst($filterModel->name)) . '</option>';
                $getchildvals = Filtervalues::find()->where(['parentid' => $filtervalueModel->id, 'parentlevel' => '1'])
                ->all();
                foreach ($getchildvals as $cData)
                {
                    $selectedDrop = ($filterVal != '' && $cData->id == $filterVal) ? 'selected="selected"' : '';
                    $options .= '<option value="' . $cData->id . '" ' . $selectedDrop . '>' . Yii::t('app', $cData->name) . '</option>';
                }
                $options .= '</select>';
                $options .= '<p class="product_attributes_' . $filterModel->id . ' errorMessage text-danger"></p>';
                $options .= '</div>';
                $options .= '</div>';
            }
            elseif ($filterModel->type == 'range')
            {
                $getProductfilters = Productfilters::find()->where(['product_id' => $productId, 'filter_id' => $filterModel
                    ->id])
                ->one();
                if (isset($getProductfilters)) $filterVal = $getProductfilters->filter_values;
                else $filterVal = '';
                $fieldname = str_replace(' ', '_', strtolower($filterModel->id));
                $filterrangeval = explode(";", $filterModel->value);
                $options .= '<div class="Category-input-box-row  no-hor-padding mt-2 location-container">';
                $options .= "<label class='Category-input-box-heading   no-hor-padding'>" . Yii::t('app', ucfirst($filterModel->name)) . "</label>";
                $options .= '<input  type="text" id="product_attributes_' . $filterModel->id . '" class="form-control productattributerange" value="' . $filterVal . '" name="Products[attributes][' . $fieldname . ']" placeholder = "' . Yii::t('app', 'Values between') . '' . $filterrangeval[0] . ' - ' . $filterrangeval[1] . '">';
                $options .= '<input type="hidden" id="product_attributes_' . $filterModel->id . '_values" class="form-control" value="' . $filterModel->value . '" name="range_values">';
                $options .= '<p class="product_attributes_' . $filterModel->id . ' errorMessage text-danger"></p>';
                $options .= '<input type="hidden" id="' . $fieldname . '" value="' . $filterModel->value . '" />';
                $options .= '</div>';
            }
            elseif ($filterModel->type == 'multilevel')
            {
                $getFiltervals = Filtervalues::find()->where(['filter_id' => $filterModel->id, 'type' => 'multilevel'])
                ->one();
                $getparentlevel = Filtervalues::find()->where(['parentid' => $getFiltervals->id, 'parentlevel' => '3'])
                ->all();
                $getProductfilters = Productfilters::find()->where(['product_id' => $productId, 'filter_id' => $filterModel
                    ->id])
                ->one();
                if (isset($getProductfilters))
                {
                    $filterVal = $getProductfilters->level_two;
                    $filterVal2 = $getProductfilters->level_three;
                }
                else
                {
                    $filterVal = '';
                    $filterVal2 = '';
                }
                $options .= '<div class="Category-select-box-row  no-hor-padding" id="multilevelss_' . $filterModel->id . '">
                <div class="form-group mt-2 no-hor-padding">';
                $options .= '<label class="Category-select-box-heading required" for="Products_category">' . Yii::t('app', $filterModel->name) . '</label>';
                $options .= ' <select id="multilevel_' . $filterModel->id . '" class="form-control select-box-down-arrow productattributes" name="Products[attributes][' . $filterModel->id . ']" onchange="getval(this);" >';
                $options .= '<option value="">' . Yii::t('app', 'Select parent option') . '</option>';
                foreach ($getparentlevel as $parentvalues)
                {
                    $selectedDrop = ($filterVal != '' && $cData->id == $filterVal) ? 'selected="selected"' : '';
                    $options .= '<option value="' . $parentvalues->id . '" ' . $selectedDrop . '>' . Yii::t('app', $parentvalues->name) . '</option>';
                }
                $options .= '</select>';
                $options .= '<p class="multilevel_' . $filterModel->id . ' errorMessage text-danger"></p>';
                $options .= '</div>';
                $options .= '<div id="multilevel_' . $filterModel->id . '"></div>';
                $options .= '</div>';
            }
        }
        $options .= '</div>';
    }
    else
    {
      $options = 0;
    }

  return $options;
  exit;
}

	/*
		Get filter by product subcategories..
	*/
	public function actionGetsubfilter()
	{
		$subcat = $_POST['subcat'];
		$category = $_POST['cat'];
		$sub_subcat = $_POST['sub_subcat'];
		$productId = (isset($_POST['productId'])) ? $_POST['productId'] : '0';
		$parentfieldOption = Categories::find()->where(['categoryId' => $category])->one();
		$subcatfieldOption = Categories::find()->where(['categoryId' => $subcat])->one();
		$sub_subcatfieldOption = Categories::find()->where(['categoryId' => $sub_subcat])->one();
			$getcateAttributes = explode(',', $parentfieldOption->categoryAttributes);
			$getsubcateAttributes = explode(',', $subcatfieldOption->categoryAttributes);
			$getsub_subcateAttributes = explode(',', $sub_subcatfieldOption->categoryAttributes);
			$getAttributes = array_unique(array_merge($getcateAttributes, $getsubcateAttributes,$getsub_subcateAttributes));
		$options = '<div class="no-hor-padding">';
		$multilevelvalues = array();
		foreach(array_filter($getAttributes) as $key=>$val)
		{
			$filterModel = Filter::find()->where(['id'=>$val])->one();
			if($filterModel->type == 'dropdown'){
				$filtervalueModel = Filtervalues::find()->where(['filter_id'=>$filterModel->id])->one();
				$getProductfilters = Productfilters::find()->where([
					'product_id'=>$productId,
					'filter_id'=>$filterModel->id
					])->one();
				if(isset($getProductfilters))
					$filterVal = $getProductfilters->level_two;
				else
					$filterVal = '';
				$options .='<div class="Category-select-box-row  no-hor-padding">
								<div class="form-group no-hor-padding">';
				$options .= '<label class="Category-select-box-heading required" for="Products_category">'.ucfirst($filterModel->name).'</label>';
				$options.=' <select id="product_attributes_'.$filterModel->id.'" class="form-control select-box-down-arrow productattributes" name="Products[attributes]['.$filterModel->id.']" >';
					$options .='<option value="">Select '.ucfirst($filterModel->name).'</option>';
					$getchildvals = Filtervalues::find()->where(['parentid'=>$filtervalueModel->id, 'parentlevel'=>'1'])->all();
					foreach($getchildvals as $cData) {
						$selectedDrop = ($filterVal != '' && $cData->id == $filterVal) ? 'selected="selected"' : '';
						$options .='<option value="'.$cData->id.'" '.$selectedDrop.'>'.$cData->name.'</option>';
					}
				$options .= '</select>';
				$options .= '<p class="text-danger product_attributes_'.$filterModel->id.' errorMessage"></p>';
				$options .= '</div>';
				$options .= '</div>';
			}elseif($filterModel->type == 'range')
			{
				$getProductfilters = Productfilters::find()->where([
					'product_id'=>$productId,
					'filter_id'=>$filterModel->id
					])->one();
				if(isset($getProductfilters))
					$filterVal = $getProductfilters->filter_values;
				else
					$filterVal = '';
				$fieldname = str_replace(' ', '_', strtolower($filterModel->id));
					$filterrangeval = explode(";",$filterModel->value);
				$options .='<div class="Category-input-box-row no-hor-padding mt-2 location-container">';
				$options .= "<label class='Category-input-box-heading   no-hor-padding'>".ucfirst($filterModel->name)."</label>";
				$options.='<input  type="text" id="product_attributes_'.$filterModel->id.'" class="form-control productattributerange" value="'.$filterVal.'" name="Products[attributes]['.$fieldname.']" placeholder = "Enter '.ucfirst($filterModel->name).' (values between '.$filterrangeval[0].' - '.$filterrangeval[1].')">';
				$options.= '<input type="hidden" id="product_attributes_'.$filterModel->id.'_values" class="form-control" value="'.$filterModel->value.'" , name="range_values">';
				$options .= '<p class="text-danger product_attributes_'.$filterModel->id.' errorMessage"></p>';
				$options.= '<input type="hidden" id="'.$fieldname.'" value="'.$filterModel->value.'" />';
				$options.= '</div>';
			}elseif($filterModel->type == 'multilevel')
			{
				$getFiltervals = Filtervalues::find()->where(['filter_id'=>$filterModel->id,
					'type'=>'multilevel'])->one();
				$getparentlevel = Filtervalues::find()->where(['parentid'=>$getFiltervals->id,
					'parentlevel'=>'3'])->all();
				$getProductfilters = Productfilters::find()->where([
					'product_id'=>$productId,
					'filter_id'=>$filterModel->id
					])->one();
					if(isset($getProductfilters)){
						$filterVal = $getProductfilters->level_two;
						$filterVal2 = $getProductfilters->level_three;
					}
					else{
						$filterVal = '';
						$filterVal2 = '';
					}
				$options.='<div class="Category-select-box-row  no-hor-padding" id="multilevelss_'.$filterModel->id.'">
								<div class="form-group  no-hor-padding">';
				$options.= '<label class="Category-select-box-heading required" for="Products_category">'.$filterModel->name.'</label>';
				$options.=' <select id="multilevel_'.$filterModel->id.'" class="form-control select-box-down-arrow productattributes" name="Products[attributes]['.$filterModel->id.']" onchange="getval(this);" >';
				$options.= '<option value="">Select parent option</option>';
				foreach($getparentlevel as $parentvalues)
				{	
					$selectedDrop = '';
					$options.= '<option value="'.$parentvalues->id.'" '.$selectedDrop.'>'.$parentvalues->name.'</option>';
				}
				$options.= '</select>';
				$options .= '<p class="text-danger multilevel_'.$filterModel->id.' errorMessage"></p>';
				$options.= '</div>';
				$options.='<div id="multilevel_'.$filterModel->id.'"></div>';
				$options.= '</div>';
			}
		}
		$options .= '</div>';
		return $options;
		exit;
	}
    public function actionStatus($status,$id)
    {
    	$products = $this->findModel($id);
		$promotionCriteria = Promotiontransaction::find();
		$promotionCriteria->andWhere(["productId" => $id]);
		$promotionCriteria->andWhere(["like","status","live"]);
		$promotionModel = $promotionCriteria->one();
		if(!empty($promotionModel) && $promotionModel->initial_check == '0'){
			$promotionModel->initial_check = '1';
			$promotionModel->approvedStatus = '1';
			$promotionModel->createdDate = time();
			$promotionModel->save(false);
		}
		if(!empty($products)) {
			if($status == 1) {
				$products->approvedStatus = 1;
				$products->save(false);
				if($products->Initial_approve==0)
				{
					$products->Initial_approve = 1;
					$products->save(false);
					$notifyMessage = 'added a product';
				$notifyAdd = 'add';
				$notifySource = '0';
				yii::$app->Myclass->addLogs($notifyAdd, $products->userId, $notifySource, $products->productId, $products->productId, $notifyMessage);
				$userid = $products->userId;
				$userdata = Userdevices::find()->where(['user_id' => $userid])->one();
				if (!empty($userdata)) {
					$currentusername = $userdata->user_id;
					 $userdata=Users::find()->where(['userId'=>$currentusername])->one();
				}
				$followCriteria = Followers::find();
				$followCriteria->andWhere(["follow_userId" => $userid]);
				$followers = $followCriteria->all();
				foreach ($followers as $follower) {
					$userdata=Users::find()->where(['userId'=>$follower->userId])->one();
					$userdata->unreadNotification+=1;
					$userdata->save(false);
					$followuserid = $follower->userId;
					$criteria = Userdevices::find();
					$criteria->andWhere(['user_id' => $followuserid]);
					$userdevicedet = $criteria->all();
					if(count($userdevicedet) > 0){
						foreach($userdevicedet as $userdevice){
							$deviceToken = $userdevice->deviceToken;
							$lang = $userdevice->lang_type;
							$badge = $userdevice->badge;
							$badge +=1;
							$userdevice->badge = $badge;
							$userdevice->deviceToken = $deviceToken;
							$userdevice->save(false);
							if(isset($deviceToken)){
								yii::$app->Myclass->push_lang($lang);
								if (isset($userdata)) {
									$messages =$userdata->username.' '.Yii::t('app','added a product').' '.$products->name;
								yii::$app->Myclass->pushnot($deviceToken,$messages,$badge);
								}
							}
						}
					}
				}
				}
				Yii::$app->session->setFlash('success',Yii::t('app','Item Activated!'));
				return $this->redirect(['index']);
			}else {
				$products->approvedStatus = 0;
				$products->save(false);
				Yii::$app->session->setFlash('info',Yii::t('app','Item Deactivated!'));
				return $this->redirect(['pending']);
			}

			$subCategoryModel =  Categories::find()->where(['parentCategory'=>$categoryId])->all();
       
            $subCategory =  ArrayHelper::map($subCategoryModel, 'categoryId', 'name');

			$subCategoryOptions = "<option value=''>".Yii::t('app','Select Subcategory')."</option>";
			foreach ($subCategory as $key => $category){
				$subCategoryOptions .= "<option value='".$key."'>".$category."</option>";
			}
			
			$sub_subCategoryOptions = "<option value=''>".Yii::t('app','Select child category')."</option>";

			$propertyData[] = $itemConditionFlag;
			$propertyData[] = $itemCondition;
			$propertyData[] = $subCategoryOptions;
			$propertyData[] = $sub_subCategoryOptions;
            $propertyDetails = Json::encode($propertyData);
			echo $propertyDetails;exit;
		}
    }
	public function actionProductproperty()
	{
		if(isset($_POST)) {
		 $categoryId = yii::$app->Myclass->checkPostvalue($_POST['selectedCategory']) ? $_POST['selectedCategory'] : "";
			   $categoryModel = Categories::find()->where(['categoryId'=>$categoryId])->all();
			   $categoryProperty = Json::decode($categoryModel[0]['categoryProperty'], true);
			   $itemCondition = "";
				$itemConditionFlag = 0;
				$sitePaymentModes = yii::$app->Myclass->getSitePaymentModes();
				if(isset($_POST['productId']) && $_POST['productId'] != ""){
					$productModel = yii::$app->Myclass->getProductDetails($_POST['productId']);
					if(!empty($productModel) && $productModel->category == $_POST['selectedCategory']){
						$itemStatus = $productModel->productCondition;
						$exchangeToBuy = $productModel->exchangeToBuy;
						$myOffers = $productModel->myoffer;
						$instantBuy = $productModel->instantBuy;
					}
				}
					if ($categoryProperty['itemCondition'] == 'enable')
				{
					$itemCondition .= '<div class="form-group mt-2">
						<label>'.Yii::t('app','Product Condition').' <span class="required" style="color: red;"> *</span></label>';
						$productConditions = Productconditions::find()->all();
						   $itemCondition .= '<select id="Products_productCondition" class="form-control select-box-down-arrow" name="Products[productCondition]" style="width:50%">';
						$itemCondition .= '<option value="">'.Yii::t('app','Select Product Condition').'</option>';
						foreach ($productConditions as $productCondition){
							if(isset($itemStatus) && $itemStatus == $productCondition->id)
							{
								$select1 = "selected";
								$itemCondition .= '<option value="'.$productCondition->id.'" '.$select1.'>'.$productCondition->condition.'</option>';
							}
							else
							{
							$select1 = "";	
							$itemCondition .= '<option value="'.$productCondition->id.'" '.$select1.'>'.$productCondition->condition.'</option>';
							}
						}
						$itemCondition .= '</select>
							<div id="Products_productCondition_em_" class="text-danger errorMessage m-t10" style="display:none;color:red"></div>
						</div>';
					$itemConditionFlag = 1;
				}
				else
				{
					$itemCondition .= '<input type="hidden" name="Products[productCondition]" value="" />';
				}
				if ($categoryProperty['exchangetoBuy'] == 'enable' && $sitePaymentModes['exchangePaymentMode'] == 1){
					$itemCondition .= '<div class="m-b20 d-flex">
					<div class="m-r50"><div class="form-group mt-2">
										<label>'.Yii::t('app','Exchange to buy').'</label>
										<div class="custom-control custom-switch" style="padding-left:3rem!important;">';
					if(isset($exchangeToBuy) && $exchangeToBuy == 1){
						$itemCondition .= '
										<input id="Products_exchangeToBuy" class="custom-control-input" checked="checked" type="checkbox" name="Products[exchangeToBuy]" value="1">
										<label class="custom-control-label" for="Products_exchangeToBuy"></label>
										</div>
								</div></div>';
					}else{
						$itemCondition .= '
										<input id="Products_exchangeToBuy" class="custom-control-input" type="checkbox" name="Products[exchangeToBuy]" value="1">
										<label class="custom-control-label" for="Products_exchangeToBuy"></label>
										</div>
								</div></div>';
					}
					$itemConditionFlag = 1;
				}else{
					$itemCondition .= '<input type="hidden" name="Products[exchangeToBuy]" value="0" />';
				}
				if(isset($_POST['givingAway']) && $_POST['givingAway'] != 0){
					$itemCondition .= '<input type="hidden" name="Products[myoffer]" value="2" />';
				} else {
							if ($categoryProperty['myOffer'] == 'enable'){
						$itemCondition .= '<div class="m-r50"><div class="form-group mt-2"><label>'.Yii::t('app','Fixed Price').'</label><div class="custom-control custom-switch" style="padding-left:3rem!important;">';
						if(isset($myOffers) && $myOffers == 1){
							$itemCondition .= '
											<input id="Products_myoffer" class="custom-control-input" checked="checked" type="checkbox" name="Products[myoffer]" value="1">
											<label class="custom-control-label" for="Products_myoffer"></label>
											</div>
									</div></div>';
						}else{
							$itemCondition .= '
											<input id="Products_myoffer" class="custom-control-input" type="checkbox" name="Products[myoffer]" value="1">
											<label class="custom-control-label" for="Products_myoffer"></label>
											</div>
									</div></div>';
						}
						$itemConditionFlag = 1;
					}else{
						$itemCondition .= '<input type="hidden" name="Products[myoffer]" value="2" />';
					}
				}
			if(isset($_POST['givingAway']) && $_POST['givingAway'] != 0  ){
				$itemCondition .= '<input type="hidden" name="Products[instantBuy]" value="0" />';
				} else {
						if ($sitePaymentModes['buynowPaymentMode'] == 1 && $categoryProperty['buyNow'] == 'enable'){
						$itemCondition .= '<div class="form-group mt-2 "><label>'.Yii::t('app','Instant Buy').'</label><div class="custom-control custom-switch" style="padding-left:3rem!important;">';
						if(isset($instantBuy) && $instantBuy == 1){
							$itemCondition .= '
											<input id="Products_instantBuy" class="custom-control-input" checked="checked" type="checkbox" name="Products[instantBuy]" value="1">
											<label class="custom-control-label" for="Products_instantBuy"></label>
											</div>
									</div>';
						}else{
							$itemCondition .= '
											<input id="Products_instantBuy" class="custom-control-input" type="checkbox" name="Products[instantBuy]" value="1">
											<label class="custom-control-label" for="Products_instantBuy"></label>
											</div>
									</div>';
						}
						$itemConditionFlag = 1;
					}else if($sitePaymentModes['buynowPaymentMode'] == 1){
						$itemCondition .= '<input type="hidden" name="Products[instantBuy]" value="0" />';
					}
				}

				$subCategoryModel =  Categories::find()->where(['parentCategory'=>$categoryId])->all();
       
            $subCategory =  ArrayHelper::map($subCategoryModel, 'categoryId', 'name');

			$subCategoryOptions = "<option value=''>".Yii::t('app','Select Subcategory')."</option>";
			foreach ($subCategory as $key => $category){
				$subCategoryOptions .= "<option value='".$key."'>".$category."</option>";
			}
			
			$sub_subCategoryOptions = "<option value=''>".Yii::t('app','Select child category')."</option>";

			$propertyData[] = $itemConditionFlag;
			$propertyData[] = $itemCondition;
			$propertyData[] = $subCategoryOptions;
			$propertyData[] = $sub_subCategoryOptions;
            if (!empty($subCategoryModel))
            {
                $propertyData[] = 1;
            }
            else
            {
                $propertyData[] = 0;
            }
            $propertyData[] =  $subCategory;
            $propertyDetails = Json::encode($propertyData);
			echo $propertyDetails;exit;
		}
	}

	public function actionItemautoapprove()
	{
		$approvestatus = $_POST['autoapprovestatus'];
		$sql = 'update `hts_sitesettings` set `product_autoapprove`="'.$approvestatus.'"';
        $model = Sitesettings::findBySql($sql)->orderBy(['id' => SORT_DESC])->one();
	}
	public function actionPendingItems()
	{
		if (Yii::$app->user->isGuest) {            
    		return $this->goHome();          
    	}
		$model=new Products('search2');
		$model->unsetAttributes();  
		if(isset($_GET['Products']))
		$model->attributes=$_GET['Products'];
		$this->render('pendings',array(
			'model'=>$model,
		));
	}
    protected function findModel($id)
    {
        if (($model = Products::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
	public function actionDeletephoto()
    {
    	$id = $_POST['id'];
    	$pid = $_POST['pid'];
    	$model = Photos::findOne($id);
       	$imgdir="uploads/".$model->name;
       	if (file_exists($imgdir))
      		{
         		unlink($imgdir);
          		$model->delete();
				return 0;
     		}
     	else
      		{
      			$model->delete();
				  Yii::$app->session->setFlash('success',Yii::t('app','Error delete file'));
          return 1;
      		}
    }
public function actionItemsold() {
		if(isset($_POST)) {
			$id = $_POST['id'];
			$value = $_POST['value'];
			$dec = yii::$app->Myclass->safe_b64decode($id);
			$spl = explode('-',$dec);
			$id = $spl[0];
			if($value == 1) {
				$product = Products::find()->where(['productId' => $id])->one();
				if($product->promotionType != 3){
					$promotionModel = Promotiontransaction::find()
										->andWhere(['like','status','Live'])
       									->andWhere(['productId' => $id])->one();
					if(!empty($promotionModel)){
						$promotionModel->status = "Expired";
						$promotionModel->save(false);
					}
					$product->promotionType = 3;
				}
				$product->soldItem = 1;
				$product->quantity = 0;
				$product->save(false);
			} else {
				$product =Products::find()->where(['productId' => $id])->one();
				$product->soldItem = 0;
				$product->quantity = 1;
				$product->save(false);
			}
		}
		echo $value;
	}
public function actionRemove_blogimage()
	{
			$image = $_POST['image'];
			$photosModel = Photos::find()->where(['name' =>$image])->one();
			//echo $photosModel['name'];die;
			$path1 = realpath(Yii::$app->basePath.'/../');
			$path = realpath($path1.'/frontend/web/media/item/tmp').'/';
			$file = $path.$image;
			if( is_file( $file ) ) {
				unlink( $file );
			}
			if(!empty($photosModel))
			$photosModel->delete();
	}
public function actionStartfileupload()
    {
    	$image = array();
    	$baseUrl = Yii::$app->request->baseUrl;
    	$tot_cnt=count($_FILES["images"]["name"]);
		$cnt = 0;
		$tocnt = 0; 
		function compress($source, $destination, $quality) {
			$info = getimagesize($source);
			$compressFlag = 0;
			if ($info['mime'] == 'image/jpeg') {
				$image = imagecreatefromjpeg($source); 
				$compressFlag = 1;
			} elseif ($info['mime'] == 'image/png') {
				$image = imagecreatefrompng($source);
				$compressFlag = 1;
			}
			if($compressFlag == 1) {
				imagejpeg($image, $destination, $quality);
			} 
			return $compressFlag; 
		}
	   foreach ($_FILES["images"]["error"] as $key => $error) {
	    	if ($tocnt >= 5) {
	    		exit;
	    	} else {
		    	if ($error == UPLOAD_ERR_OK) {
			      $name = $_FILES["images"]["name"][$key];
					$max_upload = 20971520;
					$filesize = filesize($_FILES["images"]["tmp_name"][$key]);
					if($filesize <= $max_upload)
					{
						$ext = strrchr($name, '.');
						$userid = Yii::$app->user->id;
						$random= rand(10, 1000);
						$newname = $random.time().$ext;
			       		$source_img = $_FILES["images"]["tmp_name"][$key];
						$path1 = realpath(Yii::$app->basePath.'/../'); 
						$path = realpath($path1.'/frontend/web/media/item/tmp').'/';
				 		$destination_img = $path1 . "/frontend/web/media/item/tmp" . "/".$newname; 
						if(!is_file( $path .$newname)) {
							$uploadFlag = 0;
							$client = new SightengineClient('580830197','DiKQWrbK6u6m8UmCguZS');
                            $response = $client->check(['nudity','wad','offensive'])->set_file($source_img);

                            $disqualify = 0;                    
                            if(isset($response))
                            {
                                if($response->status!='failure'){
                                    if(($response->nudity)){
                                        $raw = $response->nudity->raw+$response->nudity->partial;
                                        if($raw > $response->nudity->safe){
                                            $disqualify = 1;
                                        }
                                    }
                                    if(($response->alcohol) > 0.1){
                                        $disqualify = 1;
                                    }
                                    if(($response->weapon) > 0.1){
                                        $disqualify = 1;
                                    }
                                    if(($response->drugs) > 0.1){
                                        $disqualify = 1;
                                    }
                                    if(($response->offensive->prob) > 0.1){
                                        $disqualify = 1;
                                    }
                                }
                            }
                            if($disqualify === 0)
                            {
						      	if($filesize>=50000) {
									$uploadFlag = compress($source_img, $destination_img, 70);
								} else {
									$info = getimagesize($source_img); 
									if (($info['mime'] == 'image/jpeg' || $info['mime'] == 'image/png') && count($info) >=6) { 
										move_uploaded_file( $_FILES["images"]["tmp_name"][$key], $path .$newname);
										$uploadFlag = 1;
									}
								}
							} else if($disqualify === 1){
                                $filePath ='/var/www/html/frontend/web/media/item/tmp'.$newname;
                                $filePath = $path . $newname;
                                    if(file_exists($filePath)) {
                                        unlink($filePath);      
                                } 
                                echo "error";
                                return false;
                            }
								if($uploadFlag == 1) {
				       			chmod( $path .$newname, 0777 );
				       			array_push($image,$newname);
						       	echo '<div class="uploaded_img align_middle margin_left10" style="float: inherit;"><img src="'.Yii::$app->urlManagerfrontEnd->baseUrl.'/media/item/tmp/'.$newname.'" style="width:100px;height:100px;object-fit: scale-down;border-color: gray;border: double;padding: 5px;margin-right:20px;" class="img-responsive"><button type="button" class="close post_img_cls" data-dismiss="modal" aria-label="Close" onclick="remove_images(this,\''.$newname.'\')"><span aria-hidden="true">×</span></button></div>'; 
						      }
					   }
			       		$tocnt++; 
					}
					else if($filesize>$max_upload)
					{
						$cnt++;
					}
			   } else {
					$cnt++;
				}
			}
		}
		if($cnt==0  && count($image) > 0)
		{
			echo "***";echo json_encode($image)."***".count($image); 
		} else {
			echo "error";
		}
		return false; 	
    } 
	public function actionCancel()
    {
        return $this->redirect(['index']);
	}
}