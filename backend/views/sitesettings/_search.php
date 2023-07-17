<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<div class="sitesettings-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <?= $form->field($model, 'id') ?>
    <?= $form->field($model, 'smtpEmail') ?>
    <?= $form->field($model, 'smtpPassword') ?>
    <?= $form->field($model, 'smtpPort') ?>
    <?= $form->field($model, 'smtpHost') ?>
    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>