<script src="https://js.stripe.com/v3/"></script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<?php
use yii\grid\GridView;
use common\models\Products;
use common\models\Sitesettings;
use dosamigos\datepicker\DatePicker;
use yii\helpers\Html;
use yii\helpers\Json;
error_reporting(0);
Yii::$app->i18nJs;

$columns[] = ['attribute' => 'orderId','label' => Yii::t('app','Order ID'),'headerOptions' => ['class' => 'small-input']];

$columns[] = [
    'attribute'=>'userId',
    'label' => Yii::t('app','Buyer'),
    'format' => 'raw',
    'value' => function ($model) {                     
        return  $model->getUserName();                 
    },
    'headerOptions' => array('class'=>'userfilter'),'filter'=>false,
    'enableSorting'=>TRUE
];

$columns[] = ['attribute' => 'sellerId','label' => Yii::t('app','Seller'),'value'=>function ($model) {                     
    return  $model->getSellerName();                 
},'filter'=>false,'enableSorting'=>TRUE
];

$columns[] = [
    'attribute'=>'orderDate',
    'label' => Yii::t('app','Order Date'),
    'value' => function ($model) {                     
        return  $model->getCreatedDate();                 
    },
    'format'=>['DateTime','php:d-m-Y'],
    'filter'=>DatePicker::widget([
        'name' => 'orderDate',
        'value' => isset($_GET['orderDate']) ? $_GET['orderDate'] : '',
        'template' => '{addon}{input}',
        'clientOptions' => [
            'autoclose' => true,
            'format' => 'dd-mm-yyyy',
            'orientation'=> 'left bottom'
        ]
    ]),
    'headerOptions' => ['class' => 'small-input','style' => 'width:20%'],'enableSorting'=>TRUE]
    ;

    if($status != 'delivered' && $status != 'cancelled' && $status != 'claimed' && $status != 'approved') {
        $columns[] = ['attribute' => 'totalCost','filter'=>false,'label' => Yii::t('app','Item Cost'),'value' => function ($model) {                     
            return  $model->getItemcost();                 
        },'headerOptions' =>  ['class' => 'small-input'],'enableSorting'=>TRUE];
        $columns[] = ['attribute' => 'totalShipping','label' => Yii::t('app','Total Shipping'),'headerOptions' => ['class' => 'small-input'],'enableSorting'=>TRUE];
    }

    if($status == "")
    {
        $columns[] = ['attribute' => 'totalCost', 'label' => Yii::t('app','Total Cost'),'headerOptions' => ['class' => 'small-input'],'enableSorting'=>TRUE];
    }

    if($status == 'approved') {
        $columns[] = ['attribute' => 'totalCost','label' => Yii::t('app','Total Cost'),'headerOptions' => ['class' => 'small-input']];
        $columns[] = ['attribute' =>'Commission', 'label' => Yii::t('app','Commission'),'value'=>function ($model) {                     
            return  $model->getCommission();                 
        },'filter'=>false,'headerOptions' => ['class' => 'small-input']];
    }

    if($status != "")
    {
        if($status == "pending" || $status == "shipped")
        {
            $columns[] = ['attribute' =>'Buyer paid', 'label' => Yii::t('app','Buyer Paid'),'value'=>function ($model) {                     
                return  $model->getTotalAmount();                 
            },'filter'=>false];
        }
        else if($status == 'approved') {
            $columns[] = ['attribute' => 'Paid To Seller', 'label' => Yii::t('app','Paid To Seller'),'value'=>function ($model) {                     
                return  $model->getSellerAmount();                 
            },'filter'=>false];
        }
        else if($status == "delivered")
        {
            $columns[] = ['attribute' => 'Pay To Seller','label' => Yii::t('app','Paid To Seller'),'value'=>function ($model) {                     
                return  $model->getTotalAmount();                 
            },'filter'=>false];
        }
        else
        {
            $columns[] = ['attribute' => 'Amount Paid', 'label' => Yii::t('app','Amount Paid'),'value'=>function ($model) {                     
                return  $model->getTotalAmount();                 
            },'filter'=>false];
        }
    }

    if($status == 'delivered') {
        $columns[] = ['attribute' =>'Commission','label' => Yii::t('app','Commission'),'value'=>function ($model) {                     
            return  $model->getCommission();                 
        },'filter'=>false,'headerOptions' => ['class' => 'small-input']];
        $columns[] = ['attribute' => 'Seller Amount','label' => Yii::t('app','Seller Amount'),'value'=>function ($model) {                     
            return  $model->getSellerAmount();                 
        },'filter'=>false,'headerOptions' => ['class' => '']];
        $columns[] = ['attribute' => 'Buyer Amount', 'label' => Yii::t('app','Buyer Amount'),'value'=>function ($model) {                     
            return  $model->getTotalAmount();                 
        },'filter'=>false,'headerOptions' => ['class' => '']];
        $columns[]=['class' => 'yii\grid\ActionColumn',            
        'template' => '{approve}',
        'header'=>'Approve',   
        'buttons' => [
            'approve' => function ($url, $model, $key) {
                $icon='<i class="fa fa-check-circle" style="color:#2FDAB8; font-size:20px;"></i>';
                $sellerinfo = yii::$app->Myclass->getUserDetails($model->sellerId);
                $sellerstripeinfo = Json::decode($sellerinfo[0]->stripe_details,true);
                if($sellerstripeinfo == ""){
                    return Html::a(Yii::t('app','Notify'),array('orders/notifyseller', 'id'=>$model->sellerId),array('title'=>Yii::t('app', 'Notify seller to add the stripe details'),'class' => "btn btn-success"));
                }
                else
                    return
                '<input type="hidden" value="'.$model->sellerId.'" id="sellerId_'.$model->orderId.'" ><input type="hidden" value="'.$sellerstripeinfo['stripe_publickey'].'" id="stripekey_'.$model->orderId.'" >
                <input type="hidden" value="'.$model->getSellerAmount().'" id="amount_'.$model->orderId.'" >'.
                Html::a(Yii::t('app','Approve'),array('orders/approve', 'id'=>$model->orderId),array('title'=>Yii::t('app', 'approve'),'class' => "callMobilePaymentapprove btn btn-success"));
            },
        ]
    ];
}

if($status == 'cancelled') {
    $columns[] = ['attribute' => 'Buyer Amount','label' => Yii::t('app','Buyer Amount'),'value'=>function ($model) {return  $model->getTotalAmount();                 
    },'filter'=>false,'headerOptions' => ['class' => '']];
    $columns[]=['class' => 'yii\grid\ActionColumn',            
    'template' => '{approve}',            
    'header'=>'Refund',   
    'buttons' => [
        'approve' => function ($url, $model, $key) {
            $icon='<i class="fa fa-check-circle" style="color:#2FDAB8; font-size:20px;"></i>';
            return Html::a(Yii::t('app','Refund'), ['orders/cancelapprove', 'id'=>$model->orderId],array('class' => "callMobilePayment btn btn-success",'style'=>'display:none'));
        },
    ]
];
}

if($status == 'claimed') {
    $columns[] = ['attribute' => 'Commission','value'=>function ($model) {                     
        return  $model->getCommission();                 
    },'filter'=>false,'headerOptions' => ['class' => 'small-input']];
    $columns[] = ['attribute' => 'Seller Amount', 'label' => Yii::t('app','Seller Amount'),'value'=>function ($model) {                     
        return  $model->getSellerAmount();                 
    },'filter'=>false,'headerOptions' => ['class' => '']];
    $columns[]=['class' => 'yii\grid\ActionColumn',            
    'template' => '{approve}&nbsp;&nbsp;&nbsp;{decline}',            
    'header'=>'Action',   
    'buttons' => [
        'approve' => function ($url, $model, $key) {
            $icon='<i class="fa fa-check-circle" style="color:#2FDAB8; font-size:20px;"></i>';
            $sellerinfo = yii::$app->Myclass->getUserDetails($model->sellerId);
            $sellerstripeinfo = Json::decode($sellerinfo[0]->stripe_details,true);
            if($sellerstripeinfo == ""){
                return Html::a(Yii::t('app','Notify'),array('orders/notifyseller', 'id'=>$model->sellerId),array('title'=>Yii::t('app', 'Notify seller to add the stripe details'),'class' => "btn btn-success"));
            }
            else
                return
            '<input type="hidden" value="'.$model->sellerId.'" id="sellerId_'.$model->orderId.'" ><input type="hidden" value="'.$sellerstripeinfo['stripe_publickey'].'" id="stripekey_'.$model->orderId.'" >
            <input type="hidden" value="'.$model->getSellerAmount().'" id="amount_'.$model->orderId.'" >'.
             Html::a(Yii::t('app','Approve'),array('orders/approve', 'id'=>$model->orderId),array('title'=>Yii::t('app', 'approve'),'class' => "callMobilePaymentapprove btn btn-success"));
        },
        'decline' => function ($url, $model, $key) {
            return Html::a(Yii::t('app','Decline'), ['orders/decline', 'id'=>$model->orderId],array('class' => "callMobilePayment btn btn-danger",'style'=>'display:none'));
        },
    ]
];
}

$columns[]=[
    'attribute'=>'orderId',
    'header'=>Yii::t('app','View'),
    'headerOptions' => ['style' => 'text-align:center;'],
    'contentOptions' => ['style'=>'text-align:center;'],
    'filter' => false,
    'format'=>'raw',    
    'value' => function($model, $key, $index)
    {   
        return Html::a('<button class="btn btn-primary align-text-top border-0"><i class="fa fa-eye"></i></button>', array('orders/view', 'id'=>$model->orderId), [
            'title' => Yii::t('app', 'Click here to View Order'),
            'data-method' => 'post', 'data-pjax' => '0',
        ]);
    }
]; ?>

<?php
echo GridView::widget([
    'id'=>'mobileorders-grid',
    'dataProvider'=>$dataProvider,
    'filterModel'=>$model,
    'summary' => '<div class="summary"> '.Yii::t("app","Showing").' &nbsp<b>{begin}</b> - <b>{end}</b> &nbsp '.Yii::t("app","of").' &nbsp<b>{count}</b>&nbsp '.Yii::t("app","Orders").' </div>',
    'columns' => $columns
]); 
?>

<div class="payment-form"></div>
<script type='text/javascript'>
    jQuery('#mobileorders-grid a.callMobilePaymentapprove').live('click',function(e) {
        var aa=confirm(yii.t('app','Are you sure you want to proceed?'));
        if (aa==true) {
            linkval = $(this).attr("href");
            if(linkval != "" && linkval != undefined)
            { 
                var fullData = $(this).attr('href').split('approve/');
                var orderId = fullData[1];
                var stripekey = $('#stripekey_'+''+orderId).val();
                var amount =  $('#amount_'+''+orderId).val();
                var sellerId =  $('#sellerId_'+''+orderId).val();
                var price = amount.split(' ');
                var amt = price[0] * 100;
                var currency = price[1].toLowerCase();
                var stripe = Stripe(stripekey);
                $.ajax({
                url: '<?=Yii::$app->getUrlManager()->getBaseUrl()?>/orders/stripesessioncreation/',
                type: "POST",
                dataType : "json",
                data:{ orderId : orderId, amt : amt, currency : currency, sellerId:sellerId },
                success: function (res) {
                    if(res){
                       
                        if(res.session_id){
                            return stripe.redirectToCheckout({ sessionId: res.session_id });
                        }
                    }
                    else{
                        return false; 
                    }
                },
                });
                return false;
            }
        }
        else
        {
            return false;
        }
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('.callMobilePayment').show();
    })
</script>