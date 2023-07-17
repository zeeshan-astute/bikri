<?php
use yii\helpers\Html;
use yii\authclient\widgets\AuthChoice;
use yii\bootstrap\ActiveForm;
use common\models\Sitesettings;

$siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
$fb_appid = $siteSettings->fb_appid;
?>
<link type="text/css" rel="stylesheet" href="https://www.gstatic.com/firebasejs/ui/4.2.0/firebase-ui-auth.css" />
<script src="http://www.gstatic.com/firebasejs/5.0.4/firebase.js"></script>
<script src="https://cdn.firebase.com/libs/firebaseui/2.3.0/firebaseui.js"></script>
<link type="text/css" rel="stylesheet" href="https://cdn.firebase.com/libs/firebaseui/2.3.0/firebaseui.css" />  
<div id="content" style="min-height: 719px;">
  <div class="slider container container-1 section_container">
    <div class="row">
      <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <!-- Bottom to top-->
        <div class="row product_align_cnt">
          <div class="display-flex modal-dialog modal-dialog-width">
            <div class="signup-modal-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
              <div class="signup-modal-header col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
                <h2 class="signup-header-text"><?php echo Yii::t('app','Sign Up'); ?></h2>
              </div>
              <div class="sigup-line col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"></div>
              <div class="signup-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding ">
                <div class="signup-box col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
                  <?php $form = ActiveForm::begin(['id' => 'form-signup','options' => ['onsubmit' => 'return signuppage()'],]); ?>
                  <div class="signup-text-box col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
                    <?= $form->field($model, 'name')->textInput(['placeholder'=>Yii::t('app','Enter your Name'),'id' => 'site_name'])->label(false); ?>
                    <div id="site_name_em_" class="required"></div>
                    <?= $form->field($model, 'username', ['enableAjaxValidation' => true])->textInput(['placeholder'=>Yii::t('app','Enter your Username'),'id' => 'site_username'])->label(false); ?>
                    <div id="site_username_em_" class="required"></div>
                    <?= $form->field($model, 'email', ['enableAjaxValidation' => true])->textInput(['placeholder'=>Yii::t('app','Enter your email address'),'id' => 'site_email'])->label(false); ?>
                    <div id="site_email_em_" class="required"></div>
                    <?= $form->field($model, 'password')->passwordInput(['placeholder'=>Yii::t('app','Enter your Password'),'id' => 'site_password'])->label(false); ?>
                    <div id="site_password_em_" class="required"></div>
                    <?= $form->field($model, 'password_repeat', ['enableAjaxValidation' => true])->passwordInput(['placeholder'=>Yii::t('app','Enter confirm Password'),'id' => 'site_confirm_password'])->label(false); ?>
                    <div id="site_confirm_password_em_" class="required"></div>
                    <?= $form->field($model, 'phone')->textInput(['placeholder'=>Yii::t('app','Verify Your Number'), 'onclick' => 'js:switchVisible_addphone();','id' => 'site_phone', 'readonly'=> true ])->label(false); ?>
                    <div id="site_phone_em_" class="required"></div>
                    <div id="firebaseui-auth-container"></div>
                  </div>
                  <div class="form-group">
                    <?= Html::submitButton(Yii::t('app', 'Sign up'), ['class' => 'col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding login-btn', 'name' => 'submit', 'id' => 'submit']) ?>
                  </div>
                  <?php ActiveForm::end(); ?>
                </div>
              </div>
              <?php $lineMaring = "no-margin";
              $socialLogin = Yii::$app->Myclass->getsocialLoginDetails(); ?>
              <?php if($socialLogin['facebook']['status'] == 'enable' || $socialLogin['google']['status'] == 'enable'){ ?>
                <div class="login-div-line col-xs-12 col-sm-12 col-md-12 col-lg-12">
                  <div class="left-div-line"></div>
                  <div class="right-div-line"></div>
                  <span class="login-or"><?php echo Yii::t('app','Social signup'); ?></span>
                </div>
                <div class="social-login col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
                  <div class="social-login-center">
                    <?php $authAuthChoice = AuthChoice::begin([
                      'baseAuthUrl' => ['site/auth']
                    ]);
                    $client=$authAuthChoice->getClients();
                    ?>
                    <?php     if($socialLogin['facebook']['status'] == 'enable'){ ?>
                      <div class="facebook-login">
                        <a href='<?php echo Yii::$app->getUrlManager()->getBaseUrl().'/site/auth?authclient=facebook'; ?>' title='Facebook'>
                          <img src="<?php echo Yii::$app->urlManager->createAbsoluteUrl("/images/design/facebook.png"); ?>" alt="Facebook">
                        </a>
                      </div>
                    <?php } ?>
                    <?php if($socialLogin['google']['status'] == 'enable'){ ?>
                     <div id="customBbtn" class="facebook-login">
                      <img src="<?php echo Yii::$app->getUrlManager()->createAbsoluteUrl("/images/design/google-plus.png"); ?>" alt="Google">
                    </div>
                  <?php } ?>
                </div>
              </div>
            </div>
            <?php $lineMaring = ""; ?>
          <?php } ?>
          <div class="login-line-2 col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"></div>
          <div class="user-login col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
            <span><?php echo Yii::t('app','Already a member?'); ?></span><?=Html::a(Yii::t('app','Login'), ['site/login'],['class'=>'login-link txt-pink-color'])?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
</div>     
<?php  $baseUrl = Yii::$app->getUrlManager()->getBaseUrl(); ?>
<input type="hidden" id="baseUrl" value="<?php echo $baseUrl; ?>">
<input type="hidden" id="firebase_appid" value="<?php echo $fb_appid;?>">
<script src="https://www.gstatic.com/firebasejs/4.1.3/firebase.js"></script>

<script src="<?=Yii::$app->getUrlManager()->getBaseUrl()?>/frontend/web/js/firebaseui.js"></script>
<link type="text/css" rel="stylesheet" href="<?=Yii::$app->getUrlManager()->getBaseUrl()?>/frontend/web/css/firebaseui.css" />
 <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>

<script>
    var appid = document.getElementById("firebase_appid").value;
    var firebaseConfig = {
      apiKey: appid,
      };
    firebase.initializeApp(firebaseConfig);
      
    var ui = new firebaseui.auth.AuthUI(firebase.auth());

    var uiConfig = {
        callbacks: {
            signInSuccessWithAuthResult: function(authResult, redirectUrl) {
                var phone_no = authResult['user']['phoneNumber'];
                $('#phone_number_verifi').html(phone_no);
                $('#phone').val(phone_no);
                $('#signupform-phone').val(phone_no);
                $('#site_phone').val(phone_no);
                $('#instant').css("display", "block");
                $('#verifybox').hide();
            },
            uiShown: function() {
                console.log('shown');
                document.getElementById('loader').style.display = 'none';
            }
        },
          signInFlow: 'popup',
          signInSuccessUrl: "<?=Yii::$app->getUrlManager()->getBaseUrl()?>/",
          signInOptions: [
          
            firebase.auth.PhoneAuthProvider.PROVIDER_ID
          ],
          tosUrl: "<?=Yii::$app->getUrlManager()->getBaseUrl()?>/",
          privacyPolicyUrl: "<?=Yii::$app->getUrlManager()->getBaseUrl()?>/",
    };
    function switchVisible_addphone() {
        ui.start('#firebaseui-auth-container', uiConfig);
    }
</script>
<style type="text/css">  
  #customBbtn:hover {
    cursor: pointer;
  }
</style>
