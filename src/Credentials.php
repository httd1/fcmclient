<?php

namespace FcmClient;

use \Firebase\JWT\JWT;

/**
 * Credentials for FCM
 * 
 * @author J.M <https://github.com/httd1>
 * @link https://github.com/httd1/fcmclient
 */
class Credentials
{

    /**
     * @var array
     */
    private $tokenOAuth2 = [];

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $projectId;

    /**
     * @var string
     */
    private $privateKeyId;

    /**
     * @var string
     */
    private $privateKey;

    /**
     * @var string
     */
    private $clientEmail;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $authUri;

    /**
     * @var string
     */
    private $tokenUri;

    /**
     * @var string
     */
    private $authProviderX509CertUrl;

    /**
     * @var string
     */
    private $clientX509CertUrl;

    public function __construct($json)
    {
        // abre json ou dados em array
        if (is_array($json)) {
            $this->fromArray($json);
        } else {
            $this->fromJson($json);
        }
    }

    /**
     * Create Credentials from JSON
     * 
     * @param string $json
     * 
     * @return void
     */
    private function fromJson($json)
    {

        if (!file_exists($json)) {
            throw new \Exception("JSON file not found!");
        }

        $jsonData = file_get_contents($json);
        $json = json_decode($jsonData, true);

        if (!isset($json['private_key']) || !isset($json['project_id']) || !isset($json['client_email']) || !isset($json['token_uri'])) {
            throw new \Exception("Invalid JSON structure");
        }

        $this->type = $json['type'];
        $this->projectId = $json['project_id'];
        $this->privateKeyId = $json['private_key_id'];
        $this->privateKey = $json['private_key'];
        $this->clientEmail = $json['client_email'];
        $this->clientId = $json['client_id'];
        $this->authUri = $json['auth_uri'];
        $this->tokenUri = $json['token_uri'];
        $this->authProviderX509CertUrl = $json['auth_provider_x509_cert_url'];
        $this->clientX509CertUrl = $json['client_x509_cert_url'];
    }

    /**
     * Create credentials from array
     * 
     * @param array $data
     * 
     * @return void
     */
    private function fromArray($data)
    {

        if (!isset($json['private_key']) || !isset($json['project_id']) || !isset($json['client_email']) || !isset($json['token_uri'])) {
            throw new \Exception("Invalid array structure");
        }

        $this->type = $data['type'];
        $this->projectId = $data['project_id'];
        $this->privateKeyId = $data['private_key_id'];
        $this->privateKey = $data['private_key'];
        $this->clientEmail = $data['client_email'];
        $this->clientId = $data['client_id'];
        $this->authUri = $data['auth_uri'];
        $this->tokenUri = $data['token_uri'];
        $this->authProviderX509CertUrl = $data['auth_provider_x509_cert_url'];
        $this->clientX509CertUrl = $data['client_x509_cert_url'];
    }

    /**
     * @return string|null
     */
    private function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @return string|null
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * @return string|null
     */
    public function getClientEmail()
    {
        return $this->clientEmail;
    }

    /**
     * @return string|null
     */
    public function getTokenUri()
    {
        return $this->tokenUri;
    }

    /**
     * Return OAuth2 token to access FCM
     *
     * @return array
     * 
     * @throws \Exception
     */
    public function getTokenOAuth2()
    {

        if (($this->tokenOAuth2['expire_cache'] ?? 0) > $this->getUniversalTime()) {
            return $this->tokenOAuth2;
        }

        $jwt = $this->makeJwt();

        // desativa exceptions
        $guzzle = new \GuzzleHttp\Client(['http_errors' => false]);
        $response = $guzzle->post($this->getTokenUri(), [
            'form_params' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]
        ]);

        $jsonResponse = json_decode($response->getBody(), true);

        if (!isset($jsonResponse['access_token'])) {
            throw new \Exception("Failed to obtain access token");
        }

        $this->tokenOAuth2 = ['expire_cache' => $this->plusUniversalTime($jsonResponse['expires_in']), ...$jsonResponse];

        return $jsonResponse;

    }

    private function makeJwt()
    {
        // usa tempo universal
        $iat = $this->getUniversalTime();
        $exp = $this->plusUniversalTime(3600);

        // payload do jwt
        $payload = [
            "iss" => $this->getClientEmail(),
            "scope" => "https://www.googleapis.com/auth/firebase.messaging",
            "aud" => $this->getTokenUri(),
            "iat" => $iat,
            "exp" => $exp
        ];

        // exception se as chaves do json for vazias/null
        $jwt = JWT::encode($payload, $this->getPrivateKey(), 'RS256');

        return $jwt;
    }

    private function getUniversalTime()
    {
        return gmdate('U');
    }

    private function plusUniversalTime($seconds)
    {
        return $this->getUniversalTime() + $seconds;
    }

}