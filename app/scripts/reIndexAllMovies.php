<?php
/**
 * Created by PhpStorm.
 * User: shreyasvh
 * Date: 2019-05-19
 * Time: 16:22
 */

use app\helpers\Api;
use Phalcon\Di\FactoryDefault\Cli;

date_default_timezone_set('Asia/Kolkata');
define('APP_PATH', realpath(dirname(dirname(dirname(__FILE__)))) . '/');

require_once APP_PATH . 'app/constants/Constants.php';
require_once APP_PATH . 'app/config/loader.inc.php';

$di = new Cli();

require_once APP_PATH . 'app/config/services.inc.php';

/** @var Api $apiHelper */
$apiHelper = $di->get('api');

$movieIds = explode("\n", file_get_contents('/Users/quikr/Downloads/movies.csv'));
$movieCount = count($movieIds);

foreach($movieIds as $index => $movieId)
{
    if($index > 0)
    {
        echo "\n--------------------------------------------------\n";
    }

    echo "\nProcessing Movie. " . ($index + 1) . "/" . $movieCount . "\n";

    echo "\n" . $movieId . "\n";

    $response = $apiHelper->post('movies/index/id/' . $movieId, []);

    if(200 != $response['status'])
    {
        echo "\n\tError while indexing movie. Payload: " . json_encode([]) . ". Response: " . $response['result'] . "\n";
    }

    time_nanosleep(0, 500000);

    echo "\nProcessed Movie. " . ($index + 1) . "/" . $movieCount . "\n";
}