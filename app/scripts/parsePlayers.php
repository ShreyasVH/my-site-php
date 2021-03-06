<?php

date_default_timezone_set('Asia/Kolkata');
define('APP_PATH', realpath(dirname(dirname(dirname(__FILE__)))) . '/');

$dataDirectory = APP_PATH . 'app/documents/cricbuzz/yearWiseDetails';

$yearFolders = scandir($dataDirectory);
$yearFolders = array_filter($yearFolders, function($file){
    return (($file !== '.') && ($file !== '..'));
});
$yearFolders = array_values($yearFolders);

$playerData = [];

foreach($yearFolders as $yearIndex => $yearFolder)
{
    if($yearIndex > 0)
    {
        echo "\n-----------------------------------------------------------\n";
    }

    echo "\nProcessing year " . $yearFolder . " [" . ($yearIndex + 1) . "/" . count($yearFolders) . "]\n";

    $tourFolders = scandir($dataDirectory . '/' . $yearFolder . '/tours');
    $tourFolders = array_filter($tourFolders, function($file){
        return (($file !== '.') && ($file !== '..'));
    });
    $tourFolders = array_values($tourFolders);

    foreach($tourFolders as $tourIndex => $tourFolder)
    {
        if($tourIndex > 0)
        {
            echo "\n\t............................................................\n";
        }
        echo "\n\tProcessing tour - " . $tourFolder . " [" . ($tourIndex + 1) . "/" . count($tourFolders) . "]\n";

        $gameTypeFolders = scandir($dataDirectory . '/' . $yearFolder . '/tours/' . $tourFolder . '/series');
        $gameTypeFolders = array_filter($gameTypeFolders, function($file){
            return (($file !== '.') && ($file !== '..'));
        });
        $gameTypeFolders = array_values($gameTypeFolders);

        foreach($gameTypeFolders as $gameTypeIndex => $gameType)
        {
            if($gameTypeIndex > 0)
            {
                echo "\n\t\t---------------------------------------------------\n";
            }

            echo "\n\t\tProcessing " . $gameType . " series. [" . ($gameTypeIndex + 1) . "/" . count($gameTypeFolders) . "]\n";

            $matchFiles = scandir($dataDirectory . '/' . $yearFolder . '/tours/' . $tourFolder . '/series/' . $gameType);
            $matchFiles = array_filter($matchFiles, function($file){
                return (($file !== '.') && ($file !== '..'));
            });
            $matchFiles = array_values($matchFiles);

            foreach($matchFiles as $matchIndex => $matchFile)
            {
                if($matchIndex > 0)
                {
                    echo "\n\t\t\t...................................\n";
                }

                $matchDetails = json_decode(file_get_contents($dataDirectory . '/' . $yearFolder . '/tours/' . $tourFolder . '/series/' . $gameType . '/' . $matchFile), true);
                echo "\n\t\t\tProcessing match - " . $matchDetails['name'] . " [" . ($matchIndex + 1) . "/" . count($matchFiles) . "]\n";

                $players = [];

                if(array_key_exists('players', $matchDetails))
                {
                    $players = array_merge($players, $matchDetails['players']);
                }

                if(array_key_exists('bench', $matchDetails))
                {
                    $players = array_merge($players, $matchDetails['bench']);
                }

                foreach($players as $playerIndex => $playerDetails)
                {
                    $team = $playerDetails['team'];
                    $playerName = $playerDetails['player'];

                    if(array_key_exists('country', $playerDetails))
                    {
                        $playerCountry = $playerDetails['country'];

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
                    else
                    {
//                        echo "\n##########################################" . $matchDetails['name'] . ' - ' . $playerName . "\n";
                    }
                }

                echo "\n\t\t\tProcessed match - " . $matchDetails['name'] . " [" . ($matchIndex + 1) . "/" . count($matchFiles) . "]\n";
            }

            echo "\n\t\tProcessed " . $gameType . " series. [" . ($gameTypeIndex + 1) . "/" . count($gameTypeFolders) . "]\n";
        }

        echo "\n\tProcessed tour - " . $tourFolder . " [" . ($tourIndex + 1) . "/" . count($tourFolders) . "]\n";
    }

    echo "\nProcessed year " . $yearFolder . " [" . ($yearIndex + 1) . "/" . count($yearFolders) . "]\n";
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
