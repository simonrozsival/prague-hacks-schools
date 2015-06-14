<?php
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;

$app['elastic'] = $app->share(function ($app) {
    return new \Elastica\Client([
        'host' => $app['elastic.host'],
        'port' => $app['elastic.port'],
    ]);
});

$app['search'] = $app->share(function ($app) {
    $client = $app['elastic'];
    return new \Elastica\Search($client);
});

$app['schools'] = $app->share(function ($app) {
    /** @var \Elastica\Search $search */
    $search = $app['search'];
    $search->addIndex('schools')
        ->addType('school');
    return $search;
});

$app['guzzle'] = $app->share(function ($app) {
    $baseUri = $app['elastic.host'] . ':' . $app['elastic.port'];
    $stack = new HandlerStack();
    $stack->setHandler(new CurlHandler());
    $config = [
        'base_uri' => $baseUri,
        'exceptions' => false,
        'handler' => $stack,
    ];
    $client = new \GuzzleHttp\Client($config);
    return $client;
});

$app['db'] = $app->share(function ($app) {
    $db = Zend_Db::factory('pdo_mysql', [
        'username' => $app['db.username'],
        'password' => $app['db.password'],
        'host' => $app['db.host'],
        'dbname' => $app['db.dbname'],
    ]);
    $db->query('SET NAMES utf8');
    return $db;
});
