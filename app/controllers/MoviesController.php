<?php
/**
 * Author: shreyas.hande
 * Date: 10/19/18
 * Time: 9:06 PM
 */

namespace app\controllers;


use app\constants\Constants;
use app\enums\Mode;
use app\enums\Status;
use app\helpers\AssetHelper;
use app\models\Artist;
use app\models\Format;
use app\models\Language;
use app\models\Movie;
use app\utils\CommonUtils;

class MoviesController extends BaseController
{
    public function indexAction()
    {
        $this->dispatcher->forward(array('action' => 'dashboard'));
    }

    public function dashboardAction()
    {
        $this->view->title = 'Movies Dashboard - Movie Mania';
        $this->view->movieDbList = Movie::dashboard();
    }

    public function browseMoviesAction()
    {
        $this->view->title = 'Browse Movies - Movie Mania';
        if($this->request->isGet())
        {
            $filters = array_filter($this->request->getQuery(), function($value) {
                return is_array($value) && !empty(array_filter($value, function($v) {
                        return ("" != $v);
                    }));
            });
            $order = $this->request->getQuery('order', null, Constants::DEFAULT_ORDER_MOVIES);
        }
        elseif($this->request->isPost())
        {
            $filters = array_filter($this->request->getPost(), function($value) {
                return is_array($value) && !empty(array_filter($value, function($v) {
                        return ("" != $v);
                    }));
            });

            $order = $this->request->getPost('order', null, Constants::DEFAULT_ORDER_MOVIES);
        }

        $order = urldecode($order);
        $sortMap = [];
        $sortMapParts = explode(',', $order);

        foreach($sortMapParts as $index => $sortMapPart)
        {
            $keyValueParts = explode(" ", $sortMapPart);
            $sortMap[$keyValueParts[0]] = $keyValueParts[1];
        }

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

        $response = Movie::getMoviesWithFilter($payload);
        $movieList = $response->movies;
        $movie_count = $response->totalCount;
        $offset = $response->offset;

        if(Mode::NORMAL == CommonUtils::getCurrentMode() && !($this->request->isAjax()))
        {
            $payload = [
                'count' => 5,
                'offset' => 0,
                'sortMap' => [
                    'id' => 'DESC'
                ]
            ];
            $carousel_movies = Movie::getMoviesWithFilter($payload)->movies;
            $carousel_src = array();
            foreach($carousel_movies as $index => $movie)
            {
                $carousel_src[$index] = AssetHelper::getImage($movie->id, Constants::CONTEXT_MOVIE);
            }

            $this->view->carousel_src = $carousel_src;
        }

        $this->view->totalCount = $movie_count;
        $this->view->order = $order;
        $this->view->movieList = $movieList;
        $this->view->filters = $filters;
        $filterValues = [
            'language' => json_decode(json_encode(Language::getAllLanguages()), true),
            'format' => json_decode(json_encode(Format::getAllFormats()), true),
            'year' => [
                'min' => 1930,
                'max' => date('Y'),
                'step' => 1
            ],
            'size' => [
                'min' => 0,
                'max' => 5 * 1024 * 1024 * 1024,
                'step' => 500 * 1024 * 1024
            ],
            'basename' => [],
            'subtitles' => [
                [
                    'id' => 1,
                    'name' => 'Yes'
                ],
                [
                    'id' => 0,
                    'name' => 'No'
                ]
            ],
            'seen_in_theatre' => [
                [
                    'id' => 1,
                    'name' => 'Yes'
                ],
                [
                    'id' => 0,
                    'name' => 'No'
                ]
            ],
            'quality' => [
                [
                    'id' => 'good',
                    'name' => 'Good'
                ],
                [
                    'id' => 'normal',
                    'name' => 'Normal'
                ]
            ],
            'actors' => [
                'entityPrimaryType' => 'artist',
                'entitySecondaryType' => 'actor'
            ],
            'directors' => [
                'entityPrimaryType' => 'artist',
                'entitySecondaryType' => 'director'
            ]
        ];

        if(!$this->request->isAjax())
        {
            if(isset($filters['actors']) && !empty($filters['actors']))
            {
                $actors = [];
                foreach($filters['actors'] as $aIndex => $actorId)
                {
                    $actors[] = Artist::getArtistById($actorId);
                }
                $filterValues['actors'] = array_merge($filterValues['actors'], ['values' => $actors]);
            }

            if(isset($filters['directors']) && !empty($filters['directors']))
            {
                $directors = [];
                foreach($filters['directors'] as $dIndex => $directorId)
                {
                    $directors[] = Artist::getArtistById($directorId);
                }
                $filterValues['directors'] = array_merge($filterValues['directors'], ['values' => $directors]);
            }
        }

        $this->view->filterValues = $filterValues;
        $this->view->sortMap = $sortMap;
        $this->view->offset = $offset;

        if($this->request->isAjax())
        {
            $output = [
                'filters' => $filters,
                'sortMap' => $sortMap,
                'count' => $movie_count,
                'offset' => $offset,
                'view' => $this->view->getPartial('movies/browseMovies')
            ];

            $this->response->setContentType('application/json', 'UTF-8');
            $output_content = json_encode($output, JSON_UNESCAPED_SLASHES);
            $this->response->setContent($output_content);
            return $this->response;
        }
    }

    public function addMovieAction()
    {
        if($this->request->isget())
        {
            $this->view->title = 'Add Movie - Movie Mania';
        }
        elseif($this->request->isPost())
        {
            $payload = array(
                'name' => $this->request->getPost('movie-name'),
                'languageId' => $this->request->getPost('movie-language'),
                'size' => str_replace(",","",$this->request->getPost('movie-size')),
                'formatId' => $this->request->getPost('movie-format'),
                'quality' => $this->request->getPost('movie-quality'),
                'year' => ((int) $this->request->getPost('movie-year')),
                'subtitles' => filter_var($this->request->getPost('movie-subtitles'), FILTER_VALIDATE_BOOLEAN),
                'seenInTheatre' => filter_var($this->request->getPost('movie-seen'), FILTER_VALIDATE_BOOLEAN),
                'basename' => explode('.txt', $this->request->getPost('movie-basename'))[0],
                'actorIds' => $this->request->getPost('actors'),
                'directorIds' => $this->request->getPost('directors')
            );

            $response = $this->api->post('movies/movie', $payload);

            $redirectUrl = '/movies/addMovie';
            if($response['status'] == 200)
            {
                $movie = json_decode($response['result']);
                // $this->logger->info($movie->name . ' added. Id : ' . $movie->id);
                $this->flashSession->success('Movie added to the database');
                $redirectUrl = '/movies/editMovie?id=' . $movie->id . '&source=addMovie';
            }
            else
            {
                // $this->logger->critical('Error adding movie: ' . $this->request->getPost('movie-name') . ' . Error: ' . $response['result']);
                $this->flashSession->error('Error adding movie. Error: ' . $response['result']);
            }

            $language = Language::getLanguageById($this->request->getPost('movie-language'));
            $this->response->redirect($redirectUrl);
        }
    }

    public function editMovieAction()
    {
        if($this->request->isGet())
        {
            $this->view->title = 'Edit Movie - Movie Mania';
            $this->view->id = $id = $this->request->getQuery('id');
            $movie = null;
            $startTime = time();
            while((null == $movie) || ((time() - $startTime) > 15))
            {
                $movie = Movie::getMovieById($id);
            }

            $this->view->movie = $movie;
            $this->view->source = $this->request->getQuery('source');
        }
        else if($this->request->isPost())
        {
            $id = $this->request->getPost('id');
            $source = $this->request->getPost('source');

            if('addMovie' != $source)
            {
                $payload = array(
                    'id' => (int) $id,
                    'name' => $this->request->getPost('movie-name'),
                    'languageId' => $this->request->getPost('movie-language'),
                    'size' => str_replace(",","",$this->request->getPost('movie-size')),
                    'formatId' => $this->request->getPost('movie-format'),
                    'quality' => $this->request->getPost('movie-quality'),
                    'year' => ((int) $this->request->getPost('movie-year')),
                    'subtitles' => filter_var($this->request->getPost('movie-subtitles'), FILTER_VALIDATE_BOOLEAN),
                    'seenInTheatre' => filter_var($this->request->getPost('movie-seen'), FILTER_VALIDATE_BOOLEAN),
                    'basename' => explode('.txt', $this->request->getPost('movie-basename'))[0],
                    'actorIds' => $this->request->getPost('actors'),
                    'directorIds' => $this->request->getPost('directors')
                );

                $response = $this->api->put('movies/movie', $payload);

                if($response['status'] == 200)
                {
                    $movie = json_decode($response['result']);
                    // $this->logger->info('Edited movie. id : ' . $movie->id);
                    $this->flashSession->success('Edited the movie successfully');
                }
                else
                {
                    // $this->logger->critical('Error editing movie. Movie Id : ' . $id . ' . Error: ' . $response['result']);
                    $this->flashSession->error('Error editing movie. Error: ' . $response['result']);
                }
            }

            if($this->request->hasFiles())
            {
                $uploaded_files = $this->request->getUploadedFiles();
                $file = $uploaded_files[0];

                if('' != $file->getName())
                {
                    $filename = $id . '.' . $file->getExtension();
                    $isSuccess = $file->moveTo(Constants::PUBLIC_FOLDER . Constants::IMAGES_FOLDER . Constants::IMAGES_FOLDER_MOVIE . $filename);
                    $isSuccess = move_uploaded_file($file->getTempName(), Constants::PUBLIC_FOLDER . Constants::IMAGES_FOLDER . Constants::IMAGES_FOLDER_MOVIE . $filename);
                    // $this->logger->info($file->getTempName());
                    if(!$isSuccess)
                    {
                        // $this->logger->critical('Error saving image for movie. Image : ' . $filename);
                    }
                }
            }

            $redirectUrl = (('addMovie' == $source) ? ('/movies/addMovie') : ('/movies/editMovie?id=' . $id));

            $this->response->redirect($redirectUrl);
        }
    }

    public function removeMovieAction()
    {
        if($this->request->isGet())
        {
            $id = $this->request->getQuery('id');
            $output['success'] = true;
            $payload = [
                'id' => (int) $id,
                'status' => Status::DELETED
            ];
            $response = $this->api->put('movies/updateStatus', $payload);
            if($response['status'] != 200)
            {
                $output['success'] = false;
            }
            $this->response->setContentType('application/json', 'UTF-8');
            $ouput_content = json_encode($output, JSON_UNESCAPED_SLASHES);
            $this->response->setContent($ouput_content);
            return $this->response;
        }
    }

    public function movieDetailAction()
    {
        if($this->request->isGet())
        {
            $id = $this->request->getQuery('id');
            $movie = Movie::getMovieById($id);
            $this->view->title = $movie->name . ' - Movie Mania';
            $this->view->movie = $movie;
        }
    }

    public function actorListAction()
    {
        $this->view->title = 'Actors Database - Movie Mania';
        $actor_list = Artist::getAllActors();
        $actor_movies_alpha = [];
        foreach($actor_list as $actor)
        {
            $actor_movies_alpha[$actor->name[0]][] = $actor;
        }

        $this->view->actor_movies_alpha = $actor_movies_alpha;
    }

    public function yearListAction()
    {
        $this->view->title = 'Years Database - Movie Mania';
        $year_list = Movie::getAllYears();
        $decades = [];
        foreach($year_list as $year_snippet)
        {
            $decades[($year_snippet->year - ($year_snippet->year % 10)) . 's'][] = $year_snippet;
        }
        $this->view->decades = $decades;
    }

    public function directorListAction()
    {
        $this->view->title = 'Directors Database - Movie Mania';
        $director_list = Artist::getAllDirectors();

        $director_list_alpha = [];
        foreach($director_list as $director)
        {
            $director_list_alpha[$director->name[0]][] = $director;
        }
        $this->view->director_list_alpha = $director_list_alpha;
    }

    public function actorCombinationsAction()
    {
        $this->view->title = 'Actor Combinations - Movie Mania';
        if($this->request->isAjax())
        {
            $actor_ids = $this->request->getPost('actors');
            $this->view->movieList = Movie::getActorCombinationMovies($actor_ids);
        }
    }

    public function deletedMoviesAction()
    {
        if($this->request->isGet())
        {
            $this->view->title = 'Deleted Movies - Movie Mania';
            $payload = [
                'filters' => [
                    'status' => [
                        'DELETED'
                    ]
                ],
                'includeDeleted' => true,
                'sortMap' => [
                    'year' => 'DESC',
                    'id' => 'DESC'
                ]
            ];
            $this->view->movieList = Movie::getMoviesWithFilter($payload)->movies;
        }
    }

    public function restoreMovieAction()
    {
        if($this->request->isPost())
        {
            $id = $this->request->getPost('id');
            $output['success'] = true;

            $payload = [
                'id' => (int) $id,
                'status' => Status::ENABLED
            ];
            $response = $this->api->put('movies/updateStatus', $payload);
            if($response['status'] != 200)
            {
                $output['success'] = false;
                // $this->logger->critical('Error restoring movie: ' . $id . '. Error: ' . $response['result']);
            }

            $this->response->setContentType('application/json', 'UTF-8');
            $output_content = json_encode($output, JSON_UNESCAPED_SLASHES);
            $this->response->setContent($output_content);
            return $this->response;
        }
    }
}