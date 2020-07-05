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

function getTeams()
{
    $teams = [];
    $content = readData(APP_PATH . 'app/documents/cricbuzz/teams.json');
    if(!empty($content))
    {
        $teams = json_decode($content, true);
    }
    return $teams;
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

function addTeam($payload)
{
    global $apiHelper;

    return $apiHelper->post('cricbuzz/teams', $payload, 'CRICBUZZ');
}

function getExistingTeams()
{
    global $apiHelper;
    $teams = [];
    $response = $apiHelper->get('cricbuzz/teams', 'CRICBUZZ');
    if(200 === $response['status'])
    {
        $decodedResponse = json_decode($response['result'], true);
        foreach($decodedResponse as $team)
        {
            $teams[] = $team['name'];
        }
    }

    return $teams;
}

$teams = getTeams();
$existingTeams = getExistingTeams();

$index = 0;
foreach($teams as $team)
{
    if($index > 0)
    {
        echo "\n---------------------------------------------\n";
    }

    echo "\nProcessing Team. [" . ($index + 1) . "/" . count($teams) . "]\n";

    if(in_array($team, $existingTeams))
    {
        $stats['success']++;
    }
    else
    {
        $payload = [
            'name' => $team,
            'countryId' => 1,
            'teamType' => 0
        ];

        $response = addTeam($payload);
        if(200 === $response['status'])
        {
            $stats['success']++;
        }
        else
        {
            $stats['failure']++;
            $failures[] = [
                'name' => $team,
                'payload' => json_encode($payload),
                'response' => $response['result'],
                'status' => $response['status']
            ];
        }
    }


    writeData(APP_PATH . 'logs/importTeamStats.txt', json_encode($stats, JSON_PRETTY_PRINT));
    writeData(APP_PATH . 'logs/importTeamFailures.txt', json_encode($failures, JSON_PRETTY_PRINT));

    echo "\nProcessed Team. [" . ($index + 1) . "/" . count($teams) . "]\n";
    $index++;
}

