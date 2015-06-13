<?php
use GuzzleHttp\Exception\ClientException;
use Hacks\Subscription;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

ini_set('display_errors', 'on');
error_reporting(E_ALL);
require_once __DIR__ . '/../vendor/autoload.php';
define('ROOT', realpath(__DIR__ . '/../'));

$app = new Silex\Application();
$app['debug'] = true;

include ROOT . '/app/services.php';
include ROOT . '/app/config.php';

$app->get('/api/', function () use ($app) {
    return $app->json(['msg' => 'Hello, world!']);
});

$app->get('/api/subscribe', function (Request $request) use ($app) {
    // check params
    $schoolId = $request->get('school_id');
    $email = $request->get('email');
    if (!$schoolId || !$email) {
        return new JsonResponse(['success' => false, 'msg' => 'SchoolId or Email not set']);
    }
    $model = new Subscription($app);
    $response = $model->testSubscription($schoolId, $email);

    if ($response->getStatusCode() !== 200) {
        return $model->insert($schoolId, $email);
    } else {
        $body = json_decode($response->getBody());
        return $app->json([
            'success' => true,
            'cancel_token' => $body->_source->cancel_token,
        ]);
    }
});

$app->get('/api/unsubscribe', function (Request $request) use ($app) {
    // check params
    $schoolId = $request->get('school_id');
    $email = $request->get('email');
    $cancelationToken = $request->get('cancel_token');
    if (!$schoolId || !$email || !$cancelationToken) {
        return new JsonResponse(['success' => false, 'msg' => 'SchoolId, Email or Cancelation token not set']);
    }
    $model = new Subscription($app);
    return $model->removeSubscription($schoolId, $email, $cancelationToken);
});

$app->get('/backend/', function () use ($app) {
    return $app['twig']->render('index/index.twig', []);
});

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => ROOT . '/app/views',
));

$app->run();
