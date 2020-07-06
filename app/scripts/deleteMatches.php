<?php

use app\helpers\Api;
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

function deleteMatch($matchId)
{
    global $apiHelper;
    return $apiHelper->delete('cricbuzz/matches/' . $matchId, 'CRICBUZZ');
}

function getMatchesToDelete()
{
    $matches = [];
    $path = APP_PATH . 'app/documents/cricbuzz/matchesForDelete.csv';
    if(file_exists($path))
    {
        $matches = explode("\n", trim(readData($path), "\n"));
    }

    return $matches;
}

$matches = getMatchesToDelete();
$updatedMatches = [];
foreach($matches as $index => $matchId)
{
    $matchId = (int) $matchId;
    if($index > 0)
    {
        echo "\n-------------------------------\n";
    }

    echo "\nProcessing Match - " . $matchId . "[" . ($index + 1) . "/" . count($matches) . "]\n";

    $response = deleteMatch($matchId);
    if(200 === $response['status'])
    {
        $stats['success']++;
    }
    else
    {
        $updatedMatches[] = $matchId;
        $stats['failure']++;

        $failures[] = [
            'matchId' => $matchId,
            'response' => $response['result'],
            'status' => $response['status']
        ];
    }

    echo "\nProcessed Match - " . $matchId . "[" . ($index + 1) . "/" . count($matches) . "]\n";
}

writeData(APP_PATH . 'app/documents/cricbuzz/matchesForDelete.csv', implode("\n", $updatedMatches));
writeData(APP_PATH . 'logs/deleteMatchStats.txt', json_encode($stats, JSON_PRETTY_PRINT));
writeData(APP_PATH . 'logs/deleteMatchFailures.txt', json_encode($failures, JSON_PRETTY_PRINT));