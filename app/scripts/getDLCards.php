<?php

use app\helpers\Api;
use app\utils\CommonUtils;
use app\utils\Logger;
use Cloudinary\Uploader;
use Phalcon\Di\FactoryDefault\Cli;

date_default_timezone_set('Asia/Kolkata');
define('APP_PATH', realpath(dirname(dirname(dirname(__FILE__)))) . '/');

require_once APP_PATH . 'app/constants/Constants.php';
require_once APP_PATH . 'app/config/loader.inc.php';

$di = new Cli();

require_once APP_PATH . 'app/config/services.inc.php';


function get($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url );
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

    $result = curl_exec($ch);

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if(200 !== $status)
    {
        echo("\nERROR!! - GET API URL : " . $url . " Status : " . $status . " Response : " . $result . "\n");
    }

    curl_close($ch);

    return array(
        'result' => $result,
        'status' => $status
    );
}

function getCardsLink()
{
    $link = '';
    $url = 'https://www.duellinksmeta.com/rev-manifest.json';
    $response = get($url);
    if(200 === $response['status'])
    {
        $result = $response['result'];
        $decodedResponse = json_decode($result, true);
        $link = 'https://www.duellinksmeta.com/data-hashed/' . $decodedResponse['cards.json'];
    }
    return $link;
}

function getAllCards($url)
{
    $cards = [];
    $try = 1;

    while((empty($cards)) && ($try <= 5))
    {

        if($try > 1)
        {
            echo "\nRetrying.....\n";
        }

        $response = get($url);
        if(200 === $response['status'])
        {
            $cards = json_decode($response['result'], true);
        }
        $try++;
    }

    return $cards;
}

function saveCards($cards, $filename)
{
    $path = APP_PATH . '/app/documents/' . $filename . '.json';
    file_put_contents($path, json_encode($cards, JSON_PRETTY_PRINT));
}

function getCardDetailsFromYGO($name)
{
    $details = [];
    $try = 1;
    while((empty($details)) && ($try <= 5))
    {
        if($try > 1)
        {
            echo "\n\t\tRetrying...\n";
        }

        $url = 'https://db.ygoprodeck.com/api_internal/v7/cardinfo.php?name=' . urlencode($name);
        $response = get($url);
        if(200 === $response['status'])
        {
            $decodedResponse = json_decode($response['result'], true);
            $data = $decodedResponse['data'];
            $details = $data[0];
        }
        $try++;
    }

    return $details;
}

function createDetailsObject($dlmDetails, $ygoDetails)
{
    $rarityMap = [
        'N' => 'NORMAL',
        'R' => 'RARE',
        'SR' => 'SUPER_RARE',
        'UR' => 'ULTRA_RARE'
    ];
    $rarity = $rarityMap[$dlmDetails['rarity']];

    $cardTypeMap = [
        'Monster' => 'MONSTER',
        'Spell' => 'SPELL',
        'Trap' => 'TRAP'
    ];
    $cardType = $cardTypeMap[$dlmDetails['type']];

    $details = [
        'name' => $dlmDetails['name'],
        'rarity' => $rarity,
        'cardType' => $cardType,
        'description' => $ygoDetails['desc'],
        'limitType' => 'UNLIMITED',
//        'sources' => $dlmDetails['how'],
    ];

    if(array_key_exists('release', $dlmDetails))
    {
        $details['releaseDate'] = $dlmDetails['release'];
    }

    $cardSubTypes= [
        'NORMAL'
    ];

    $race = $ygoDetails['race'];

    $formattedRace = strtoupper(str_replace(['-', ' '], '_', $race));

    if('MONSTER' === $cardType)
    {
      $details['level'] = $ygoDetails['level'];
      $details['attribute'] = $ygoDetails['attribute'];
      $details['type'] = $formattedRace;
      $details['attack'] = $ygoDetails['atk'];
      $details['defense'] = $ygoDetails['def'];
    }
    else
    {
        $cardSubTypes = [
            $formattedRace
        ];
    }

    $details['cardSubTypes'] = $cardSubTypes;

    $images = [];
    foreach($ygoDetails['card_images'] as $imageObject)
    {
        $images[] = $imageObject['image_url'];
    }

    if (count($images) > 1)
    {
        $details['images'] = $images;
    }

    if(!empty($images))
    {
        $details['imageUrl'] = $images[0];
    }

    return $details;
}

function getExistingCards()
{
    return json_decode(file_get_contents(APP_PATH . 'app/documents/dlExistingCards.json'), true);
}

$cardsLink = getCardsLink();
// var_dump($cardsLink);

$cards = getAllCards($cardsLink);
saveCards($cards, 'dlAllCards');

$existingCards = getExistingCards();

$todayObject = new DateTime();

$newCards = [];
foreach($cards as $index => $card)
{
   // if($index > 9)
   // {
   //     break;
   // }

    if($index > 0)
    {
        echo "\n...............................\n";
    }

    echo "\nProcessing card. [" . ($index + 1) . "/" . count($cards) . "]\n";

    if(array_key_exists('how', $card))
    {
        $isNewCard = !array_key_exists($card['name'], $existingCards);
        if(array_key_exists('release', $card))
        {
            $releaseDateObject = new DateTime($card['release']);
            $isNewCard = ($isNewCard && ($todayObject->getTimestamp() > $releaseDateObject->getTimestamp()));
        }

        if($isNewCard)
        {
            echo "\n\tGetting YGO details\n";
            $ygoDetails = getCardDetailsFromYGO($card['name']);
            echo "\n\tGot YGO details\n";

            $details = createDetailsObject($card, $ygoDetails);


            $newCards[$card['name']] = $details;
            $existingCards[$card['name']] = $details;

            usleep(500000);
        }
    }

    echo "\nProcessed card. [" . ($index + 1) . "/" . count($cards) . "]\n";
}

saveCards($newCards, 'dlNewCards');
saveCards($existingCards, 'dlExistingCards');

