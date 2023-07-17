<?php
use yii\helpers\Html;
$this->title = 'Update Sitesettings: {nameAttribute}';
$this->params['breadcrumbs'][] = ['label' => 'Sitesettings', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="sitesettings-update">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>