<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="boxShadow p-3 bgWhite m-b20">
    <div class="d-flex justify-content-between  flex-column flex-sm-row">
            <h4 class="m-b25 blueTxtClr p-t10 p-b10"><?php echo Yii::t('app','User').' '.Yii::t('app','Details'); ?></h4>
            <div class="">
                <button class='btn btn-primary align-text-top border-0 m-b10'>
                    <?=Html::a('<i class="fa fa-angle-double-left  p-r10"></i> '.Yii::t('app','Back'),['users/index']); ?> 
                </button> 
            </div>
    </div>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            ['attribute' => 'userId',
            'label' => Yii::t('app','User').' '.Yii::t('app','Id')],
             ['attribute' => 'username',
            'label' => Yii::t('app','Username')],
            ['attribute' => 'name',
            'label' => Yii::t('app','Name')],
            ['attribute' => 'email',
            'label' => Yii::t('app','Email')],
            /*[
                'attribute'=>'email',
                  'header'=>Yii::t('app','Email'),
                  'filter' =>false,
                  'label'=>Yii::t('app','Email'),
                  'format'=>'raw',
                   'value'=> function ($model) {
                    return preg_replace('/(?:^|@).\K|\.[^@]*$(*SKIP)(*F)|.(?=.*?\.)/', '*', $model->email);
                   }
                ],*/
                /*Mobile OTP addons*/
                ['attribute' => 'phone',
                 'label' => Yii::t('app','Phone Number'),
                 'value'=> function ($model) {
                  if($model->phone != "")
                    return "+".$model->phone;
                  else
                    return $model->phone;
                  }
                ],
                /*Mobile OTP addons*/
            [
                'attribute' => 'fbdetails',
                'filter' => false,
                'label'=> 'Facebook'.' '.Yii::t('app','Details'),
                'format' => 'raw',
                 'value' => function ($data) {
                     return  $data->generateFbdtails();
                 },
              ],
        ],
    ]) ?>
        <?php
        if(count($userdevicemodel) > 0){ ?>
          <?=  DetailView::widget([
        'model' => $userdevicemodel,
        'attributes' => [
            ['attribute' => 'deviceModel',
            'label' => Yii::t('app','Device').' '.Yii::t('app','Model')],
             ['attribute' => 'deviceName',
           'label' => Yii::t('app','Device').' '.Yii::t('app','Name')],
        ],
    ]) ?>
    <?php }
     ?>
</div>