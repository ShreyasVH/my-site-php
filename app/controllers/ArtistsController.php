<?php
/**
 * Author: shreyas.hande
 * Date: 10/20/18
 * Time: 12:13 PM
 */

namespace app\controllers;


use app\models\Artist;
use Phalcon\Http\Request\File;

class ArtistsController extends BaseController
{
    public function addArtistAction()
    {
        if($this->request->isAjax())
        {
            $payload = array(
                "name" => ucwords($this->request->getPost('name')),
                "gender" => $this->request->getPost('gender'),
                'imageUrl' => getenv('ARTISTS_DEFAULT_IMAGE_URL_' . $this->request->getPost('gender'))
            );

            $response = $this->api->post('artists/artist', $payload);

            if($response['status'] == 200)
            {
                $artist = json_decode($response['result']);
                // $this->logger->info('Added New Artist. Name : ' . $artist->name);
                $output['success'] = true;
                $output['artist'] = $artist;
                $output['context'] = $this->request->getPost('context');
            }
            else
            {
                // $this->logger->critical('Error adding artist: ' . ucwords($this->request->getPost('name')) . ' . Error: ' . $response['result']);
                $output['success'] = false;
                $output['error'] = $response['result'];
            }
            $this->response->setContentType('application/json', 'UTF-8');
            $output_content = json_encode($output, JSON_UNESCAPED_SLASHES);
            $this->response->setContent($output_content);
            return $this->response;
        }
    }

    public function editAction()
    {
        if($this->request->isGet())
        {
            $this->view->title = 'Edit Artist - Movie Mania';
            $this->view->id = $id = $this->request->getQuery('id');
            $artist = null;
            $startTime = time();
            while((null == $artist) || ((time() - $startTime) > 15))
            {
                $artist = Artist::getArtistById($id);
            }

            $this->view->artist = $artist;
        }
        else if($this->request->isPost())
        {
            $id = $this->request->getPost('id');

            $payload = [
                'id' => $id,
                'name' => $this->request->getPost('artist-name'),
                'gender' => $this->request->getPost('artist-gender')
            ];

            $imageUrl = '';
            if($this->request->hasFiles())
            {
                /** @var File[] $uploaded_files */
                $uploaded_files = $this->request->getUploadedFiles();
                $file = $uploaded_files[0];

                if('' != $file->getName())
                {
                    $filename = $id;

                    $imageUrl = $this->api->uploadImage($file->getTempName(), 'artists', $filename, $file->getExtension());
                }
            }
            if(!empty($imageUrl))
            {
                $payload['imageUrl'] = $imageUrl;
            }

            $response = $this->api->put('artists/artist', $payload);
            if($response['status'] == 200)
            {
                $this->flashSession->success('Edited the artist successfully');
            }
            else
            {
                $this->flashSession->error('Error editing artist. Error: ' . $response['result']);
            }
        }
    }
}