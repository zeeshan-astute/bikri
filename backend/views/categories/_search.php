<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<div class="categories-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <?= $form->field($model, 'categoryId') ?>
    <?= $form->field($model, 'name') ?>
    <?= $form->field($model, 'parentCategory') ?>
    <?= $form->field($model, 'image') ?>
    <?= $form->field($model, 'categoryProperty') ?>
    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>