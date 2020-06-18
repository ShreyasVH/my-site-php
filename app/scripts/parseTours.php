<?php

date_default_timezone_set('Asia/Kolkata');
define('APP_PATH', realpath(dirname(dirname(dirname(__FILE__)))) . '/');

$dataDirectory = APP_PATH . 'app/documents/cricbuzz/yearWiseDetails';

$files = scandir($dataDirectory);
$files = array_filter($files, function($file){
    return (($file !== '.') && ($file !== '..'));
});

$tours = [];

foreach($files as $file)
{
    $data = json_decode(file_get_contents($dataDirectory . '/' . $file), true);

    foreach($data as $seriesName => $seriesDetails)
    {
        echo "\n" . $seriesName . "\n";
        $tours[] = $seriesName;
    }
}

file_put_contents(APP_PATH . 'app/documents/cricbuzz/tours.json', json_encode($tours, JSON_PRETTY_PRINT));
