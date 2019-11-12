<?php


namespace app\models;


class Card extends BaseModel
{
    public static function filters($payload)
    {
        $response = (object) [
            'totalCount' => 0,
            'cards' => [],
            'offset' => 0
        ];
        $apiResponse = self::getAPISource()->post('cards/filters', $payload, 'DUEL_LINKS');
        if(200 === $apiResponse['status'])
        {
            $response = json_decode($apiResponse['result']);
        }
        return $response;
    }

    public static function getAllAttributes()
    {
        $attributes = [];

        $apiResponse = self::getAPISource()->get('cards/attributes', 'DUEL_LINKS');
        if(200 === $apiResponse['status'])
        {
            $decodedResponse = json_decode($apiResponse['result'], true);
            if(array_key_exists('attributes', $decodedResponse))
            {
                $attributes = $decodedResponse['attributes'];
            }
        }

        return $attributes;
    }

    public static function getAllTypes()
    {
        $types = [];

        $apiResponse = self::getAPISource()->get('cards/types', 'DUEL_LINKS');
        if(200 === $apiResponse['status'])
        {
            $decodedResponse = json_decode($apiResponse['result'], true);
            if(array_key_exists('types', $decodedResponse))
            {
                $types = $decodedResponse['types'];
            }
        }

        return $types;
    }

    public static function getAllCardTypes()
    {
        $cardTypes = [];

        $apiResponse = self::getAPISource()->get('cards/cardTypes', 'DUEL_LINKS');
        if(200 === $apiResponse['status'])
        {
            $decodedResponse = json_decode($apiResponse['result'], true);
            if(array_key_exists('cardTypes', $decodedResponse))
            {
                $cardTypes = $decodedResponse['cardTypes'];
            }
        }

        return $cardTypes;
    }

    public static function getAllCardSubTypes()
    {
        $cardSubTypes = [];

        $apiResponse = self::getAPISource()->get('cards/cardSubTypes', 'DUEL_LINKS');
        if(200 === $apiResponse['status'])
        {
            $decodedResponse = json_decode($apiResponse['result'], true);
            if(array_key_exists('cardSubTypes', $decodedResponse))
            {
                $cardSubTypes = $decodedResponse['cardSubTypes'];
            }
        }

        return $cardSubTypes;
    }

    public static function getAllRarities()
    {
        $rarities = [];

        $apiResponse = self::getAPISource()->get('cards/rarities', 'DUEL_LINKS');
        if(200 === $apiResponse['status'])
        {
            $decodedResponse = json_decode($apiResponse['result'], true);
            if(array_key_exists('rarities', $decodedResponse))
            {
                $rarities = $decodedResponse['rarities'];
            }
        }

        return $rarities;
    }

    public static function getAllLimitTypes()
    {
        $limitTypes = [];

        $apiResponse = self::getAPISource()->get('cards/limitTypes', 'DUEL_LINKS');
        if(200 === $apiResponse['status'])
        {
            $decodedResponse = json_decode($apiResponse['result'], true);
            if(array_key_exists('limitTypes', $decodedResponse))
            {
                $limitTypes = $decodedResponse['limitTypes'];
            }
        }

        return $limitTypes;
    }

    public static function getById($id)
    {
        $card = null;
        $response = self::getAPISource()->get('cards/' . $id, 'DUEL_LINKS');
        if($response['status'] == 200)
        {
            $card = json_decode($response['result']);
        }
        return $card;
    }

    /**
     * @param string $keyword
     * @return Card[]
     */
    public static function getByKeyword($keyword)
    {
        $cards = [];
        $response = self::getAPISource()->get('cards/keyword/' . $keyword, 'DUEL_LINKS');
        if($response['status'] == 200)
        {
            $cards = json_decode($response['result']);
        }

        return $cards;
    }

    public static function getMyCardsById($cardId)
    {
        $myCards = [];
        $response = self::getAPISource()->get('cards/myCards/' . $cardId, 'DUEL_LINKS');
        if($response['status'] == 200)
        {
            $myCards = json_decode($response['result']);
        }
        return $myCards;
    }
}