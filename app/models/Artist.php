<?php
/**
 * Author: shreyas.hande
 * Date: 10/19/18
 * Time: 10:09 PM
 */

namespace app\models;


class Artist extends BaseModel
{
    /**
     * @return Artist[]
     */
    public static function getAllActors()
    {
        $actors = [];
        $response = self::getAPISource()->get('artists/actors');
        if($response['status'] == 200)
        {
            $actors = json_decode($response['result'])->actors;
        }
        return $actors;
    }

    /**
     * @return Artist[]
     */
    public static function getAllDirectors()
    {
        $directors = [];
        $response = self::getAPISource()->get('artists/directors');
        if($response['status'] == 200)
        {
            $directors = json_decode($response['result'])->directors;
        }
        return $directors;
    }

    /**
     * @param $id
     * @return Artist
     */
    public static function getArtistById($id)
    {
        $artist = null;
        $response = self::getAPISource()->get('artists/' . $id);
        if($response['status'] == 200)
        {
            $artist = json_decode($response['result']);
        }
        return $artist;
    }

    /**
     * @param $keyword
     * @return Artist
     */
    public static function getArtistsByKeyword($keyword)
    {
        $artists = [];
        $response = self::getAPISource()->get('artists/keyword/' . urlencode($keyword));
        if($response['status'] == 200)
        {
            $artists = json_decode($response['result']);
        }
        return $artists;
    }

    /**
     * @return Artist[]
     */
    public static function getAllSingers()
    {
        $singers = [];
        $response = self::getAPISource()->get('artists/singers');
        if($response['status'] == 200)
        {
            $singers = json_decode($response['result'])->singers;
        }
        return $singers;
    }


    /**
     * @return Artist[]
     */
    public static function getAllComposers()
    {
        $composers = [];
        $response = self::getAPISource()->get('artists/composers');
        if($response['status'] == 200)
        {
            $composers = json_decode($response['result'])->composers;
        }
        return $composers;
    }

    /**
     * @return Artist[]
     */
    public static function getAllLyricists()
    {
        $lyricists = [];
        $response = self::getAPISource()->get('artists/lyricists');
        if($response['status'] == 200)
        {
            $lyricists = json_decode($response['result'])->lyricists;
        }
        return $lyricists;
    }
}