<?php

namespace FcmClient;

use \GuzzleHttp\Client;
use \FcmClient\Credentials;

/**
 * @author J.M <https://github.com/httd1>
 * @link https://github.com/httd1/fcmclient
 */
class FcmClient
{

    /**
     * @var \GuzzleHttp\Client
     */
    private $guzzleClient;

    /**
     * @var \FcmClient\Credentials
     */
    private $credentials;

    /**
     * @param array|string $fcmJson
     */
    public function __construct($fcmJson)
    {

        $this->credentials = new Credentials($fcmJson);

        $this->guzzleClient = new Client([
            'http_errors' => false,
            'base_uri' => 'https://fcm.googleapis.com',
        ]);
    }

    /**
     * Send a message!
     *
     * @param array $data
     * 
     * @link https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages
     *
     * @return array
     */
    public function send($data)
    {

        $tokenOAuth2 = $this->credentials->getTokenOAuth2();

        $uri = sprintf('/v1/projects/%s/messages:send', $this->credentials->getProjectId());

        $request = $this->guzzleClient->post($uri, [
            'json' => $data,
            'headers' => ['Authorization' => 'Bearer ' . $tokenOAuth2['access_token']]
        ]);

        return json_decode($request->getBody(), true);

    }

}