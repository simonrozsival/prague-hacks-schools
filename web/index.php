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
ini_set('display_errors', 'on');

$app = new Silex\Application();
if ($_SERVER['SERVER_NAME'] == 'localhost') {
    ini_set('display_errors', 'on');
    error_reporting(E_ALL);
    require __DIR__ . '/../resources/config/dev.php';
} else {
    require __DIR__ . '/../resources/config/prod.php';
}

require __DIR__ . '/../src/app.php';

//$app['http_cache']->run();
$app->run();

exit;


$hostSpecificConfig = ROOT_PATH . '/app/config.' . $_SERVER['HTTP_HOST'] . '.php';
if (file_exists($hostSpecificConfig)) {
    require_once $hostSpecificConfig;
} else {
    require_once ROOT_PATH . '/app/config.php';
}

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
