<?php
/**
 * Author: shreyas.hande
 * Date: 10/20/18
 * Time: 12:13 PM
 */

namespace app\controllers;


class ArtistsController extends BaseController
{
    public function addArtistAction()
    {
        if($this->request->isAjax())
        {
            $payload = array(
                "name" => ucwords($this->request->getPost('name')),
                "gender" => $this->request->getPost('gender')
            );

            $response = $this->api->post('artists/artist', $payload);

            if($response['status'] == 200)
            {
                $artist = json_decode($response['result']);
                $this->logger->info('Added New Artist. Name : ' . $artist->name);
                $output['success'] = true;
                $output['artist'] = $artist;
                $output['context'] = $this->request->getPost('context');
            }
            else
            {
                $this->logger->critical('Error adding artist: ' . ucwords($this->request->getPost('name')) . ' . Error: ' . $response['result']);
                $output['success'] = false;
                $output['error'] = $response['result'];
            }
            $this->response->setContentType('application/json', 'UTF-8');
            $output_content = json_encode($output, JSON_UNESCAPED_SLASHES);
            $this->response->setContent($output_content);
            return $this->response;
        }
    }
}