<?php
/**
 * Author: shreyas.hande
 * Date: 10/20/18
 * Time: 11:38 AM
 */

namespace app\controllers;


use app\constants\Constants;
use app\helpers\AssetHelper;
use app\models\Artist;
use app\models\Language;
use app\models\Song;

class SongsController extends BaseController
{
    public function indexAction()
    {

    }

    public function dashboardAction()
    {
        $this->view->title = 'Songs Dashboard - Audio Box';
        $this->view->songs_by_languages = Song::dashboard();
    }

    public function browseSongsAction()
    {
        if($this->request->isGet())
        {
            $language_name = ucfirst($this->request->getQuery('language', null, Constants::DEFAULT_SONG_LANGUAGE));
            $language = Language::getLanguageByName($language_name);
            $this->view->title = $language_name . ' Songs - Audio Box';
            $order = $this->request->getQuery('order', null, Constants::DEFAULT_ORDER_SONGS);
            $sortMap = [];
            $sortMapParts = explode(',', $order);
            foreach($sortMapParts as $index => $sortMapPart)
            {
                $keyValueParts = explode(" ", $sortMapPart);
                $sortMap[$keyValueParts[0]] = $keyValueParts[1];
            }
            $page = $this->request->getQuery('page', null, Constants::DEFAULT_PAGE);

            $skip = ($page - 1) * Constants::DEFAULT_SONGS_PER_PAGE;

            $songs_count = Song::getSongCount($language->id);

            $totalPages = ceil($songs_count / Constants::DEFAULT_SONGS_PER_PAGE);

            $payload = [
                'filters' => [
                    'language' => [
                        $language->id
                    ]
                ],
                'sortMap' => $sortMap,
                'count' => Constants::DEFAULT_SONGS_PER_PAGE,
                'offset' => $skip
            ];
            $songsList = Song::getSongsFromFilter($payload);

            $payload = [
                'filters' => [
                    'language' => [
                        $language->id
                    ]
                ],
                'sortMap' => [
                    'year' => 'DESC',
                    'id' => 'DESC'
                ],
                'count' => 5,
                'offset' => 0
            ];
            $carousel_songs = Song::getSongsFromFilter($payload);

            $carousel_src = [];

            foreach($carousel_songs as $song)
            {
                $carousel_src[] = '/images/movies/' . $song->movie->id . '.jpg';
            }

            $this->view->totalPages = $totalPages;
            $this->view->song_count = $songs_count;
            $this->view->page = $page;
            $this->view->order = $order;
            $this->view->songsList = $songsList;
            $this->view->carousel_src = $carousel_src;
            $this->view->language = $language;
        }
    }

    public function getSongInfoAction()
    {
        if($this->request->isGet())
        {
            $id = $this->request->getQuery('id');
            $output['success'] = true;
            $song = Song::getSongById($id);

            if($song == false)
            {
                $output['success'] = false;
            }
            else
            {
                $output['title'] = $song->name;
                $output['image_src'] = AssetHelper::getImage($song->movie->id, Constants::CONTEXT_MOVIE);
                $output['audio_src'] = AssetHelper::getSongUrl($song);
                $output['singers'] = array_column($song->singers, 'name');
                $output['composers'] = array_column($song->composers, 'name');
                $output['lyricists'] = array_column($song->lyricists, 'name');
            }
            $this->response->setContentType('application/json', 'UTF-8');
            $ouput_content = json_encode($output, JSON_UNESCAPED_SLASHES);
            $this->response->setContent($ouput_content);
            return $this->response;
        }
    }

    public function singerListAction()
    {
        $this->view->title = 'Singers Database - Audio Box';
        $singers_list = Artist::getAllSingers();
        $singer_list_alpha = [];

        foreach($singers_list as $singer)
        {
            $singer_list_alpha[$singer->name[0]][] = $singer;
        }

        $this->view->singer_list_alpha = $singer_list_alpha;
    }

    public function singerSongsAction()
    {
        if($this->request->isget())
        {
            $id = $this->request->getQuery('id');
            $singer = Artist::getArtistById($id);
            $this->view->singer = $singer;
            $this->view->title = 'Songs by ' . $singer->name . ' - Audio Box';
            $payload = [
                'filters' => [
                    'singers' => [
                        $id
                    ]
                ],
                'sortMap' => [
                    'year' => 'DESC',
                    'id' => 'DESC'
                ]
            ];
            $this->view->songList = Song::getSongsFromFilter($payload);
        }
    }

    public function composerListAction()
    {
        $this->view->title = 'Composers Database - Audio Box';
        $composer_list = Artist::getAllComposers();
        $composer_list_alpha = [];

        foreach($composer_list as $composer)
        {
            $composer_list_alpha[$composer->name[0]][] = $composer;
        }

        $this->view->composer_list_alpha = $composer_list_alpha;
    }

    public function composerSongsAction()
    {
        if($this->request->isget())
        {
            $id = $this->request->getQuery('id');
            $composer = Artist::getArtistById($id);;
            $this->view->composer = $composer;
            $this->view->title = 'Songs by ' . $composer->name. ' - Audio Box';
            $payload = [
                'filters' => [
                    'composers' => [
                        $id
                    ]
                ],
                'sortMap' => [
                    'year' => 'DESC',
                    'id' => 'DESC'
                ]
            ];
            $this->view->songList = Song::getSongsFromFilter($payload);
        }
    }

    public function lyricistListAction()
    {
        $this->view->title = 'Lyricists Database - Audio Box';
        $lyricist_list = Artist::getAllLyricists();
        $lyricist_list_alpha = [];

        foreach($lyricist_list as $lyricist)
        {
            $lyricist_list_alpha[$lyricist->name[0]][] = $lyricist;
        }

        $this->view->lyricist_list_alpha = $lyricist_list_alpha;
    }

    public function lyricistSongsAction()
    {
        if($this->request->isget())
        {
            $id = $this->request->getQuery('id');
            $lyricist = Artist::getArtistById($id);
            $this->view->lyricist = $lyricist;
            $this->view->title = 'Songs by ' . $lyricist->name . ' - Audio Box';
            $payload = [
                'filters' => [
                    'lyricists' => [
                        $id
                    ]
                ],
                'sortMap' => [
                    'year' => 'DESC',
                    'id' => 'DESC'
                ]
            ];
            $this->view->songList = Song::getSongsFromFilter($payload);
        }
    }

    public function yearListAction()
    {
        $this->view->title = 'Years Database - Audio Box';
        $year_list = Song::getAllYears();
        $decades = [];
        foreach($year_list as $year_snippet)
        {
            $decades[($year_snippet->year - ($year_snippet->year % 10)) . 's'][] = $year_snippet;
        }
        $this->view->decades = $decades;
    }

    public function yearSongsAction()
    {
        if($this->request->isget())
        {
            $year = $this->request->getQuery('year');
            $this->view->title = 'Songs of ' . $year . ' - Audio Box';
            $payload = [
                'filters' => [
                    'year' => [
                        $year
                    ]
                ],
                'sortMap' => [
                    'id' => 'DESC'
                ]
            ];
            $this->view->songList = Song::getSongsFromFilter($payload);
        }
    }

    public function songDetailAction()
    {
        if($this->request->isGet())
        {
            $id = $this->request->getQuery('id');
            $song = Song::getSongById($id);
            $this->view->title = $song->name . ' - Audio Box';
            $this->view->song = $song;
        }
    }

    public function addSongAction()
    {
        if($this->request->isGet())
        {
            $this->view->title = 'Add Song - Audio Box';
        }
        elseif($this->request->isPost())
        {
            $payload = [
                'name' => trim($this->request->getPost('name')),
                'movie_id' => $this->request->getPost('movies')[0],
                'size' => str_replace(',', '', $this->request->getPost('size')),
                'singer_ids' => $this->request->getPost('singers'),
                'composer_ids' => $this->request->getPost('composers'),
                'lyricist_ids' => $this->request->getPost('lyricists')
            ];

            $response = $this->api->post('songs/song', $payload);

            if($response['status'] == 200)
            {
                $song = json_decode($response['result']);
                $this->logger->info($song->name . ' added. Id : ' . $song->id);
                $this->flashSession->success('Song added to the database');
            }
            else
            {
                $this->logger->critical('Error adding song: ' . $this->request->getPost('name') . ' . Error: ' . $response['result']);
                $this->flashSession->error('Error adding song. Error: ' . $response['result']);
            }
        }
    }

    public function editSongAction()
    {
        if($this->request->isGet())
        {
            $this->view->title = 'Edit Song - Audio Box';
            $id = $this->request->getQuery('id');
            $this->view->song = Song::getSongById($id);
        }
        elseif($this->request->isPost())
        {
            $id = $this->request->getPost('id');
            $payload = [
                'id' => $id,
                'name' => trim($this->request->getPost('name')),
                'movie_id' => $this->request->getPost('movies')[0],
                'size' => str_replace(',', '', $this->request->getPost('size')),
                'singer_ids' => $this->request->getPost('singers'),
                'composer_ids' => $this->request->getPost('composers'),
                'lyricist_ids' => $this->request->getPost('lyricists')
            ];

            $response = $this->api->put('songs/song', $payload);

            if($response['status'] == 200)
            {
                $song = json_decode($response['result']);
                $this->logger->info('Edited song. id : ' . $song->id);
                $this->flashSession->success('Edited the song successfully');
            }
            else
            {
                $this->logger->critical('Error editing song. Song Id : ' . $id . ' . Error: ' . $response['result']);
                $this->flashSession->error('Error editing song. Error: ' . $response['result']);
            }
            $this->response->redirect('/songs/editSong?id=' . $id);
        }
    }

    public function testAction()
    {

    }
}