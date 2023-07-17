<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<div class="adverister-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <?= $form->field($model, 'id') ?>
    <?= $form->field($model, 'userid') ?>
    <?= $form->field($model, 'webbanner') ?>
    <?= $form->field($model, 'appbanner') ?>
    <?= $form->field($model, 'bannerlink') ?>
    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>