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
use app\models\Source;
use Phalcon\Http\Request\File;

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

    public function addAction()
    {
        $this->view->title = 'Add Card';
        if($this->request->isGet())
        {

        }
        else if($this->request->isPost())
        {
            $name = $this->request->getPost('name');
            $cardType = $this->request->getPost('cardType');

            $payload = [
                'name' => $name,
                'description' => $this->request->getPost('description'),
                'cardType' => $this->request->getPost('cardType'),
                'cardSubTypes' => $this->request->getPost('cardSubTypes'),
                'rarity' => $this->request->getPost('rarity'),
                'limitType' => $this->request->getPost('limitType')
            ];

            if(CardType::MONSTER == $cardType)
            {
                $payload['level'] = $this->request->getPost('level');
                $payload['type'] = $this->request->getPost('type');
                $payload['attribute'] = $this->request->getPost('attribute');
                $payload['attack'] = $this->request->getPost('attack');
                $payload['defense'] = $this->request->getPost('defense');
            }

            $imageUrl = getenv('DUEL_LINKS_DEFAULT_IMAGE_URL');

            if($this->request->hasFiles())
            {
                /** @var File[] $uploaded_files */
                $uploaded_files = $this->request->getUploadedFiles();
                $file = $uploaded_files[0];

                if('' != $file->getName())
                {
                    $formattedName = str_replace(['#', ' ', '-', ', '], '_', strtolower($name));
                    $version = 1;

                    if($version > 1)
                    {
                        $formattedName = $formattedName . '_' . $version;
                    }

                    $filename = $formattedName . '.' . $file->getExtension();

                    $fields = [
                        'folderName' => 'cards'
                    ];

                    $fileObjects = [
                        [
                            'name' => $filename,
                            'path' => $file->getTempName()
                        ]
                    ];

                    $files = [];
                    foreach($fileObjects as $index => $fileObject)
                    {
                        $fileContent = file_get_contents($fileObject['path']);
                        $files[$fileObject['name']] = $fileContent;
                    }
                    $uploadResponse = $this->api->uploadFile($fields, $files);
                    if(array_key_exists('status', $uploadResponse) && (200 === $uploadResponse['status']))
                    {
                        $decodedResponse = json_decode($uploadResponse['result'], true);
                        $imageUrl = $decodedResponse['url'];
                    }
                }
            }

            if(!empty($imageUrl))
            {
                $payload['imageUrl'] = $imageUrl;
            }

            $response = $this->api->post('cards', $payload, 'DUEL_LINKS');
            if($response['status'] == 200)
            {
                $this->flashSession->success('Card added to the database');
            }
            else
            {
                $this->flashSession->error('Error adding card. Error: ' . $response['result']);
            }
        }

    }

    public function editAction()
    {
        $this->view->title = 'Edit Card';
        if($this->request->isGet())
        {
            $this->view->id = $id = $this->request->getQuery('id');
            $card = null;
            $startTime = time();
            while((null == $card) || ((time() - $startTime) > 15))
            {
                $card = Card::getById($id);
            }

            $this->view->card = $card;
        }
        else if($this->request->isPost())
        {
            $id = $this->request->getPost('id');
            $name = $this->request->getPost('name');
            $cardType = $this->request->getPost('cardType');

            $payload = [
                'id' => $id,
                'name' => $name,
                'description' => $this->request->getPost('description'),
                'cardType' => $this->request->getPost('cardType'),
                'cardSubTypes' => $this->request->getPost('cardSubTypes'),
                'rarity' => $this->request->getPost('rarity'),
                'limitType' => $this->request->getPost('limitType')
            ];

            if(CardType::MONSTER == $cardType)
            {
                $payload['level'] = $this->request->getPost('level');
                $payload['type'] = $this->request->getPost('type');
                $payload['attribute'] = $this->request->getPost('attribute');
                $payload['attack'] = $this->request->getPost('attack');
                $payload['defense'] = $this->request->getPost('defense');
            }

            $imageUrl = $this->request->getPost('image', null, getenv('DUEL_LINKS_DEFAULT_IMAGE_URL'));

            if($this->request->hasFiles())
            {
                /** @var File[] $uploaded_files */
                $uploaded_files = $this->request->getUploadedFiles();
                $file = $uploaded_files[0];

                if('' != $file->getName())
                {
                    $formattedName = str_replace(['#', ' ', '-', ', '], '_', strtolower($name));
                    $version = $this->request->getPost('version', null, 1);

                    if($version > 1)
                    {
                        $formattedName = $formattedName . '_' . $version;
                    }

                    $filename = $formattedName . '.' . $file->getExtension();

                    $fields = [
                        'folderName' => 'cards'
                    ];

                    $fileObjects = [
                        [
                            'name' => $filename,
                            'path' => $file->getTempName()
                        ]
                    ];

                    $files = [];
                    foreach($fileObjects as $index => $fileObject)
                    {
                        $fileContent = file_get_contents($fileObject['path']);
                        $files[$fileObject['name']] = $fileContent;
                    }
                    $uploadResponse = $this->api->uploadFile($fields, $files);
                    if(array_key_exists('status', $uploadResponse) && (200 === $uploadResponse['status']))
                    {
                        $decodedResponse = json_decode($uploadResponse['result'], true);
                        $imageUrl = $decodedResponse['url'];
                    }
                }
            }

            if(!empty($imageUrl))
            {
                $payload['imageUrl'] = $imageUrl;
            }

            $response = $this->api->put('cards', $payload, 'DUEL_LINKS');
            if($response['status'] == 200)
            {
                $this->flashSession->success('Card updated successfully');
            }
            else
            {
                $this->flashSession->error('Error updating card. Error: ' . $response['result']);
            }
            $this->response->redirect('/cards/edit?id=' . $id);
        }
    }

    public function sourcesAction()
    {
        $this->view->title = 'Sources - Let\'s Duel';
        $this->view->sources = Source::getAll();
    }

    public function addSourceAction()
    {
        if($this->request->isGet())
        {
            $this->view->title = 'Add Source - Let\'s Duel';
        }
        else if($this->request->isPost())
        {
            $payload = [
                'name' => $this->request->getPost('name'),
                'type' => $this->request->getPost('type'),
                'cards' => $this->request->getPost('cards')
            ];

            $expiryDate = $this->request->getPost('expiryDate');
            $expiryTime = $this->request->getPost('expiryTime', null, '00:00:00');

            if(!empty($expiryDate) && !empty($expiryTime))
            {
                $payload['expiry'] = ($expiryDate . ' ' . $expiryTime);
            }

            $response = $this->api->post('cards/source', $payload, 'DUEL_LINKS');
            if($response['status'] == 200)
            {
                $this->flashSession->success('Source created successfully');
            }
            else
            {
                $this->flashSession->error('Error creating source. Error: ' . $response['result']);
            }
        }
    }

    public function editSourceAction()
    {
        if($this->request->isGet())
        {
            $this->view->title = 'Edit Source - Let\'s Duel';
            $this->view->id = $id = $this->request->getQuery('id');
            $source = null;
            $startTime = time();
            while((null == $source) || ((time() - $startTime) > 15))
            {
                $source = Source::getById($id);
            }

            $this->view->source = $source;
        }
        else if($this->request->isPost())
        {
            $id = $this->request->getPost('id');
            $payload = [
                'id' => $id,
                'name' => $this->request->getPost('name'),
                'type' => $this->request->getPost('type'),
                'cards' => $this->request->getPost('cards')
            ];

            $expiryDate = $this->request->getPost('expiryDate');
            $expiryTime = $this->request->getPost('expiryTime', null, '00:00:00');

            if(!empty($expiryDate) && !empty($expiryTime))
            {
                $payload['expiry'] = ($expiryDate . ' ' . $expiryTime);
            }

            $response = $this->api->put('cards/source', $payload, 'DUEL_LINKS');
            if($response['status'] == 200)
            {
                $this->flashSession->success('Source created successfully');
            }
            else
            {
                $this->flashSession->error('Error creating source. Error: ' . $response['result']);
            }
            $this->response->redirect('/cards/editSource?id=' . $id);
        }
    }

    public function obtainAction()
    {
        if($this->request->isPost())
        {
            $cardId = $this->request->getPost('cardId');
            $foilType = $this->request->getPost('foilType');

            $payload = [
                'cardId' => $cardId,
                'glossType' => $foilType
            ];

            $apiResponse = $this->api->post('cards/myCards', $payload, 'DUEL_LINKS');

            $response = [
                'success' => (200 === $apiResponse['status']),
                'response' => $apiResponse['result']
            ];

            $this->response->setContentType('application/json', 'UTF-8');
            $outputContent = json_encode($response, JSON_UNESCAPED_SLASHES);
            $this->response->setContent($outputContent);
            return $this->response;
        }
    }

    public function versionAction()
    {
        if($this->request->isPost())
        {
            $cardName = $this->request->getPost('cardName');
            $foilType = $this->request->getPost('foilType');

            $payload = [
                'name' => $cardName
            ];

            $imageUrl = getenv('DUEL_LINKS_DEFAULT_IMAGE_URL');

            if($this->request->hasFiles())
            {
                /** @var File[] $uploaded_files */
                $uploaded_files = $this->request->getUploadedFiles();
                $file = $uploaded_files[0];

                if('' != $file->getName())
                {
                    $formattedName = str_replace(['#', ' ', '-', ', '], '_', strtolower($cardName));
                    $formattedName = $formattedName . '_' . time();
                    $filename = $formattedName . '.' . $file->getExtension();

                    $fields = [
                        'folderName' => 'cards'
                    ];

                    $fileObjects = [
                        [
                            'name' => $filename,
                            'path' => $file->getTempName()
                        ]
                    ];

                    $files = [];
                    foreach($fileObjects as $index => $fileObject)
                    {
                        $fileContent = file_get_contents($fileObject['path']);
                        $files[$fileObject['name']] = $fileContent;
                    }
                    $uploadResponse = $this->api->uploadFile($fields, $files);
                    if(array_key_exists('status', $uploadResponse) && (200 === $uploadResponse['status']))
                    {
                        $decodedResponse = json_decode($uploadResponse['result'], true);
                        $imageUrl = $decodedResponse['url'];
                    }
                }
            }

            if(!empty($imageUrl))
            {
                $payload['imageUrl'] = $imageUrl;
            }

            $apiResponse = $this->api->post('cards/version', $payload, 'DUEL_LINKS');

            $response = [
                'success' => (200 === $apiResponse['status']),
                'response' => $apiResponse['result'],
                'cardHtml' => $this->view->getPartial('cards/card', [
                    'card' => json_decode($apiResponse['result']),
                    'isRarityRequired' => true
                ])
            ];

            $this->response->setContentType('application/json', 'UTF-8');
            $outputContent = json_encode($response, JSON_UNESCAPED_SLASHES);
            $this->response->setContent($outputContent);
            return $this->response;
        }
    }

    public function detailAction()
    {
        if($this->request->isGet())
        {
            $id = $this->request->getQuery('id');
            $this->view->card = $card = Card::getById($id);
            if(!empty($card))
            {
                $this->view->title = $card->name . ' - Let\'s Duel';
            }
        }
    }
}