<?php
use GuzzleHttp\Exception\ClientException;
use App\EditRequest;
use App\Subscription;
use App\Owner;
use App\Version;
use App\School;
use App\SchoolDesign;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nette\Utils\Json;

date_default_timezone_set('Europe/London');
define('ROOT_PATH', realpath(__DIR__ . '/../'));
require_once __DIR__ . '/../vendor/autoload.php';


$app = new Silex\Application();
if ($_SERVER['SERVER_NAME'] == 'localhost') {
    ini_set('display_errors', 'on');
    error_reporting(E_ALL);
    require __DIR__ . '/../resources/config/dev.php';
} else {
    require __DIR__ . '/../resources/config/prod.php';
}

require __DIR__ . '/../src/app.php';

$app['http_cache']->run();

exit;


$hostSpecificConfig = ROOT_PATH . '/app/config.' . $_SERVER['HTTP_HOST'] . '.php';
if (file_exists($hostSpecificConfig)) {
    require_once $hostSpecificConfig;
} else {
    require_once ROOT_PATH . '/app/config.php';
}

$app->get('/api/', function () use ($app) {
    return $app->json(['msg' => 'Hello, world!']);
});

/**
 * Subscribe
 */
$app->post('/api/subscribe', function (Request $request) use ($app) {


});

/**
 * Unsubscribe
 */
$app->post('/api/unsubscribe', function (Request $request) use ($app) {

    // check params
    $schoolId = $request->get('school_id');
    $email = $request->get('email');
    $cancelationToken = $request->get('cancel_token');

    if (!$schoolId || !$email || !$cancelationToken) {
        return new JsonResponse(['success' => false, 'msg' => 'SchoolId, Email or Cancelation token not set']);
    }

    $model = new Subscription($app);
    if (!$token = $model->getSubscriptionToken($schoolId, $email)) {
        return $app->json(['success' => false, 'msg' => 'Not subscribed.'], 400);
    }
    if ($token == $cancelationToken) {
        $model->unsubscribe($token);
        return $app['success'];
    }
    return $app->json(['success' => false, 'msg' => 'Invalid cancel token.'], 400);
});

/**
 * Request school edit
 */
$app->post('api/request-edit', function (Request $request) use ($app) {
    $model = new EditRequest($app);
    $schoolId = $request->get('school_id');
    $email = $request->get('email');
    $model->handleEditRequest($schoolId, $email);

    return $app['success'];
});

/**
 * Edit school
 */
$app->post('/api/school/{school_id}/edit/{edit_token}', function (Request $request, $school_id, $edit_token) use ($app) {
    $document = $request->getContent();

    // check the edit token
    $editRequestModel = new EditRequest($app);
    $editRequest = $editRequestModel->getByToken($edit_token);
    if (!$editRequest) {
        return $app->json([
            'success' => false,
            'msg' => "Invalid edit token.",
        ], 401);
    }

    $email = $editRequest['email'];

    if (!$editRequestModel->allowed($school_id, $email, $edit_token)) {
        return $app->json([
            'success' => false,
            'msg' => "Invalid edit token.",
        ], 401);
    }

    // get the user level
    $ownerModel = new Owner($app, $editRequestModel);
    $level = $ownerModel->getEditLevel($school_id, $email);

    // retrieve the actual school document from elastic
    $schoolModel = new School($app);
    $school = $schoolModel->get($school_id);

    // check the level privileges - compare old and new versions, find all categories
    // incompatibilities and check if all of those are less or equal to user's level
    $schoolDesignModel = new SchoolDesign($app);
    if (!$schoolDesignModel->isUpdateValid($school, $document, $level)) {
        return $app->json([
            'success' => false,
            'msg' => "Cannot edit data of higher level.",
            'school' => $school,
        ], 400);
    }

    // add it to version log
    (new Version($app))
        ->addVersion($school_id, $email, $school);

    // store the new document to elastic
    $schoolModel->update($school_id, $document);
    return $app['success'];
});

$app->post('/api/claim-ownership/', function (Application $app, Request $request) {
    $schoolId = $request->get('school_id');
    $email = $request->get('email');
    $message = $request->get('message');
    if (!$schoolId || !$email || !$message) {
        return new JsonResponse(['success' => false, 'msg' => 'SchoolId, Email or Message not set'], 400);
    }
    $owner = new Owner($app);
    $owner->claimOwnership($schoolId, $email, $message);
    return $app['success'];
});

$app->get('/backend/', function () use ($app) {
    return $app['twig']->render('index/index.twig', []);
});

// does not work :(
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

$app->run();
