<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<div class="users-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <?= $form->field($model, 'userId') ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'name') ?>
    <?= $form->field($model, 'password') ?>
    <?= $form->field($model, 'email') ?>
    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>