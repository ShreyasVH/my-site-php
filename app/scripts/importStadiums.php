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

$countries = [];

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


function getCountryId($name)
{
    $id = null;

    global $apiHelper;
    global $countries;

    if(array_key_exists($name, $countries))
    {
        $id = $countries[$name];
    }
    else
    {
        $response = $apiHelper->get('cricbuzz/countries/name/' . urlencode($name), 'CRICBUZZ');
        if(200 === $response['status'])
        {
            $decodedResponse = json_decode($response['result'], true);
            $id = $decodedResponse['id'];
            $countries[$name] = $id;
        }
    }

    return $id;
}


echo "\nImporting Stadiums\n";
$stadiums = getStadiums();

$index = 0;
foreach($stadiums as $stadium)
{
    if($index > 0)
    {
        echo "\n\t---------------------------------------------\n";
    }

    echo "\n\tProcessing Stadium. [" . ($index + 1) . "/" . count($stadiums) . "]\n";

    $payload = [
        'name' => $stadium['name'],
        'countryId' => getCountryId($stadium['country']),
        'city' => $stadium['city']
    ];

    $response = addStadium($payload);
    if(200 === $response['status'])
    {
        $stats['success']++;
    }
    else
    {
        $decodedResponse = json_decode($response['result'], true);
        $errorCode = $decodedResponse['code'];
        $description = $decodedResponse['description'];
        if($errorCode === 4004)
        {
            $stats['success']++;
        }
        else
        {
            $stats['failure']++;
            $failures[] = [
                'name' => $stadium['name'],
                'payload' => json_encode($payload),
                'error' => $description,
                'status' => $response['status']
            ];
        }
    }


    writeData(APP_PATH . 'logs/importStadiumStats.txt', json_encode($stats, JSON_PRETTY_PRINT));
    writeData(APP_PATH . 'logs/importStadiumFailures.txt', json_encode($failures, JSON_PRETTY_PRINT));

    echo "\n\tProcessed Stadium. [" . ($index + 1) . "/" . count($stadiums) . "]\n";
    $index++;
}

echo "\nImported Stadiums\n";