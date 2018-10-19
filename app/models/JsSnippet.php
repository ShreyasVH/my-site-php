<?php
/**
 * Author: shreyas.hande
 * Date: 10/19/18
 * Time: 6:56 PM
 */

namespace app\models;

class JsSnippet extends Resource
{
    /**
     * JsSnippet constructor.
     * @param string $path
     * @param int $position
     * @param boolean $footprint
     * @param mixed[]|null $attributes
     */
    public function __construct($path, $position = Resource::POSITION_HEADER, $footprint = true, $attributes = null)
    {
        parent::__construct('js', $position, $footprint, $path, true, false, $attributes);
    }
}