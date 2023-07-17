<?php
$config = [
    'components' => [
        'request' => [
            'cookieValidationKey' => 'DB3LpynSNYa85mhUgf6m-ivmAulRZ7OM',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
           'viewPath' => '@common/mail',
           'useFileTransport' => false,
           'transport' => [
               'class' => 'Swift_SmtpTransport',
               'host' => 'smtp.gmail.com',  
               'username' => 'livzastream@gmail.com',
               'password' => 'livza123',
               'port' => '25', 
               'encryption' => 'tls', 
           ],
       ]
    ],
];
if (!YII_ENV_TEST) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}
return $config;