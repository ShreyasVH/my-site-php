<?php
/**
 * Author: shreyas.hande
 * Date: 10/19/18
 * Time: 6:43 PM
 */

namespace app\controllers;

use Phalcon\Mvc\View;

class IndexController extends BaseController
{
    public function indexAction()
    {
        $this->view->title = 'My New Website';
    }

    public function knowYourDayAction()
    {
        if($this->request->isPost()) {
            $date = $this->request->getPost('date');
            $month = $this->request->getPost('month');
            $year = $this->request->getPost('year');
            if(($date < 1) || ($date > 31) || ($year < 1) || ($year > 3000) || !isset($month)) {
                $this->response->redirect("index/knowYourDay?response=fail");
            }
            $count = 0;

            if(self::_isLeapYear($year)) {
                $o = 1;
            } else {
                $o = 0;
            }
            $leapYearCount = 0;
            $nonLeapYearCount = 0;
            $monthlyOffset = array(3,$o,3,2,3,2,3,3,2,3,2,3);
            $days = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');

            for ($i = 1; $i < $year; $i++) {
                if(self::_isLeapYear($i)) {
                    $count = $count + 2;

                } else {
                    $count++;

                }
            }
            $monthOffset = 0;
            for ($i = 0; $i < $month; $i++) {
                $monthOffset += $monthlyOffset[$i];
            }

            $finalOffset = (($count + $monthOffset + $date)) % 7;     // 1 subtracted because the $days array index starts with 0
            $day = $days[$finalOffset];
            $this->view->day = $day;
        }
        $this->view->title = 'Know Your Day';
    }

    private function _isLeapYear($year)
    {
        if((($year % 4 == 0) && ($year % 100 != 0)) || ($year % 400 ==0)) {
            return true;
        } else {
            return false;
        }
    }

    public function phpinfoAction()
    {
        $this->view->title = 'phpinfo()';
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
    }

    public function getLoadedExtensionsAction()
    {
        $this->view->title = 'Loaded Extensions';
        $extensions = get_loaded_extensions();
        sort($extensions, (SORT_STRING | SORT_FLAG_CASE));
        $this->view->extensions = $extensions;
    }

    public function changeModeAction()
    {
        $newMode = $this->request->getQuery('newMode');
        $this->session->set('mode', $newMode);
        $output = [
            'success' => ($newMode === $this->session->get('mode'))
        ];
        $this->response->setContentType('application/json', 'UTF-8');
        $output_content = json_encode($output, JSON_UNESCAPED_SLASHES);
        $this->response->setContent($output_content);
        return $this->response;
    }
}