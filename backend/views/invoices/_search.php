<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<div class="invoices-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <?= $form->field($model, 'invoiceId') ?>
    <?= $form->field($model, 'orderId') ?>
    <?= $form->field($model, 'invoiceNo') ?>
    <?= $form->field($model, 'invoiceDate') ?>
    <?= $form->field($model, 'invoiceStatus') ?>
    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>