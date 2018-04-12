<?php
define("APP_PATH",  realpath(dirname(__FILE__) . '/../'));
define("APPLICATION_CONFIG_PATH", APP_PATH . '/conf');
require_once APPLICATION_CONFIG_PATH . '/env.php';
$app  = new Yaf_Application(APP_PATH . "/conf/application.ini");
$app
    ->bootstrap()
    ->run();
