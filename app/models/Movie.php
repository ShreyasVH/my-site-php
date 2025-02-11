<?php
/**
 * Author: shreyas.hande
 * Date: 10/19/18
 * Time: 10:03 PM
 */

namespace app\models;


use app\constants\Constants;

class Movie extends BaseModel
{
    /**
     * @return array
     */
    public static function dashboard()
    {
        $dashboard = [];
        $response = self::getAPISource()->get('movies/dashboard');
        if($response['status'] == 200)
        {
            $dashboard = json_decode($response['result']);
        }
        return $dashboard;
    }

    /**
     * @param array $filterRequest
     * @return Movie[]
     */
    public static function getMoviesWithFilter($filterRequest)
    {
        $response = (object) [
            'movies' => [],
            'totalCount' => 0,
            'offset' => Constants::DEFAULT_OFFSET
        ];
        $response = self::getAPISource()->post('movies/filter', $filterRequest);
        if($response['status'] == 200)
        {
            $response = json_decode($response['result']);
        }
        return $response;
    }

    /**
     * @return array
     */
    public static function getAllYears()
    {
        $years = [];
        $response = self::getAPISource()->get('movies/years');
        if($response['status'] == 200)
        {
            $years = json_decode($response['result'])->years;
        }
        return $years;
    }

    /**
     * @param $id
     * @return Movie
     */
    public static function getMovieById($id)
    {
        $movie = null;
        $response = self::getAPISource()->get('movies/' . $id);
        if($response['status'] == 200)
        {
            $movie = json_decode($response['result']);
        }
        return $movie;
    }

    /**
     * @param array $actorIds
     * @return Movie[]
     */
    public static function getActorCombinationMovies($actorIds)
    {
        $movies = [];
        $payload = [
            'actorIds' => $actorIds
        ];
        $response = self::getAPISource()->post('movies/moviesWithActors', $payload);
        if($response['status'] == 200)
        {
            $movies = json_decode($response['result']);
        }
        return $movies;
    }

    /**
     * @param string $keyword
     * @return Movie[]
     */
    public static function getMoviesByKeyword($keyword)
    {
        $movies = [];
        $response = self::getAPISource()->get('movies/keyword/' . $keyword);
        if($response['status'] == 200)
        {
            $movies = json_decode($response['result']);
        }

        return $movies;
    }
}