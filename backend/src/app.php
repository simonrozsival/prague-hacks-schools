<?php
date_default_timezone_set('Europe/London');
define("ROOT_PATH", __DIR__ . "/..");

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => ROOT_PATH . '/app/views',
));
