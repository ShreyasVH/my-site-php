<?php

date_default_timezone_set('Asia/Kolkata');
define('APP_PATH', realpath(dirname(dirname(dirname(__FILE__)))) . '/');

$dataDirectory = APP_PATH . 'app/documents/cricbuzz/yearWiseDetails';

$yearFolders = scandir($dataDirectory);
$yearFolders = array_filter($yearFolders, function($file){
    return (($file !== '.') && ($file !== '..'));
});
$yearFolders = array_values($yearFolders);

$series = [];

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
            $seriesStartTime = '';
            $teams = [];
            if($gameTypeIndex > 0)
            {
                echo "\n\t\\t---------------------------------------------------\n";
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

                if($matchIndex === 0)
                {
                    $seriesStartTime = $matchDetails['startTime'];
                }

                echo "\n\t\t\tProcessing match - " . $matchDetails['name'] . " [" . ($matchIndex + 1) . "/" . count($matchFiles) . "]\n";

                $matchName = $matchDetails['name'];
                if(preg_match('/(Group|Pool) [A-Za-z0-9]/', $matchName))
                {
                    preg_match('/(.*) vs (.*), (.*), (Group|Pool) (.*)/', $matchName, $patternMatches);
                    // echo "\n" . $matchName . "\n";
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

                echo "\n\t\t\tProcessed match - " . $matchDetails['name'] . " [" . ($matchIndex + 1) . "/" . count($matchFiles) . "]\n";
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
                'name' => $tourFolder,
                'startTime' => $seriesStartTime,
                'endTime' => $seriesStartTime,
                'gameType' => $gameType,
                'type' => $type,
                'teams' => $teams,
                'tour' => $tourFolder
            ];

            echo "\n\t\tProcessed " . $gameType . " series. [" . ($gameTypeIndex + 1) . "/" . count($gameTypeFolders) . "]\n";
        }

        echo "\n\tProcessed tour - " . $tourFolder . " [" . ($tourIndex + 1) . "/" . count($tourFolders) . "]\n";
    }

    echo "\nProcessed year " . $yearFolder . " [" . ($yearIndex + 1) . "/" . count($yearFolders) . "]\n";
}

//$dataDirectory = APP_PATH . 'app/documents/cricbuzz/yearWiseDetails';
//
//$files = scandir($dataDirectory);
//$files = array_filter($files, function($file){
//    return (($file !== '.') && ($file !== '..'));
//});
//
//$series = [];
//
//foreach($files as $file)
//{
//    $data = json_decode(file_get_contents($dataDirectory . '/' . $file), true);
//
//    foreach($data as $tourName => $tourDetails)
//    {
//        echo "\n" . $tourName . "\n";
//
//        $seriesInfo = $tourDetails['series'];
//        foreach($seriesInfo as $gameType => $seriesDetails)
//        {
//            $matches = $seriesDetails['matches'];
//            $teams = [];
//
//            foreach($matches as $matchName => $matchDetails)
//            {
//                if(preg_match('/Group [A-Za-z0-9]/', $matchName))
//                {
//                    preg_match('/(.*) vs (.*), (.*), Group (.*)/', $matchName, $patternMatches);
//                }
//                else
//                {
//                    preg_match('/(.*) vs (.*), (.*)/', $matchName, $patternMatches);
//                }
//
//                $team1 = $patternMatches[1];
//                $team2 = $patternMatches[2];
//
//                if(!in_array($team1, $teams))
//                {
//                    $teams[] = $team1;
//                }
//
//                if(!in_array($team2, $teams))
//                {
//                    $teams[] = $team2;
//                }
//            }
//
//            $type = 'BI_LATERAL';
//            if(count($teams) === 3)
//            {
//                $type = 'TRI_SERIES';
//            }
//            else if(count($teams) > 3)
//            {
//                $type = 'TOURNAMENT';
//            }
//
//            $series[] = [
//                'name' => $tourName,
//                'startTime' => $seriesDetails['startTime'],
//                'endTime' => $seriesDetails['endTime'],
//                'gameType' => $gameType,
//                'type' => $type,
//                'teams' => $teams,
//                'tour' => $tourName
//            ];
//        }
//    }
//}

file_put_contents(APP_PATH . 'app/documents/cricbuzz/series.json', json_encode($series, JSON_PRETTY_PRINT));
