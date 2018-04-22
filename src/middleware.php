<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

$app->add(new \Slim\Middleware\Session([
    'name' => 'proxy_session',
    'autorefresh' => true,
    'lifetime' => '5 hours',
    'httponly' => true
]));
