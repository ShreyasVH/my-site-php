<?php
/**
 * Author: shreyas.hande
 * Date: 10/19/18
 * Time: 10:09 PM
 */

namespace app\models;


class Format extends BaseModel
{
    /**
     * @return Format[]
     */
    public static function getAllFormats()
    {
        $formats = [];
        $response = self::getAPISource()->get('movies/formats');
        if($response['status'] == 200)
        {
            $formats = json_decode($response['result'])->formats;
        }
        return $formats;
    }

    /**
     * @param $id
     * @return Format
     */
    public static function getFormatById($id)
    {
        $format = null;
        $response = self::getAPISource()->get('movies/formats/id/' . $id);
        if($response['status'] == 200)
        {
            $format = json_decode($response['result']);
        }
        return $format;
    }

    /**
     * @param $name
     * @return Format
     */
    public static function getFormatByName($name)
    {
        $format = null;
        $response = self::getAPISource()->get('movies/formats/name/' . $name);
        if($response['status'] == 200)
        {
            $format = json_decode($response['result']);
        }
        return $format;
    }
}