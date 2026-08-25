<?php
/**
 * Author: shreyas.hande
 * Date: 10/19/18
 * Time: 6:26 PM
 */

use app\constants\Constants;
use app\helpers\Api;
use Cloudinary\Configuration\Configuration;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Dispatcher;
use app\helpers\AssetHelper;
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\Session\Adapter\Stream as Session;
use Phalcon\Flash\FlashSession;
use Phalcon\Flash\Direct;
use Phalcon\Session\Manager;
//use Phalcon\Flash\SessionFactory;

// if(file_exists(APP_PATH . '.env'))
// {
//     $dotenv = Dotenv\Dotenv::createImmutable(APP_PATH);
//     $dotenv->load();
// }

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
    $session = new Manager();
    $files = new Session([
        'savePath' => '/tmp', // Adjust the path as needed
    ]);
    $session->setAdapter($files);
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

Configuration::instance([
    'cloud' => [
        'cloud_name' => 'dyoxubvbg',
        'api_key'    => '488511299236237',
        'api_secret' => '0OdBhSYSmt70YlrXYHv083cxF04'
    ],
    'url' => [
        'secure' => true
    ]
]);
