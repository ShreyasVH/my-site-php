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

function getCountries()
{
    global $apiHelper;
    $countries = [];
    
    $apiResponse = $apiHelper->get('cricbuzz/countries', 'CRICBUZZ');
    if($apiResponse['status'] === 200)
    {
        $countries = json_decode($apiResponse['result'], true);
    }

    return $countries;
}

function getStadiums()
{
    $stadiums = [];
    $content = readData(APP_PATH . 'app/documents/cricbuzz/stadiums.json');
    if(!empty($content))
    {
        $stadiums = json_decode($content, true);
    }
    return $stadiums;
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

function addStadium($payload)
{
    global $apiHelper;

    return $apiHelper->post('cricbuzz/stadiums', $payload, 'CRICBUZZ');
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
    $id = null;

    if(array_key_exists($name, $countryMap))
    {
        $id = $countryMap[$name];
    }

    return $id;
}

function getExistingStadiums()
{
    global $apiHelper;
    $stadiums = [];
    $response = $apiHelper->get('cricbuzz/stadiums', 'CRICBUZZ');
    if(200 === $response['status'])
    {
        $decodedResponse = json_decode($response['result'], true);
        foreach($decodedResponse as $stadium)
        {
            $stadiums[] = $stadium['name'] . '_' . $stadium['country']['id'];
        }
    }

    return $stadiums;
}

$stadiums = getStadiums();
$countryMap = createCountryMap();

$existingStadiums = getExistingStadiums();

$index = 0;
foreach($stadiums as $stadium)
{
    if($index > 0)
    {
        echo "\n---------------------------------------------\n";
    }

    echo "\nProcessing Stadium. [" . ($index + 1) . "/" . count($stadiums) . "]\n";

    if(in_array($stadium['name'] . '_' . getCountryId($stadium['country'], $countryMap), $existingStadiums))
    {
        $stats['success']++;
    }
    else
    {
        $payload = [
            'name' => $stadium['name'],
            'countryId' => getCountryId($stadium['country'], $countryMap),
            'city' => $stadium['city']
        ];

        $response = addStadium($payload);
        if(200 === $response['status'])
        {
            $stats['success']++;
        }
        else
        {
            $stats['failure']++;
            $failures[] = [
                'name' => $stadium['name'],
                'payload' => json_encode($payload),
                'response' => $response['result'],
                'status' => $response['status']
            ];
        }
    }


    writeData(APP_PATH . 'logs/importStadiumStats.txt', json_encode($stats, JSON_PRETTY_PRINT));
    writeData(APP_PATH . 'logs/importStadiumFailures.txt', json_encode($failures, JSON_PRETTY_PRINT));

    echo "\nProcessed Stadium. [" . ($index + 1) . "/" . count($stadiums) . "]\n";
    $index++;
}

