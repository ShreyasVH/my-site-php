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
$years = [];

function getTeams()
{
    global $apiHelper;
    $teams = [];

    $apiResponse = $apiHelper->get('cricbuzz/teams', 'CRICBUZZ');
    if($apiResponse['status'] === 200)
    {
        $teams = json_decode($apiResponse['result'], true);
    }

    return $teams;
}

function getTours()
{
    global $apiHelper;
    global $years;
    $tours = [];

    foreach($years as $year)
    {
        $payload = [
            'year' => $year,
            'count' => 1000
        ];

        $apiResponse = $apiHelper->post('cricbuzz/tours/filter', $payload, 'CRICBUZZ');
        if($apiResponse['status'] === 200)
        {
            $decodedResponse = json_decode($apiResponse['result'], true);
            $tours[$year] = array_column($decodedResponse, 'name');
        }
        else
        {
            echo "\nTour loading failed - " . $year . "\n";
        }
    }

    return $tours;
}

function getSeries()
{
    $series = [];
    $content = readData(APP_PATH . 'app/documents/cricbuzz/series.json');
    if(!empty($content))
    {
        $series = json_decode($content, true);
    }


    global $years;
    foreach($series as $seriesObject)
    {
        $startTime = $seriesObject['startTime'];
        $year = (int) date('Y', $startTime / 1000);
        if(!in_array($year, $years))
        {
            $years[] = $year;
        }
    }
    if(!empty($years))
    {
        $min = $years[0];
        $years[] = ($min - 1);
    }
    sort($years);

    return $series;
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

function addSeries($payload)
{
    global $apiHelper;

    return $apiHelper->post('cricbuzz/series', $payload, 'CRICBUZZ');
}

function createTeamMap()
{
    $teamMap = [];
    $teams = getTeams();
    foreach($teams as $team)
    {
        $teamMap[$team['name']] = $team['id'];
    }
    return $teamMap;
}

function createTourMap()
{
    global $apiHelper;
    global $existingSeries;
    $tourMap = [];
    $tours = getTours();
    foreach($tours as $tour)
    {
        $tourMap[$tour['name']] = $tour['id'];
    }
    return $tourMap;
}

function getTeamId($name, $teamMap)
{
    $id = null;

    if(array_key_exists($name, $teamMap))
    {
        $id = $teamMap[$name];
    }
    else if(array_key_exists(ucwords(strtolower($name)), $teamMap))
    {
        $id = $teamMap[ucwords(strtolower($name))];
    }
    return $id;
}

function getTourId($name, $tourMap)
{
    $id = null;

    if(array_key_exists($name, $tourMap))
    {
        $id = $tourMap[$name];
    }

    return $id;
}

function getExistingSeries()
{
    global $apiHelper;
    $existingSeries = [];
    $response = $apiHelper->get('cricbuzz/series', 'CRICBUZZ');
    if($response['status'] === 200)
    {
        $decodedResponse = json_decode($response['result'], true);
        foreach($decodedResponse as $series)
        {
            $existingSeries[] = $series['name'] . '_' . $series['gameType'];
        }
    }
    return $existingSeries;
}

$series = getSeries();
$teamMap = createTeamMap();
$tourMap = createTourMap();

$existingSeries = getExistingSeries();

$index = 0;
foreach($series as $seriesDetails)
{
    if($index > 0)
    {
        echo "\n---------------------------------------------\n";
    }

    echo "\nProcessing Series. [" . ($index + 1) . "/" . count($series) . "]\n";

    if(in_array($seriesDetails['tour'] . '_' . $seriesDetails['gameType'], $existingSeries))
    {
        $stats['success']++;
    }
    else
    {
        $payload = [
            'name' => $seriesDetails['name'],
            'startTime' => date('Y-m-d H:i:s', $seriesDetails['startTime'] / 1000),
            'gameType' => $seriesDetails['gameType'],
            'type' => $seriesDetails['type'],
            'tourId' => getTourId($seriesDetails['tour'], $tourMap),
            'homeCountryId' => 1
        ];

        $teams = [];
        foreach($seriesDetails['teams'] as $team)
        {
            $teams[] = getTeamId($team, $teamMap);
        }
        $payload['teams'] = $teams;

        $response = addSeries($payload);
        if(200 === $response['status'])
        {
            $stats['success']++;
        }
        else
        {
            $stats['failure']++;
            $failures[] = [
                'name' => $seriesDetails['name'],
                'gameType' => $seriesDetails['gameType'],
                'payload' => json_encode($payload),
                'response' => $response['result'],
                'status' => $response['status']
            ];
        }
    }

    writeData(APP_PATH . 'logs/importSeriesStats.txt', json_encode($stats, JSON_PRETTY_PRINT));
    writeData(APP_PATH . 'logs/importSeriesFailures.txt', json_encode($failures, JSON_PRETTY_PRINT));

    echo "\nProcessed Series. [" . ($index + 1) . "/" . count($series) . "]\n";
    $index++;
}

