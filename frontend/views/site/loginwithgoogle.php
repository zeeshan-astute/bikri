<?php
use yii\helpers\Html;
use yii\authclient\widgets\AuthChoice;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\db\Expression;
use common\models\Sitesettings;
if (isset($type) && $type == 'google') {
	$username = $data->first_name;
	$name = $data->name->givenName;
	$email = $data->email;
}
$siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
$sitekey = !empty($siteSettings->google_recaptcha_key) ? $siteSettings->google_recaptcha_key : "";
?>
<!-- Google recapch addon -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<div class="slider container container-1 section_container">
			  <div class="row">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
					  <div class="row product_align_cnt">
						<div class="display-flex modal-dialog modal-dialog-width">
							<div class="signup-modal-content col-xs-8 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
								<div class="signup-modal-header col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
									<h2 class="signup-header-text"><?php echo Yii::t('app','Sign Up'); ?></h2>
								</div>
									<div class="sigup-line col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"></div>
										<div class="signup-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding " style="padding-bottom:0px">
											<div class="signup-box col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
											<?php $form = ActiveForm::begin(['id' => 'form-signup','options' => ['onsubmit' => 'return socialsignuppage()'],]); ?>
													<div class="signup-text-box col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
                              <?= $form->field($model, 'name')->textInput(['placeholder'=>Yii::t('app','Enter your Name'),'id' => 'social_site_name','value'=>$name])->label(false); ?>
                              <div id="site_name_em_" class="required"></div>
                              <?= $form->field($model, 'username')->textInput(['placeholder'=>Yii::t('app','Enter your Username'),'id' => 'social_user_name', 'value' => $username])->label(false); ?>
                              <div id="site_username_em_" class="required"></div>
                              <?= $form->field($model, 'email')->textInput(['placeholder'=>Yii::t('app','Enter your email address'),'id' => 'social_site_email', 'value'=>$email])->label(false); ?>
                               <div id="site_email_em_" class="required"></div>
                              <?= $form->field($model, 'password')->passwordInput(['placeholder'=>Yii::t('app','Enter your Password'), 'id' => 'social_site_pass'])->label(false); ?>
                              <div id="site_password_em_" class="required"></div>
															<?= $form->field($model, 'password_repeat')->passwordInput(['placeholder'=>Yii::t('app','Enter confirm Password'), 'id' => 'social_site_cpass'])->label(false); ?>
															<div id="site_confirm_password_em_" class="required"></div>
															<!-- Google recapcha addon -->
															<!-- <div class="g-recaptcha" data-sitekey="<?php //echo $sitekey;?>" ></div>
															<div id="site_confirm_captcha_em_" class="required"></div> -->
													</div>
                          <?= Html::submitButton(Yii::t('app','Sign Up'), ['class' => 'col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding login-btn', 'name' => 'signup-button', 'onclick' => 'aaa()']) ?>
                          <?php ActiveForm::end(); ?>
											</div>					
										</div>	
														<div class="user-login col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">				
															<span><?php echo Yii::t('app','Already a member?'); ?></span><?=Html::a(Yii::t('app','Login'), ['site/login'],['class'=>'login-link txt-pink-color'])?>
														</div>
							</div>
						</div>
					  </div>
				</div>
			</div>
		</div>