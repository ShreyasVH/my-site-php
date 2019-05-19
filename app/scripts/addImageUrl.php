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

require_once APP_PATH . 'app/constants/constants.php';
require_once APP_PATH . 'app/config/loader.inc.php';

$di = new Cli();

require_once APP_PATH . 'app/config/services.inc.php';

/** @var Api $apiHelper */
$apiHelper = $di->get('api');

$movieIds = explode("\r\n", file_get_contents('/Users/quikr/Downloads/movies.csv'));
$movieCount = count($movieIds);

foreach($movieIds as $index => $movieId)
{
    if($index > 0)
    {
        echo "\n--------------------------------------------------\n";
    }

    if(file_get_contents('http://18.222.34.97/images/movies/' . $movieId . '.jpg'))
    {
        $imageUrl = 'http://18.222.34.97/images/movies/' . $movieId . '.jpg';
    }
    else
    {
        if(file_get_contents('http://18.222.34.97/images/movies/' . $movieId . '.jpg'))
        {
            $imageUrl = 'http://18.222.34.97/images/movies/' . $movieId . '.jpg';
        }
        else
        {
            $imageUrl = 'http://18.222.34.97/images/movies/default.jpg';
        }
    }

    echo "\nProcessing Movie. " . ($index + 1) . "/" . $movieCount . "\n";

    $payload = [
        'id' => (int) $movieId,
        'imageUrl' => $imageUrl
    ];
var_dump($payload);die;
    $response = $apiHelper->put('movies/movie', $payload);

    if(200 != $response['status'])
    {
        echo "\n\tError while updating imageUrl. Payload: " . json_encode($payload) . ". Response: " . $response['result'] . "\n";
    }

    echo "\nProcessed Movie. " . ($index + 1) . "/" . $movieCount . "\n";
}