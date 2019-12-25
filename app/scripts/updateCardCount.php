<?php
/**
 * Created by PhpStorm.
 * User: shreyasvh
 * Date: 2019-05-19
 * Time: 16:22
 */

use app\enums\cards\FoilType;
use app\helpers\Api;
use app\models\Card;
use Phalcon\Di\FactoryDefault\Cli;

date_default_timezone_set('Asia/Kolkata');
define('APP_PATH', realpath(dirname(dirname(dirname(__FILE__)))) . '/');

require_once APP_PATH . 'app/constants/Constants.php';
require_once APP_PATH . 'app/config/loader.inc.php';

$di = new Cli();

require_once APP_PATH . 'app/config/services.inc.php';

/** @var Api $apiHelper */
$apiHelper = $di->get('api');

function getData()
{
    $content = file_get_contents(APP_PATH . 'app/documents/cardCountData.json');
    return json_decode($content, true);
}

function getCardFoilTypeStats($cardId)
{
    $stats = [];

    $try = 1;
    $maxRetryCount = 5;

    $response = null;
    while((is_null($response)) && ($try <= $maxRetryCount))
    {
        if($try > 1)
        {
            echo "\n\tRetrying for foil type stats...\n";
        }

        $response = Card::getById($cardId);
        $try++;
    }

    if(!is_null($response))
    {
        $response = json_decode(json_encode($response), true);
        if(array_key_exists('glossTypeStats', $response))
        {
            $stats = json_decode($response['glossTypeStats'], true);
        }
    }

    return $stats;
}

function obtainCard($cardId, $foilType)
{
    global $apiHelper;
    $payload = [
        'cardId' => $cardId,
        'glossType' => $foilType
    ];

    $try = 1;
    $maxTries = 5;

    while((empty($response)) && $try <= $maxTries)
    {
        if($try > 1)
        {
            echo "\n\t\t\tRetrying...\n";
        }

        $response = $apiHelper->post('cards/myCards', $payload, 'DUEL_LINKS');
        if(200 === $response['status'])
        {
            break;
        }
        else
        {
            $response = null;
        }

        $try++;
    }

    return (null !== $response);
}

function indexCard($cardId)
{
    global $apiHelper;

    $response = $apiHelper->get('cards/index/' . $cardId, 'DUEL_LINKS');
    return (200 === $response['status']);
}


$data = getData();
$cardCount = count(array_keys($data));
$index = 0;
foreach($data as $cardId => $cardData)
{
    $index++;
    if($index > 1)
    {
        echo "\n-------------------------------------------------------------------\n";
    }

    echo "\nProcessing card.[" . $index . "/" . $cardCount . "]\n";

    $stats = getCardFoilTypeStats($cardId);
    if(!empty($stats))
    {
        $fIndex = 0;
        foreach($cardData as $foilType => $value)
        {
            $fIndex++;

            if($fIndex > 1)
            {
                echo "\n\t........................\n";
            }

            echo "\n\tProcessing foilType. [" . $fIndex . "/" . count(array_keys($cardData)) . "]\n";
            $currentCount = ((array_key_exists($foilType, $stats)) ? $stats[$foilType] : 0);

            if($value > $currentCount)
            {
                for($i = 1; $i <= ($value - $currentCount); $i++)
                {
                    echo "\n\t\tObtaining card. Id: " . $cardId . ". FoilType: " . $foilType . "\n";
                    $success = obtainCard($cardId, $foilType);
                    echo "\n\t\tObtain Success = " . json_encode($success) . "\n";
                }
            }

            echo "\n\tProcessed foilType. [" . $fIndex . "/" . count(array_keys($cardData)) . "]\n";

        }

        $indexResponse = indexCard($cardId);
        echo "\nIndex Success: " . json_encode($indexResponse) . "\n";
    }

    echo "\nProcessed card.[" . $index . "/" . $cardCount . "]\n";
    usleep(500000);
}




