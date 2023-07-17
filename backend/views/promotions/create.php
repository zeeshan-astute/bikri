<?php
use yii\helpers\Html;
$this->params['breadcrumbs'][] = ['label' => 'Promotions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->title="Classifieds - Add Promotion";
?>
<?= $this->render('_form', [
        'model' => $model,'type'=>"create",'placeholder'=>$placeholder[1]
]) ?>