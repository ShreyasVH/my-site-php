<?php

use app\enums\cards\SourceType;
use app\enums\cards\Type;
use app\helpers\Api;
use Phalcon\Di\FactoryDefault\Cli;

date_default_timezone_set('Asia/Kolkata');
define('APP_PATH', realpath(dirname(dirname(dirname(__FILE__)))) . '/');

require_once APP_PATH . 'app/constants/constants.php';
require_once APP_PATH . 'app/config/loader.inc.php';

$di = new Cli();

require_once APP_PATH . 'app/config/services.inc.php';

/** @var Api $apiHelper */
$apiHelper = $di->get('api');

$data = [];

function getAllSources()
{
    $sources = [];
//    $query = 'SELECT * FROM `sources` WHERE `type` = ' . SourceType::TICKET . ' AND ((`expiry` IS NULL) OR (`expiry` >= ' . date('Y-m-d') . ')) AND `quantity` > 0';
    $query = 'SELECT * FROM `sources` WHERE `type` = ' . SourceType::TICKET . ' AND ((`expiry` IS NULL) OR (`expiry` >= ' . date('Y-m-d') . ')) AND `quantity` > 0 LIMIT 2';
    $response = runQuery($query, '7LwLc51AZ7');
    if($response)
    {
        $sources = $response->fetch_all(MYSQLI_ASSOC);
    }

    return $sources;
}

$cachedCardsForSource = [];

function getCardsForSource($sourceId)
{
    $cards = [];
    global $cachedCardsForSource;
    if(isset($cachedCardsForSource[$sourceId]))
    {
        $cards = $cachedCardsForSource[$sourceId];
    }
    else
    {
        $query = 'SELECT c.id as id, c.name as name, c.version as version FROM `source_card_map` scm INNER JOIN `cards` c ON scm.card_id = c.id WHERE scm.source_id = ' . $sourceId;
        $response = runQuery($query, '7LwLc51AZ7');
        if($response)
        {
            $cards = $response->fetch_all(MYSQLI_ASSOC);
            $cachedCardsForSource[$sourceId] = $cards;
        }
    }

    return $cards;
}

$cachedOwnedCount = [];
function getOwnedCount($cardId)
{
    $count = 0;
    global $cachedOwnedCount;

    if(isset($cachedOwnedCount[$cardId]))
    {
        $count = $cachedOwnedCount[$cardId];
    }
    else
    {
        $query = 'SELECT COUNT(*) as count FROM `my_cards` WHERE `card_id` = ' . $cardId;
        $response = runQuery($query, '7LwLc51AZ7');
        if($response)
        {
            $rows = $response->fetch_all(MYSQLI_ASSOC);
            if(!empty($rows))
            {
                $row = $rows[0];
                $count = (int) $row['count'];
                $cachedOwnedCount[$cardId] = $count;
            }
        }
    }

    return $count;
}

$cachedSourcesForCard = [];
function getSourcesForCard($cardId)
{
    global $cachedSourcesForCard;
    $sources = [];

    if(isset($cachedSourcesForCard[$cardId]))
    {
        $sources = $cachedSourcesForCard[$cardId];
    }
    else
    {
        $query = 'SELECT s.id as id, s.type as typeId FROM `sources` s INNER JOIN `source_card_map` scm ON scm.source_id = s.id WHERE scm.card_id = ' . $cardId;
        $response = runQuery($query, '7LwLc51AZ7');
        if($response)
        {
            $sources = $response->fetch_all(MYSQLI_ASSOC);
            $cachedSourcesForCard[$cardId] = $sources;
        }
    }

    return $sources;
}

function runQuery($query, $databaseName)
{
    /** @var \mysqli $dbLink */
    $dbLink = mysqli_connect('remotemysql.com', '7LwLc51AZ7', '0gjqxza8GP');
    $result = false;
    if($dbLink)
    {
        if(isset($databaseName) && !empty($databaseName))
        {
            $q = 'USE ' . $databaseName;
            mysqli_query($dbLink, $q);
        }
        $result = mysqli_query($dbLink, $query);
        if(!$result)
        {
            echo "\nError executing mysql query.\nDatabase : " . $databaseName . "\nQuery : " . $query . ".\nResponse : " . json_encode($result, JSON_PRETTY_PRINT) . "\nError : " . $dbLink->error . "\n";
        }

        $dbLink->close();
    }
    else
    {
        echo "\nError executing mysql query.\nDatabase : " . $databaseName . "\nQuery : " . $query . ".\nResponse : " . json_encode($result, JSON_PRETTY_PRINT) . "\nError : Couldn't connect to DB" . "\n";
    }
    return $result;
}

$sources = getAllSources();
foreach($sources as $sourceIndex => $source)
{
    if($sourceIndex > 0)
    {
        echo "\n----------------------------------------------\n";
    }

    echo "\nProcessing source " . $source['name'] . "[" . ($sourceIndex + 1) . "/" . count($sources) . "]\n";
    $mainSourceId = $source['id'];
    $data[$source['name']] = [
        'exclusive' => [],
        'primary' => [],
        'secondary' => [],
        'repeated' => [],
        'unwanted' => []
    ];


    $cards = getCardsForSource($mainSourceId);
    foreach($cards as $index => $card)
    {
        $cardId = $card['id'];
        if($index > 0)
        {
            echo "\n\t-------------------------------------\n";
        }

        echo "\n\tProcessing Card. " . $card['name'] . "[" . ($index + 1) . "/" . count($cards) . "]\n";
        $ownedCount = getOwnedCount($cardId);

        if($ownedCount >= 3)
        {
            $data[$source['name']]['unwanted'][] = [
                'name' => $card['name'],
                'version' => $card['version'],
                'owned' => $ownedCount
            ];
        }
        else
        {
//                $sourceIds = $card['sourceIds'];
            $secondarySources = getSourcesForCard($cardId);

            if(count($sources) === 1)
            {
                $data[$source['name']]['exclusive'][] = [
                    'name' => $card['name'],
                    'version' => $card['version'],
                    'owned' => $ownedCount
                ];
            }
            else
            {
                $isRepeated = false;
                $allTickets = true;
                foreach($secondarySources as $sIndex => $secondarySource)
                {
                    $sourceId = $secondarySource['id'];
                    if($sIndex > 0)
                    {
                        echo "\n\t\t................................\n";
                    }
                    echo "\n\t\tProcessing Secondary Source.[" . ($sIndex + 1) . "/" . count($secondarySources) . "]\n";
                    if($sourceId !== $mainSourceId)
                    {
                        $typeId = $secondarySource['typeId'];
                        $type = SourceType::label($typeId);
                        if(in_array($type, ['STARTER_DECK', 'GATE_DROP', 'CARD_TRADER']))
                        {
                            $isRepeated = true;
                            break;
                        }
                        $allTickets = ($allTickets && ('TICKET' === $type));
                    }

                    echo "\n\t\tProcessed Secondary Source.[" . ($sIndex + 1) . "/" . count($secondarySources) . "]\n";
                }

                if($isRepeated)
                {
                    $data[$source['name']]['repeated'][] = [
                        'name' => $card['name'],
                        'version' => $card['version'],
                        'owned' => $ownedCount
                    ];
                }
                else if($allTickets)
                {
                    $data[$source['name']]['primary'][] = [
                        'name' => $card['name'],
                        'version' => $card['version'],
                        'owned' => $ownedCount
                    ];
                }
                else
                {
                    $data[$source['name']]['secondary'][] = [
                        'name' => $card['name'],
                        'version' => $card['version'],
                        'owned' => $ownedCount
                    ];
                }
            }
        }

        echo "\n\tProcessed Card. " . $card['name'] . "[" . ($index + 1) . "/" . count($cards) . "]\n";
        usleep(100000);
    }

    echo "\nProcessed source " . $source['name'] . "[" . ($sourceIndex + 1) . "/" . count($sources) . "]\n";
    usleep(500000);
}


file_put_contents(APP_PATH . 'app/documents/cardSuggestions.json', json_encode($data, JSON_PRETTY_PRINT));
