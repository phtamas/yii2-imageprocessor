<?php
require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';
Yii::setAlias('@test', __DIR__);
new yii\web\Application([
    'id' => '',
    'basePath' => '',
]);
