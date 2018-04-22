<?php
// Application middleware
use Symfony\Component\Dotenv\Dotenv;
use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigResponseCookies;

// e.g: $app->add(new \Slim\Csrf\Guard);

// middleware for validating csrf tokens from header
$app->add(function ($request, $response, $next) {
    $session = new \SlimSession\Helper;

    if (!$session->exists('csrf_token')) {
      $csrf_token = bin2hex(random_bytes(60));
      $session->set('csrf_token', $csrf_token);
    }

    $csrf_token = $session->get('csrf_token');

    // store csrf token in cookie
    $response = FigResponseCookies::set($response, SetCookie::create('XSRF-TOKEN')
        ->withValue($csrf_token)
        ->withExpires(strtotime(getenv('SESSION_LIFETIME')))
    );

    $clientCsrfToken = $request->getHeaderLine('X-XSRF-TOKEN');

    if(empty($clientCsrfToken) || $csrf_token !== $clientCsrfToken) {
      return $response->withJson([
        'authenticated' => 'false',
        'token' => [
          'error' => 'csrf_validation_failure',
          'message' => 'Missing or invalid csrf token'
        ]
      ]);
    }

    return $next($request, $response);
});

// must be executed before csrf token middleware
$app->add(new \Slim\Middleware\Session([
    'name' => 'proxy_session',
    'autorefresh' => true,
    'lifetime' => getenv('SESSION_LIFETIME'),
    'httponly' => true
]));


// needs to be executed first (last in this file) in order to source env variables
$app->add(function ($request, $response, $next) {
    $dotenv = new Dotenv();
    $dotenv->load(__DIR__.'/../.env');
	  return $next($request, $response);
});
