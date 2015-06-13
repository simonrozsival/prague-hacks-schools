<?php
require_once __DIR__ . '/../vendor/autoload.php';
define('ROOT', realpath(__DIR__ . '/../'));

$app = new Silex\Application();
$app['debug'] = true;

$app->get('/api', function () use ($app) {
    return $app->json(['msg' => 'Hello, world!']);
});

$app->get('/backend/', function () use ($app) {
    return $app['twig']->render('index/index.twig', []);
});

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => ROOT . '/app/views',
));

$app->run();
