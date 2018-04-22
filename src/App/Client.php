<?php
namespace App;

class Client
{
    protected $verify_ssl;

    public function __construct() {
        $this->verify_ssl = (strtolower(getenv('CURL_SSL_VERIFICATION')) === 'false')? false : true;
    }

    public function sendRequest($url, $data, $method = 'POST') {
        $request = [
            'http_errors' => false,
            'verify' => $this->verify_ssl,
            'form_params' => $data
        ];

        $client = new \GuzzleHttp\Client();

        try {
            $res = $client->request($method, $url, $request);
        } catch(\Exception $e) {
            return [
                'error' => 'guzzle_error',
                'message' => $e->getMessage(),
                'code' => $res->getStatusCode()
            ];
        }

        $response = json_decode((string)$res->getBody());

        return $response;
    }
}
