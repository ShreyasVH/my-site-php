<?php


namespace app\utils;


use app\helpers\Api;
use Phalcon\Di;
use Phalcon\Mvc\User\Component;

class Logger extends Component
{
    private static function log($content, $type)
    {
        $di = Di::getDefault();

        /* @var Api $api */
        $api = $di->get('api');
        $payload = [
            'type' => $type,
            'source' => getenv('LOGGER_SOURCE'),
            'content' => $content
        ];

        $result = $api->post('logs', $payload, 'LOGGER');
    }

    public static function success($content)
    {
        self::log($content, 'SUCCESS');
    }

    public static function error($content)
    {
        self::log($content, 'ERROR');
    }

    public static function debug($content)
    {
        self::log($content, 'DEBUG');
    }
}