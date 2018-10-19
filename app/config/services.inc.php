<?php
/**
 * Author: shreyas.hande
 * Date: 10/19/18
 * Time: 6:26 PM
 */

use app\constants\Constants;
use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Dispatcher;
use app\helpers\AssetHelper;

//$config = new ConfigIni(APP_PATH . 'app/config/config.ini');
//
//$di->set('config', function() use($config){
//    return $config;
//});

$di->set('view', function() {
    $view = new View();
    $view->setViewsDir(APP_PATH . 'app/views/');
    return $view;
});

$di->set('dispatcher', function () {
    $dispatcher = new Dispatcher();
    $dispatcher->setDefaultNamespace(Constants::NAMESPACE_CONTROLLERS);
    return $dispatcher;
});

$di->set('assetHelper', function() {
    return new AssetHelper();
});
