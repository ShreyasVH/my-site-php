<?php

date_default_timezone_set('Asia/Kolkata');
define('APP_PATH', realpath(dirname(dirname(dirname(__FILE__)))) . '/');

$dataDirectory = APP_PATH . 'app/documents/cricbuzz/yearWiseDetails';

$yearFolders = scandir($dataDirectory);
$yearFolders = array_filter($yearFolders, function($file){
    return (($file !== '.') && ($file !== '..'));
});
$yearFolders = array_values($yearFolders);

$tours = [];

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

        if(file_exists($dataDirectory . '/' . $yearFolder . '/tours/' . $tourFolder . '/details.json'))
        {
            $tourDetails = json_decode(file_get_contents($dataDirectory . '/' . $yearFolder . '/tours/' . $tourFolder . '/details.json'), true);

            $tours[] = [
                'name' => $tourDetails['name'],
                'startTime' => $tourDetails['startTime'],
                'endTime' => $tourDetails['endTime']
            ];
        }

        echo "\n\tProcessed tour - " . $tourFolder . " [" . ($tourIndex + 1) . "/" . count($tourFolders) . "]\n";
    }

    echo "\nProcessed year " . $yearFolder . " [" . ($yearIndex + 1) . "/" . count($yearFolders) . "]\n";
}

file_put_contents(APP_PATH . 'app/documents/cricbuzz/tours.json', json_encode($tours, JSON_PRETTY_PRINT));
