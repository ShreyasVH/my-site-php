<?php

date_default_timezone_set('Asia/Kolkata');
define('APP_PATH', realpath(dirname(dirname(dirname(__FILE__)))) . '/');

$dataDirectory = APP_PATH . 'app/documents/cricbuzz/yearWiseDetails';

$files = scandir($dataDirectory);
$files = array_filter($files, function($file){
    return (($file !== '.') && ($file !== '..'));
});

$countries = [];

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
                foreach($matchDetails['players'] as $player)
                {
                    if((array_key_exists('country', $player)) && !in_array($player['country'], $countries))
                    {
                        $countries[] = $player['country'];
                    }
                }

                if(!is_null($matchDetails['stadium']['country']) && !in_array($matchDetails['stadium']['country'], $countries))
                {
                    $countries[] = $matchDetails['stadium']['country'];
                }
            }
        }
    }
}
usort($countries, 'strcasecmp');

file_put_contents(APP_PATH . 'app/documents/cricbuzz/countries.json', json_encode($countries, JSON_PRETTY_PRINT));
