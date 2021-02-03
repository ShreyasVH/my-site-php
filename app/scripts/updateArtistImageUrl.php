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

$artists = explode("\r\n", file_get_contents(APP_PATH . 'app/documents/artists.csv'));
$artistCount = count($artists);

foreach($artists as $index => $rawArtist) {
    if ($index > 0) {
        echo "\n--------------------------------------------------\n";
    }

    $artist = explode(",", $rawArtist);

    $artistId = json_decode($artist[0]);
    $gender = json_decode($artist[1]);

    if ('M' === $gender)
    {
        $imageUrl = "https://res.cloudinary.com/dyoxubvbg/image/upload/v1577106216/artists/default_m.jpg";
    }
    else
    {
        $imageUrl = "https://res.cloudinary.com/dyoxubvbg/image/upload/v1577106217/artists/default_f.png";
    }

    echo "\nProcessing Movie. " . ($index + 1) . "/" . $artistCount . "\n";

    $payload = [
        'id' => $artistId,
        'imageUrl' => $imageUrl
    ];

//    echo "\n" . json_encode($payload, JSON_PRETTY_PRINT) . "\n";

    $response = $apiHelper->put('artists/artist', $payload);

    if(200 != $response['status'])
    {
        echo "\n\tError while updating imageUrl. Payload: " . json_encode($payload) . ". Response: " . $response['result'] . "\n";
    }

    echo "\nProcessed Movie. " . ($index + 1) . "/" . $artistCount . "\n";
}