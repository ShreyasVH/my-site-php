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
    $count = 100;

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
        echo "\n" . json_encode($playerMap, JSON_PRETTY_PRINT) . "\n";
        die;
    }

    return $id;
}

function getPlayerIdFromShortName($name, $players, $bench)
{
    $playerId = null;
    $options = [];

    foreach($players as $player)
    {
        if($name === $player['name'])
        {
            // $playerId = $player['playerId'];
            // $count++;
            $options[] = $player;
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
        }
    }

    foreach($bench as $player)
    {
        if($name === $player['name'])
        {
            // $playerId = $player['playerId'];
            // $count++;
            $options[] = $player;
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
            $lastName = $nameParts[count($nameParts) - 1];
            $playerLastName = $playerNameParts[count($playerNameParts) - 1];

            $firstName = $nameParts[0];
            $playerFirstName = $playerNameParts[0];

            if(count($nameParts) === 1)
            {
                $playerId = $player['playerId'];
                // $count++;
            }
            else if(strtolower($firstName[0]) === strtolower($playerFirstName[0]))
            {
                $playerId = $player['playerId'];
                // $count++;
            }
            else
            {
                // echo "\n" . $name . "\n";
                // echo "\n" . $player['name'] . "\n";
                // echo "\n " . strtolower($nameParts[0][0]) . " - " . strtolower($player['name'][0]) . "\n";
            }
        }

        if(null === $playerId)
        {
            echo 'Failed to select from options. Player Name - ' . $name;
            echo "\nOptions: " . json_encode($options, JSON_PRETTY_PRINT) . "\n";
            die;
        }
    }
    else
    {
        echo ("\nNot found. Player Name - " . $name . "\n");die;
    }

    // if($count !== 1)
    // {
    //     var_dump('Multiple / No Players. Player Name - ' . $name . '. Count - ' . $count);die;
    // }

    // if(null == $playerId)
    // {
    //     var_dump('Not found. Name - ' . $name . '. Count: ' . $count);
    //     echo "\n" . (json_encode($players, JSON_PRETTY_PRINT)) . "\n";
    //     die;
    // }

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

$teamMap = createTeamMap();
$stadiumMap = createStadiumMap();
$playerMap = createPlayerMap();
$dismissalModeMap = createDismissalModeMap();
// echo "\n" . json_encode($teamMap) . "\n";die;

$dataDirectory = APP_PATH . 'app/documents/cricbuzz/yearWiseDetails';

$files = scandir($dataDirectory);
$files = array_filter($files, function($file){
    return (($file !== '.') && ($file !== '..'));
});

$playerData = [];

$fIndex = 0;
foreach($files as $file)
{
    if($fIndex > 0)
    {
        echo "\n---------------------------------------------\n";
    }

    $year = str_replace('.json', '', $file);
    echo "\nProcessing year - " . $year . " [" . ($fIndex + 1) . "/" . count($files) . "]\n";

    $data = json_decode(file_get_contents($dataDirectory . '/' . $file), true);

    $sIndex = 1;
    foreach($data as $seriesName => $seriesDetails)
    {
        if($sIndex > 1)
        {
            echo "\n\t.........................................\n";
        }

        echo "\n\tProcessing series - " . $seriesName . " [" . $sIndex . "/" . count(array_keys($data)) . "]\n";
        $mIndex = 1;
        foreach($seriesDetails as $matchName => $matchDetails)
        {
            if(!empty($matchDetails))
            {
                if($mIndex > 1)
                {
                    echo "\n\t\t-----------------------------------\n";
                }
                echo "\n\t\tProcessing Match - " . $matchName . " [" . $mIndex . "/" . count(array_keys($seriesDetails)) . "]\n";
                

                $payload = [
                    'seriesId' => 1,
                    'team1' => getTeamId($matchDetails['team1'], $teamMap),
                    'team2' => getTeamId($matchDetails['team2'], $teamMap),
                    'result' => $matchDetails['result'],
                    'stadium' => getStadiumId($matchDetails['stadium'], $stadiumMap),
                    'startTime' => date('Y-m-d H:i:s')
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
                            'runs' => $battingScore['runs'],
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
                        $motm[] = getPlayerIdFromShortName($player, $players, $bench);
                    }

                    $payload['manOfTheMatchList'] = $motm;
                }

                echo "\n\t\t\t" . json_encode($payload) . "\n";

                // $response = addPlayer($payload);
                // if(200 === $response['status'])
                // {
                //     $stats['success']++;
                // }
                // else
                // {
                //     $stats['failure']++;
                //     $failures[] = [
                //         'series' => $seriesName,
                //         'match' => $matchName,
                //         'response' => $response['result'],
                //         'status' => $response['status']
                //     ];
                // }

                writeData(APP_PATH . 'app/documents/importMatchStats.txt', json_encode($stats, JSON_PRETTY_PRINT));
                writeData(APP_PATH . 'app/documents/importMatchFailures.txt', json_encode($failures, JSON_PRETTY_PRINT));

                echo "\n\t\tProcessed Match - " . $matchName . " [" . $mIndex . "/" . count(array_keys($seriesDetails)) . "]\n";
                $mIndex++;
            }
        }
        echo "\n\tProcessed series - " . $seriesName . " [" . $sIndex . "/" . count(array_keys($data)) . "]\n";
        $sIndex++;
    }

    echo "\nProcessed year - " . $year . "[" . ($fIndex + 1) . "/" . count($files) . "]\n";
    $fIndex++;
}

