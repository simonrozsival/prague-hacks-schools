<?php
require_once __DIR__.'/../vendor/autoload.php';
use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application();
$app['debug'] = true;


$app->get('/', function () {
    return "Hello World!";
});

$app->run();
