<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
$this->params['breadcrumbs'][] = ['label' => 'Promotions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="boxShadow p-3 bgWhite m-b20">
    <div class="d-flex justify-content-between  flex-column flex-sm-row">
            <h4 class="m-b25 blueTxtClr p-t10 p-b10"><?php echo Yii::t('app','Promotion').' '.Yii::t('app','Details'); ?></h4>
            <div class="">
                <button class='btn btn-primary align-text-top border-0 m-b10'>
                    <?=Html::a('<i class="fa fa-angle-double-left  p-r10"></i> '.Yii::t('app','Back'),['promotions/index']); ?> 
                </button> 
            </div>
    </div>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
                ['attribute' => 'name',
               'label'=>Yii::t('app','Name'),
              ],
           [
                'attribute' => 'days',
               'label'=>Yii::t('app','Days'),
              ],
            [
                'attribute' => 'price',
               'label'=>Yii::t('app','Price'),
              ],
        ],
    ]) ?>
</div>