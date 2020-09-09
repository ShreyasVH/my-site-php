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
    $countries = [];
    $content = readData(APP_PATH . 'app/documents/cricbuzz/countries.json');
    if(!empty($content))
    {
        $countries = json_decode($content, true);
    }
    return $countries;
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

function addCountry($payload)
{
    global $apiHelper;

    return $apiHelper->post('cricbuzz/countries', $payload, 'CRICBUZZ');
}

$countries = getCountries();

echo "\nImporting Countries\n";

$index = 0;
foreach($countries as $country)
{
    if($index > 0)
    {
        echo "\n\t---------------------------------------------\n";
    }

    echo "\n\tProcessing Country. [" . ($index + 1) . "/" . count($countries) . "]\n";

    $payload = [
        'name' => $country
    ];

    $response = addCountry($payload);
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
                'name' => $country,
                'payload' => json_encode($payload),
                'error' => $description,
                'status' => $response['status']
            ];
        }
    }

    writeData(APP_PATH . 'logs/importCountryStats.txt', json_encode($stats, JSON_PRETTY_PRINT));
    writeData(APP_PATH . 'logs/importCountryFailures.txt', json_encode($failures, JSON_PRETTY_PRINT));

    echo "\n\tProcessed Country. [" . ($index + 1) . "/" . count($countries) . "]\n";
    $index++;
}

echo "\nImported Countries\n";