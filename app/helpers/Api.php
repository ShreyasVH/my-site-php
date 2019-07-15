<?php
/**
 * Author: shreyas.hande
 * Date: 10/19/18
 * Time: 9:18 PM
 */

namespace app\helpers;


use app\utils\CommonUtils;

class Api extends BaseHelper
{
    private function _getEndpoint()
    {
        return getenv('ENDPOINT_' . CommonUtils::getCurrentMode());
    }

    /**
     * @param string $url
     * @param array $postdata
     * @return array
     */
    public function post($url, $postdata)
    {
        $url = $this->_getEndpoint() . $url;
        $postdata = json_encode($postdata, JSON_UNESCAPED_SLASHES);
        if(CommonUtils::isDebugMode())
        {
            // $this->logger->debug('Calling POST API. URL : ' . $url . ' Payload : ' . $postdata);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 30000);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 30000);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        if(CommonUtils::isDebugMode())
        {
            // $this->logger->debug('Finished executing POST API. URL : ' . $url);
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if($status != 200)
        {
            // $this->logger->critical('ERROR!! - POST API URL : ' . $url . ' Payload : ' . $postdata . ' Status : ' . $status . ' Response : ' . $result);
        }

        curl_close($ch);

        return array(
            'result' => $result,
            'status' => $status
        );
    }

    public function get($url)
    {
        $url = $this->_getEndpoint() . $url;
        if(CommonUtils::isDebugMode())
        {
            // $this->logger->debug('Calling GET API. URL : ' . $url);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 30000);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 30000);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

        $result = curl_exec($ch);

        if(CommonUtils::isDebugMode())
        {
            // $this->logger->debug('Finished executing GET API. URL : ' . $url);
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if($status != 200)
        {
            // $this->logger->critical('ERROR!! - GET API URL : ' . $url . ' Status : ' . $status . ' Response : ' . $result);
        }

        curl_close($ch);

        return array(
            'result' => $result,
            'status' => $status
        );
    }

    public function delete($url)
    {
        $url = $this->_getEndpoint() . $url;
        if(CommonUtils::isDebugMode())
        {
            // $this->logger->debug('Calling DELETE API. URL : ' . $url);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 30000);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 30000);

        $result = curl_exec($ch);

        if(CommonUtils::isDebugMode())
        {
            // $this->logger->debug('Finished executing DELETE API. URL : ' . $url);
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if($status != 200)
        {
            // $this->logger->critical('ERROR!! - DELETE API URL : ' . $url . ' Status : ' . $status . ' Response : ' . $result);
        }

        curl_close($ch);

        return array(
            'result' => $result,
            'status' => $status
        );
    }

    public function put($url, $putdata)
    {
        $url = $this->_getEndpoint() . $url;
        $putdata = json_encode($putdata, JSON_UNESCAPED_SLASHES);
        if(CommonUtils::isDebugMode())
        {
            // $this->logger->debug('Calling PUT API. URL : ' . $url . ' Payload : ' . $putdata);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 30000);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 30000);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $putdata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        if(CommonUtils::isDebugMode())
        {
            // $this->logger->debug('Finished executing PUT API. URL : ' . $url);
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if($status != 200)
        {
            // $this->logger->critical('ERROR!! - PUT API URL : ' . $url . ' Payload : ' . $putdata . ' Status : ' . $status . ' Response : ' . $result);
        }

        curl_close($ch);

        return array(
            'result' => $result,
            'status' => $status
        );
    }

    public function uploadFile($fields, $files)
    {
        $url = getenv('UPLOAD_API_ENDPOINT') . 'upload/file';

        $boundary = uniqid();
        $delimiter = '-------------' . $boundary;

        $payload = $this->_buildPayloadForUpload($boundary, $fields, $files);
        $curlHandle = curl_init();
        curl_setopt_array($curlHandle, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: multipart/form-data; boundary=" . $delimiter,
                "Content-Length: " . strlen($payload)
            )
        ));
        $response = curl_exec($curlHandle);
        $status = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        curl_close($curlHandle);
        return [
            'status' => $status,
            'result' => $response
        ];
    }

    private function _buildPayloadForUpload($boundary, $fields, $files)
    {
        $data = '';
        $eol = "\r\n";

        $delimiter = '-------------' . $boundary;

        foreach ($fields as $name => $content)
        {
            $data .= "--" . $delimiter . $eol . 'Content-Disposition: form-data; name="' . $name . "\"" . $eol . $eol . $content . $eol;
        }


        foreach ($files as $name => $content)
        {
            $data .= "--" . $delimiter . $eol . 'Content-Disposition: form-data; name="' . $name . '"; filename="' . $name . '"' . $eol . 'Content-Transfer-Encoding: binary' . $eol;
            $data .= $eol;
            $data .= $content . $eol;
        }
        $data .= "--" . $delimiter . "--".$eol;

        return $data;
    }
}