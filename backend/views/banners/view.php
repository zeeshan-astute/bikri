<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
$this->params['breadcrumbs'][] = ['label' => 'Banners', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$path = Yii::$app->urlManagerfrontEnd->baseUrl.'/media/banners/';
?>
<div class="boxShadow p-3 bgWhite m-b20">
    <div class="d-flex justify-content-between  flex-column flex-sm-row">
            <h4 class="m-b25 blueTxtClr p-t10 p-b10"><?php echo Yii::t('app','Banner').' '.Yii::t('app','Details'); ?></h4>
            <div class="">
               
            </div>
    </div>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
            'label' => Yii::t('app','Banner Image'),
             'format' => 'raw',
            'value' => html_entity_decode(Html::img($path.$model->bannerimage,['height' => '100','width'=> '300']))],
            [
            'label' => Yii::t('app','App Banner Image'),
             'format' => 'raw',
            'value' => html_entity_decode(Html::img($path.$model->appbannerimage,['height' => '100','width'=> '150']))],
            'bannerurl:ntext',
        ],
    ]) ?>
</div>