<?php
/**
 * Author: shreyas.hande
 * Date: 10/19/18
 * Time: 6:42 PM
 */

namespace app\controllers;

use app\helpers\Api;
use app\helpers\AssetHelper;
use app\models\Resource;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;

/**
 * @property AssetHelper assetHelper
 * @property Api api
 */
class BaseController extends Controller
{
    /**
     * @param Dispatcher $dispatcher
     */
    public function beforeExecuteRoute(Dispatcher $dispatcher)
    {
        $css_files = $this->assetHelper->getCssFiles($dispatcher->getControllerName(), $dispatcher->getActionName());
        $js_files = $this->assetHelper->getJsFiles($dispatcher->getControllerName(), $dispatcher->getActionName());

        $headerCss = $this->assets->collection('header');

        $bodyJs = $this->assets->collection('body');

        $footerJs = $this->assets->collection('footer');

        foreach($css_files as $index => $file)
        {
            $headerCss->addCss($file->getPath(), $file->getLocal(), $file->getFilter());
        }

        foreach($js_files as $index => $file)
        {
            if($file->getPosition() == Resource::POSITION_BODY)
            {
                $bodyJs->addJs($file->getPath(), $file->getLocal(), $file->getFilter());
            }
            else
            {
                $footerJs->addJs($file->getPath(), $file->getLocal(), $file->getFilter());
            }
        }
    }
}
