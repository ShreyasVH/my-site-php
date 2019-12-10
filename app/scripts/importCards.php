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
    $content = file_get_contents(APP_PATH . 'app/documents/cardsForImport.json');
    return json_decode($content, true);
}

function addImage($cardUrl, $name)
{
    $imageUrl = getenv('DUEL_LINKS_DEFAULT_IMAGE_URL');
    $uploadResponse =  Uploader::upload($cardUrl, [
        'folder' => 'cards',
        'public_id' => $name
    ]);

    if(!empty($uploadResponse) && array_key_exists('secure_url', $uploadResponse))
    {
        $imageUrl = $uploadResponse['secure_url'];
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

    $formattedName = str_replace(['#', ' ', '-', ', ', '\'', '"', '/'], '_', strtolower($card['name']));
//    var_dump($formattedName);die;

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

    echo "\nProcessed Card. [" . ($index + 1) . "/" . count(array_keys($cards)) . "]\n";
    $index++;
}

file_put_contents(APP_PATH . 'app/documents/importCardStats.txt', json_encode($stats, JSON_PRETTY_PRINT));
file_put_contents(APP_PATH . 'app/documents/importCardFailures.txt', json_encode($failures, JSON_PRETTY_PRINT));
