<?php

/**
 *
 */

class Linepay_curl
{
    protected $headers;

    public function __construct($argument)
    {
        $this->headers = $this->getRequestHeader($argument['channelId'], $argument['channelSecret']);
    }

    /**
     * Private function: send request by php_curl
     * @param  [String] $method      Request method: 'POST', 'GET'
     * @param  [String] $relativeUrl Target API url path
     * @param  [Array]  $params      Request parameters
     * @return [Array]               Result by the request
     */
    public function request($method = 'GET', $relativeUrl = null, $params = [])
    {
        if (is_null($relativeUrl)) {
            throw new \Exception('API endpoint is required.');
        }
        $ch = curl_init();
        if ($method === 'GET') {
            $relativeUrl .= '?' . http_build_query($params);
        } elseif ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }
        curl_setopt($ch, CURLOPT_URL, $relativeUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        // curl_setopt($ch, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1');
        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch));
        }
        // $response = curl_exec($ch);
        $response = json_decode(curl_exec($ch), true, 512, JSON_BIGINT_AS_STRING);
        curl_close($ch);
        return $response;
    }

    /**
     * Static function: Generate header content by channelId & channelSecret
     * @param  [String] $channelId
     * @param  [String] $channelSecret
     * @return [Array]                 Header content for CURLOPT_HTTPHEADER
     */
    protected function getRequestHeader($channelId = null, $channelSecret = null)
    {
        if (is_null($channelId) || is_null($channelSecret)) {
            throw new \Exception('Header info are required.');
        }

        return [
            'Content-Type:application/json; charset=UTF-8',
            'X-LINE-ChannelId:' . $channelId,
            'X-LINE-ChannelSecret:' . $channelSecret,
        ];
    }
}
