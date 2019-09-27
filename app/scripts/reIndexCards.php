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

$cardIdContent = file_get_contents('/Users/quikr/Downloads/cards.csv');
$cardIdContent = str_replace(["\r"], '', $cardIdContent);
$cardIds = explode("\n", $cardIdContent);
$cardCount = count($cardIds);

foreach($cardIds as $index => $cardId)
{
    if($index > 0)
    {
        echo "\n--------------------------------------------------\n";
    }

    echo "\nProcessing Card. " . ($index + 1) . "/" . $cardCount . "\n";

    echo "\nCard Id: " . $cardId . "\n";

    $response = $apiHelper->get('cards/index/' . $cardId, 'DUEL_LINKS');

    if(200 != $response['status'])
    {
        echo "\n\tError while indexing card. Payload: " . json_encode([]) . ". Response: " . $response['result'] . "\n";
    }

    time_nanosleep(0, 500000);

    echo "\nProcessed Card. " . ($index + 1) . "/" . $cardCount . "\n";
}