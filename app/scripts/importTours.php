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

function getTours()
{
    $tours = [];
    $content = readData(APP_PATH . 'app/documents/cricbuzz/tours.json');
    if(!empty($content))
    {
        $tours = json_decode($content, true);
    }
    return $tours;
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

function addTour($payload)
{
    global $apiHelper;

    return $apiHelper->post('cricbuzz/tours', $payload, 'CRICBUZZ');
}

$tours = getTours();

$index = 0;
foreach($tours as $tour)
{
    if($index > 0)
    {
        echo "\n---------------------------------------------\n";
    }

    echo "\nProcessing Tour. [" . ($index + 1) . "/" . count($tours) . "]\n";

    $payload = [
        'name' => $tour['name'],
        'startTime' => date('Y-m-d H:i:s', $tour['startTime'] / 1000),
        'endTime' => date('Y-m-d H:i:s', $tour['endTime'] / 1000)
    ];

    $response = addTour($payload);
    if(200 === $response['status'])
    {
        $stats['success']++;
    }
    else
    {
        $stats['failure']++;
        $failures[] = [
            'name' => $tour,
            'response' => $response['result'],
            'status' => $response['status']
        ];
    }

    writeData(APP_PATH . 'app/documents/importTourStats.txt', json_encode($stats, JSON_PRETTY_PRINT));
    writeData(APP_PATH . 'app/documents/importTourFailures.txt', json_encode($failures, JSON_PRETTY_PRINT));

    echo "\nProcessed Tour. [" . ($index + 1) . "/" . count($tours) . "]\n";
    $index++;
}

