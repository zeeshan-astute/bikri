<?php
use yii\helpers\Html;
use yii\authclient\widgets\AuthChoice;
use conquer\toastr\ToastrWidget;
use common\models\Sitesettings;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
$siteSettings = Sitesettings::find()->orderBy(['id' => SORT_DESC])->one();
$fb_appid = $siteSettings->fb_appid;

$user = json_decode(base64_decode($user));
//echo "<pre>"; print_r($data->type);die;
$data = $data;

$flag = $_GET['login'];
if(isset($data) && $data->type == 'google'){
  $flag = 2;
  $givenName = $data->name->givenName;
  $google_id = $data->id;
  $url = $data->image->url;
}
if(isset($data) && $type == 'facebook'){
  $flag = 3;
  $fbid = $data['id'];
  $fbfirstname = $data['first_name'];
  $fblastname = $data['last_name'];
  $fbemail = $data['email'];
  
}




?>


<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
 <!-- Bottom to top-->
 <div class="row product_align_cnt">




  <div class="display-flex modal-dialog modal-dialog-width">

   
   <div class="login-modal-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
    <div class="login-modal-header col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
      <h2 class="login-header-text"> <?php echo Yii::t('app','Login to'); ?>&nbsp;<?php echo yii::$app->Myclass->getSiteName(); ?></h2>
      <p class="login-sub-header-text"><?php echo Yii::t('app','Signup or login to explore the great things available near you'); ?></p>
      <p class="login-sub-header-text" id="phonemsg" style="color:red !important;"></p>
      
    </div>
    
    
    <div class="login-line col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"></div>

    


<?php $lineMaring = "no-margin";

 ?>
<link type="text/css" rel="stylesheet" href="https://www.gstatic.com/firebasejs/ui/4.2.0/firebase-ui-auth.css" />
            <script src="http://www.gstatic.com/firebasejs/5.0.4/firebase.js"></script>
              <script>
               
              </script>
              <script src="https://cdn.firebase.com/libs/firebaseui/2.3.0/firebaseui.js"></script>
              <link type="text/css" rel="stylesheet" href="https://cdn.firebase.com/libs/firebaseui/2.3.0/firebaseui.css" />           

              <div class="login-line col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding "></div>

                <div class="login-content col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
                  <div class="login-box col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">
                <input type="hidden" id="ajax-email" value="<?php echo $user->email;?>">
<input type="hidden" id="ajax-name" name="ajax-name" value="<?php echo $user->name;?>">
<input type="hidden" id="ajax-username" name="ajax-username" value="<?php echo $user->username;?>">
<input type="hidden" id="ajax-password" name="ajax-password" value="<?php echo $user->password;?>">
<input type="hidden" id="ajax-password-repeat" name="ajax-password-repeat" value="<?php echo $user->password_repeat;?>">
<input type="hidden" id="ajax-phone" name="ajax-phone" value="">
<!-- For Google Signup -->
<input type="hidden" id="givenName" name="givenName" value="<?php echo $givenName; ?>">
<input type="hidden" id="google_id" name="google_id" value="<?php echo $google_id; ?>"> 
<input type="hidden" id="url" name="url" value="<?php echo $url; ?>">  
<!-- For Facebook Signup -->
<input type="hidden" id="facebookid" name="facebookid" value="<?php echo $fbid; ?>">
<input type="hidden" id="facebook_first_name" name="facebook_first_name" value="<?php echo $fbfirstname; ?>">
<input type="hidden" id="facebook_last_name" name="facebook_last_name" value="<?php echo $fblastname; ?>">
<input type="hidden" id="facebook_email" name="facebook_email" value="<?php echo $fbemail; ?>">
               
                  <div id="firebaseui-auth-container"></div>
                  <input type="hidden" id="flag" value="<?php echo $flag;?>">
                

<div class="login-line-2 col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding"></div>
<div class="new-signup col-xs-12 col-sm-12 col-md-12 col-lg-12 no-hor-padding">

 <span><?php echo Yii::t('app','Not a member yet ?'); ?></span>

 <?=Html::a(Yii::t('app','click here'), ['site/signup'], ['class' => 'signup-link txt-pink-color'])?></li>
 
 
</div>

</div>
</div>
</div>
<!-- end Bottom to top-->
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
var baseUrl = document.getElementById("baseUrl").value;
var name = document.getElementById("ajax-name").value;
var email = document.getElementById("ajax-email").value;
var username = document.getElementById("ajax-username").value;
var password = document.getElementById("ajax-password").value;
var password_repeat = document.getElementById("ajax-password-repeat").value;
var flag = document.getElementById("flag").value;
    //var ajaxEmail = document.getElementById("ajax-email");
    
  var firebaseConfig = {
  apiKey: appid,
  };
  firebase.initializeApp(firebaseConfig);
  
var ui = new firebaseui.auth.AuthUI(firebase.auth());

var uiConfig = {
  callbacks: {
    signInSuccessWithAuthResult: function(authResult, redirectUrl) {
      //alert(flag);
    var phone_no = authResult['user']['phoneNumber'];
    if(flag == 1)
    {
      $.ajax({
        type : 'POST',
        url : '<?=Yii::$app->getUrlManager()->getBaseUrl()?>/site/loginwithotp',
        data : {'phone_no': phone_no},
        success : function(data) {
              if(data)
           {
            console.log(data);
            window.location.href = baseUrl + '/';
           }
           else
           {
            console.log('nodata');
            //alert('Phone number not registered with this account');
            $('#phonemsg').html('Phone no not registered');
            window.location.href = baseUrl + '/';
           }
           } 
        
        }); 
    }else if(flag == 2){
      //alert(flag);
      var givenName = document.getElementById("givenName").value;
      var google_id = document.getElementById("google_id").value;
      var url = document.getElementById("url").value;

      $.ajax({
        type : 'POST',
        url : '<?=Yii::$app->getUrlManager()->getBaseUrl()?>/site/googlesignup',
        data : {'phone': phone_no, 'name': name, 'username': username, 'email': email, 'password': password, 'password_repeat': password_repeat, 'givenName': givenName, 'google_id': google_id, 'url': url},
        success : function(data) {
                   
           } 
        
        });
    }
    else if(flag == 3){
      //alert(flag);
      var fbfirstname = document.getElementById("facebook_first_name").value;
      var fblastname = document.getElementById("facebook_last_name").value;
      var fbemail = document.getElementById("facebook_email").value;
      var fbid = document.getElementById("facebookid").value;
      //var url = document.getElementById("url").value;

      $.ajax({
        type : 'POST',
        url : '<?=Yii::$app->getUrlManager()->getBaseUrl()?>/site/fbsignup',
        data : {'phone': phone_no, 'name': name, 'username': username, 'email': email, 'password': password, 'password_repeat': password_repeat, 'fbfirstname': fbfirstname, 'fblastname': fblastname, 'fbid': fbid, 'fbemail': fbemail},
        success : function(data) {
                   
           } 
        
        });
    }
    else{
      $.ajax({
        type : 'POST',
        url : '<?=Yii::$app->getUrlManager()->getBaseUrl()?>/site/phonesignup',
        data : {'phone': phone_no, 'name': name, 'username': username, 'email': email, 'password': password, 'password_repeat': password_repeat},
        success : function(data) {
            if(data)
             {
                console.log(data);
                window.location.href = baseUrl + '/';
             }
              
           } 
        
        });
    }
     
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

ui.start('#firebaseui-auth-container', uiConfig);

</script>