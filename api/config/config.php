<?php

$params = array_merge(
    require(__DIR__ . '/../../config/params.php')
);

$config = [
    'id' => 'wanju',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'defaultRoute'  => 'index',
    //'session.use_cookies' => true,
    'extensions' => require(__DIR__ . YII_VENDOR . 'yiisoft/extensions.php'),
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'wanju_',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'err/index',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logFile' => '@runtime/logs/'. date('Ymd') . '_app.log',
                ],
            ],
        ],
        'urlManager'	=> [
        	'enablePrettyUrl'		=> true,
        	'showScriptName'		=> false,
        	//'suffix'				=> '.html',
        	'rules'					=> [
                ''  => 'index/index'
        	],
        ],
        'view'  => [
            'class'     => 'yii\web\View',
            'renderers'     => [
                'html'       => [
                    'class'     => 'yii\smarty\ViewRenderer',
                    'cachePath' => '@runtime/Smarty/cache',
                ],
            ],
            'theme' => [
                'pathMap' => [
                    '@app/views/vendor' => [],
                    '@app/views'    => '../views',
                ],
            ],
        ],
    ],
    'timeZone'=>'Asia/Shanghai',
    'params' => $params,
];
$config['bootstrap'][] = 'debug';
$config['modules']['debug'] = 'yii\debug\Module';

return $config;
