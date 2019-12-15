<?php

use app\helpers\Api;
use Cloudinary\Uploader;
use Phalcon\Di\FactoryDefault\Cli;

date_default_timezone_set('Asia/Kolkata');
define('APP_PATH', realpath(dirname(dirname(dirname(__FILE__)))) . '/');

require_once APP_PATH . 'app/constants/Constants.php';
require_once APP_PATH . 'app/config/loader.inc.php';

$di = new Cli();

require_once APP_PATH . 'app/config/services.inc.php';

/** @var Api $apiHelper */
$apiHelper = $di->get('api');

$stats = [
    'success' => 0,
    'failure' => 0
];

$failures = [];

function getCards()
{
    $cards = [];
    $content = readData(APP_PATH . 'app/documents/cardsForImport.json');
    if(!empty($content))
    {
        $cards = json_decode($content, true);
    }
    return $cards;
}

function readData($fileName)
{
    $fh = fopen($fileName, 'r');
    $content = fread($fh, filesize($fileName));
    fclose($fh);
    return $content;

}

function writeData($fileName, $data)
{
    $fh = fopen($fileName, 'w');
    $response = fwrite($fh, $data);
    fclose($fh);
    return $response;
}

function addImage($cardUrl, $name)
{
    $imageUrl = getenv('DUEL_LINKS_DEFAULT_IMAGE_URL');
    try
    {
        $uploadResponse =  Uploader::upload($cardUrl, [
            'folder' => 'cards',
            'public_id' => $name
        ]);

        if(!empty($uploadResponse) && array_key_exists('secure_url', $uploadResponse))
        {
            $imageUrl = $uploadResponse['secure_url'];
        }
    }
    catch(Exception $ex)
    {
        echo "\n" . $ex->getMessage() . "\n";
    }
    return $imageUrl;
}

function addCard($payload)
{
    global $apiHelper;

    return $apiHelper->post('cards', $payload, 'DUEL_LINKS');
}

$cards = getCards();

$index = 0;
foreach($cards as $card)
{
    if($index > 0)
    {
        echo "\n---------------------------------------------\n";
    }

    echo "\nProcessing Card. [" . ($index + 1) . "/" . count(array_keys($cards)) . "]\n";

    $payload = [
        'name' => $card['name'],
        'rarity' => $card['rarity'],
        'cardType' => $card['cardType'],
        'cardSubTypes' => $card['cardSubTypes'],
        'description' => $card['description'],
        'limitType' => $card['limitType']
    ];

    $formattedName = str_replace(['#', ' ', '-', ', ', '\'', '"', '/', '!', '?', '&'], '_', strtolower($card['name']));

    $imageUrl = addImage($card['imageUrl'], $formattedName);
    $payload['imageUrl'] = $imageUrl;

    if(array_key_exists('level', $card))
    {
        $payload['level'] = $card['level'];
    }

    if(array_key_exists('attribute', $card))
    {
        $payload['attribute'] = $card['attribute'];
    }

    if(array_key_exists('attribute', $card))
    {
        $payload['attribute'] = $card['attribute'];
    }

    if(array_key_exists('type', $card))
    {
        $payload['type'] = $card['type'];
    }

    if(array_key_exists('attack', $card))
    {
        $payload['attack'] = $card['attack'];
    }

    if(array_key_exists('defense', $card))
    {
        $payload['defense'] = $card['defense'];
    }

    $response = addCard($payload);
    if(200 === $response['status'])
    {
        $stats['success']++;
    }
    else
    {
        $stats['failure']++;
        $failures[] = [
            'name' => $card['name'],
            'response' => $response['result'],
            'status' => $response['status']
        ];
    }

    writeData(APP_PATH . 'app/documents/importCardStats.txt', json_encode($stats, JSON_PRETTY_PRINT));
    writeData(APP_PATH . 'app/documents/importCardFailures.txt', json_encode($failures, JSON_PRETTY_PRINT));

    echo "\nProcessed Card. [" . ($index + 1) . "/" . count(array_keys($cards)) . "]\n";
    $index++;
}
