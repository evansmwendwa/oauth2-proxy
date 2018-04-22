<?php
// Application middleware
use Symfony\Component\Dotenv\Dotenv;

// e.g: $app->add(new \Slim\Csrf\Guard);
$app->add(function ($request, $response, $next) {
    $dotenv = new Dotenv();
    $dotenv->load(__DIR__.'/../.env');
	  return $next($request, $response);
});

$app->add(new \Slim\Middleware\Session([
    'name' => 'proxy_session',
    'autorefresh' => true,
    'lifetime' => getenv('SESSION_LIFETIME'),
    'httponly' => true
]));
