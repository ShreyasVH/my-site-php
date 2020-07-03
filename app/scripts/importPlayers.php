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

function getPlayers()
{
    $players = [];
    $content = readData(APP_PATH . 'app/documents/cricbuzz/players.json');
    if(!empty($content))
    {
        $players = json_decode($content, true);
    }
    return $players;
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

function addPlayer($payload)
{
    global $apiHelper;

    return $apiHelper->post('cricbuzz/players', $payload, 'CRICBUZZ');
}

function getCountries()
{
    global $apiHelper;
    $teams = [];

    $apiResponse = $apiHelper->get('cricbuzz/countries', 'CRICBUZZ');
    if($apiResponse['status'] === 200)
    {
        $teams = json_decode($apiResponse['result'], true);
    }

    return $teams;
}

function createCountryMap()
{
    $countryMap = [];
    $countries = getCountries();
    foreach($countries as $country)
    {
        $countryMap[$country['name']] = $country['id'];
    }
    return $countryMap;
}

function getCountryId($name, $countryMap)
{
    $id = 1;

    if(array_key_exists($name, $countryMap))
    {
        $id = $countryMap[$name];
    }

    return $id;
}

function getExistingPlayers()
{
    global $apiHelper;
    $players = [];
    $offset = 0;
    $count = 1000;

    while(true)
    {
        $apiResponse = $apiHelper->get('cricbuzz/players/all/' . $offset . '/' . $count, 'CRICBUZZ');
        if($apiResponse['status'] === 200)
        {
            $batchPlayers = json_decode($apiResponse['result'], true);
            foreach($batchPlayers as $player)
            {
                $players[] = $player['name'] . '_' . $player['country']['name'];
            }

            if(count($batchPlayers) < $count)
            {
                break;
            }
            else
            {
                $offset += $count;
            }
        }
        else
        {
            echo "\nRetrying....\n";
        }
    }

    return $players;
}

$playerMap = getPlayers();

$countryMap = createCountryMap();
$existingPlayers = getExistingPlayers();

$index = 0;
foreach($playerMap as $team => $players)
{
    if($index > 0)
    {
        echo "\n---------------------------------------------\n";
    }

    echo "\nProcessing Team. [" . ($index + 1) . "/" . count(array_keys($playerMap)) . "]\n";

    $pIndex = 0;
    foreach($players as $playerName => $player)
    {
        if($pIndex > 0)
        {
            echo "\n\t...................................\n";
        }
        echo "\n\tProcessing Player. [" . ($pIndex + 1) . "/" . count($players) . "]\n";

        if(in_array($player['name'] . '_' . $player['country'], $existingPlayers))
        {
            $stats['success']++;
        }
        else
        {
            $payload = [
                'name' => $player['name'],
                'countryId' => getCountryId($player['country'], $countryMap),
                'image' => 'https://res.cloudinary.com/dyoxubvbg/image/upload/v1577106216/artists/default_m.jpg'
            ];

            $response = addPlayer($payload);
            if(200 === $response['status'])
            {
                $stats['success']++;
            }
            else
            {
                $stats['failure']++;
                $failures[] = [
                    'name' => $player,
                    'team' => $team,
                    'response' => $response['result'],
                    'status' => $response['status']
                ];
            }
        }

        writeData(APP_PATH . 'logs/importPlayerStats.txt', json_encode($stats, JSON_PRETTY_PRINT));
        writeData(APP_PATH . 'logs/importPlayerFailures.txt', json_encode($failures, JSON_PRETTY_PRINT));

        echo "\n\tProcessed Player. [" . ($pIndex + 1) . "/" . count($players) . "]\n";
        $pIndex++;
    }
    echo "\nProcessed Team. [" . ($index + 1) . "/" . count(array_keys($playerMap)) . "]\n";
    $index++;
}

