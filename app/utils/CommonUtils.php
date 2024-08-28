<?php
/**
 * Author: shreyas.hande
 * Date: 10/19/18
 * Time: 9:19 PM
 */

namespace app\utils;

use app\enums\cards\ViewMode;
use Phalcon\Di\Di;
//use Phalcon\Session\Adapter\Files as Session;
use Phalcon\Di\Injectable;

class CommonUtils extends Injectable
{
    const SCREEN_SIZE_XS = 'xs';

    const SCREEN_SIZE_SM = 'sm';

    const SCREEN_SIZE_MD = 'md';

    const SCREEN_SIZE_LG = 'lg';

    const INDIVIDUAL_GUTTER_WIDTH = 30;

    private static $_max_container_widths = [
        self::SCREEN_SIZE_XS => 320,
        self::SCREEN_SIZE_SM => 750,
        self::SCREEN_SIZE_MD => 970,
        self::SCREEN_SIZE_LG => 1170
    ];

    public static function getBestCardDimensions($screen_size, $no_of_cards, $container_percent = 100)
    {
        $max_container_width = self::getMaxContainerWidth($screen_size);
        $max_available_width = (int) floor($container_percent * $max_container_width / 100);
        $total_gutter_width = $no_of_cards * self::INDIVIDUAL_GUTTER_WIDTH;

        $remaining_width = $max_available_width - $total_gutter_width;

        $max_width_per_card = intdiv($remaining_width, $no_of_cards);

        $card_width = ($max_width_per_card - ($max_width_per_card % 3));
        $card_height = ($card_width * 4 / 3);
        return [
            'width' => $card_width,
            'height' => $card_height
        ];
    }

    public static function getMaxContainerWidth($screen_size)
    {
        return self::$_max_container_widths[$screen_size];
    }

    public static function getCurrentMode()
    {
        $di = Di::getDefault();
//        $mode = getenv('DEFAULT_MODE');
        $mode = 'NORMAL';

//        /** @var Session $session */
//        $session = $di->get('session');
//        if($session->has('mode'))
//        {
//            $mode = $session->get('mode');
//        }
        return $mode;
    }

    public static function isDebugMode()
    {
        return filter_var(getenv('IS_DEBUG_MODE'), FILTER_VALIDATE_BOOLEAN);
    }

    public static function formatStringWithEscapeChars($string)
    {
        return htmlspecialchars(str_replace('"', '\"', str_replace("'", "\'", $string)));
    }

    public static function getProtocol()
    {
        return ((self::isSecureRequest()) ? 'https://' : 'http://');
    }

    public static function isSecureRequest()
    {
        return (
            (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1))
            ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
        );
    }

    public static function isLocalEnv()
    {
        return ('127.0.0.1' === $_SERVER['SERVER_ADDR']);
    }

    public static function getViewMode()
    {
        $viewMode = ViewMode::DEFAULT;

        $di = Di::getDefault();

        /** @var Session $session */
        $session = $di->get('session');
        if($session->has('viewMode'))
        {
            $viewMode = $session->get('viewMode');
        }

        return $viewMode;
    }
}