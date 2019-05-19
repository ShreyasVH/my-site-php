<?php
/**
 * Author: shreyas.hande
 * Date: 10/19/18
 * Time: 6:26 PM
 */

use app\constants\Constants;
use app\helpers\Api;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Dispatcher;
use app\helpers\AssetHelper;
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\Session\Adapter\Files as Session;

if(('127.0.0.1' === $_SERVER['SERVER_ADDR']) || ('cli' === php_sapi_name()))
{
    $dotenv = Dotenv\Dotenv::create(APP_PATH);
    $dotenv->load();
}

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

$di->setShared('session', function () {
    $session = new Session();
    $session->start();
    return $session;
});

$di->set('assetHelper', function() {
    return new AssetHelper();
});

$di->set('api', function () {
    return new Api();
});

$di->set('logger', function() {
    $log_dir = APP_PATH . getenv('LOG_PATH');
    $filename = 'app_log_' . @date('Y_m_d') . '.txt';

    $logger = new FileAdapter($log_dir . $filename);

    return $logger;
});