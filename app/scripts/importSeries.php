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
$existingSeries = [];

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
            $tours = array_merge($tours, $decodedResponse);
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
        $tourResponse = $apiHelper->get('cricbuzz/tours/' . $tour['id'], 'CRICBUZZ');
        if($tourResponse['status'] === 200)
        {
            $decodedResponse = json_decode($tourResponse['result'], true);
            foreach($decodedResponse['seriesList'] as $series)
            {
                $existingSeries[] = $tour['name'] . '_' . $series['gameType'];
            }
        }
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

$series = getSeries();
$teamMap = createTeamMap();
$tourMap = createTourMap();

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
            'endTime' => date('Y-m-d H:i:s', $seriesDetails['endTime'] / 1000),
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

