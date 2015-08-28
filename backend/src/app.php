<?php

use Silex\Application;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//handling CORS preflight request
/** @var Application $app  */
$app->before(function (Request $request) {
    if ($request->getMethod() === "OPTIONS") {
        $response = new Response();
        $response->headers->set("Access-Control-Allow-Origin", "*");
        $response->headers->set("Access-Control-Allow-Methods", "GET,POST,PUT,DELETE,OPTIONS");
        $response->headers->set("Access-Control-Allow-Headers", "Content-Type");
        $response->setStatusCode(200);
        return $response->send();
    }
}, Application::EARLY_EVENT);

//handling CORS respons with right headers
$app->after(function (Request $request, Response $response) {
    $response->headers->set("Access-Control-Allow-Origin", "*");
    $response->headers->set("Access-Control-Allow-Methods", "GET,POST,PUT,DELETE,OPTIONS");
});

$app->register(new ServiceControllerServiceProvider());

/*$app->register(new MonologServiceProvider(), array(
    "monolog.logfile" => ROOT_PATH . "/storage/logs/" . date('Y-m-d') . ".log",
    "monolog.level" => $app["log.level"],
    "monolog.name" => "application",
));*/

//$app->register(new HttpCacheServiceProvider(), array("http_cache.cache_dir" => ROOT_PATH . "/storage/cache",));

//load services
$servicesLoader = new App\ServicesLoader($app);
$servicesLoader->bindServicesIntoContainer();

//load routes
$routesLoader = new App\RoutesLoader($app);
$routesLoader->bindRoutesToControllers();

$app->register(new TwigServiceProvider(), array(
    'twig.path' => ROOT_PATH . '/app/views',
));

$app->error(function (Exception $e, $code) use ($app) {
    $err = [
        'success' => false,
        'msg' => 'Server error',
    ];
    if ($app['debug']) {
        $err ['msg'] = $e->getMessage();
        $err['code'] = $e->getCode();
        $err['stack'] = $e->getTraceAsString();
        $err['previous'] = isset($err['previous']) ? $err['previous'] : '';
    }
    $app->json($err, $code);
});
