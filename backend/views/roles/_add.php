<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Sitesettings;
use yii\helpers\Json;
$settings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
$sitePaymentMode = json::decode($settings->sitepaymentmodes, true);
use kartik\alert\Alert;
?>
    <?php 
            if(Yii::$app->session->hasFlash('success')): 
                echo Alert::widget([
                'type' => Alert::TYPE_SUCCESS,
                'body' => Yii::$app->session->getFlash('success'),
                'delay' => 8000
                ]); 
            endif; 
            if(Yii::$app->session->hasFlash('error')): 
                echo Alert::widget([
                'type' => Alert::TYPE_DANGER,
                'body' => Yii::$app->session->getFlash('error'),
                'delay' => 8000
                ]); 
            endif; 
        ?>
<div class="roles-form">
    <?php $form = ActiveForm::begin(['options' =>['class' => 'boxShadow p-3 bgWhite m-b20']]); ?>
    <h4 class="m-b25  blueTxtClr p-t10 p-b10"> <?php echo Yii::t('app','Create').' '.Yii::t('app','Roles'); ?></h4>
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'comments')->textInput(['maxlength' => true]) ?>
  <div class="row">
  <div class="col-lg-9 col-md-6">
<h5 class="text-dark header-title m-t-0 m-b-30"><?=Yii::t('app','Assign Roles')?></h5></div>
  <div class="col-lg-3 col-md-6">
    <div style="text-align:right;">
      <div class="form-group" >
      <div class="m-b20 d-flex">
        <div class="m-r50">
          <div class="custom-control custom-checkbox">
            <input  type="hidden"  />
            <input type="checkbox" class="custom-control-input" onclick="toggle(this);"  id="check_uncheck" >
            <label class="custom-control-label" for="check_uncheck"><?=Yii::t('app', 'Select all')?></label>
          </div>
        </div>
      </div>
        </div>
      </div>   
    </div>  
    </div>                              
<div class="row">
      <div class="col-lg-4 col-md-6">
      <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges1" value="users" >
              <label class="custom-control-label" for="priviliges1"><?=Yii::t('app', 'Users Management')?></label>
            </div>
          </div>
        </div>
        </div>
          <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges2" value="module" >
              <label class="custom-control-label" for="priviliges2"><?=Yii::t('app', 'Module Management')?></label>
            </div>
          </div>
        </div>
        </div>
        <?php if($settings['promotionStatus']==1){?>  
         <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges3" value="promotion" >
              <label class="custom-control-label" for="priviliges3"><?=Yii::t('app', 'Promotions')?></label>
            </div>
          </div>
        </div>
        </div>                       
   <?php } ?>                                    
    </div>
    <div class="col-lg-4 col-md-6">
             <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges4" value="notification" >
              <label class="custom-control-label" for="priviliges4"><?=Yii::t('app', 'Notification')?></label>
            </div>
          </div>
        </div>
        </div>  
          <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges5" value="currency" >
              <label class="custom-control-label" for="priviliges5"><?=Yii::t('app', 'Currency Management')?></label>
            </div>
          </div>
        </div>
        </div>  
         <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges6" value="help" >
              <label class="custom-control-label" for="priviliges6"><?=Yii::t('app', 'Help Pages Management')?></label>
            </div>
          </div>
        </div>
        </div>                                           
          </div>                                
    <div class="col-lg-4 col-md-6">
          <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges7" value="category" >
              <label class="custom-control-label" for="priviliges7"><?=Yii::t('app', 'Category Management')?></label>
            </div>
          </div>
        </div> 
         </div>   
             <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges8" value="filters" >
              <label class="custom-control-label" for="priviliges8"><?=Yii::t('app', 'Filters Management')?></label>
            </div>
          </div>
        </div> 
         </div>                                          
          <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges9" value="seo" >
              <label class="custom-control-label" for="priviliges9"><?=Yii::t('app', 'SEO Settings')?></label>
            </div>
          </div>
        </div> 
         </div>   
          <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges10" value="banner" >
              <label class="custom-control-label" for="priviliges10"><?=Yii::t('app', 'Banner Management')?></label>
            </div>
          </div>
        </div> 
         </div>                                         
    </div>
  </div>                                           
     <div class="row">
     <div class="col-lg-4 col-md-6" id="gameschild">
       <h6 class="m-b20"> <?=Yii::t('app','REVENUE MANAGEMENT')?></h6>
              <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges11" value="revenue" >
              <label class="custom-control-label" for="priviliges11"><?=Yii::t('app', 'Revenue Management')?></label>
            </div>
          </div>
        </div> 
         </div>   
        <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges12" value="revenuelog" >
              <label class="custom-control-label" for="priviliges12"><?=Yii::t('app', 'Revenue Log')?></label>
            </div>
          </div>
        </div> 
         </div>      
         <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges13" value="promotionlog" >
              <label class="custom-control-label" for="priviliges13"><?=Yii::t('app', 'Promotion Log')?></label>
            </div>
          </div>
        </div> 
         </div> 
               <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges31" value="paidbanner" >
              <label class="custom-control-label" for="priviliges31"><?=Yii::t('app', 'Paidbanner Log')?></label>
            </div>
          </div>
        </div> 
         </div> 
   </div>
<div class="col-lg-4 col-md-6" id="gameschild">
<h6 class="m-b20"><?=Yii::t('app','ROLES &  PRIVILEGES')?></h6>
     <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges14" value="roles" >
              <label class="custom-control-label" for="priviliges14"><?=Yii::t('app', 'Roles')?></label>
            </div>
          </div>
        </div> 
         </div> 
           <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges15" value="moderator" >
              <label class="custom-control-label" for="priviliges15"><?=Yii::t('app', 'Moderator')?></label>
            </div>
          </div>
        </div> 
         </div> 
</div>
<div class="col-lg-4 col-md-6">
<h6 class="m-b20"> <?=Yii::t('app','SITE PAYMENT OPTIONS')?></h6>
          <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges16" value="sitepayment" >
              <label class="custom-control-label" for="priviliges16"><?=Yii::t('app', 'Site Payment Modes')?></label>
            </div>
          </div>
        </div> 
         </div> 
               <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges17" value="braintree" >
              <label class="custom-control-label" for="priviliges17"><?=Yii::t('app', 'Brain Tree Settings')?></label>
            </div>
          </div>
        </div> 
         </div> 
                   <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges51" value="stripe" >
              <label class="custom-control-label" for="priviliges51"><?=Yii::t('app', 'StripeSettings')?></label>
            </div>
          </div>
        </div> 
         </div> 
           </div>                                
     </div>                                                                 
   <div class="row">
      <div class="col-lg-4 col-md-6" id="gameschild">
            <h6 class="m-b20"> <?=Yii::t('app','ITEMS MANAGEMENT')?></h6>
                      <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges18" value="items" >
              <label class="custom-control-label" for="priviliges18"><?=Yii::t('app', 'Items Management')?></label>
            </div>
          </div>
        </div> 
         </div> 
        <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges19" value="productCnt" >
              <label class="custom-control-label" for="priviliges19"><?=Yii::t('app', 'Product Condition Management')?></label>
            </div>
          </div>
        </div> 
         </div> 
          <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges20" value="report" >
              <label class="custom-control-label" for="priviliges20"><?=Yii::t('app', 'Report Item Management')?></label>
            </div>
          </div>
        </div> 
         </div>
        <!-- <div class="form-group ">
          <div class="m-b20 d-flex">
            <div class="m-r50">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges32" value="freelist" >
                <label class="custom-control-label" for="priviliges32"><?=Yii::t('app', 'Subscription Management')?></label>
              </div>
            </div>
          </div> 
        </div> 
        <div class="form-group ">
          <div class="m-b20 d-flex">
            <div class="m-r50">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges33" value="user_freelist" >
                <label class="custom-control-label" for="priviliges33"><?=Yii::t('app', 'User Subscription Management')?></label>
              </div>
            </div>
          </div> 
        </div>  -->                                                                      
            </div>                                                                           
    <div class="col-lg-4 col-md-6">
                           <h6 class="m-b20"><?=Yii::t('app','SITE SETTINGS')?></h6>
                              <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges21" value="api" >
              <label class="custom-control-label" for="priviliges21"><?=Yii::t('app', 'API Credentials')?></label>
            </div>
          </div>
        </div> 
         </div> 
                                                 <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges22" value="socialNtw" >
              <label class="custom-control-label" for="priviliges22"><?=Yii::t('app', 'Social Networks')?></label>
            </div>
          </div>
        </div> 
         </div>   
         <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges23" value="footer" >
              <label class="custom-control-label" for="priviliges23"><?=Yii::t('app', 'Footer Settings')?></label>
            </div>
          </div>
        </div> 
         </div>                                                 
        <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges24" value="default" >
              <label class="custom-control-label" for="priviliges24"><?=Yii::t('app', 'Default Settings')?></label>
            </div>
          </div>
        </div> 
         </div>                                          
       <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges25" value="email" >
              <label class="custom-control-label" for="priviliges25"><?=Yii::t('app', 'Email Settings')?></label>
            </div>
          </div>
        </div> 
         </div>
         <div class="form-group ">
          <div class="m-b20 d-flex">
            <div class="m-r50">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges34" value="adsense">
                <label class="custom-control-label" for="priviliges34"><?=Yii::t('app', 'Adsense Management')?></label>
              </div>
            </div>
          </div> 
        </div> 
        <!-- <div class="form-group ">
          <div class="m-b20 d-flex">
            <div class="m-r50">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges35" value="addons">
                <label class="custom-control-label" for="priviliges35"><?=Yii::t('app', 'Addons Management')?></label>
              </div>
            </div>
          </div> 
        </div>   -->                                     
        <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges26" value="sms" >
              <label class="custom-control-label" for="priviliges26"><?=Yii::t('app', 'Mobile SMS Settings')?></label>
            </div>
          </div>
        </div> 
         </div>                                                
                                                  <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges27" value="logo" >
              <label class="custom-control-label" for="priviliges27"><?=Yii::t('app', 'Logo Settings')?></label>
            </div>
          </div>
        </div> 
         </div> 
        </div>                                  
                           
                                    <?php if($sitePaymentMode['buynowPaymentMode']==1){ ?>
                                           <div class="col-lg-4 col-md-6">
                                           <h6 class="m-b20"> <?=Yii::t('app','BUY NOW MANAGEMENT')?> </h6>
                                                  <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges28" value="commission" >
              <label class="custom-control-label" for="priviliges28"><?=Yii::t('app', 'Commission Setup')?></label>
            </div>
          </div>
        </div> 
         </div> 
                                                  <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges29" value="invoice" >
              <label class="custom-control-label" for="priviliges29"><?=Yii::t('app', 'Invoices')?></label>
            </div>
          </div>
        </div> 
         </div> 
                                                  <div class="form-group ">
        <div class="m-b20 d-flex">
          <div class="m-r50">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input priviliges" name="priviliges[]" id="priviliges30" value="order" >
              <label class="custom-control-label" for="priviliges30"><?=Yii::t('app', 'Orders Management')?></label>
            </div>
          </div>
        </div> 
         </div> 
                                           </div><?php } ?>

  </div>
  <div class="field-roles-checkboxes">
    <div class="help-block"></div>
  </div>
   <div class="field-roles-privilages">
   </div>
   <div class="form-group">
       <?= Html::submitButton(Yii::t('app','Save'), ['class' => 'btn btn-primary' , 'id'=>'saverolebtn']) ?>
   </div>
   <?php ActiveForm::end(); ?>
</div>
<script>
function toggle(source) {
    var checkboxes = document.querySelectorAll('input[type="checkbox"]');
    for (var i = 1; i < checkboxes.length; i++) {

        if (checkboxes[i] != source)
            checkboxes[i].checked = source.checked;
    }

}
</script>   
