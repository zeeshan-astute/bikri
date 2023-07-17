<?php
use yii\helpers\Html;
$this->title = 'Create Sitesettings';
$this->params['breadcrumbs'][] = ['label' => 'Sitesettings', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sitesettings-create">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', [
        'model' => $model,'parentCategory'=>$parentCategory
    ]) ?>
</div>