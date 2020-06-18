<?php

date_default_timezone_set('Asia/Kolkata');
define('APP_PATH', realpath(dirname(dirname(dirname(__FILE__)))) . '/');

$dataDirectory = APP_PATH . 'app/documents/cricbuzz/yearWiseDetails';

$files = scandir($dataDirectory);
$files = array_filter($files, function($file){
	return (($file !== '.') && ($file !== '..'));
});

$teams = [];

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
                if(array_key_exists('team1', $matchDetails))
                {
                    $team1 = $matchDetails['team1'];
                    if(!in_array($team1, $teams))
                    {
                        $teams[] = $team1;
                    }

                    $team2 = $matchDetails['team2'];
                    if(!in_array($team2, $teams))
                    {
                        $teams[] = $team2;
                    }
                }
            }
		}
	}
}
usort($teams, 'strcasecmp');

file_put_contents(APP_PATH . 'app/documents/cricbuzz/teams.json', json_encode($teams, JSON_PRETTY_PRINT));
