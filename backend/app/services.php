<?php
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
    $config = [
        'base_uri' => $baseUri,
        'exceptions' => false,
    ];
    $client = new \GuzzleHttp\Client($config);
    return $client;
});
