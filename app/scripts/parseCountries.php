<?php

date_default_timezone_set('Asia/Kolkata');
define('APP_PATH', realpath(dirname(dirname(dirname(__FILE__)))) . '/');

$dataDirectory = APP_PATH . 'app/documents/cricbuzz/yearWiseDetails';

$yearFolders = scandir($dataDirectory);
$yearFolders = array_filter($yearFolders, function($file){
    return (($file !== '.') && ($file !== '..'));
});
$yearFolders = array_values($yearFolders);

$countries = [];

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

                if(array_key_exists('players', $matchDetails))
                {
                    foreach($matchDetails['players'] as $player)
                    {
                        if((array_key_exists('country', $player)) && !in_array($player['country'], $countries))
                        {
                            $countries[] = $player['country'];
                        }
                    }
                }
                else
                {
//                    echo "\n############################" . $matchDetails['name'] . "\n";
                }

                if(!is_null($matchDetails['stadium']['country']) && !in_array($matchDetails['stadium']['country'], $countries))
                {
                    $countries[] = $matchDetails['stadium']['country'];
                }

                echo "\n\t\t\tProcessed match - " . $matchDetails['name'] . " [" . ($matchIndex + 1) . "/" . count($matchFiles) . "]\n";
            }

            echo "\n\t\tProcessed " . $gameType . " series. [" . ($gameTypeIndex + 1) . "/" . count($gameTypeFolders) . "]\n";
        }

        echo "\n\tProcessed tour - " . $tourFolder . " [" . ($tourIndex + 1) . "/" . count($tourFolders) . "]\n";
    }

    echo "\nProcessed year " . $yearFolder . " [" . ($yearIndex + 1) . "/" . count($yearFolders) . "]\n";
}
usort($countries, 'strcasecmp');

file_put_contents(APP_PATH . 'app/documents/cricbuzz/countries.json', json_encode($countries, JSON_PRETTY_PRINT));
