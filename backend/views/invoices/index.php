<?php
use yii\helpers\Html;
use yii\grid\GridView;
use dosamigos\datepicker\DatePicker;
?>
<div class="boxShadow p-t20 p-b20 p-l15 p-r10 bgWhite m-b20">
       <div class="d-flex justify-content-between  flex-column flex-sm-row">
            <h4 class="m-b25 blueTxtClr p-t10 p-b10"><?php echo Yii::t('app','Invoice Management'); ?></h4>
        </div>
        <div class="table-responsive"  id="users-grid">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'summary' => '<div class="summary"> '.Yii::t("app","Showing").' &nbsp<b>{begin}</b> - <b>{end}</b> &nbsp '.Yii::t("app","of").' &nbsp<b>{count}</b>&nbsp '.Yii::t("app","Invoices").' </div>',
                'columns' => [
                    ['attribute' => 'invoiceNo',
                    'value' => 'invoiceNo',
                    'label' => Yii::t('app','Invoice ID')],
                     [
                         'attribute' => 'orderId',
                        'label'=>Yii::t('app','Order ID'),
                       ],
                     [
                         'attribute'=>'invoiceDate',
                         'label' => Yii::t('app','Invoice Date'),
                         'value'=>'invoiceDate',
                         'format'=>['DateTime','php:d-m-Y'],
                                             'filter'=>DatePicker::widget([
                                 'name' => 'invoiceDate',
                                 'value' => isset($_GET['invoiceDate']) ? $_GET['invoiceDate'] : '',
                                 'template' => '{addon}{input}',
                                     'clientOptions' => [
                                         'autoclose' => true,
                                         'format' => 'dd-mm-yyyy'
                                     ]
                             ]),
                      ],
                      [
                         'attribute' => 'invoiceStatus',
                        'label'=>Yii::t('app','Invoice Status'),
                       ],
                       [
                         'attribute' => 'paymentMethod',
                        'label'=>Yii::t('app','Payment Method'),
                       ],
                       [
                        'attribute'=>'invoiceStatus',
                        'header'=>Yii::t('app','View'),
                        'filter' =>false,
                        'format'=>'raw',    
                        'value' => function($model, $key, $index)
                        {   
                            if($model->invoiceStatus == 'Completed')
                            {
                                $icon='<i class="fa fa-eye"></i>';
                               return ' <a  onclick="showinvoicepopup('.$model->orderId.')" class="showinvoicepopup btn btn-sm btn-primary" href="" data-toggle="modal" data-target="#invoice-modal">'.$icon.'</a>';
             
                            }
                        },
                    ],
                    ]
                ]); 
            ?>
        </div>
        <div class="modal fade" id="invoice-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body">
      <div class="login-line"></div>
<div class="login-content ">
    <div class="promotion-details-cnt">
        <div id="invoice_content" class="invoice-popup">
        </div>
    </div>
</div>
      </div>
    </div>
  </div>
</div>
    </div>