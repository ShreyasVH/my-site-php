<?php
/**
 * Author: shreyas.hande
 * Date: 10/19/18
 * Time: 6:34 PM
 */

//var_dump('hello');die;

use Phalcon\Mvc\Application;
use Phalcon\Di\FactoryDefault;


define('APP_PATH', realpath('..') . '/');

date_default_timezone_set('Asia/Kolkata');

try
{
    require APP_PATH . 'app/config/loader.inc.php';

    // Create a DI
    $di = new FactoryDefault();
    require APP_PATH . 'app/config/services.inc.php';

    $application = new Application($di);

    // Handle the request
    $response = $application->handle($_SERVER["REQUEST_URI"]);

    $response->send();

}
catch (\Exception $e)
{
    include(APP_PATH . 'app/views/index/error.phtml');
}
