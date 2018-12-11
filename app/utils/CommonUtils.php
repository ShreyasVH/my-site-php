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
        return false;
    }

    public static function formatStringWithEscapeChars($string)
    {
        return str_replace('"', '\"', str_replace("'", "\'", $string));
    }
}