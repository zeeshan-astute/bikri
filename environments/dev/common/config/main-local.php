<?php
return [
    'components' => [
      'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=joysale',    
            'username' => 'root',
            'password' => '12345678', 
            'charset' => 'utf8',
            'enableSchemaCache'=>true,
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            'useFileTransport' => true,
        ],
    ],
];