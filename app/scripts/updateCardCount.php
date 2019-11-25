<?php
/**
 * Created by PhpStorm.
 * User: shreyasvh
 * Date: 2019-05-19
 * Time: 16:22
 */

use app\enums\cards\FoilType;
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

function runQuery($query, $databaseName)
{
    /** @var \mysqli $dbLink */
    $dbLink = mysqli_connect(getenv('MYSQL_IP'), getenv('MYSQL_USERNAME'), getenv('MYSQL_PASSWORD'));
    $result = false;
    if($dbLink)
    {
        if(isset($databaseName) && !empty($databaseName))
        {
            $q = 'USE ' . $databaseName;
            mysqli_query($dbLink, $q);
        }
        $result = mysqli_query($dbLink, $query);
        if(!$result)
        {
            echo "\nError executing mysql query.\nDatabase : " . $databaseName . "\nQuery : " . $query . ".\nResponse : " . json_encode($result, JSON_PRETTY_PRINT) . "\nError : " . $dbLink->error . "\n";
        }

        $dbLink->close();
    }
    else
    {
        echo "\nError executing mysql query.\nDatabase : " . $databaseName . "\nQuery : " . $query . ".\nResponse : " . json_encode($result, JSON_PRETTY_PRINT) . "\nError : Couldn't connect to DB" . "\n";
    }
    return $result;
}

function getData()
{
    $content = file_get_contents(APP_PATH . 'app/documents/cardCountData.json');
    return json_decode($content, true);
}

function getCardFoilTypeStats($cardId)
{
    $stats = [];
    $query = 'SELECT gloss_type as foilType, count(*) as count FROM `my_cards` WHERE `card_id` = ' . $cardId . ' GROUP BY card_id, gloss_type';
    $result = runQuery($query, getenv('MYSQL_DB'));
    if($result)
    {
        $statsData = $result->fetch_all(MYSQLI_ASSOC);
        $stats = array_combine(array_column($statsData, 'foilType'), array_column($statsData, 'count'));
        $result->free();
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
    $response = $apiHelper->post('cards/myCards', $payload, 'DUEL_LINKS');
    return (200 === $response['status']);
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
    $fIndex = 0;
    foreach($cardData as $foilType => $value)
    {
        $fIndex++;

        if($fIndex > 1)
        {
            echo "\n\t........................\n";
        }

        echo "\n\tProcessing foilType. [" . $fIndex . "/" . count(array_keys($cardData)) . "]\n";
        $foilTypeValue = FoilType::fromString($foilType);
        $currentCount = ((array_key_exists($foilTypeValue, $stats)) ? $stats[$foilTypeValue] : 0);

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

    echo "\nProcessed card.[" . $index . "/" . $cardCount . "]\n";
    usleep(500000);
}




