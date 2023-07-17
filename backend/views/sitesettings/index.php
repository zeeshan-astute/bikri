<?php
use yii\helpers\Html;
use yii\grid\GridView;
use conquer\toastr\ToastrWidget;
$this->title = 'Sitesettings';
$this->params['breadcrumbs'][] = $this->title;
?>
  <?php if(Yii::$app->session->hasFlash('error')): ?>
    <?=ToastrWidget::widget(['type' => 'error', 'message'=>Yii::$app->session->getFlash('error'),
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
]);?>
    <?php endif; ?>
	    <?php if(Yii::$app->session->hasFlash('success')): ?>
    <?=ToastrWidget::widget(['type' => 'success', 'message'=>Yii::$app->session->getFlash('success'),
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
]);?>
    <?php endif; ?>
<div class="sitesettings-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>
        <?= Html::a('Create Sitesettings', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            'smtpEmail:email',
            'smtpPassword',
            'smtpPort',
            'smtpHost',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>