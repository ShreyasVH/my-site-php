<?php

date_default_timezone_set('Asia/Kolkata');
define('APP_PATH', realpath(dirname(dirname(dirname(__FILE__)))) . '/');

$dataDirectory = APP_PATH . 'app/documents/cricbuzz/yearWiseDetails';

$files = scandir($dataDirectory);
$files = array_filter($files, function($file){
	return (($file !== '.') && ($file !== '..'));
});

$stadiums = [];

foreach($files as $file)
{
	$data = json_decode(file_get_contents($dataDirectory . '/' . $file), true);

	foreach($data as $seriesName => $seriesDetails)
	{
		echo "\n" . $seriesName . "\n";
		foreach($seriesDetails as $matchName => $matchDetails)
		{
			echo "\n\t" . $matchName . "\n";
			if(array_key_exists('stadium', $matchDetails))
			{
				$stadium = $matchDetails['stadium'];
				if(!in_array($stadium, $stadiums))
				{
					$stadiums[] = $stadium;
				}
				
			}
			
		}
	}
}

usort($stadiums, 'strcasecmp');

file_put_contents(APP_PATH . 'app/documents/cricbuzz/stadiums.json', json_encode($stadiums, JSON_PRETTY_PRINT));
