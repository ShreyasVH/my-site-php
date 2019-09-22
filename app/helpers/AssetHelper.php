<?php
/**
 * Author: shreyas.hande
 * Date: 10/19/18
 * Time: 6:50 PM
 */

namespace app\helpers;

use app\constants\Constants;
use app\enums\Gender;
use app\models\CssSnippet;
use app\models\JsSnippet;
use app\models\Resource;
use app\models\Song;

class AssetHelper extends BaseHelper
{
    private $pageCssMap;

    private $pageJsMap;

    public function __construct()
    {
        $this->pageCssMap = [
            'artists' => [
                'edit' => [
                    new CssSnippet('/css/add-movie.css'),
                    new CssSnippet('/css/add-actor.css'),
                    new CssSnippet('/css/forms.css')
                ]
            ],
            'cards' => [
                'add' => [
                    new CssSnippet('/css/add-movie.css'),
                    new CssSnippet('/css/add-actor.css'),
                    new CssSnippet('/css/forms.css')
                ],
                'addSource' => [
                    new CssSnippet('/css/add-movie.css'),
                    new CssSnippet('/css/add-actor.css'),
                    new CssSnippet('/css/forms.css'),
                    new CssSnippet('/css/jquery-ui.min.css')
                ],
                'browse' => [
                    new CssSnippet('/css/jquery-ui.min.css'),
                    new CssSnippet('/css/card.css'),
                    new CssSnippet('/css/filters.css'),
                    new CssSnippet('/css/add-actor.css')
                ],
                'edit' => [
                    new CssSnippet('/css/add-movie.css'),
                    new CssSnippet('/css/add-actor.css'),
                    new CssSnippet('/css/forms.css')
                ],
            ],
            'movies' => [
                'actorCombinations' => [
                    new CssSnippet('/css/add-movie.css'),
                    new CssSnippet('/css/forms.css'),
                    new CssSnippet('/css/card.css')
                ],
                'actorMovies' => [
                    new CssSnippet('/css/card.css')
                ],
                'addActor' => [
                    new CssSnippet('/css/add-actor.css')
                ],
                'addDirector' => [
                    new CssSnippet('/css/add-actor.css')
                ],
                'addMovie' => [
                    new CssSnippet('/css/add-movie.css'),
                    new CssSnippet('/css/add-actor.css'),
                    new CssSnippet('/css/forms.css')
                ],
                'browseMovies' => [
                    new CssSnippet('/css/jquery-ui.min.css'),
                    new CssSnippet('/css/forms.css'),
                    new CssSnippet('/css/card.css'),
                    new CssSnippet('/css/filters.css')
                ],
                'deletedMovies' => [
                    new CssSnippet('/css/card.css')
                ],
                'directorMovies' => [
                    new CssSnippet('/css/card.css')
                ],
                'editMovie' => [
                    new CssSnippet('/css/add-movie.css'),
                    new CssSnippet('/css/add-actor.css'),
                    new CssSnippet('/css/forms.css')
                ],
                'movieDetail' => [
                    new CssSnippet('/css/add-movie.css'),
                    new CssSnippet('/css/add-actor.css'),
                    new CssSnippet('/css/movie-detail.css')
                ],
                'yearMovies' => [
                    new CssSnippet('/css/card.css')
                ]
            ],
            'songs' => [
                'addSong' => [
                    new CssSnippet('/css/add-movie.css'),
                    new CssSnippet('/css/add-actor.css'),
                    new CssSnippet('/css/forms.css')
                ],
                'browseSongs' => [
                    new CssSnippet('/css/card.css'),
                    new CssSnippet('/css/play-song.css'),
                    new CssSnippet('/css/browse-songs.css')
                ],
                'composerSongs' => [
                    new CssSnippet('/css/card.css'),
                    new CssSnippet('/css/play-song.css')
                ],
                'editSong' => [
                    new CssSnippet('/css/add-movie.css'),
                    new CssSnippet('/css/add-actor.css'),
                    new CssSnippet('/css/forms.css')
                ],
                'lyricistSongs' => [
                    new CssSnippet('/css/card.css'),
                    new CssSnippet('/css/play-song.css')
                ],
                'singerSongs' => [
                    new CssSnippet('/css/card.css'),
                    new CssSnippet('/css/play-song.css')
                ],
                'songDetail' => [
                    new CssSnippet('/css/song-detail.css'),
                    new CssSnippet('/css/play-song.css')
                ],
                'yearSongs' => [
                    new CssSnippet('/css/card.css'),
                    new CssSnippet('/css/play-song.css')
                ]
            ]
        ];

        $this->pageJsMap = [
            'cards' => [
                'add' => [
                    new JsSnippet('/js/cards.js', Resource::POSITION_FOOTER),
                ],
                'addSource' => [
                    new JsSnippet('/js/jquery-ui.min.js', Resource::POSITION_FOOTER)
                ],
                'browse' => [
                    new JsSnippet('/js/jquery-ui.min.js', Resource::POSITION_FOOTER),
                    new JsSnippet('/js/data-manipulation.js', Resource::POSITION_FOOTER),
                    new JsSnippet('/js/cards.js', Resource::POSITION_FOOTER)
                ],
                'edit' => [
                    new JsSnippet('/js/cards.js', Resource::POSITION_FOOTER),
                ],
            ],
            'movies' => [
                'browseMovies' => [
                    new JsSnippet('/js/jquery-ui.min.js', Resource::POSITION_FOOTER),
                    new JsSnippet('/js/data-manipulation.js', Resource::POSITION_FOOTER)
                ]
            ],
            'songs' => [
                'browseSongs' => [
                    new JsSnippet('/js/play-song.js', Resource::POSITION_FOOTER)
                ],
                'composerSongs' => [
                    new JsSnippet('/js/play-song.js', Resource::POSITION_FOOTER)
                ],
                'lyricistSongs' => [
                    new JsSnippet('/js/play-song.js', Resource::POSITION_FOOTER)
                ],
                'singerSongs' => [
                    new JsSnippet('/js/play-song.js', Resource::POSITION_FOOTER)
                ],
                'songDetail' => [
                    new JsSnippet('/js/play-song.js', Resource::POSITION_FOOTER)
                ],
                'yearSongs' => [
                    new JsSnippet('/js/play-song.js', Resource::POSITION_FOOTER)
                ]
            ]
        ];
    }

    public function getCssFiles($controller = 'index', $action = 'index')
    {
        $common_css_array = [
            new CssSnippet('/css/bootstrap.min.css'),
            new CssSnippet('/css/fonts.css'),
            new CssSnippet('/css/mystyles.css')
        ];

        $specific_css_array = $this->_getPageSpecificCssFiles($controller, $action);
        $css_files = array_merge($common_css_array, $specific_css_array);
        /** @var CssSnippet[] $css_files */
        foreach($css_files as $index => $cssSnippet)
        {
            $cssSnippet->setPath(self::getFileWithFootprint($cssSnippet->getPath()));
        }
        return $css_files;
    }

    private function _getPageSpecificCssFiles($controller, $action)
    {
        return (isset($this->pageCssMap[$controller][$action]) ? $this->pageCssMap[$controller][$action] : array());
    }

    public function getJsFiles($controller = 'index', $action = 'index')
    {
        if(($pos = (strpos($action, 'Api'))) != false)
        {
            $action = substr($action, 0, $pos);
        }

        $common_js_array = [
            new JsSnippet('/js/jquery.js', Resource::POSITION_BODY),
            new JsSnippet('/js/bootstrap.min.js', Resource::POSITION_BODY),
            new JsSnippet('/js/myscripts.js', Resource::POSITION_BODY),
            new JsSnippet('/js/forms.js', Resource::POSITION_BODY),
            new JsSnippet('/js/notify.js', Resource::POSITION_BODY)
        ];
        $specific_js_array = $this->_getPageSpecificJsFiles($controller, $action);
        /** @var JsSnippet[] $js_files */
        $js_files = array_merge($common_js_array, $specific_js_array);

        foreach($js_files as $jsSnippet)
        {
            $jsSnippet->setPath(self::getFileWithFootprint($jsSnippet->getPath()));
        }

        return $js_files;
    }

    private function _getPageSpecificJsFiles($controller, $action)
    {
        return (isset($this->pageJsMap[$controller][$action]) ? $this->pageJsMap[$controller][$action] : array());
    }

    public static function getFootprint($file_path)
    {
        $file_path = Constants::PUBLIC_FOLDER . $file_path;
        $footprint = time();
        if(file_exists($file_path)) {
            $footprint = filemtime($file_path);
        }
        return '?t=' . $footprint;
    }

    public static function getFileWithFootprint($filePath)
    {
        return $filePath . self::getFootprint($filePath);
    }

    public static function getImage($filename, $context, $params = array())
    {
        $filename .= '.jpg';
        switch($context)
        {
            case Constants::CONTEXT_ARTIST :
                $folder = Constants::IMAGES_FOLDER . Constants::IMAGES_FOLDER_ARTIST;
                if($params['gender'] == Gender::MALE)
                {
                    $default_file = Constants::DEFAULT_IMAGE_ARTIST_MALE;
                }
                else
                {
                    $default_file = Constants::DEFAULT_IMAGE_ARTIST_FEMALE;
                }
                break;
            case Constants::CONTEXT_MOVIE :
                $folder = Constants::IMAGES_FOLDER . Constants::IMAGES_FOLDER_MOVIE;
                $default_file = Constants::DEFAULT_IMAGE_MOVIE;
                break;

        }

        $file_path = Constants::PUBLIC_FOLDER . $folder . $filename;
        $default_file_path = $folder . $default_file;

        if(file_exists($file_path))
        {
            return self::getFileWithFootprint('/' . $folder . $filename);
        }
        return self::getFileWithFootprint('/' . $default_file_path);
    }

    /**
     * @param Song $song
     * @return string
     */
    public static function getSongUrl($song)
    {
        return DIRECTORY_SEPARATOR . 'audio' . DIRECTORY_SEPARATOR . $song->movie->language->name . DIRECTORY_SEPARATOR . $song->name . '.mp3';
    }
}