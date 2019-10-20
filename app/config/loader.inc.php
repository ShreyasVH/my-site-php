<?php
/**
 * Author: shreyas.hande
 * Date: 10/19/18
 * Time: 6:22 PM
 */

require APP_PATH . 'vendor/autoload.php';

/**
 * This file is meant to be included / required in the bootstrap file.
 **/
use Phalcon\Loader;

 // Register an autoloader
$loader = new Loader();

$directories = [
    APP_PATH . 'app/config/',
    APP_PATH . 'app/constants/',
    APP_PATH . 'app/controllers/',
    APP_PATH . 'app/enums/',
    APP_PATH . 'app/helpers/',
    APP_PATH . 'app/models/',
    APP_PATH . 'app/utils/'
];

$loader->registerDirs($directories)->register();

$namespaces = [
    'app\\config' => APP_PATH . 'app/config',
    'app\\constants' => APP_PATH . 'app/constants',
    'app\\controllers' => APP_PATH . 'app/controllers',
    'app\\enums' => APP_PATH . 'app/enums',
    'app\\helpers' => APP_PATH . 'app/helpers',
    'app\\models' => APP_PATH . 'app/models',
    'app\\utils' => APP_PATH . 'app/utils'
];

$loader->registerNamespaces($namespaces)->register();
