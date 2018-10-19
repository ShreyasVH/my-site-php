<?php
/**
 * Author: shreyas.hande
 * Date: 10/19/18
 * Time: 6:50 PM
 */

namespace app\helpers;

use app\constants\Constants;
use app\models\CssSnippet;
use app\models\JsSnippet;
use app\models\Resource;

class AssetHelper extends BaseHelper
{
    private $pageCssMap;

    private $pageJsMap;

    public function __construct()
    {
        $this->pageCssMap = [
        ];

        $this->pageJsMap = [
        ];
    }

    public function getCssFiles($controller = 'index', $action = 'index')
    {
        $common_css_array = [
            new CssSnippet('/css/bootstrap.min.css'),
            new CssSnippet('/css/mystyles.css')
//            new CssSnippet('/css/fonts.css')
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
            new JsSnippet('/js/forms.js', Resource::POSITION_BODY)
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
}