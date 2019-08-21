<?php


namespace app\controllers;


use app\constants\Constants;
use app\enums\cards\Attribute;
use app\enums\cards\CardSubType;
use app\enums\cards\CardType;
use app\enums\cards\LimitType;
use app\enums\cards\Rarity;
use app\enums\cards\Type;
use app\models\Card;

class CardsController extends BaseController
{
    public function browseAction()
    {
        $this->view->title = 'Browse Cards - Let\'s Duel';

        if($this->request->isGet())
        {
            $filterParams = array_filter($this->request->getQuery(), function($value) {
                return is_array($value) && !empty(array_filter($value, function($v) {
                        return ("" != $v);
                    }));
            });
            $order = $this->request->getQuery('order', null, Constants::DEFAULT_ORDER_CARDS);
        }
        elseif($this->request->isPost())
        {
            $filterParams = array_filter($this->request->getPost(), function($value) {
                return is_array($value) && !empty(array_filter($value, function($v) {
                        return ("" != $v);
                    }));
            });
            $order = $this->request->getPost('order', null, Constants::DEFAULT_ORDER_CARDS);
        }

        $filters = [];
        $rangeFilters = [];

        foreach($filterParams as $key => $valueList)
        {
            $cardAttribute = Constants::getCardAttribute($key);
            if(Constants::FILTER_TYPE_RANGE === $cardAttribute['filterType'])
            {
                $rangeFilters[$key] = [
                    'min' => $valueList[0]
                ];

                if(array_key_exists(1, $valueList))
                {
                    $rangeFilters[$key]['max'] = $valueList[1];
                }
            }
            else
            {
                $filters[$key] = $valueList;
            }
        }

        $sortMap = [];
        $sortMapParts = explode(',', $order);

        foreach($sortMapParts as $index => $sortMapPart)
        {
            $keyValueParts = explode(" ", $sortMapPart);
            $sortMap[$keyValueParts[0]] = $keyValueParts[1];
        }
        $this->view->sortMap = $sortMap;

        $currentOffset = $this->request->getPost('offset', null, Constants::DEFAULT_OFFSET);

        $payload = [
            'count' => Constants::DEFAULT_RESULTS_PER_PAGE,
            'offset' => $currentOffset,
            'sortMap' => $sortMap
        ];

        if(isset($filters) && !empty($filters))
        {
            $payload['filters'] = $filters;
        }

        if(isset($rangeFilters) && !empty($rangeFilters))
        {
            $payload['rangeFilters'] = $rangeFilters;
        }

        $response = Card::filters($payload);

        $cardList = $response->cards;
        $offset = $response->offset;
        $totalCount = $response->totalCount;
        $this->view->offset = $offset;
        $this->view->totalCount = $totalCount;
        $this->view->cardList = $cardList;
        $this->view->filters = $filterParams;

        $filterValues = [
            Constants::CARD_ATTRIBUTE_LEVEL => [
                'min' => 1,
                'max' => 12,
                'step' => 1
            ],
            Constants::CARD_ATTRIBUTE_ATTRIBUTE => Attribute::getAllValuesAsIdValueObjects(),
            Constants::CARD_ATTRIBUTE_TYPE => Type::getAllValuesAsIdValueObjects(),
            Constants::CARD_ATTRIBUTE_ATTACK => [
                'min' => -50,
                'max' => 5000,
                'step' => 50
            ],
            Constants::CARD_ATTRIBUTE_DEFENSE => [
                'min' => -50,
                'max' => 5000,
                'step' => 50
            ],
            Constants::CARD_ATTRIBUTE_CARD_TYPE => CardType::getAllValuesAsIdValueObjects(),
            Constants::CARD_ATTRIBUTE_CARD_SUB_TYPES => CardSubType::getAllValuesAsIdValueObjects(),
            Constants::CARD_ATTRIBUTE_RARITY => Rarity::getAllValuesAsIdValueObjects(),
            Constants::CARD_ATTRIBUTE_LIMIT_TYPE => LimitType::getAllValuesAsIdValueObjects()
        ];
        $this->view->filterValues = $filterValues;

        if($this->request->isAjax())
        {
            $output = [
                'filters' => $filterParams,
                'sortMap' => $sortMap,
                'count' => $totalCount,
                'offset' => $offset,
                'view' => $this->view->getPartial('cards/browse')
            ];

            $this->response->setContentType('application/json', 'UTF-8');
            $output_content = json_encode($output, JSON_UNESCAPED_SLASHES);
            $this->response->setContent($output_content);
            return $this->response;
        }
    }

}