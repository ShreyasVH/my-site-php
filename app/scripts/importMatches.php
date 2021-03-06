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

function getStadiums()
{
    global $apiHelper;
    $stadiums = [];
    
    $apiResponse = $apiHelper->get('cricbuzz/stadiums', 'CRICBUZZ');
    if($apiResponse['status'] === 200)
    {
        $stadiums = json_decode($apiResponse['result'], true);
    }

    return $stadiums;
}

function getPlayers()
{
    global $apiHelper;
    $players = [];
    $offset = 0;
    $count = 1000;

    while(true)
    {
        $apiResponse = $apiHelper->get('cricbuzz/players/all/' . $offset . '/' . $count, 'CRICBUZZ');
        if($apiResponse['status'] === 200)
        {
            $batchPlayers = json_decode($apiResponse['result'], true);
            $players = array_merge($players, $batchPlayers);
            if(count($batchPlayers) < $count)
            {
                break;
            }
            else
            {
                $offset += $count;
            }
        }
        else
        {
            echo "\nRetrying....\n";
        }
    }

    return $players;
}

function getDismissalModes()
{
    $dismissalModes = [];
    $data = readData(APP_PATH . 'app/documents/cricbuzz/dismissalModes.json');
    if(!empty($data))
    {
        $dismissalModes = json_decode($data, true);
    }
    return $dismissalModes;
}

function getSeries()
{
    global $apiHelper;
    $series = [];

    $apiResponse = $apiHelper->get('cricbuzz/series', 'CRICBUZZ');
    if($apiResponse['status'] === 200)
    {
        $series = json_decode($apiResponse['result'], true);
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

function addMatch($payload)
{
    global $apiHelper;

    return $apiHelper->post('cricbuzz/matches', $payload, 'CRICBUZZ');
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

function getTeamId($name, $teamMap)
{
    $id = null;
    if(array_key_exists($name, $teamMap))
    {
        $id = $teamMap[$name];
    }

    return $id;
}

function createStadiumMap()
{
    $stadiumMap = [];
    $stadiums = getStadiums();
    foreach($stadiums as $stadium)
    {
        $stadiumMap[$stadium['name']] = $stadium['id'];
    }
    return $stadiumMap;
}

function getStadiumId($name, $stadiumMap)
{
    $id = null;

    if(array_key_exists($name, $stadiumMap))
    {
        $id = $stadiumMap[$name];
    }

    return $id;
}

function createPlayerMap()
{
    $playerMap = [];
    $players = getPlayers();
    foreach($players as $player)
    {
        $playerMap[$player['name']] = (int) $player['id'];
    }
    return $playerMap;
}

function createSeriesMap()
{
    $seriesMap = [];
    $series = getSeries();
    foreach($series as $seriesDetails)
    {
        if(!array_key_exists($seriesDetails['name'], $seriesMap))
        {
            $seriesMap[$seriesDetails['name']] = [];
        }

        $seriesMap[$seriesDetails['name']][$seriesDetails['gameType']] = $seriesDetails['id'];

    }
    return $seriesMap;
}

function getPlayerId($name, $playerMap)
{
    $id = null;

    if(array_key_exists($name, $playerMap))
    {
        $id = $playerMap[$name];
    }
    else
    {
        var_dump("\nPlayer Not found. name - " . $name . "\n");
        // echo "\n" . json_encode($playerMap, JSON_PRETTY_PRINT) . "\n";
        // die;
    }

    return $id;
}

function getSeriesId($tourName, $gameType, $seriesMap)
{
    $seriesId = null;

    if(array_key_exists($tourName, $seriesMap) && array_key_exists($gameType, $seriesMap[$tourName]))
    {
        $seriesId = $seriesMap[$tourName][$gameType];
    }

    return $seriesId;
}

function getPlayerIdFromShortName($name, $players, $bench)
{
    $name = trim($name);
    $name = correctPlayer($name);
    $playerId = null;
    $options = [];

    foreach($players as $player)
    {
        if($name === $player['name'])
        {
            return $player['playerId'];
        }
        else
        {
            $nameParts = explode(' ', $name);
            $playerNameParts = explode(' ', $player['name']);
            $lastName = $nameParts[count($nameParts) - 1];
            $playerLastName = $playerNameParts[count($playerNameParts) - 1];
            $firstName = $nameParts[0];
            $playerFirstName = $playerNameParts[0];

            if(strtolower($lastName) === strtolower($playerLastName))
            {
                $options[] = $player;
            }
            else if((count($nameParts) === 1) && (strtolower($firstName) === strtolower($playerFirstName)))
            {
                return $player['playerId'];
            }
        }
    }

    foreach($bench as $player)
    {
        if($name === $player['name'])
        {
            return $player['playerId'];
        }
        else
        {
            $nameParts = explode(' ', $name);
            $playerNameParts = explode(' ', $player['name']);
            $lastName = $nameParts[count($nameParts) - 1];
            $playerLastName = $playerNameParts[count($playerNameParts) - 1];
            $firstName = $nameParts[0];
            $playerFirstName = $playerNameParts[0];

            if(strtolower($lastName) === strtolower($playerLastName))
            {
                $options[] = $player;
            }
            else if((count($nameParts) === 1) && (strtolower($firstName) === strtolower($playerFirstName)))
            {
                return $player['playerId'];
            }
        }
    }

    if(count($options) === 1)
    {
        $playerId = $options[0]['playerId'];
    }
    else if(count($options) > 1)
    {
        foreach($options as $player)
        {
            $nameParts = explode(' ', $name);
            $playerNameParts = explode(' ', $player['name']);

            $firstName = $nameParts[0];
            $playerFirstName = $playerNameParts[0];

            if(count($nameParts) === 1)
            {
                $playerId = $player['playerId'];
            }
            else if(strtolower($firstName[0]) === strtolower($playerFirstName[0]))
            {
                $playerId = $player['playerId'];
            }
        }

        if(null === $playerId)
        {
            echo 'Failed to select from options. Player Name - ' . $name;
            echo "\nOptions: " . json_encode($options, JSON_PRETTY_PRINT) . "\n";
        }
    }
    else
    {
        echo ("\nNot found. Player Name - " . $name . "\n");
    }

    return $playerId;
}

function createDismissalModeMap()
{
    $dismissalModeMap = [];
    $dismissalModes = getDismissalModes();
    foreach($dismissalModes as $dismissalMode)
    {
        $dismissalModeMap[$dismissalMode['name']] = (int) $dismissalMode['id'];
    }
    return $dismissalModeMap;
}

function getDismissalModeId($name, $dismissalModeMap)
{
    $id = null;

    if(array_key_exists($name, $dismissalModeMap))
    {
        $id = $dismissalModeMap[$name];
    }

    return $id;
}

function correctPlayer($input)
{
    $output = $input;
    $corrections = json_decode(readData(APP_PATH . 'app/documents/cricbuzz/playerReplacements.json'), true);

    if(array_key_exists($input, $corrections))
    {
        $output = $corrections[$input];
    }
    
    return $output;
}

function getFiles($path)
{
    $files = scandir($path);
    $files = array_filter($files, function($file){
        return (($file !== '.') && ($file !== '..'));
    });
    $files = array_values($files);
    return $files;
}

$teamMap = createTeamMap();
$stadiumMap = createStadiumMap();
$playerMap = createPlayerMap();
$dismissalModeMap = createDismissalModeMap();
$seriesMap = createSeriesMap();

$dataDirectory = APP_PATH . 'app/documents/cricbuzz/yearWiseDetails';

$yearFolders = getFiles($dataDirectory);

foreach($yearFolders as $yearIndex => $yearFolder)
{
    $yearFolderPath = $dataDirectory . DIRECTORY_SEPARATOR . $yearFolder;
    if($yearIndex > 0)
    {
        echo "\n-----------------------------------------------------------\n";
    }

    echo "\nProcessing year " . $yearFolder . " [" . ($yearIndex + 1) . "/" . count($yearFolders) . "]\n";

    $tourListFilePath = $yearFolderPath . DIRECTORY_SEPARATOR . 'tourList.json';

    $tourFolders = getFiles($yearFolderPath . DIRECTORY_SEPARATOR . 'tours');

    foreach($tourFolders as $tourIndex => $tourFolder)
    {
        $tourFolderPath = $yearFolderPath . DIRECTORY_SEPARATOR . 'tours' . DIRECTORY_SEPARATOR . $tourFolder;
        if($tourIndex > 0)
        {
            echo "\n\t............................................................\n";
        }
        echo "\n\tProcessing tour - " . $tourFolder . " [" . ($tourIndex + 1) . "/" . count($tourFolders) . "]\n";

        $tourDetailsFilePath = $tourFolderPath . DIRECTORY_SEPARATOR . 'details.json';

        $gameTypeFolders = getFiles($tourFolderPath . DIRECTORY_SEPARATOR . 'series');

        foreach($gameTypeFolders as $gameTypeIndex => $gameType)
        {
            $gameTypeFolderPath = $tourFolderPath . DIRECTORY_SEPARATOR . 'series' . DIRECTORY_SEPARATOR . $gameType;
            if($gameTypeIndex > 0)
            {
                echo "\n\t\t---------------------------------------------------\n";
            }

            echo "\n\t\tProcessing " . $gameType . " series. [" . ($gameTypeIndex + 1) . "/" . count($gameTypeFolders) . "]\n";

            $matchFiles = getFiles($gameTypeFolderPath);

            foreach($matchFiles as $matchIndex => $matchFile)
            {
                $matchFilePath = $gameTypeFolderPath . DIRECTORY_SEPARATOR . $matchFile;
                if($matchIndex > 0)
                {
                    echo "\n\t\t\t...................................\n";
                }

                $matchDetails = json_decode(file_get_contents($matchFilePath), true);
                echo "\n\t\t\tProcessing match - " . $matchDetails['name'] . " [" . ($matchIndex + 1) . "/" . count($matchFiles) . "]\n";
                $tourName = $matchDetails['tourName'];
                $matchName = $matchDetails['name'];

                $payload = [
                    'seriesId' => getSeriesId($tourName, $gameType, $seriesMap),
                    'team1' => getTeamId($matchDetails['team1'], $teamMap),
                    'team2' => getTeamId($matchDetails['team2'], $teamMap),
                    'result' => $matchDetails['result'],
                    'stadium' => getStadiumId($matchDetails['stadium']['name'], $stadiumMap),
                    'startTime' => date('Y-m-d H:i:s', $matchDetails['startTime'] / 1000)
                ];

                if(array_key_exists('tossWinner', $matchDetails))
                {
                    $payload['tossWinner'] = getTeamId($matchDetails['tossWinner'], $teamMap);
                }

                if(array_key_exists('batFirst', $matchDetails))
                {
                    $payload['batFirst'] = getTeamId($matchDetails['batFirst'], $teamMap);
                }

                if(array_key_exists('winner', $matchDetails))
                {
                    $payload['winner'] = getTeamId($matchDetails['winner'], $teamMap);
                }

                if(array_key_exists('winMargin', $matchDetails))
                {
                    $payload['winMargin'] = (int) $matchDetails['winMargin'];
                }

                if(array_key_exists('winMarginType', $matchDetails))
                {
                    $payload['winMarginType'] = $matchDetails['winMarginType'];
                }

                if(array_key_exists('extras', $matchDetails))
                {
                    $extras = [];
                    foreach($matchDetails['extras'] as $extrasObject)
                    {
                        $extras[] = [
                            'runs' => $extrasObject['runs'],
                            'type' => $extrasObject['type'],
                            'battingTeam' => getTeamId($extrasObject['battingTeam'], $teamMap),
                            'bowlingTeam' => getTeamId($extrasObject['bowlingTeam'], $teamMap),
                            'innings' => $extrasObject['innings'],
                            'teamInnings' => $extrasObject['teamInnings']
                        ];
                    }
                    $payload['extras'] = $extras;
                }
                $players = [];
                $bench = [];
                if(array_key_exists('players', $matchDetails))
                {
                    foreach($matchDetails['players'] as $player)
                    {
                        $players[] = [
                            'playerId' => getPlayerId($player['player'], $playerMap),
                            'teamId' => getTeamId($player['team'], $teamMap),
                            'name' => $player['player']
                        ];
                    }
                }

                if(array_key_exists('bench', $matchDetails))
                {
                    foreach($matchDetails['bench'] as $player)
                    {
                        $bench[] = [
                            'playerId' => getPlayerId($player['player'], $playerMap),
                            'teamId' => getTeamId($player['team'], $teamMap),
                            'name' => $player['player']
                        ];
                    }

                    $payload['players'] = $players;
                }

                if(array_key_exists('battingScores', $matchDetails))
                {
                    $battingScores = [];

                    foreach($matchDetails['battingScores'] as $battingScore)
                    {
                        $battingScoreObject = [
                            'playerId' => getPlayerIdFromShortName($battingScore['player'], $players, $bench),
                            'runs' => $battingScore['runs'],
                            'balls' => $battingScore['balls'],
                            'fours' => $battingScore['fours'],
                            'sixes' => $battingScore['sixes'],
                            'innings' => $battingScore['innings'],
                            'teamInnings' => $battingScore['teamInnings']
                        ];

                        if(array_key_exists('dismissalMode', $battingScore))
                        {
                            $battingScoreObject['dismissalMode'] = getDismissalModeId($battingScore['dismissalMode'], $dismissalModeMap);

                            if(array_key_exists('bowler', $battingScore))
                            {
                                $battingScoreObject['bowlerId'] = getPlayerIdFromShortName($battingScore['bowler'], $players, $bench);
                            }

                            if(array_key_exists('fielders', $battingScore))
                            {
                                $fielders = [];
                                $fielderParts = explode(', ', $battingScore['fielders']);
                                foreach($fielderParts as $fielder)
                                {
                                    $fielders[] = getPlayerIdFromShortName($fielder, $players, $bench);
                                }

                                $battingScoreObject['fielders'] = implode(', ', $fielders);
                            }
                        }

                        $battingScores[] = $battingScoreObject;
                    }

                    $payload['battingScores'] = $battingScores;
                }

                if(array_key_exists('bowlingFigures', $matchDetails))
                {
                    $bowlingFigures = [];

                    foreach($matchDetails['bowlingFigures'] as $bowlingFigure)
                    {
                        $bowlingFigures[] = [
                            'playerId' => getPlayerIdFromShortName($bowlingFigure['player'], $players, $bench),
                            'balls' => $bowlingFigure['balls'],
                            'maidens' => $bowlingFigure['maidens'],
                            'runs' => $bowlingFigure['runs'],
                            'wickets' => $bowlingFigure['wickets'],
                            'innings' => $bowlingFigure['innings'],
                            'teamInnings' => $bowlingFigure['teamInnings']
                        ];
                    }

                    $payload['bowlingFigures'] = $bowlingFigures;
                }


                if(!empty($players))
                {
                    $playerObjects = [];
                    foreach($players as $player)
                    {
                        $playerObjects[] = [
                            'playerId' => $player['playerId'],
                            'teamId' => $player['teamId']
                        ];
                    }
                    $payload['players'] = $playerObjects;
                }

                if(!empty($bench))
                {
                    $benchObjects = [];
                    foreach($bench as $player)
                    {
                        $benchObjects[] = [
                            'playerId' => $player['playerId'],
                            'teamId' => $player['teamId']
                        ];
                    }
                    $payload['bench'] = $benchObjects;
                }

                if(array_key_exists('manOfTheMatchList', $matchDetails))
                {
                    $motmList = [];

                    foreach($matchDetails['manOfTheMatchList'] as $player)
                    {
                        if(!empty($player))
                        {
                            $motmList[] = getPlayerIdFromShortName($player, $players, $bench);
                        }
                    }

                    $payload['manOfTheMatchList'] = $motmList;
                }

                $response = addMatch($payload);
                if(200 === $response['status'])
                {
                    $stats['success']++;
                    unlink($matchFilePath);
                }
                else
                {
                    $decodedResponse = json_decode($response['result'], true);
                    $errorCode = $decodedResponse['code'];
                    $description = $decodedResponse['description'];

                    if(4004 === $errorCode)
                    {
                        $stats['success']++;
                        unlink($matchFilePath);
                    }
                    else
                    {
                        $stats['failure']++;
                        $failures[] = [
                            'tour' => $tourName,
                            'gameType' => $gameType,
                            'match' => $matchName,
                            'payload' => json_encode($payload),
                            'error' => $description,
                            'status' => $response['status']
                        ];
                    }
                }

                writeData(APP_PATH . 'logs/importMatchStats.txt', json_encode($stats, JSON_PRETTY_PRINT));
                writeData(APP_PATH . 'logs/importMatchFailures.txt', json_encode($failures, JSON_PRETTY_PRINT));

                echo "\n\t\t\tProcessed match - " . $matchDetails['name'] . " [" . ($matchIndex + 1) . "/" . count($matchFiles) . "]\n";
            }

            $files = getFiles($gameTypeFolderPath);
            if(count($files) === 0)
            {
                rmdir($gameTypeFolderPath);
            }
            echo "\n\t\tProcessed " . $gameType . " series. [" . ($gameTypeIndex + 1) . "/" . count($gameTypeFolders) . "]\n";
        }

        $files = getFiles($tourFolderPath . DIRECTORY_SEPARATOR . 'series');
        if(count($files) === 0)
        {
            rmdir($tourFolderPath . DIRECTORY_SEPARATOR . 'series');
            if(file_exists($tourDetailsFilePath))
            {
                unlink($tourDetailsFilePath);
            }
            rmdir($tourFolderPath);
        }
        echo "\n\tProcessed tour - " . $tourFolder . " [" . ($tourIndex + 1) . "/" . count($tourFolders) . "]\n";
    }

    $files = getFiles($yearFolderPath . DIRECTORY_SEPARATOR . 'tours');
    $files = array_filter($files, function($fileName) {
        return ($fileName !== 'tourList.json');
    });
    if(count($files) === 0)
    {
        rmdir($yearFolderPath . DIRECTORY_SEPARATOR . 'tours');
        if(file_exists($tourListFilePath))
        {
            unlink($tourListFilePath);
        }
        rmdir($yearFolderPath);
    }
    echo "\nProcessed year " . $yearFolder . " [" . ($yearIndex + 1) . "/" . count($yearFolders) . "]\n";
}

writeData(APP_PATH . 'logs/importMatchStats.txt', json_encode($stats, JSON_PRETTY_PRINT));
writeData(APP_PATH . 'logs/importMatchFailures.txt', json_encode($failures, JSON_PRETTY_PRINT));
