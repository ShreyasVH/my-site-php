<?php
/**
 * Author: shreyas.hande
 * Date: 10/19/18
 * Time: 6:54 PM
 */

namespace app\models;


class Resource extends \Phalcon\Assets\Resource
{
    protected $footprint;

    protected $position;

    const POSITION_HEADER = 0;

    const POSITION_BODY = 1;

    const POSITION_FOOTER = 2;

//    public static $resource_positions =

    /**
     * Resource constructor.
     * @param string $type
     * @param int $position
     * @param boolean $footprint
     * @param string $path
     * @param boolean $local
     * @param boolean $filter
     * @param mixed[]|null $attributes
     */
    public function __construct($type, $position, $footprint, $path, $local, $filter, $attributes)
    {
        $this->footprint = $footprint;
        $this->position = $position;
        parent::__construct($type, $path, $local, $filter, $attributes);
    }

    /**
     * @return bool
     */
    public function isFootprintRequired()
    {
        return $this->footprint;
    }

    public function getPosition()
    {
        return $this->position;
    }
}