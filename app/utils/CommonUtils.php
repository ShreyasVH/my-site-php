<?php
/**
 * Author: shreyas.hande
 * Date: 10/19/18
 * Time: 9:19 PM
 */

namespace app\utils;

use Phalcon\Di;
use Phalcon\Session\Adapter\Files as Session;
use Phalcon\Mvc\User\Component;

class CommonUtils extends Component
{
    public static function getCurrentMode()
    {
        $di = Di::getDefault();
        $mode = getenv('DEFAULT_MODE');

        /** @var Session $session */
        $session = $di->get('session');
        if($session->has('mode'))
        {
            $mode = $session->get('mode');
        }
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
}