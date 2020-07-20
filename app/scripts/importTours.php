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

function getExistingTours()
{
    global $apiHelper;
    $tours = [];

    for($year = 1890; $year <= date('Y'); $year++)
    {
        $payload = [
            'year' => $year,
            'count' => 1000
        ];

        $apiResponse = $apiHelper->post('cricbuzz/tours/filter', $payload, 'CRICBUZZ');
        if($apiResponse['status'] === 200)
        {
            $decodedResponse = json_decode($apiResponse['result'], true);
            $tours = array_merge($tours, array_column($decodedResponse, 'name'));
        }
        else
        {
            echo "\nTour loading failed - " . $year . "\n";
        }
    }

    return $tours;
}

$tours = getTours();
$existingTours = getExistingTours();

$index = 0;
foreach($tours as $tour)
{
    if($index > 0)
    {
        echo "\n---------------------------------------------\n";
    }

    echo "\nProcessing Tour. [" . ($index + 1) . "/" . count($tours) . "]\n";

    if(in_array($tour['name'], $existingTours))
    {
        $stats['success']++;
    }
    else
    {
        $payload = [
            'name' => $tour['name'],
            'startTime' => date('Y-m-d H:i:s', $tour['startTime'] / 1000)
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
                'name' => $tour['name'],
                'payload' => json_encode($payload),
                'response' => $response['result'],
                'status' => $response['status']
            ];
        }
    }

    writeData(APP_PATH . 'logs/importTourStats.txt', json_encode($stats, JSON_PRETTY_PRINT));
    writeData(APP_PATH . 'logs/importTourFailures.txt', json_encode($failures, JSON_PRETTY_PRINT));

    echo "\nProcessed Tour. [" . ($index + 1) . "/" . count($tours) . "]\n";
    $index++;
}

