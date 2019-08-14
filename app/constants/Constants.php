<?php
/**
 * Author: shreyas.hande
 * Date: 10/19/18
 * Time: 6:37 PM
 */

namespace app\constants;


class Constants
{
    const NAMESPACE_CONTROLLERS = 'app\\controllers';

    const PUBLIC_FOLDER = APP_PATH . 'public/';
    const IMAGES_FOLDER = 'images/';
    const IMAGES_FOLDER_MOVIE = 'movies/';
    const IMAGES_FOLDER_ARTIST = 'artists/';

    const DEFAULT_OFFSET = 0;
    const DEFAULT_PAGE = 1;
    const DEFAULT_RESULTS_PER_PAGE = 24;
    const DEFAULT_ORDER_MOVIES = 'name ASC';
    const DEFAULT_ORDER_SONGS = 'name ASC';
    const DEFAULT_SONG_LANGUAGE = 'Kannada';
    const DEFAULT_SONGS_PER_PAGE = 24;

    const DEFAULT_ORDER_CARDS = 'name ASC';

    const CONTEXT_MOVIE = 'movie';
    const CONTEXT_ARTIST = 'artist';

    const DEFAULT_IMAGE_ARTIST_MALE = 'default_m.jpg';
    const DEFAULT_IMAGE_ARTIST_FEMALE = 'default_f.jpg';
    const DEFAULT_IMAGE_MOVIE = 'default.jpg';

    const MOVIE_ATTRIBUTE_ID = 'id';
    const MOVIE_ATTRIBUTE_NAME = 'name';
    const MOVIE_ATTRIBUTE_LANGUAGE = 'language';
    const MOVIE_ATTRIBUTE_SIZE = 'size';
    const MOVIE_ATTRIBUTE_FORMAT = 'format';
    const MOVIE_ATTRIBUTE_QUALITY = 'quality';
    const MOVIE_ATTRIBUTE_SUBTITLES = 'subtitles';
    const MOVIE_ATTRIBUTE_YEAR = 'year';
    const MOVIE_ATTRIBUTE_SEEN = 'seen_in_theatre';
    const MOVIE_ATTRIBUTE_BASENAME = 'basename';
    const MOVIE_ATTRIBUTE_STATUS = 'status';
    const MOVIE_ATTRIBUTE_ACTORS = 'actors';
    const MOVIE_ATTRIBUTE_DIRECTORS = 'directors';

    const CARD_ATTRIBUTE_ID = 'id';
    const CARD_ATTRIBUTE_NAME = 'name';
    const CARD_ATTRIBUTE_LEVEL = 'level';
    const CARD_ATTRIBUTE_ATTRIBUTE = 'attribute';
    const CARD_ATTRIBUTE_TYPE = 'type';
    const CARD_ATTRIBUTE_ATTACK = 'attack';
    const CARD_ATTRIBUTE_DEFENSE = 'defense';
    const CARD_ATTRIBUTE_CARD_TYPE = 'cardType';
    const CARD_ATTRIBUTE_CARD_SUB_TYPES = 'cardSubTypes';
    const CARD_ATTRIBUTE_RARITY = 'rarity';
    const CARD_ATTRIBUTE_LIMIT_TYPE = 'limitType';

    const FILTER_TYPE_CHECKBOX = 'checkbox';
    const FILTER_TYPE_SEARCH = 'search';
    const FILTER_TYPE_RANGE = 'range';
    const FILTER_TYPE_TEXT = 'text';

    private static $movieAttributes = [
        self::MOVIE_ATTRIBUTE_ID => [
            'filterLabel' => 'Id',
            'filterType' => self::FILTER_TYPE_SEARCH,
            'isFilterEnabled' => false,
            'isSortEnabled' => true,
            'sortLabel' => 'Created'
        ],
        self::MOVIE_ATTRIBUTE_NAME => [
            'filterLabel' => 'Name',
            'filterType' => self::FILTER_TYPE_SEARCH,
            'isFilterEnabled' => false,
            'isSortEnabled' => true,
            'sortLabel' => 'Name'
        ],
        self::MOVIE_ATTRIBUTE_LANGUAGE => [
            'filterLabel' => 'Languages',
            'filterType' => self::FILTER_TYPE_CHECKBOX
        ],
        self::MOVIE_ATTRIBUTE_FORMAT => [
            'filterLabel' => 'Formats',
            'filterType' => self::FILTER_TYPE_CHECKBOX
        ],
        self::MOVIE_ATTRIBUTE_YEAR => [
            'filterLabel' => 'Years',
            'filterType' => self::FILTER_TYPE_RANGE,
            'isSortEnabled' => true,
            'sortLabel' => 'Year'
        ],
        self::MOVIE_ATTRIBUTE_SUBTITLES => [
            'filterLabel' => 'Subtitles',
            'filterType' => self::FILTER_TYPE_CHECKBOX
        ],
        self::MOVIE_ATTRIBUTE_SEEN => [
            'filterLabel' => 'Seen',
            'filterType' => self::FILTER_TYPE_CHECKBOX
        ],
        self::MOVIE_ATTRIBUTE_QUALITY => [
            'filterLabel' => 'Quality',
            'filterType' => self::FILTER_TYPE_CHECKBOX
        ],
        self::MOVIE_ATTRIBUTE_ACTORS => [
            'filterLabel' => 'Actors',
            'filterType' => self::FILTER_TYPE_SEARCH
        ],
        self::MOVIE_ATTRIBUTE_DIRECTORS => [
            'filterLabel' => 'Directors',
            'filterType' => self::FILTER_TYPE_SEARCH
        ],
        self::MOVIE_ATTRIBUTE_SIZE => [
            'filterLabel' => 'Size',
            'filterType' => self::FILTER_TYPE_RANGE,
            'isSortEnabled' => true,
            'sortLabel' => 'Size'
        ],
        self::MOVIE_ATTRIBUTE_BASENAME => [
            'filterLabel' => 'Basename',
            'filterType' => self::FILTER_TYPE_TEXT
        ],
        self::MOVIE_ATTRIBUTE_STATUS => [
            'filterLabel' => 'Status',
            'filterType' => self::FILTER_TYPE_CHECKBOX,
            'isFilterEnabled' => false
        ]
    ];

    private static $cardAttributes = [
        self::CARD_ATTRIBUTE_ID => [
            'filterLabel' => 'Id',
            'filterType' => self::FILTER_TYPE_SEARCH,
            'isFilterEnabled' => false,
            'isSortEnabled' => true,
            'sortLabel' => 'Created'
        ],
        self::CARD_ATTRIBUTE_NAME => [
            'filterLabel' => 'Name',
            'filterType' => self::FILTER_TYPE_SEARCH,
            'isFilterEnabled' => false,
            'isSortEnabled' => true,
            'sortLabel' => 'Name'
        ],
        self::CARD_ATTRIBUTE_LEVEL => [
            'filterLabel' => 'Level',
            'filterType' => self::FILTER_TYPE_RANGE,
            'isSortEnabled' => true,
            'sortLabel' => 'Level'
        ],
        self::CARD_ATTRIBUTE_ATTRIBUTE => [
            'filterLabel' => 'Attribute',
            'filterType' => self::FILTER_TYPE_CHECKBOX
        ],
        self::CARD_ATTRIBUTE_TYPE => [
            'filterLabel' => 'Type',
            'filterType' => self::FILTER_TYPE_CHECKBOX
        ],
        self::CARD_ATTRIBUTE_ATTACK => [
            'filterLabel' => 'Attack',
            'filterType' => self::FILTER_TYPE_RANGE,
            'isSortEnabled' => true,
            'sortLabel' => 'Attack',
        ],
        self::CARD_ATTRIBUTE_DEFENSE => [
            'filterLabel' => 'Defense',
            'filterType' => self::FILTER_TYPE_RANGE,
            'isSortEnabled' => true,
            'sortLabel' => 'Defense',
        ],
        self::CARD_ATTRIBUTE_CARD_TYPE => [
            'filterLabel' => 'Card Type',
            'filterType' => self::FILTER_TYPE_CHECKBOX
        ],
        self::CARD_ATTRIBUTE_CARD_SUB_TYPES => [
            'filterLabel' => 'Card SubType',
            'filterType' => self::FILTER_TYPE_CHECKBOX
        ],
        self::CARD_ATTRIBUTE_RARITY => [
            'filterLabel' => 'Rarity',
            'filterType' => self::FILTER_TYPE_CHECKBOX
        ],
        self::CARD_ATTRIBUTE_LIMIT_TYPE => [
            'filterLabel' => 'LimitType',
            'filterType' => self::FILTER_TYPE_CHECKBOX
        ]
    ];

    public static function getMovieAttributes()
    {
        return self::$movieAttributes;
    }

    public static function getCardAttributes()
    {
        return self::$cardAttributes;
    }

    public static function getCardAttribute($key)
    {
        $cardAttribute = null;

        if(array_key_exists($key, self::$cardAttributes))
        {
            $cardAttribute = self::$cardAttributes[$key];
        }

        return $cardAttribute;
    }
}