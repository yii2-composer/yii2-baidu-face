<?php
require '../../vendor/autoload.php';

require_once(dirname(__FILE__) . '/../../vendor/yiisoft/yii2/Yii.php');
@(Yii::$app->charset = 'UTF-8');

$conf = file_get_contents('./config.txt');
$conf = json_decode($conf, 1);

$sdk = new \liyifei\baiduface\Service([
    'apiKey' => $conf['apiKey'],
    'secretKey' => $conf['secretKey'],
    'accessTokenFilePath' => './baiduface.txt'
]);

print_r($sdk->policeValidation('https://apic.douyucdn.cn/upload/avanew/face/201709/17/17/10c38f8e25909405d87df6a1921a5c0a_big.jpg',
    'URL',
    'xxx',
    'xxx',
    'LOW',
    'NONE'
));