<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigResponseCookies;

// Routes
$app->get('/refresh', function (Request $request, Response $response, array $args) {
    $this->logger->info("Slim-Skeleton '/refresh' route");

    $authenticated = false;
    $token = null;

    if($this->session->exists('refresh_token')) {
        $data = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->session->get('refresh_token'),
            'scope' => '',
            'client_id' => getenv('CLIENT_ID'),
            'client_secret' => getenv('CLIENT_SECRET')
        ];

        $token = $this->HttpClient->sendRequest(getenv('LOGIN_ENDPOINT'), $data);

        if(isset($token->access_token)) {
            $authenticated = true;
            // remember users session
            $this->session->set('refresh_token', $token->refresh_token);
            // remove dangerours token from frontend
            unset($token->refresh_token);
        }
    }

    $data = [
      'authenticated' => $authenticated,
      'token' => $token
    ];

    return $response->withJson($data);
});

$app->post('/login', function (Request $request, Response $response, array $args) {
    $this->logger->info("Slim-Skeleton '/login' route");

    $post = filter_input_array(INPUT_POST, $request->getParsedBody(), FILTER_SANITIZE_STRING);

    if(!isset($post['username']) || !isset($post['password'])) {
      return $response->withJson([
        'authenticated' => 'false',
        'token' => [
          'error' => 'validation_failure',
          'message' => 'Missing username or password input'
        ]
      ]);
    }

    $data = [
        'username' => $post['username'],
        'password' => $post['password'],
        'grant_type' => 'password',
        'scope' => '',
        'client_id' => getenv('CLIENT_ID'),
        'client_secret' => getenv('CLIENT_SECRET')
    ];

    $token = $this->HttpClient->sendRequest(getenv('LOGIN_ENDPOINT'), $data);

    $authenticated = false;

    if(isset($token->access_token)) {
        $authenticated = true;
        // remember users session
        $this->session->set('refresh_token', $token->refresh_token);
        // remove dangerours token from frontend
        unset($token->refresh_token);
    }

    $data = [
      'authenticated' => $authenticated,
      'token' => $token
    ];

    return $response->withJson($data);
});
