<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use conquer\toastr\ToastrWidget;
use kartik\alert\Alert;
?>
<?php //echo '<pre>';print_r($model);die;?>
<?php $form = ActiveForm::begin(['id'=>'sitesettings-form','options' =>['class' => 'boxShadow p-3 bgWhite m-b20','enctype' => 'multipart/form-data','onsubmit' => 'return checkvalidation()']]); ?>
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
        <h4 class="m-b25  blueTxtClr p-t10 p-b10"><?php echo Yii::t('app','Adsense').' '.Yii::t('app','Settings'); ?></h4>
        
        <div class="form-group ">
            <label><u><?php echo Yii::t('app','Google Ads Footer'); ?> </u></label>
				<div class="m-b20 d-flex">
					<div class="m-r50">
						<div class="custom-control custom-radio">
								<input type="radio" class="custom-control-input" id="enable_google_ads_footer" name="Sitesettings[google_ads_footer]" value="1" <?php if($model->google_ads_footer == '1')echo 'checked'?>>
								<label class="custom-control-label" for="enable_google_ads_footer"><?php echo Yii::t('app','Enable'); ?></label>
						</div>
					</div>
					<div class="custom-control custom-radio">
							<input type="radio" class="custom-control-input" id="Disable_google_ads_footer" name="Sitesettings[google_ads_footer]" value="0" <?php if($model->google_ads_footer == '0' &&  $model->google_ads_footer == ' ')echo 'checked'?>>
							<label class="custom-control-label" for="Disable_google_ads_footer"><?php echo Yii::t('app','Disable'); ?></label>
					</div>
			</div>
		</div>
				
		<div class="form-group">
			<label><?php echo Yii::t('app','Google Ads Client'); ?> </label>
			<?= $form->field($model, 'google_ad_client_footer')->textInput(['class' => 'form-control','placeholder'=>Yii::t('app','Enter google ad client footer'),'id'=>'google_ad_client_footer'])->label(false); ?>
			<p class="text-danger" id="Sitesettings_google_ad_client_footer"></p>
		</div>

		<div class="form-group">
			<label><?=Yii::t('app','Google Adslot')?> </label>
			<?= $form->field($model, 'google_ad_slot_footer')->textInput(['class' => 'form-control','maxlength' => true,'placeholder'=>Yii::t('app','Enter google ad slot footer'),'id'=>'google_ad_slot_footer'])->label(false); ?>
			<p class="text-danger" id="Sitesettings_google_ad_slot_footer"></p>
		</div>
				


		<div class="form-group ">
            <label><u><?php echo Yii::t('app','Google Ads Profile'); ?></u> </label>
				<div class="m-b20 d-flex">
					<div class="m-r50">
						<div class="custom-control custom-radio">
								<input type="radio" class="custom-control-input" id="enable_google_ads_profile" name="Sitesettings[google_ads_profile]" value="1" <?php if($model->google_ads_profile == '1')echo 'checked'?>>
								<label class="custom-control-label" for="enable_google_ads_profile"><?php echo Yii::t('app','Enable'); ?></label>
						</div>
					</div>
					<div class="custom-control custom-radio">
							<input type="radio" class="custom-control-input" id="Disable_google_ads_profile" name="Sitesettings[google_ads_profile]" value="0" <?php if($model->google_ads_profile == '0')echo 'checked'?>>
							<label class="custom-control-label" for="Disable_google_ads_profile"><?php echo Yii::t('app','Disable'); ?></label>
					</div>
			</div>
		</div>
				
		<div class="form-group">
			<label><?php echo Yii::t('app','Google Ads Client'); ?> </label>
			<?= $form->field($model, 'google_ad_client_profile')->textInput(['class' => 'form-control','placeholder'=>Yii::t('app','Enter google ad client profile'),'id'=>'google_ad_client_profile'])->label(false); ?>
			<p class="text-danger" id="Sitesettings_google_ad_client_profile"></p>
		</div>

		<div class="form-group">
			<label><?=Yii::t('app','Google Adslot')?> </label>
			<?= $form->field($model, 'google_ad_slot_profile')->textInput(['class' => 'form-control','maxlength' => true,'placeholder'=>Yii::t('app','Enter google ad slot profile'),'id'=>'google_ad_slot_profile'])->label(false); ?>
			<p class="text-danger" id="Sitesettings_google_ad_slot_profile"></p>
		</div>




		<div class="form-group ">
            <label><u><?php echo Yii::t('app','Google Ads Product'); ?></u> </label>
				<div class="m-b20 d-flex">
					<div class="m-r50">
						<div class="custom-control custom-radio">
								<input type="radio" class="custom-control-input" id="enable_google_ads_product" name="Sitesettings[google_ads_product]" value="1" <?php if($model->google_ads_product == '1')echo 'checked'?>>
								<label class="custom-control-label" for="enable_google_ads_product"><?php echo Yii::t('app','Enable'); ?></label>
						</div>
					</div>
					<div class="custom-control custom-radio">
							<input type="radio" class="custom-control-input" id="Disable_google_ads_product" name="Sitesettings[google_ads_product]" value="0" <?php if($model->google_ads_product == '0')echo 'checked'?>>
							<label class="custom-control-label" for="Disable_google_ads_product"><?php echo Yii::t('app','Disable'); ?></label>
					</div>
			</div>
		</div>
				
		<div class="form-group">
			<label><?php echo Yii::t('app','Google Ads Client'); ?> </label>
			<?= $form->field($model, 'google_ad_client_product')->textInput(['class' => 'form-control','placeholder'=>Yii::t('app','Enter google ad client product'),'id'=>'google_ad_client_product'])->label(false); ?>
			<p class="text-danger" id="Sitesettings_google_ad_client_product"></p>
		</div>

		<div class="form-group">
			<label><?=Yii::t('app','Google Adslot')?> </label>
			<?= $form->field($model, 'google_ad_slot_product')->textInput(['class' => 'form-control','maxlength' => true,'placeholder'=>Yii::t('app','Enter google ad slot product'),'id'=>'google_ad_slot_product'])->label(false); ?>
			<p class="text-danger" id="Sitesettings_google_ad_slot_product"></p>
		</div>






		<div class="form-group ">
            <label><u><?php echo Yii::t('app','Google Ads Product Right'); ?></u> </label>
				<div class="m-b20 d-flex">
					<div class="m-r50">
						<div class="custom-control custom-radio">
								<input type="radio" class="custom-control-input" id="enable_google_ads_productright" name="Sitesettings[google_ads_productright]" value="1" <?php if($model->google_ads_productright == '1')echo 'checked'?>>
								<label class="custom-control-label" for="enable_google_ads_productright"><?php echo Yii::t('app','Enable'); ?></label>
						</div>
					</div>
					<div class="custom-control custom-radio">
							<input type="radio" class="custom-control-input" id="Disable_google_ads_productright" name="Sitesettings[google_ads_productright]" value="0" <?php if($model->google_ads_productright == '0')echo 'checked'?>>
							<label class="custom-control-label" for="Disable_google_ads_productright"><?php echo Yii::t('app','Disable'); ?></label>
					</div>
			</div>
		</div>
				
		<div class="form-group">
			<label><?php echo Yii::t('app','Google Ads Client'); ?> </label>
			<?= $form->field($model, 'google_ad_client_productright')->textInput(['class' => 'form-control','placeholder'=>Yii::t('app','Enter google ad client product right'),'id'=>'google_ad_client_productright'])->label(false); ?>
			<p class="text-danger" id="Sitesettings_google_ad_client_productright"></p>
		</div>

		<div class="form-group">
			<label><?=Yii::t('app','Google Adslot')?> </label>
			<?= $form->field($model, 'google_ad_slot_productright')->textInput(['class' => 'form-control','maxlength' => true,'placeholder'=>Yii::t('app','Enter google ad slot product right'),'id'=>'google_ad_slot_productright'])->label(false); ?>
			<p class="text-danger" id="Sitesettings_google_ad_slot_productright"></p>
		</div>









		<div class="form-group ">
            <label><u><?php echo Yii::t('app','Google Ads Mobile'); ?></u> </label>
				<div class="m-b20 d-flex">
					<div class="m-r50">
						<div class="custom-control custom-radio">
								<input type="radio" class="custom-control-input" id="enable_google_ads_mobile" name="Sitesettings[google_ads_mobile]" value="1" <?php if($model->google_ads_mobile == '1')echo 'checked'?>>
								<label class="custom-control-label" for="enable_google_ads_mobile"><?php echo Yii::t('app','Enable'); ?></label>
						</div>
					</div>
					<div class="custom-control custom-radio">
							<input type="radio" class="custom-control-input" id="Disable_google_ads_mobile" name="Sitesettings[google_ads_mobile]" value="0" <?php if($model->google_ads_mobile == '0')echo 'checked'?>>
							<label class="custom-control-label" for="Disable_google_ads_mobile"><?php echo Yii::t('app','Disable'); ?></label>
					</div>
			</div>
		</div>
				
		<div class="form-group">
			<label><?php echo Yii::t('app','Google Ads Android'); ?> </label>
			<?= $form->field($model, 'google_ad_client_mobile')->textInput(['class' => 'form-control','placeholder'=>Yii::t('app','Enter google ad client product right'),'id'=>'google_ad_client_mobile'])->label(false); ?>
			<p class="text-danger" id="Sitesettings_google_ad_client_mobile"></p>

			<label><?php echo Yii::t('app','Google Ads IOS'); ?> </label>
			<?= $form->field($model, 'google_ad_client_ios')->textInput(['class' => 'form-control','placeholder'=>Yii::t('app','Enter google ad client product right'),'id'=>'google_ad_client_ios'])->label(false); ?>
			<p class="text-danger" id="Sitesettings_google_ad_client_ios"></p>
		</div>	

		<div class="form-group">
		<p><b>Note:</b> <span style="color:red;">Google adsense setting has vertical ads size are 336x280 & 280x320 and Horizantal ads are size 780x90 & 1130x100 only</span>.</p>
		</div>


		<div class="m-t20">
				<?= Html::submitButton(Yii::t('app','Save'), ['class' => 'btn btn-primary align-text-top border-0 m-b10']) ?>
		</div>
       
							
<?php ActiveForm::end(); ?>


<script type="text/javascript">
		function checkvalidation() {
	//alert(0);
	var s1 = $("#google_ad_client_footer").val();
	var s2 = $("#google_ad_slot_footer").val();
	var s3 = $("#google_ad_client_profile").val();
	var s4 = $("#google_ad_slot_profile").val();
	var s5 = $("#google_ad_client_product").val();
	var s6 = $("#google_ad_slot_product").val();
	var s7 = $("#google_ad_client_productright").val();
	var s8 = $("#google_ad_slot_productright").val();
	var s9 = $("#google_ad_client_mobile").val();
	var s10 = $("#google_ad_client_ios").val();
	//alert(s1);
if (s1 == "") {
		$("#Sitesettings_google_ad_client_footer").html(yii.t('app','Google Ad Client Footer cannot be blank.'));
		 setTimeout(function() {
            $("#Sitesettings_google_ad_client_footer").fadeOut();
        }, 3000);
		return false;
	} else {
		$("#Sitesettings_google_ad_client_footer").fadeOut();
	}
	if (s2 == "") {
		$("#Sitesettings_google_ad_slot_footer").html(yii.t('app','Google Ad Slot Footer cannot be blank.'));
		 setTimeout(function() {
            $("#Sitesettings_google_ad_slot_footer").fadeOut();
        }, 3000);
		return false;
	} else {
		$("#Sitesettings_google_ad_slot_footer").fadeOut();
	}
	if (s3 == "") {
		$("#Sitesettings_google_ad_client_profile").html(yii.t('app','Google Ad Client Profile cannot be blank.'));
		 setTimeout(function() {
            $("#Sitesettings_google_ad_client_profile").fadeOut();
        }, 3000);
		return false;
	} else {
		$("#Sitesettings_google_ad_client_profile").fadeOut();
	}
	if (s4 == "") {
		$("#Sitesettings_google_ad_slot_profile").html(yii.t('app','Google Ad Slot Profile cannot be blank.'));
		 setTimeout(function() {
            $("#Sitesettings_google_ad_slot_profile").fadeOut();
        }, 3000);
		return false;
	} else {
		$("#Sitesettings_google_ad_slot_profile").fadeOut();
	}
	if (s5 == "") {
		$("#Sitesettings_google_ad_client_product").html(yii.t('app','Google Ad Client Product cannot be blank.'));
		 setTimeout(function() {
            $("#Sitesettings_google_ad_client_product").fadeOut();
        }, 3000);
		return false;
	} else {
		$("#Sitesettings_google_ad_client_product").fadeOut();
	}
	if (s6 == "") {
		$("#Sitesettings_google_ad_slot_product").html(yii.t('app','Google Ad Slot Product cannot be blank.'));
		 setTimeout(function() {
            $("#Sitesettings_google_ad_slot_product").fadeOut();
        }, 3000);
		return false;
	} else {
		$("#Sitesettings_google_ad_slot_product").fadeOut();
	}
	if (s7 == "") {
		$("#Sitesettings_google_ad_client_productright").html(yii.t('app','Google Ad Client Productright cannot be blank.'));
		 setTimeout(function() {
            $("#Sitesettings_google_ad_client_productright").fadeOut();
        }, 3000);
		return false;
	} else {
		$("#Sitesettings_google_ad_client_productright").fadeOut();
	}
	if (s8 == "") {
		$("#Sitesettings_google_ad_slot_productright").html(yii.t('app','Google Ad Slot Productright cannot be blank.'));
		 setTimeout(function() {
            $("#Sitesettings_google_ad_slot_productright").fadeOut();
        }, 3000);
		return false;
	} else {
		$("#Sitesettings_google_ad_slot_productright").fadeOut();
	}
	if (s9 == "") {
		$("#Sitesettings_google_ad_client_mobile").html(yii.t('app','Google Ad Slot Productright cannot be blank.'));
		 setTimeout(function() {
            $("#Sitesettings_google_ad_client_mobile").fadeOut();
        }, 3000);
		return false;
	} else {
		$("#Sitesettings_google_ad_client_mobile").fadeOut();
	}
	if (s10 == "") {
		$("#Sitesettings_google_ad_client_ios").html(yii.t('app','Google Ad Slot Productright cannot be blank.'));
		 setTimeout(function() {
            $("#Sitesettings_google_ad_client_ios").fadeOut();
        }, 3000);
		return false;
	} else {
		$("#Sitesettings_google_ad_client_ios").fadeOut();
	}
return true;
	}
	</script>			
