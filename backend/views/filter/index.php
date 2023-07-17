<?php
use yii\helpers\Html;
use yii\grid\GridView;
$siteSettings = yii::$app->Myclass->getSitesettings();
$this->title = 'Classifieds';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content">
    <div class="row">
        <div class="col-lg-12 userinfo"></div>
    </div>
    <div class="container">
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12" style="text-align: right;">
                    <?=Html::a('<i class="fa fa-plus"></i> '.Yii::t('app','Add').' '.Yii::t('app','Filters'),['filter/add','id'=>$_GET['id']],['class' => 'btn btn-info']); ?>&nbsp;&nbsp;&nbsp;
                    <br><br>
                </div>
            </div>
            <div class="row">
                <?php if(Yii::$app->session->hasFlash('success')): ?>
                <?php endif; ?>
                <?php if(Yii::$app->session->hasFlash('warning')): 
                    echo Yii::$app->session->getFlash('warning');
                endif; ?>
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php echo Yii::t('app','Filters Management')?>
                        <?php  echo Html::a( '<i class="fa  fa-angle-double-left"></i> '.Yii::t('app','Back').'', Yii::$app->request->referrer,['class'=>'label light  text-sm text-dark pull-right' , 'style'=>['font-size' => '12px']]); ?>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive" id="categories-grid">
                            <?= GridView::widget([
                                'dataProvider' => $dataProvider,
                                'filterModel' => $searchModel,
                                'columns' => [
                                    ['class' => 'yii\grid\SerialColumn'],
                                    'id',
                                    'name',
                                    'type',
                                    ['class' => 'yii\grid\ActionColumn'],
                                ],
                            ]); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>