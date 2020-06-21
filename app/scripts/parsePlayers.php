<?php

date_default_timezone_set('Asia/Kolkata');
define('APP_PATH', realpath(dirname(dirname(dirname(__FILE__)))) . '/');

$dataDirectory = APP_PATH . 'app/documents/cricbuzz/yearWiseDetails';

$files = scandir($dataDirectory);
$files = array_filter($files, function($file){
	return (($file !== '.') && ($file !== '..'));
});

$playerData = [];

foreach($files as $file)
{
	$data = json_decode(file_get_contents($dataDirectory . '/' . $file), true);

    foreach($data as $tourName => $tourDetails)
    {
        echo "\n" . $tourName . "\n";
        $seriesInfo = $tourDetails['series'];
        foreach($seriesInfo as $gameType => $seriesDetails)
        {
            echo "\n\t" . $gameType . "\n";
            $matches = $seriesDetails['matches'];
            foreach($matches as $matchName => $matchDetails)
            {
                echo "\n\t\t" . $matchName . "\n";
                if(array_key_exists('players', $matchDetails))
                {
                    $players = $matchDetails['players'];

                    foreach($players as $playerDetails)
                    {
                        $team = $playerDetails['team'];
                        $playerName = $playerDetails['player'];
                        $playerCountry = $playerDetails['country'];
                        echo "\n\t\t" . $team . "\n";
                        echo "\n\t\t" . $playerName . "\n";
                        if(array_key_exists($team, $playerData))
                        {
                            $playerData[$team][$playerName] = [
                                'name' => $playerName,
                                'country' => $playerCountry
                            ];
                        }
                        else
                        {
                            $playerData[$team] = [
                                $playerName => [
                                    'name' => $playerName,
                                    'country' => $playerCountry
                                ]
                            ];
                        }
                    }
                }

                if(array_key_exists('bench', $matchDetails))
                {
                    $bench = $matchDetails['bench'];

                    foreach($bench as $playerDetails)
                    {
                        $team = $playerDetails['team'];
                        $playerName = $playerDetails['player'];
                        $playerCountry = $playerDetails['country'];
                        echo "\n\t\t" . $team . "\n";
                        echo "\n\t\t" . $playerName . "\n";
                        if(array_key_exists($team, $playerData))
                        {
                            $playerData[$team][$playerName] = [
                                'name' => $playerName,
                                'country' => $playerCountry
                            ];
                        }
                        else
                        {
                            $playerData[$team] = [
                                $playerName => [
                                    'name' => $playerName,
                                    'country' => $playerCountry
                                ]
                            ];
                        }
                    }
                }
            }
		}
	}
}

foreach($playerData as $team => $players)
{
	usort($players, function($player1, $player2) {
	    return strcasecmp($player1['name'], $player2['name']);
    });
	$playerData[$team] = $players;
}

uksort($playerData, 'strcasecmp');

file_put_contents(APP_PATH . 'app/documents/cricbuzz/players.json', json_encode($playerData, JSON_PRETTY_PRINT));
