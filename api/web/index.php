<?php
// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'local');

define('YII_VENDOR', '/../../vendor/');

require(__DIR__ . '/../common/corelib/jpush/autoload.php');
require(__DIR__ . '/../common/corelib/alidayu/api_sdk/vendor/autoload.php');
require(__DIR__ . YII_VENDOR . 'autoload.php');
require(__DIR__ . YII_VENDOR . 'yiisoft/yii2/Yii.php');


$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../config/config.php'),
    require(__DIR__ . '/../../config/local.php')
);

require_once(__DIR__ . '/../../common/functions.php');

$application = new yii\web\Application($config);
$application->run();