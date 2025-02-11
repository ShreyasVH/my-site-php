<?php
/**
 * Author: shreyas.hande
 * Date: 10/20/18
 * Time: 11:39 AM
 */

namespace app\models;


class Song extends BaseModel
{
    /**
     * @return array
     */
    public static function dashboard()
    {
        $dashboard = [];
        $response = self::getAPISource()->get('songs/dashboard');
        if($response['status'] == 200)
        {
            $dashboard = json_decode($response['result']);
        }

        return $dashboard;
    }

    /**
     * @param string $id
     * @return Song
     */
    public static function getSongById($id)
    {
        $song = null;
        $response = self::getAPISource()->get('songs/' . $id);
        if($response['status'] == 200)
        {
            $song = json_decode($response['result']);
        }
        return $song;
    }

    /**
     * @param array  $filterRequest
     * @return Song[]
     */
    public static function getSongsFromFilter($filterRequest)
    {
        $songs = [];
        $response = self::getAPISource()->post('songs/filter', $filterRequest);
        if($response['status'] == 200)
        {
            $songs = json_decode($response['result']);
        }
        return $songs;
    }

    /**
     * @param $languageId
     * @return int
     */
    public static function getSongCount($languageId)
    {
        $count = 0;
        $response = self::getAPISource()->get('songs/songsCount/languageId/' . $languageId);
        if($response['status'] == 200)
        {
            $count = json_decode($response['result'])->song_count;
        }
        return $count;
    }

    /**
     * @return array
     */
    public static function getAllYears()
    {
        $years = [];
        $response = self::getAPISource()->get('songs/years');
        if($response['status'] == 200)
        {
            $years = json_decode($response['result'])->years;
        }
        return $years;
    }
}