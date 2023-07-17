<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<div class="orders-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <?= $form->field($model, 'orderId') ?>
    <?= $form->field($model, 'userId') ?>
    <?= $form->field($model, 'sellerId') ?>
    <?= $form->field($model, 'totalCost') ?>
    <?= $form->field($model, 'totalShipping') ?>
    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>