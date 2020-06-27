<?php

date_default_timezone_set('Asia/Kolkata');
define('APP_PATH', realpath(dirname(dirname(dirname(__FILE__)))) . '/');

$dataDirectory = APP_PATH . 'app/documents/cricbuzz/yearWiseDetails';

$yearFolders = scandir($dataDirectory);
$yearFolders = array_filter($yearFolders, function($file){
    return (($file !== '.') && ($file !== '..'));
});
$yearFolders = array_values($yearFolders);

$stadiums = [];

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
                echo "\n\t\t\tProcessing match - " . $matchDetails['name'] . " [" . ($matchIndex + 1) . "/" . count($matchFiles) . "]\n";

                if(array_key_exists('stadium', $matchDetails))
                {
                    $stadium = $matchDetails['stadium'];
                    $stadiums[$stadium['name']] = $stadium;
                }

                echo "\n\t\t\tProcessed match - " . $matchDetails['name'] . " [" . ($matchIndex + 1) . "/" . count($matchFiles) . "]\n";
            }

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
//	return (($file !== '.') && ($file !== '..'));
//});
//
//$stadiums = [];
//
//foreach($files as $file)
//{
//	$data = json_decode(file_get_contents($dataDirectory . '/' . $file), true);
//
//    foreach($data as $tourName => $tourDetails)
//    {
//        echo "\n" . $tourName . "\n";
//        $seriesInfo = $tourDetails['series'];
//        foreach($seriesInfo as $gameType => $seriesDetails)
//        {
//            echo "\n\t" . $gameType . "\n";
//            $matches = $seriesDetails['matches'];
//            foreach($matches as $matchName => $matchDetails)
//            {
//                echo "\n\t\t" . $matchName . "\n";
//                if(array_key_exists('stadium', $matchDetails))
//                {
//                    $stadium = $matchDetails['stadium'];
//                    $stadiums[$stadium['name']] = $stadium;
//
//                }
//            }
//		}
//	}
//}

usort($stadiums, function($stadium1, $stadium2) {
	return strcasecmp($stadium1['name'], $stadium2['name']);
});

file_put_contents(APP_PATH . 'app/documents/cricbuzz/stadiums.json', json_encode($stadiums, JSON_PRETTY_PRINT));
