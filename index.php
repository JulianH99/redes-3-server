<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . './vendor/autoload.php';

$app = AppFactory::create();


$twig = Twig::create('./templates', ['cache' => FALSE]);

$app->add(TwigMiddleware::create($app, $twig));

$app->get('/', function (Request $request, Response $response) {
    $view = Twig::fromRequest($request);

    return $view->render($response, 'home.html.twig');
});

$app->post('/login', function (Request $request, Response $response) {
});

$app->get('/home', function (Request $request, Response $response) {
});

$app->post('/send-mail', function (Request $request, Response $response) {
});

$app->run();
