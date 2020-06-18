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

$index = 0;
foreach($countries as $country)
{
    if($index > 0)
    {
        echo "\n---------------------------------------------\n";
    }

    echo "\nProcessing Country. [" . ($index + 1) . "/" . count($countries) . "]\n";

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
        $stats['failure']++;
        $failures[] = [
            'name' => $country,
            'response' => $response['result'],
            'status' => $response['status']
        ];
    }

    writeData(APP_PATH . 'app/documents/importCountryStats.txt', json_encode($stats, JSON_PRETTY_PRINT));
    writeData(APP_PATH . 'app/documents/importCountryFailures.txt', json_encode($failures, JSON_PRETTY_PRINT));

    echo "\nProcessed Country. [" . ($index + 1) . "/" . count($countries) . "]\n";
    $index++;
}

