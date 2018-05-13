<?php
// Application middleware
use Symfony\Component\Dotenv\Dotenv;
use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigResponseCookies;

// e.g: $app->add(new \Slim\Csrf\Guard);

// middleware for validating csrf tokens from header
$app->add(function ($request, $response, $next) use ($app) {
    $session = new \SlimSession\Helper;

    if (!$session->exists('csrf_token')) {
        $csrf_token = bin2hex(random_bytes(60));
        $session->set('csrf_token', $csrf_token);
    }

    $csrf_token = $session->get('csrf_token');

    // store csrf token in cookie
    $response = FigResponseCookies::set(
        $response,
        SetCookie::create('XSRF-TOKEN')
          ->withValue($csrf_token)
          ->withExpires(strtotime(getenv('SESSION_LIFETIME')))
    );

    $clientCsrfToken = $request->getHeaderLine('X-XSRF-TOKEN');

    if(empty($clientCsrfToken) || $csrf_token !== $clientCsrfToken) {
        $response = $response->withJson([
          'authenticated' => false,
          'token' => [
            'error' => 'csrf_validation_failure',
            'message' => 'Missing or invalid csrf token'
          ]
        ]);

        $method = $request->getMethod();

        if(strtolower($method) === 'options') {
            return $next($request, $response);
        } else {
            return $response;
        }
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

/**
 * enable CORS in dev mode
 * WARNING for security this proxy is supposed to be served
 * from same origin as your SPA app without CORS enabled
 * make sure CORS is not enabled in production
 **/
$app->add(function ($request, $response, $next) {
    $debug = (getenv('DEBUG') === 'true');

    if($debug) {
        $corsResponse = $response
            ->withHeader('Access-Control-Allow-Origin', getenv('ORIGIN'))
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization, X-XSRF-TOKEN, withCredentials')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');

        return $next($request, $corsResponse);
    }

    return $next($request, $response);
});
