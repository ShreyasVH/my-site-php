<?php
/**
 * Author: shreyas.hande
 * Date: 10/20/18
 * Time: 11:34 AM
 */

namespace app\controllers;


use app\models\Artist;
use app\models\Card;
use app\models\Movie;
use app\models\Source;
use Phalcon\Mvc\View;

class SuggestionsController extends BaseController
{
    public function movieSuggestionsAction()
    {
        if($this->request->isget())
        {
            $keyword = urlencode($this->request->getQuery('keyword'));

            $this->view->related_movies = Movie::getMoviesByKeyword($keyword);
            $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
        }
    }

    public function artistSuggestionsAction()
    {
        $keyword = $this->request->getQuery('keyword');

        $this->view->artists = Artist::getArtistsByKeyword($keyword);
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
    }

    public function cardSuggestionsAction()
    {
        $keyword = urlencode($this->request->getQuery('keyword'));

        $this->view->cards = Card::getByKeyword($keyword);
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
    }

    public function sourceSuggestionsAction()
    {
        $keyword = urlencode($this->request->getQuery('keyword'));

        $this->view->sources = Source::getByKeyword($keyword);
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
    }
}