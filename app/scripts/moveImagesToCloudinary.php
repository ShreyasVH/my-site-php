<?php

use app\helpers\Api;
use Cloudinary\Uploader;
use Phalcon\Di\FactoryDefault\Cli;

date_default_timezone_set('Asia/Kolkata');
define('APP_PATH', realpath(dirname(dirname(dirname(__FILE__)))) . '/');

require_once APP_PATH . 'app/constants/constants.php';
require_once APP_PATH . 'app/config/loader.inc.php';

$di = new Cli();

require_once APP_PATH . 'app/config/services.inc.php';

/** @var Api $apiHelper */
$apiHelper = $di->get('api');
$stats = [
    'cards' => [
        'success' => [],
        'failure' => [
            'uploadFailed' => [],
            'updateFailed' => []
        ]
    ],
    'movies' => [
        'success' => [],
        'failure' => [
            'uploadFailed' => [],
            'updateFailed' => []
        ]
    ],
    'artists' => [
        'success' => [],
        'failure' => [
            'uploadFailed' => [],
            'updateFailed' => []
        ]
    ]
];

function uploadImage($currentUrl, $name, $folder)
{
    return Uploader::upload($currentUrl, [
        'folder' => $folder,
        'public_id' => $name
    ]);
}

function updateCard($cardId, $newUrl)
{
    global $apiHelper;
    $payload = [
        'id' => $cardId,
        'imageUrl' => $newUrl
    ];

    $response = $apiHelper->put('cards', $payload, 'DUEL_LINKS');

    return $response;
}

function updateMovie($movieId, $newUrl)
{
    global $apiHelper;
    $payload = [
        'id' => $movieId,
        'imageUrl' => $newUrl
    ];

    $response = $apiHelper->put('movies/movie', $payload);

    return $response;
}

function updateArtist($artistId, $newUrl)
{
    global $apiHelper;
    $payload = [
        'id' => $artistId,
        'imageUrl' => $newUrl
    ];

    $response = $apiHelper->put('artists/artist', $payload);

    return $response;
}

function getCards()
{
    $content = str_replace("\r\n", "\n", file_get_contents(APP_PATH . 'app/documents/cardsForImageMigration.csv'));
    $cards = explode("\n", $content);
    return $cards;
}

function getMovies()
{
    $content = str_replace("\r\n", "\n", file_get_contents(APP_PATH . 'app/documents/moviesForImageMigration.csv'));
    $movies = explode("\n", $content);
    return $movies;
}

function getArtists()
{
    $content = str_replace("\r\n", "\n", file_get_contents(APP_PATH . 'app/documents/artistsForImageMigration.csv'));
    $artists = explode("\n", $content);
    return $artists;
}

function moveImagesForCards()
{
    global $stats;
    $cards = getCards();
    $cardCount = count($cards);

    foreach($cards as $index => $cardObjectRow)
    {
        if($index > 0)
        {
            echo "\n-----------------------------------------------------\n";
        }

        echo "\nProcessing card. [" . ($index + 1) . "/" . $cardCount . "]\n";
        $cardObject = explode(',', $cardObjectRow);
        $cardId = $cardObject[0];
        $name = $cardObject[1];
        $currentUrl = $cardObject[2];
        $version = $cardObject[3];

        $formattedName = str_replace(['#', ' ', '-', ', ', '\'', '"'], '_', strtolower($name));
        if($version > 1)
        {
            $formattedName = $formattedName . '_' . $version;
        }

        $response = uploadImage($currentUrl, $formattedName, 'cards');
        if(array_key_exists('secure_url', $response))
        {
            $newUrl = $response['secure_url'];
            $updateResponse = updateCard($cardId, $newUrl);

            if(200 === $updateResponse['status'])
            {
                $stats['cards']['success'][] = [
                    'id' => $cardId,
                    'name' => $name
                ];
            }
            else
            {
                $stats['cards']['failure']['updateFailed'][] = [
                    'id' => $cardId,
                    'name' => $name
                ];
            }
        }
        else
        {
            $stats['cards']['failure']['uploadFailed'][] = [
                'id' => $cardId,
                'name' => $name,
                'cause' => 'Image upload failed'
            ];
        }

        saveStats($stats);

        echo "\nProcessed card. [" . ($index + 1) . "/" . $cardCount . "]\n";
        usleep(100000);
    }
}

function moveImagesForMovies()
{
    global $stats;
    $movies = getMovies();
    $movieCount = count($movies);

    foreach($movies as $index => $movieObjectRow)
    {
        if($index > 0)
        {
            echo "\n-----------------------------------------------------\n";
        }

        echo "\nProcessing movie. [" . ($index + 1) . "/" . $movieCount . "]\n";
        $movieObject = explode(',', $movieObjectRow);
        $movieId = $movieObject[0];
        $currentUrl = $movieObject[1];

        $response = uploadImage($currentUrl, $movieId, 'movies');
        if(array_key_exists('secure_url', $response))
        {
            $newUrl = $response['secure_url'];
            $updateResponse = updateMovie($movieId, $newUrl);

            if(200 === $updateResponse['status'])
            {
                $stats['movies']['success'][] = [
                    'id' => $movieId,
                ];
            }
            else
            {
                $stats['movie']['failure']['updateFailed'][] = [
                    'id' => $movieId,
                    'cause' => $updateResponse['result']
                ];
            }
        }
        else
        {
            $stats['movies']['failure']['uploadFailed'][] = [
                'id' => $movieId,
                'cause' => 'Image upload failed'
            ];
        }

        saveStats($stats);

        echo "\nProcessed movie. [" . ($index + 1) . "/" . $movieCount . "]\n";
        usleep(100000);
    }
}

function moveImagesForArtists()
{
    global $stats;
    $artists = getArtists();
    $artistCount = count($artists);

    foreach($artists as $index => $artistObjectRow)
    {
        if($index > 0)
        {
            echo "\n-----------------------------------------------------\n";
        }

        echo "\nProcessing artist. [" . ($index + 1) . "/" . $artistCount . "]\n";
        $artistObject = explode(',', $artistObjectRow);
        $artistId = $artistObject[0];
        $currentUrl = $artistObject[1];

        $response = uploadImage($currentUrl, $artistId, 'artists');
        if(array_key_exists('secure_url', $response))
        {
            $newUrl = $response['secure_url'];
            $updateResponse = updateArtist($artistId, $newUrl);

            if(200 === $updateResponse['status'])
            {
                $stats['artists']['success'][] = [
                    'id' => $artistId,
                ];
            }
            else
            {
                $stats['artists']['failure']['updateFailed'][] = [
                    'id' => $artistId,
                    'cause' => $updateResponse['result']
                ];
            }
        }
        else
        {
            $stats['artists']['failure']['uploadFailed'][] = [
                'id' => $artistId,
                'cause' => 'Image upload failed'
            ];
        }

        saveStats($stats);

        echo "\nProcessed artist. [" . ($index + 1) . "/" . $artistCount . "]\n";
        usleep(100000);
    }
}

function saveStats($stats)
{
    file_put_contents(APP_PATH . 'app/documents/imageMigrationStats.json', json_encode($stats, JSON_PRETTY_PRINT));
}


moveImagesForCards();
moveImagesForMovies();
moveImagesForArtists();



echo json_encode($stats);
saveStats($stats);