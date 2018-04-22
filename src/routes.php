<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/refresh', function (Request $request, Response $response, array $args) {
    $this->logger->info("Slim-Skeleton '/refresh' route");

    $data = [
      'authenticated' => false,
      'token' => [
          'token_type' => 'Bearer',
          'expires_in' => 0,
          'access_token' => null
      ]
    ];

    return $response->withJson($data);
});
