<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;
$this->title = 'sitemap';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-contact">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>
        If you have business inquiries or other questions, please fill out the following form to contact us. Thank you.
    </p>
    <div class="row">
        <div class="col-lg-5">
            <h1><?= Html::encode('Categories') ?></h1>
            <ul>
            <?php 
                foreach($categories as $key=>$val)
                {
                    echo '<li>'.$val->name.'</li>';
                }
            ?>
            </ul>
        </div>
    </div>
</div>