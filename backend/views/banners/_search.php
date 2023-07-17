<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<div class="banners-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <?= $form->field($model, 'id') ?>
    <?= $form->field($model, 'bannerimage') ?>
    <?= $form->field($model, 'appbannerimage') ?>
    <?= $form->field($model, 'bannerurl') ?>
    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>