<?php
/**
 * Author: shreyas.hande
 * Date: 10/19/18
 * Time: 6:43 PM
 */

namespace app\controllers;


class IndexController extends BaseController
{
    public function indexAction()
    {
        $this->view->title = 'My New Website';
    }
}