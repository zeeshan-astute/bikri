<?php
return [
    'components' => [
      'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=sajilokharidbikri',    
            'username' => 'root',
            // 'password' => 'Sajilokharidbikri@2021', For Live
            'password' => '', 
            'charset' => 'utf8mb4',
            'enableSchemaCache'=>true,
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            'useFileTransport' => true,
        ],
    ],
];
