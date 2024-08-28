<?php
/**
 * Author: shreyas.hande
 * Date: 10/19/18
 * Time: 6:55 PM
 */

namespace app\models;


use app\helpers\Api;
use Phalcon\Di\Di;
use Phalcon\Mvc\Model;

class BaseModel extends Model
{
    /**
     * @return Api
     */
    public static function getAPISource()
    {
        return Di::getDefault()->get('api');
    }
}