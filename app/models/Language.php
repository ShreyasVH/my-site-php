<?php
/**
 * Author: shreyas.hande
 * Date: 10/19/18
 * Time: 10:09 PM
 */

namespace app\models;


class Language extends BaseModel
{
    /**
     * @return Language[]
     *
     */
    public static function getAllLanguages()
    {
        $languages = [];
        $response = self::getAPISource()->get('movies/languages');
        if($response['status'] == 200)
        {
            $languages = json_decode($response['result'])->languages;
        }
        return $languages;
    }

    /**
     * @param $id
     * @return Language
     */
    public static function getLanguageById($id)
    {
        $language = null;
        $response = self::getAPISource()->get('movies/language/id/' . $id);
        if($response['status'] == 200)
        {
            $language = json_decode($response['result']);
        }
        return $language;
    }


    /**
     * @param $name
     * @return Language
     */
    public static function getLanguageByName($name)
    {
        $language = null;
        $response = self::getAPISource()->get('movies/language/name/' . ucfirst($name));
        if($response['status'] == 200)
        {
            $language = json_decode($response['result']);
        }
        return $language;
    }
}