<?php echo "fdsgdf"; exit;?>

<?php
use yii\helpers\Html;
use yii\grid\GridView;
$this->title = 'Categories';
?>
<div id="page-wrapper">
	<div class="row">
		<div class="col-lg-12">
			<h1 class="page-header"></h1>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-12">
			<div class="panel panel-default">
				<div class="panel-heading"></div>
				<div class="panel-body">
  <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
         ['class' => 'yii\grid\SerialColumn'],
            'categoryId',
            'name',
            ['class' => 'yii\grid\ActionColumn','header' => 'Action'],
        ],
    ]); ?>
				</div>
			</div>
		</div>
	</div>
</div>