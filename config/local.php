<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=wanju',
            'username' => 'root',
            'password' => 'UVpeEt33',
//            'username' => 'root',
//            'password' => '',
            'charset' => 'utf8',
            'tablePrefix'=>'tbl_',
        ],
        'mail' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            'useFileTransport' => true
        ],
        'cache' => [
          //  'class' => 'yii\caching\FileCache',
          'class' => 'yii\redis\Cache',
            'redis' => [
                'hostname' => '127.0.0.1',
                'port' => 6379,
                'database' => 0,
            ],
            'keyPrefix'=> 'cache_local_wj',
        ],
        'cache_api' => [
          //  'class' => 'yii\caching\FileCache',
          'class' => 'common\corelib\YiiCache',
            'redis' => [
                'hostname' => '127.0.0.1',
                'port' => 6379,
                'database' => 0,
            ],
            'keyPrefix'=> 'cache_api_wj',
        ],
       'session' => [
            'class' => 'yii\redis\Session',
            'redis' => [
                'hostname' => '127.0.0.1',
                'port' => 6379,
                'database' => 0,
            ],
            'keyPrefix'=>'session_local_wj',
            'name' => 'wanju',
            'timeout' => 43200, //12hrs
        ],
    ],
];
