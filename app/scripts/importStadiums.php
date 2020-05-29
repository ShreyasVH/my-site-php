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

function getStadiums()
{
    $stadiums = [];
    $content = readData(APP_PATH . 'app/documents/stadiums.json');
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

$stadiums = getStadiums();

$index = 0;
foreach($stadiums as $stadium)
{
    if($index > 0)
    {
        echo "\n---------------------------------------------\n";
    }

    echo "\nProcessing Stadium. [" . ($index + 1) . "/" . count(array_keys($stadiums)) . "]\n";

    $payload = [
        'name' => $stadium,
        'countryId' => 1
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
            'name' => $stadium,
            'response' => $response['result'],
            'status' => $response['status']
        ];
    }

    writeData(APP_PATH . 'app/documents/importStadiumStats.txt', json_encode($stats, JSON_PRETTY_PRINT));
    writeData(APP_PATH . 'app/documents/importStadiumFailures.txt', json_encode($failures, JSON_PRETTY_PRINT));

    echo "\nProcessed Stadium. [" . ($index + 1) . "/" . count(array_keys($stadiums)) . "]\n";
    $index++;
}

