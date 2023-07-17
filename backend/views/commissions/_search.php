<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<div class="commissions-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <?= $form->field($model, 'id') ?>
    <?= $form->field($model, 'percentage') ?>
    <?= $form->field($model, 'minRate') ?>
    <?= $form->field($model, 'maxRate') ?>
    <?= $form->field($model, 'status') ?>
    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>