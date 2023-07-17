<?php
namespace backend\controllers;
use yii\helpers\ArrayHelper;
use Yii;
use common\models\Categories;
use common\models\Sitesettings;
use common\models\Filter;
use common\models\Filtervalues;
use backend\models\CategoriesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use common\models\Products;
use yii\data\Pagination;
use common\components\Myclass;
use yii\helpers\Json;
error_reporting(0);
class CategoriesController extends Controller
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

    public function beforeAction($action) {
        if (Yii::$app->user->isGuest) {            
            return $this->goHome();          
        }
        return true;
    }

    public function actionIndex()
    {
        $this->layout="page";
        $searchModel = new CategoriesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['=','parentCategory',0]);
        $dataProvider->pagination->pageSize=10;
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionSubcategory($id)
    {
        $this->layout="page";
        $searchModel = new CategoriesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['=','parentCategory',$id]);
        $dataProvider->pagination->pageSize=10;
        return $this->render('subcategory', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionSub_subcategory($id)
    {
        $this->layout="page";
        $searchModel = new CategoriesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['=','parentCategory',$id]);
        $dataProvider->pagination->pageSize=10;
        return $this->render('sub_subcategory', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAdmin()
    {
        $model=new Categories();
        $searchModel = new CategoriesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        if(isset($_GET['Categories']))
            $model->attributes=$_GET['Categories'];
        $this->render('admin',array(
            'model'=>$model,'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ));
    }

    public function actionView($id)
    {
        $getCategory = $this->findModel($id);
        $attributes = explode(',', $getCategory->categoryAttributes);
        $filterValues = array();
        foreach($attributes as $key=>$val)
        {
            $filterValues[] = Filter::find()->where(['id'=>$val])->one();
        }
        return $this->render('view', [
            'model' => $this->findModel($id),
            'filters'=>$filterValues
        ]);
    }

    public function actionCreate()
    {
        $model=new Categories();
        $parentCategory = array();
        $parentCategory = Categories::find()->where(['parentCategory' => 0])->all();
        if (!empty($parentCategory)){
            $parentCategory = ArrayHelper::map($parentCategory, 'categoryId', 'name');
        }
        $filters = Filter::find()->where(['status' => 1])->all();    
        if(isset($_POST['Categories']))
        {
            $existcategory = Categories::find()
            ->where(['name'=>$_POST['Categories']['name']] )
            ->andWhere(['parentCategory' => 0])
            ->all();
            if(count($existcategory)==0)
            {
                $model->attributes=$_POST['Categories'];
                $model->meta_Title=$_POST['Categories']['meta_Title'];
                $model->meta_Description=$_POST['Categories']['meta_Description'];
                if ($model->parentCategory == ''){
                    $model->parentCategory = 0;
                    $model->subcategoryVisible=0;

                }else{
                    $model->subcategoryVisible=$_POST['Categories']['subcategoryVisible'];

                }

                $catImage = UploadedFile::getInstances($model,'image');

                if(!empty($catImage)) {
                    $imageName = explode(".",$catImage[0]->name);
                    $model->image = rand(000,9999).'-'.$catImage[0]->extension;
                }
                $categoryProperty = array();
                if ($_POST['Categories']['itemCondition'] == 1){
                    $categoryProperty['itemCondition'] = 'enable';
                }elseif ($_POST['Categories']['itemCondition'] == 0){
                    $categoryProperty['itemCondition'] = 'disable';
                }
                if ($_POST['Categories']['exchangetoBuy'] == 1){
                    $categoryProperty['exchangetoBuy'] = 'enable';
                }elseif ($_POST['Categories']['exchangetoBuy'] == 0){
                    $categoryProperty['exchangetoBuy'] = 'disable';
                }
                if(isset($_POST['Categories']['buyNow'])) {
                    if ($_POST['Categories']['buyNow'] == 1){
                        $categoryProperty['buyNow'] = 'enable';
                    }elseif ($_POST['Categories']['buyNow'] == 0){
                        $categoryProperty['buyNow'] = 'disable';
                    } 

                }
                if ($_POST['Categories']['myOffer'] == '1'){
                    $categoryProperty['myOffer'] = 'enable';
                }elseif ($_POST['Categories']['myOffer'] == 0){
                    $categoryProperty['myOffer'] = 'disable';
                }

                $model->categoryProperty = json_encode($categoryProperty);
                $model->slug = yii::$app->Myclass->productSlug($model->name);
                if(!empty($_POST['attributes']))
                {
                    $model->categoryAttributes = implode(',', $_POST['attributes']);    
                }
                $model->createdDate = date('Y-m-d h:m:s');
                $catImage = UploadedFile::getInstances($model,'image');
                if(!is_null($catImage)) 
                {
                    $logoUploadValues = array();
                    $logoUploadValues = getimagesize($catImage[0]->tempName);
                    $extensionarray = array('jpg', 'png', 'jpeg');
                    $extension=$catImage[0]->extension;
                    if (in_array($extension, $extensionarray) && $logoUploadValues[0] > "0" && $logoUploadValues[1] > "0"  && (end($logoUploadValues) == "image/jpeg" || end($logoUploadValues) == "image/png") && count($logoUploadValues) >= 6) {
                        $imageName = explode(".",$catImage[0]->name);
                        $model->image = rand(000,9999).'-'.$catImage[0]->extension;
                        $catImage[0]->saveAs('uploads/'. $model->image);  
                    } 
                }
                if ($model->validate()) 
                {
                    $model->save(false);
                    $siteSettings =  Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();

                    $decodeValue = json_decode($siteSettings->category_priority);

                    if(!in_array($model->categoryId, $decodeValue))
                    {

                        array_push($decodeValue, $model->categoryId);
                        $jsonData = json_encode($decodeValue);
                        $siteSettingsModel =  Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
                        $siteSettingsModel->category_priority = $jsonData;
                        $siteSettingsModel->save(false);
                    }
                    Yii::$app->session->setFlash('success',Yii::t('app','Category/Subcategory Created'));
                    return $this->redirect(['index']);
                }

            }else{
                Yii::$app->session->setFlash('success',Yii::t('app','Category already exists')); 
                return $this->redirect(['index']);
            }
        }
        return $this->render('create', [
            'model'=>$model, 
            'parentCategory'=>$parentCategory,
            'attributes'=>$filters,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->setScenario('update');
        $parentCategory = array();
        $parentCategory = Categories::find()->where(['parentCategory' => 0])->all();
        $getattributes = Filter::find()->where(['status' => 1])->all();
        if (!empty($parentCategory)){
            $parentCategory =\yii\helpers\ArrayHelper::map($parentCategory, 'categoryId', 'name');
        }
        $oldImage = $model->image;
        $categoryProperty = json_decode($model->categoryProperty, true);
        if ($categoryProperty['itemCondition'] == 'enable'){
            $model->itemCondition = '1';
        }elseif ($categoryProperty['itemCondition'] == 'disable'){
            $model->itemCondition = '0';
        }
        if ($categoryProperty['exchangetoBuy'] == 'enable'){
            $model->exchangetoBuy = '1';
        }elseif ($categoryProperty['exchangetoBuy'] == 'disable'){
            $model->exchangetoBuy = '0';
        }
        if ($categoryProperty['buyNow'] == 'enable'){
            $model->buyNow = '1';
        }elseif ($categoryProperty['buyNow'] == 'disable'){
            $model->buyNow = '0';
        }
        if ($categoryProperty['myOffer'] == 'enable'){
            $model->myOffer = '1';
        }elseif ($categoryProperty['myOffer'] == 'disable'){
            $model->myOffer = '0';
        }
        $getsubCategories = Categories::find()->where(['parentCategory'=>$id])->all();
        
        
        $mergeCat = array();
        foreach($getsubCategories as $subkey=>$subval)
        {
            $mergeCat[] = $subval->categoryAttributes;
            $getsub_subCategories = Categories::find()->where(['parentCategory'=>$subval->categoryId])->all();
        }
        if(!empty($mergeCat))
        {
            $slistchild = implode(',', $mergeCat);
            $listchild1 = explode(',', $slistchild);
        }else{
            $listchild1 = array();
        }
        $mergesubCat = array();
        foreach($getsub_subCategories as $subkey=>$subval)
        {
            $mergesubCat[] .= $subval->categoryAttributes;
        }
        if(!empty($mergesubCat))
        {
            $slistchild1 = implode(',', $mergesubCat);
            $listchild2 = explode(',', $slistchild1);
        }else{
            $listchild2 = array();
        }
        $listchild = array_merge($listchild1, $listchild2);
        if(isset($_POST['Categories']))
        {
            $existcategory = Categories::find()->where(['<>','categoryId', $id])->andWhere(['like','name',$_POST['Categories']['name']])->all();
            $model->name=$_POST['Categories']['name'];
            if(!isset($_POST['Categories']['parentCategory']) || $_POST['Categories']['parentCategory'] == ""){
                $model->parentCategory = 0;
            }
            if(isset($_POST['Categories']['subcategoryVisible']) || $_POST['Categories']['subcategoryVisible'] != ""){
                $model->subcategoryVisible = $_POST['Categories']['subcategoryVisible'];
            }
            if(isset($_POST['Categories']['meta_Title']) || $_POST['Categories']['subcategoryVisible'] != ""){
                $model->meta_Title = $_POST['Categories']['meta_Title'];
            }
            if(isset($_POST['Categories']['meta_Description']) || $_POST['Categories']['subcategoryVisible'] != ""){
                $model->meta_Description = $_POST['Categories']['meta_Description'];
            }
            $catImage = UploadedFile::getInstances($model,'image');
            if(!empty($catImage)) {
                $logoUploadValues = array();
                $logoUploadValues = getimagesize($catImage[0]->tempName);
                $extensionarray = array('jpg', 'png', 'jpeg');
                $extension=$catImage[0]->extension; 
                if (in_array($extension, $extensionarray) && $logoUploadValues[0] > "0" && $logoUploadValues[1] > "0"  && (end($logoUploadValues) == "image/jpeg" || end($logoUploadValues) == "image/png") && count($logoUploadValues) >= 6) {
                    $imageName = explode(".",$catImage[0]->name);
                    $model->image = rand(000,9999).'-'.$extension; 
                    $catImage[0]->saveAs('uploads/'.$model->image);
                } else {
                    $model->image = $oldImage;
                }
            } else {
                $model->image = $oldImage;
            }
            $categoryProperty = array();
            if ($_POST['Categories']['itemCondition'] == 1){
                $categoryProperty['itemCondition'] = 'enable';
            }elseif ($_POST['Categories']['itemCondition'] == 0){
                $categoryProperty['itemCondition'] = 'disable';
            }
            if ($_POST['Categories']['exchangetoBuy'] == 1){
                $categoryProperty['exchangetoBuy'] = 'enable';
            }elseif ($_POST['Categories']['exchangetoBuy'] == 0){
                $categoryProperty['exchangetoBuy'] = 'disable';
            }
            if ($_POST['Categories']['buyNow'] == 1){
                $categoryProperty['buyNow'] = 'enable';
            }elseif ($_POST['Categories']['buyNow'] == 0){
                $categoryProperty['buyNow'] = 'disable';
            }
            if ($_POST['Categories']['myOffer'] == '1'){
                $categoryProperty['myOffer'] = 'enable';
            }elseif ($_POST['Categories']['myOffer'] == 0){
                $categoryProperty['myOffer'] = 'disable';
            }
            $valMerge = $_POST['attributes'];
            $model->categoryProperty = json_encode($categoryProperty);
            $model->categoryAttributes = implode(',', $valMerge);
            $model->slug = yii::$app->Myclass->productSlug($model->name);
            $model->save(false);
            Yii::$app->session->setFlash('success',Yii::t('app','Category Updated'));
            return $this->redirect(['index']);
        }
        
        return $this->render('create', [
            'model' => $model, 
            'parentCategory'=>$parentCategory,
            'attributes'=>$getattributes,
            'parentAttribute'=>$listchild
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $products = Products::find()->where(['category'=>$id])
        ->orWhere(['subCategory'=>$id])->all();
        if(empty($products)) {
            $siteSettings =  Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
            $priorityCategories = $siteSettings->category_priority;
            if(!empty($priorityCategories)){
                $priorityCategories = Json::decode($priorityCategories, true);
                if(in_array($id, $priorityCategories)){
                    $restricedCategories = array();
                    foreach($priorityCategories as $priorityKey => $priorityCategory){
                        if($priorityCategory != $id)
                            $restricedCategories[] = $priorityCategory;
                    }
                    $filteredCategories = "";
                    if(!empty($restricedCategories))
                        $filteredCategories = Json::encode($restricedCategories);
                    $siteSettings->category_priority = $filteredCategories;
                    $siteSettings->save(false);
                }
            }
            $subcategories = Categories::find()->where(['parentCategory' => $id])->all();
            foreach($subcategories as $subcategory):
                $subcategory->delete();
            endforeach;
            $model->delete();
            $val = 0; 
        } else {
            $val = 1;
        }
        if($val == 1) {
            Yii::$app->session->setFlash('error',Yii::t('app', 'One or more products has been added to this category.You cannot delete this category'));
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            Yii::$app->session->setFlash('success',Yii::t('app', 'Category Deleted'));
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    function actionShowtopcategory()
    {
        $model = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        $decodeval = json_decode($model->category_priority);
        $category = array();
        foreach($decodeval as $categoriesValue)
        {
            $category_settings = Categories::find()->where(['categoryId'=>$categoriesValue,
                'parentCategory'=>'0'])->one();
            $category[] = $category_settings->name;   
            
        }

        if(count($category) === 0) {
            $allcategories = Categories::find()->where(['parentCategory'=>'0'])->all();
            
            foreach($allcategories as $eachcategory)
            {
                $category[] = $eachcategory->name;
            }
            
        }

        $totalCategories = count($categories);
        $model = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();

        if(isset($_POST['Sitesettings'])) 
        {
            $categoryCount = Categories::find()->where(['parentCategory'=>'0'])->count();
            $unique = $_POST['Sitesettings']['priority'];
            $split = explode(',', $unique);

            if( $categoryCount != count($split))
            {   
                $allcategories = Categories::find()->where(['parentCategory'=>'0'])->all();

                $category_ids = [];

                if($categoryCount > 0){
                    foreach($allcategories as $eachcategory)
                    {
                        array_push($category_ids, $eachcategory->categoryId);
                    }
                }
                
                $model->category_priority = json_encode($category_ids);
                $model->save(false);
                
            }

            $categoryId = array();
            foreach($split as $keyval)
            {
                $category_settings = Categories::find()->where(['name'=>$keyval,
                    'parentCategory'=>'0'])->one();  
                $categoryId[] = $category_settings->categoryId;
            }

            $settings = json_encode($categoryId);
            $model->category_priority = $settings;
            $model->save(false);

            Yii::$app->session->setFlash('success',Yii::t('app','Category priority settings updated successfully.'));
            return $this->redirect(['showtopcategory']);
        }

        return $this->render('showtopcategory', [
            'categorylist'=>implode(',', $category),
            'categoryCount'=>count($category),
            'categories' => $categories,
            'topTen' => $topTen, 
            'totalCategories' => $totalCategories
        ]);
    }

    function actionShowtopcatesssgory() 
    {
        $categories = Categories::find()->where(['parentCategory' => 0])->all();
        $model = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
        if(isset($_POST['Sitesettings'])) {
            $unique = $_POST['Sitesettings']['priority'];
            foreach($_POST['Sitesettings']['priority'] as $value):
                if (in_array($value,$unique)) {
                }
            endforeach;
            $settings = json_encode($_POST['Sitesettings']['priority']);
            $model->category_priority = $settings;
            $model->save(false);
            Yii::$app->session->setFlash('success',Yii::t('app','Category priority settings updated successfully'));
            return $this->redirect(['showtopcategory']);
        }
        if(!empty($model->category_priority)) {
            $topTen = json_decode($model->category_priority);
            $count = count($topTen);
            for($i=$count;$i < 10 ; $i++) {
                $topTen[] = 'empty';
            }
            if($topTen[0] == 'empty') {
                $curs = Categories::find()->where(['parentCategory' => 0])->limit(10)->all();
                $count = count($curs);
                $topTen = array();
                for($i=0;$i < 10 ; $i++) {
                    $topTen[] = 'empty';
                }
            }
        } else {
            $curs = Categories::find()->where(['parentCategory' => 0])->limit(10)->all();
            $count = count($curs);
            foreach($curs as $cur):
                $topTen[] = $cur->categoryId;
            endforeach;
            for($i=$count;$i < 10 ; $i++) {
                $topTen[] = 'empty';
            }
        }
        return $this->render('showtopcategory', [
            'categories' => $categories,'topTen' => $topTen,
        ]);
    }

    public function actionAdd()
    {
        $model=new Categories();
        $parentCategory = array();
        $parentCategory = Categories::find()->where(['parentCategory' => 0])->all();
        if (!empty($parentCategory)){
            $parentCategory = ArrayHelper::map($parentCategory, 'categoryId', 'name');
        }
        $filters = Filter::find()->where(['status' => 1])->all();
        $getParentdata = Categories::find()->where(['categoryId'=>$_GET['id']])->one();
        $parentAttributes = explode(',', $getParentdata->categoryAttributes);
        if(isset($_POST['Categories']))
        {
            $existcategory = Categories::find()->where(['name'=>$_POST['Categories']['name'], 'parentCategory'=>$_POST['Categories']['parentCategory']])->all();  
            if(count($existcategory)==0)
            {
                $model->attributes=$_POST['Categories'];
                if ($model->parentCategory == ''){
                    $model->parentCategory = 0;
                    $model->subcategoryVisible=0;
                }
                else
                {
                    $model->subcategoryVisible=0;
                }
                $model->meta_Title=$_POST['Categories']['meta_Title'];
                $model->meta_Description=$_POST['Categories']['meta_Description'];
                $catImage = UploadedFile::getInstances($model,'image');
                if(!empty($catImage)) {
                    $imageName = explode(".",$catImage[0]->name);
                    $model->image = rand(000,9999).'-'.$catImage[0]->extension;
                } 
                $categoryProperty = array();
                if ($_POST['Categories']['itemCondition'] == 1) {
                    $categoryProperty['itemCondition'] = 'enable';
                } elseif ($_POST['Categories']['itemCondition'] == 0) {
                    $categoryProperty['itemCondition'] = 'disable';
                }
                if ($_POST['Categories']['exchangetoBuy'] == 1) {
                    $categoryProperty['exchangetoBuy'] = 'enable';
                } elseif ($_POST['Categories']['exchangetoBuy'] == 0) {
                    $categoryProperty['exchangetoBuy'] = 'disable';
                }
                if(isset($_POST['Categories']['buyNow'])) {
                    if ($_POST['Categories']['buyNow'] == 1) {
                        $categoryProperty['buyNow'] = 'enable';
                    } elseif ($_POST['Categories']['buyNow'] == 0) {
                        $categoryProperty['buyNow'] = 'disable';
                    } 
                }
                if ($_POST['Categories']['myOffer'] == '1') {
                    $categoryProperty['myOffer'] = 'enable';
                } elseif ($_POST['Categories']['myOffer'] == 0) {
                    $categoryProperty['myOffer'] = 'disable';
                }
                $model->categoryProperty = json_encode($categoryProperty);
                $model->slug = yii::$app->Myclass->productSlug($model->name);            
                $model->createdDate = date('Y-m-d h:m:s');
                if(!empty($catImage)) {
                    $catImage[0]->saveAs('uploads/'. $model->image);
                } else {
                    $model->image="";
                }
                $model->filters="";
                if(!empty($_POST['attributes']))
                {
                    $model->categoryAttributes = implode(',', $_POST['attributes']);   
                }
                if ($model->validate()) {
                    $model->save(false);
                    Yii::$app->session->setFlash('success',Yii::t('app','Subcategory Created'));
                    return $this->redirect(['subcategory','id'=>$_GET['id']]);
                }
            } else {
                Yii::$app->session->setFlash('error',Yii::t('app','Subcategory already exists')); 
                return $this->redirect(['subcategory','id'=>$_GET['id']]);
            }
        }
        
        return $this->render('add', [
            'model'=> $model, 
            'parentCategory'=> $parentCategory,
            'attributes'=> $filters,
            'parentAttributes'=> $parentAttributes
        ]);
    }

    public function actionSubadd()
    {
        $model=new Categories();
        $parentCategory = array();
        $parentCategory = Categories::find()->where(['parentCategory' => 0])->all();
        if (!empty($parentCategory)){
            $parentCategory = ArrayHelper::map($parentCategory, 'categoryId', 'name');
        }
        $filters = Filter::find()->where(['status' => 1])->all();
        $sub_getParentdata = Categories::find()->where(['categoryId'=>$_GET['id']])->one();
        $getParentdata = Categories::find()->where(['categoryId'=>$sub_getParentdata->parentCategory])->one();
        $sub_parentAttributes = explode(',', $sub_getParentdata->categoryAttributes);
        $parentAttributes = explode(',', $getParentdata->categoryAttributes);
        $attributes = array_merge($parentAttributes, $sub_parentAttributes);
        if(isset($_POST['Categories']))
        {
            $existcategory = Categories::find()->where(['name'=>$_POST['Categories']['name'], 'parentCategory'=>$_POST['Categories']['parentCategory']])->all();  
            if(count($existcategory)==0)
            {
                $model->attributes=$_POST['Categories'];
                if ($model->parentCategory == ''){
                    $model->parentCategory = 0;
                    $model->subcategoryVisible=0;
                }
                else
                {
                    $model->subcategoryVisible=0;
                }
                $model->meta_Title=$_POST['Categories']['meta_Title'];
                $model->meta_Description=$_POST['Categories']['meta_Description'];
                $catImage = UploadedFile::getInstances($model,'image');
                if(!empty($catImage)) {
                    $imageName = explode(".",$catImage[0]->name);
                    $model->image = rand(000,9999).'-'.$catImage[0]->extension;
                } 

                $categoryProperty = array();
                if ($_POST['Categories']['itemCondition'] == 1) {
                    $categoryProperty['itemCondition'] = 'enable';
                } elseif ($_POST['Categories']['itemCondition'] == 0) {
                    $categoryProperty['itemCondition'] = 'disable';
                }
                if ($_POST['Categories']['exchangetoBuy'] == 1) {
                    $categoryProperty['exchangetoBuy'] = 'enable';
                } elseif ($_POST['Categories']['exchangetoBuy'] == 0) {
                    $categoryProperty['exchangetoBuy'] = 'disable';
                }
                if(isset($_POST['Categories']['buyNow'])) {
                    if ($_POST['Categories']['buyNow'] == 1) {
                        $categoryProperty['buyNow'] = 'enable';
                    } elseif ($_POST['Categories']['buyNow'] == 0) {
                        $categoryProperty['buyNow'] = 'disable';
                    } 
                }
                if ($_POST['Categories']['myOffer'] == '1') {
                    $categoryProperty['myOffer'] = 'enable';
                } elseif ($_POST['Categories']['myOffer'] == 0) {
                    $categoryProperty['myOffer'] = 'disable';
                }
                $model->categoryProperty = json_encode($categoryProperty);
                $model->slug = yii::$app->Myclass->productSlug($model->name);            
                $model->createdDate = date('Y-m-d h:m:s');
                if(!empty($catImage)) {
                    $catImage[0]->saveAs('uploads/'. $model->image);
                } else {
                    $model->image="";
                }
                $model->filters="";
                if(!empty($_POST['attributes']))
                {
                    $model->categoryAttributes = implode(',', $_POST['attributes']);   
                }
                if ($model->validate()) {
                    $model->save(false);
                    Yii::$app->session->setFlash('success',Yii::t('app','Subcategory Created'));
                    return $this->redirect(['sub_subcategory','id'=>$_GET['id']]);
                }
            } else {
                Yii::$app->session->setFlash('error',Yii::t('app','Subcategory already exists')); 
                return $this->redirect(['sub_subcategory','id'=>$_GET['id']]);
            }
        }
        return $this->render('sub_add', [
            'model'=> $model, 
            'parentCategory'=> $parentCategory,
            'attributes'=> $filters,
            'parentAttributes'=> $attributes
        ]);
    }

    public function actionUpdatesubcategory($id,$cat)
    {
        $model=$this->findModel($id);
        $model->setScenario('update');
        $parentCategory = array();
        $parentCategory = Categories::find()->where(['parentCategory' => 0])->all();
        if (!empty($parentCategory)){
            $parentCategory =\yii\helpers\ArrayHelper::map($parentCategory, 'categoryId', 'name');
        }
        $oldImage = $model->image;
        $categoryProperty = json_decode($model->categoryProperty, true);
        if ($categoryProperty['itemCondition'] == 'enable'){
            $model->itemCondition = '1';
        } elseif ($categoryProperty['itemCondition'] == 'disable'){
            $model->itemCondition = '0';
        }
        if ($categoryProperty['exchangetoBuy'] == 'enable'){
            $model->exchangetoBuy = '1';
        } elseif ($categoryProperty['exchangetoBuy'] == 'disable'){
            $model->exchangetoBuy = '0';
        }
        if ($categoryProperty['buyNow'] == 'enable'){
            $model->buyNow = '1';
        } elseif ($categoryProperty['buyNow'] == 'disable'){
            $model->buyNow = '0';
        }
        if ($categoryProperty['myOffer'] == 'enable'){
            $model->myOffer = '1';
        } elseif ($categoryProperty['myOffer'] == 'disable'){
            $model->myOffer = '0';
        }
        if(isset($_POST['Categories']['meta_Title']) || $_POST['Categories']['subcategoryVisible'] != ""){
            $model->meta_Title = $_POST['Categories']['meta_Title'];
        }
        if(isset($_POST['Categories']['meta_Description']) || $_POST['Categories']['subcategoryVisible'] != ""){
            $model->meta_Description = $_POST['Categories']['meta_Description'];
        }
        $getattributes = Filter::find()->where(['status' => 1])->all();
        if(isset($_POST['Categories']))
        {

            $existcategory = Categories::find()->where(['<>','categoryId', $id])->andWhere(['name' => $_POST['Categories']['name'], 'parentCategory'=>$cat])->all();
            if(count($existcategory)==0)
            {

                $model->name=$_POST['Categories']['name']; 
                if(!isset($_POST['Categories']['parentCategory']) || $_POST['Categories']['parentCategory'] == ""){
                    $model->parentCategory = 0;
                }
                if(isset($_POST['Categories']['subcategoryVisible']) || $_POST['Categories']['subcategoryVisible'] != ""){
                    $model->subcategoryVisible = $_POST['Categories']['subcategoryVisible'];
                }
                if(isset($_POST['Categories']['meta_Title']) || $_POST['Categories']['subcategoryVisible'] != ""){
                    $model->meta_Title = $_POST['Categories']['meta_Title'];
                }
                if(isset($_POST['Categories']['meta_Description']) || $_POST['Categories']['subcategoryVisible'] != ""){
                    $model->meta_Description = $_POST['Categories']['meta_Description'];
                }
                $catImage = UploadedFile::getInstances($model,'image');
                if(!empty($catImage)) {
                    $imageName = explode(".",$catImage[0]->name);
                    $model->image = rand(000,9999).'-'.$catImage[0]->extension;
                } else {
                    $model->image = $oldImage;
                }
                $categoryProperty = array();
                if ($_POST['Categories']['itemCondition'] == 1){
                    $categoryProperty['itemCondition'] = 'enable';
                } elseif ($_POST['Categories']['itemCondition'] == 0){
                    $categoryProperty['itemCondition'] = 'disable';
                }
                if ($_POST['Categories']['exchangetoBuy'] == 1){
                    $categoryProperty['exchangetoBuy'] = 'enable';
                }  elseif ($_POST['Categories']['exchangetoBuy'] == 0){
                    $categoryProperty['exchangetoBuy'] = 'disable';
                }
                if ($_POST['Categories']['buyNow'] == 1){
                    $categoryProperty['buyNow'] = 'enable';
                }  elseif ($_POST['Categories']['buyNow'] == 0){
                    $categoryProperty['buyNow'] = 'disable';
                }
                if ($_POST['Categories']['myOffer'] == '1'){
                    $categoryProperty['myOffer'] = 'enable';
                }  elseif ($_POST['Categories']['myOffer'] == 0){
                    $categoryProperty['myOffer'] = 'disable';
                }
                $model->categoryProperty = json_encode($categoryProperty);
                $model->slug = yii::$app->Myclass->productSlug($model->name);
                $model->categoryAttributes = implode(',', $_POST['attributes']);
                if(!empty($catImage)) {
                    $catImage[0]->saveAs('uploads/'.$model->image);
                }
                $model->save(false);
                Yii::$app->session->setFlash('success',Yii::t('app','SubCategory Updated'));
                return $this->redirect(['subcategory','id'=>$cat]);
            }  else  {
                Yii::$app->session->setFlash('warning',Yii::t('app','SubCategory not exists'));
            }
        }

        
        $getParentdata = Categories::find()->where(['categoryId'=>$_GET['cat']])->one();
        $parentAttributes = explode(',', $getParentdata->categoryAttributes);

        $getChildData = Categories::find()->where(['parentCategory'=>$id])->one();
        $childAttributes = explode(',', $getChildData->categoryAttributes);

        $addedAttributes = array_merge(array_filter($parentAttributes),array_filter($childAttributes));
        return $this->render('add', [
            'model' => $model, 
            'parentCategory'=>$parentCategory,
            'attributes'=>$getattributes,
            'parentAttributes'=> $addedAttributes
        ]);
    }

    public function actionUpdatesub_subcategory($id,$cat)
    {
        $model=$this->findModel($id);
        $model->setScenario('update');
        $parentCategory = array();
        $parentCategory = Categories::find()->where(['parentCategory' => 0])->all();
        if (!empty($parentCategory)){
            $parentCategory =\yii\helpers\ArrayHelper::map($parentCategory, 'categoryId', 'name');
        }
        $oldImage = $model->image;
        $categoryProperty = json_decode($model->categoryProperty, true);
        $getattributes = Filter::find()->where(['status' => 1])->all();
        if(isset($_POST['Categories']))
        {
            $existcategory = Categories::find()->where(['<>','categoryId', $id])->andWhere(['name' => $_POST['Categories']['name'], 'parentCategory'=>$cat])->all();
            if(count($existcategory)==0)
            {
                $model->name=$_POST['Categories']['name']; 
                if(!isset($_POST['Categories']['parentCategory']) || $_POST['Categories']['parentCategory'] == ""){
                    $model->parentCategory = 0;
                }
                if(isset($_POST['Categories']['subcategoryVisible']) || $_POST['Categories']['subcategoryVisible'] != ""){
                    $model->subcategoryVisible = $_POST['Categories']['subcategoryVisible'];
                }
                $catImage = UploadedFile::getInstances($model,'image');
                if(!empty($catImage)) {
                    $imageName = explode(".",$catImage[0]->name);
                    $model->image = rand(000,9999).'-'.$catImage[0]->extension;
                } else {
                    $model->image = $oldImage;
                }
                $categoryProperty = array();
                if ($_POST['Categories']['itemCondition'] == 1){
                    $categoryProperty['itemCondition'] = 'enable';
                } elseif ($_POST['Categories']['itemCondition'] == 0){
                    $categoryProperty['itemCondition'] = 'disable';
                }
                if ($_POST['Categories']['exchangetoBuy'] == 1){
                    $categoryProperty['exchangetoBuy'] = 'enable';
                }  elseif ($_POST['Categories']['exchangetoBuy'] == 0){
                    $categoryProperty['exchangetoBuy'] = 'disable';
                }
                if ($_POST['Categories']['buyNow'] == 1){
                    $categoryProperty['buyNow'] = 'enable';
                }  elseif ($_POST['Categories']['buyNow'] == 0){
                    $categoryProperty['buyNow'] = 'disable';
                }
                if ($_POST['Categories']['myOffer'] == '1'){
                    $categoryProperty['myOffer'] = 'enable';
                }  elseif ($_POST['Categories']['myOffer'] == 0){
                    $categoryProperty['myOffer'] = 'disable';
                }
                if(isset($_POST['Categories']['meta_Title']) || $_POST['Categories']['subcategoryVisible'] != ""){
                    $model->meta_Title = $_POST['Categories']['meta_Title'];
                }
                if(isset($_POST['Categories']['meta_Description']) || $_POST['Categories']['subcategoryVisible'] != ""){
                    $model->meta_Description = $_POST['Categories']['meta_Description'];
                }
                $model->categoryProperty = json_encode($categoryProperty);
                $model->slug = yii::$app->Myclass->productSlug($model->name);
                $model->categoryAttributes = implode(',', $_POST['attributes']);
                if(!empty($catImage)) {
                    $catImage[0]->saveAs('uploads/'.$model->image);
                }
                $model->save(false);
                Yii::$app->session->setFlash('success',Yii::t('app','SubCategory Updated'));
                return $this->redirect(['sub_subcategory','id'=>$cat]);
            }  else  {
                Yii::$app->session->setFlash('warning',Yii::t('app','SubCategory not exists'));
            }
        }
        $sub_getParentdata = Categories::find()->where(['categoryId'=>$_GET['cat']])->one();
        $getParentdata = Categories::find()->where(['categoryId'=>$sub_getParentdata->parentCategory])->one();
        $sub_parentAttributes = explode(',', $sub_getParentdata->categoryAttributes);
        $parentAttributes = explode(',', $getParentdata->categoryAttributes);
        $attributes = array_merge($parentAttributes, $sub_parentAttributes);
        return $this->render('sub_add', [
            'model' => $model, 
            'parentCategory'=>$parentCategory,
            'attributes'=>$getattributes,
            'parentAttributes'=> $attributes 
        ]);
    }

    public function actionRemove($id)
    {
        $model = $this->findModel($id);
        $products = Products::find()->where(['category'=>$id])
        ->orWhere(['subCategory'=>$id])->orWhere(['sub_subCategory'=>$id])->all();
        if(empty($products)) {
            $siteSettings =  Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
            $priorityCategories = $siteSettings->category_priority;
            if(!empty($priorityCategories)){
                $priorityCategories = Json::decode($priorityCategories, true);
                if(in_array($id, $priorityCategories)){
                    $restricedCategories = array();
                    foreach($priorityCategories as $priorityKey => $priorityCategory){
                        if($priorityCategory != $id)
                            $restricedCategories[] = $priorityCategory;
                    }
                    $filteredCategories = "";
                    if(!empty($restricedCategories))
                        $filteredCategories = Json::encode($restricedCategories);
                    $siteSettings->category_priority = $filteredCategories;
                    $siteSettings->save(false);
                }
            }
            $subcategories = Categories::find()->where(['parentCategory' => $id])->all();
            foreach($subcategories as $subcategory):
                $subcategory->delete();
            endforeach;
            $model->delete();
            $val = 0; 
        } else {
            $val = 1;
        }
        if($val == 1) {
            Yii::$app->session->setFlash('error',Yii::t('app', 'One or more products has been added to this category.You cannot delete this category'));
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            Yii::$app->session->setFlash('success',Yii::t('app', 'Subcategory Deleted'));
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    public function actionRemovesub($id)
    {
        $model = $this->findModel($id);
        $products = Products::find()->where(['category'=>$id])
        ->orWhere(['subCategory'=>$id])->all();
        if(empty($products)) {
            $siteSettings =  Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
            $priorityCategories = $siteSettings->category_priority;
            if(!empty($priorityCategories)){
                $priorityCategories = Json::decode($priorityCategories, true);
                if(in_array($id, $priorityCategories)){
                    $restricedCategories = array();
                    foreach($priorityCategories as $priorityKey => $priorityCategory){
                        if($priorityCategory != $id)
                            $restricedCategories[] = $priorityCategory;
                    }
                    $filteredCategories = "";
                    if(!empty($restricedCategories))
                        $filteredCategories = Json::encode($restricedCategories);
                    $siteSettings->category_priority = $filteredCategories;
                    $siteSettings->save(false);
                }
            }
            $subcategories = Categories::find()->where(['parentCategory' => $id])->all();
            foreach($subcategories as $subcategory):
                $subcategory->delete();
            endforeach;
            $model->delete();
            $val = 0; 
        } else {
            $val = 1;
        }
        if($val == 1) {
            Yii::$app->session->setFlash('error',Yii::t('app', 'One or more products has been added to this category.You cannot delete this category'));
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            Yii::$app->session->setFlash('success',Yii::t('app', 'Subcategory Deleted'));
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    protected function findModel($id)
    {
        if (($model = Categories::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionCancel()
    {
        return $this->redirect(['index']);
    }

    public function actionGetsublevel()
    {
        $parentLevel = $_POST['parentlevel'];
        $loadFilter = Filter::find()->select('value')->where(['id'=>$parentLevel])->one();
        $splitvals = explode(',', $loadFilter->value);
        $options = '<div class="form-group">';
        $options.= '<label class="control-label">Sub level values</label>';
        $options.= '<select name="sublevel" class="form-control">';
        $options.= '<option value="">Select sublevel<options>';
        foreach($splitvals as $subval)
        {
            $options.='<option value="'.$subval.'">'.$subval.'</options>';
        }
        $options.= '</select>';
        $options.= '</div>';
        return $options;
    }

    public function actionCancels($id)
    {
        return $this->redirect(['subcategory','id'=>$id]);
    }
}