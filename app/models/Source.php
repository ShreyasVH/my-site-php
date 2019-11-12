<?php

namespace app\models;

class Source extends BaseModel
{
    public static function getAll()
    {
        $sources = [];

        $response = self::getAPISource()->get('cards/source/all', 'DUEL_LINKS');
        if($response['status'] == 200)
        {
            $sources = json_decode($response['result'], true);
        }

        return $sources;
    }

    public static function getById($id)
    {
        $source = [];

        $response = self::getAPISource()->get('cards/source/' . $id, 'DUEL_LINKS');
        if($response['status'] == 200)
        {
            $source = json_decode($response['result'], true);
        }

        return $source;
    }

    public static function getByKeyword($keyword)
    {
        $sources = [];

        return $sources;
    }

    public static function getByCard($cardId)
    {
        $sources = [];
        $response = self::getAPISource()->get('cards/source/card/' . $cardId, 'DUEL_LINKS');
        if($response['status'] == 200)
        {
            $sources = json_decode($response['result']);
        }

        return $sources;
    }
}
