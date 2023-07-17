<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<div class="promotions-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <?= $form->field($model, 'id') ?>
    <?= $form->field($model, 'name') ?>
    <?= $form->field($model, 'days') ?>
    <?= $form->field($model, 'price') ?>
    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>