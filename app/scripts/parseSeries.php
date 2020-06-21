<?php

date_default_timezone_set('Asia/Kolkata');
define('APP_PATH', realpath(dirname(dirname(dirname(__FILE__)))) . '/');

$dataDirectory = APP_PATH . 'app/documents/cricbuzz/yearWiseDetails';

$files = scandir($dataDirectory);
$files = array_filter($files, function($file){
    return (($file !== '.') && ($file !== '..'));
});

$series = [];

foreach($files as $file)
{
    $data = json_decode(file_get_contents($dataDirectory . '/' . $file), true);

    foreach($data as $tourName => $tourDetails)
    {
        echo "\n" . $tourName . "\n";

        $seriesInfo = $tourDetails['series'];
        foreach($seriesInfo as $gameType => $seriesDetails)
        {
            $matches = $seriesDetails['matches'];
            $teams = [];

            foreach($matches as $matchName => $matchDetails)
            {
                if(preg_match('/Group [A-Za-z0-9]/', $matchName))
                {
                    preg_match('/(.*) vs (.*), (.*), Group (.*)/', $matchName, $patternMatches);
                }
                else
                {
                    preg_match('/(.*) vs (.*), (.*)/', $matchName, $patternMatches);
                }

                $team1 = $patternMatches[1];
                $team2 = $patternMatches[2];

                if(!in_array($team1, $teams))
                {
                    $teams[] = $team1;
                }

                if(!in_array($team2, $teams))
                {
                    $teams[] = $team2;
                }
            }

            $type = 'BI_LATERAL';
            if(count($teams) === 3)
            {
                $type = 'TRI_SERIES';
            }
            else if(count($teams) > 3)
            {
                $type = 'TOURNAMENT';
            }

            $series[] = [
                'name' => $tourName,
                'startTime' => $seriesDetails['startTime'],
                'endTime' => $seriesDetails['endTime'],
                'gameType' => $gameType,
                'type' => $type,
                'teams' => $teams,
                'tour' => $tourName
            ];
        }
    }
}

file_put_contents(APP_PATH . 'app/documents/cricbuzz/series.json', json_encode($series, JSON_PRETTY_PRINT));
