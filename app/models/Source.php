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
}
