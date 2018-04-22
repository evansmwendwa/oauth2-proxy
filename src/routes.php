<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigResponseCookies;
// Routes

$app->get('/refresh', function (Request $request, Response $response, array $args) {
    $this->logger->info("Slim-Skeleton '/refresh' route");

    if (!$this->session->exists('csrf_token')) {
      $token = bin2hex(random_bytes(60));
      $this->session->set('csrf_token', $token);
    }

    $token = $this->session->get('csrf_token');

    // store csrf token in cookie
    $response = FigResponseCookies::set($response, SetCookie::create('XSRF-TOKEN')
        ->withValue($token)
        ->withExpires(strtotime(getenv('SESSION_LIFETIME')))
    );

    $data = [
      'authenticated' => false,
      'token' => [
          'token_type' => 'Bearer',
          'expires_in' => 0,
          'access_token' => null,
      ]
    ];

    return $response->withJson($data);
});
