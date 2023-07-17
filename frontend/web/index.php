<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');


require(__DIR__ . '/../../vendor/braintreelib/Braintree/Base.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Instance.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/IsNode.php');

$folder = __DIR__ . '/../../vendor/braintreelib/Braintree/Result';
    foreach (glob("{$folder}/*.php") as $filename)
    {
        require $filename;
    }  
$folder = __DIR__ . '/../../vendor/braintreelib';
    foreach (glob("{$folder}/*.php") as $filename)
    {
        require $filename;
    }

require(__DIR__ . '/../../vendor/braintreelib/Braintree/Modification.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/AccountUpdaterDailyReport.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/AddOn.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/AddOnGateway.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Address.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/AddressGateway.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/AmexExpressCheckoutCard.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/AndroidPayCard.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/ApplePayCard.php');

require(__DIR__ . '/../../vendor/braintreelib/Braintree/ClientToken.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/ClientTokenGateway.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/CoinbaseAccount.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Collection.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Configuration.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/CredentialsParser.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/CreditCard.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/CreditCardGateway.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/CreditCardVerification.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/CreditCardVerificationGateway.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/CreditCardVerificationSearch.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Customer.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/CustomerGateway.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/CustomerSearch.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Descriptor.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Digest.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Disbursement.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/DisbursementDetails.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Discount.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/DiscountGateway.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Dispute.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/EqualityNode.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/EuropeBankAccount.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Exception.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/FacilitatorDetails.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Gateway.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Http.php');

require(__DIR__ . '/../../vendor/braintreelib/Braintree/KeyValueNode.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Merchant.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/MerchantAccount.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/MerchantAccountGateway.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/MerchantGateway.php');

require(__DIR__ . '/../../vendor/braintreelib/Braintree/MultipleValueNode.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/MultipleValueOrTextNode.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/OAuthCredentials.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/OAuthGateway.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/OAuthResult.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/PartialMatchNode.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/PartnerMerchant.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/PayPalAccount.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/PayPalAccountGateway.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/PaymentInstrumentType.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/PaymentMethod.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/PaymentMethodGateway.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/PaymentMethodNonce.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/PaymentMethodNonceGateway.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Plan.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/PlanGateway.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/RangeNode.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/ResourceCollection.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/RiskData.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/SettlementBatchSummary.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/SettlementBatchSummaryGateway.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/SignatureService.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Subscription.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/SubscriptionGateway.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/SubscriptionSearch.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/TestingGateway.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/TextNode.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/ThreeDSecureInfo.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Transaction.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/TransactionGateway.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/TransactionSearch.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/TransparentRedirect.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/TransparentRedirectGateway.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/UnknownPaymentMethod.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Util.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/VenmoAccount.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Version.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/WebhookNotification.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/WebhookTesting.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Xml.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Error/Codes.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Error/ErrorCollection.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Error/Validation.php');
require(__DIR__ . '/../../vendor/braintreelib/Braintree/Error/ValidationErrorCollection.php');
 
// $folder = __DIR__ . '/../../vendor/braintreelib/Braintree/Error';
//     foreach (glob("{$folder}/*.php") as $filename)
//     {
//         require $filename;
//     }  
    
$folder = __DIR__ . '/../../vendor/braintreelib/Braintree/Dispute';
    foreach (glob("{$folder}/*.php") as $filename)
    {
        require $filename;
    }
$folder = __DIR__ . '/../../vendor/braintreelib/Braintree/Exception';
    foreach (glob("{$folder}/*.php") as $filename)
    {
        require $filename;
    }
$folder = __DIR__ . '/../../vendor/braintreelib/Braintree/MerchantAccount';
    foreach (glob("{$folder}/*.php") as $filename)
    {
        require $filename;
    }
$folder = __DIR__ . '/../../vendor/braintreelib/Braintree/Subscription';
    foreach (glob("{$folder}/*.php") as $filename)
    {
        require $filename;
    }
$folder = __DIR__ . '/../../vendor/braintreelib/Braintree/Test';
    foreach (glob("{$folder}/*.php") as $filename)
    {
        require $filename;
    }
$folder = __DIR__ . '/../../vendor/braintreelib/Braintree/Transaction';
    foreach (glob("{$folder}/*.php") as $filename)
    {
        require $filename;
    }
$folder = __DIR__ . '/../../vendor/braintreelib/Braintree/Xml';
    foreach (glob("{$folder}/*.php") as $filename)
    {
        require $filename;
    }        
  




require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../../common/config/bootstrap.php';
require __DIR__ . '/../config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../../common/config/main.php',
    require __DIR__ . '/../../common/config/main-local.php',
    require __DIR__ . '/../config/main.php',
    require __DIR__ . '/../config/main-local.php'
);

(new yii\web\Application($config))->run();
