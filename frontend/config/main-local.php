<?php
$config = [
    'components' => [
        'request' => [
            'cookieValidationKey' => 'wbiAr9_iciO-R4n9T7Bmp5pM9pnWHMpx',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.gmail.com',  
                'username' => 'trentfernandez23@gmail.com',
                'password' => 'Trent@91',
                'port' => '465',
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
