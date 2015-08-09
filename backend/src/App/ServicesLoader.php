<?php
namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Zend_Db;

class ServicesLoader
{
    /**
     * @var Application
     */
    private $app;

    /**
     * ServicesLoader constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function bindServicesIntoContainer()
    {
        $this->app['elastic'] = $this -
            app->share(function ($app) {
        return new \Elastica\Client([
            'host' => $app['elastic.host'],
            'port' => $app['elastic.port'],
        ]);
    });

        $this->app['search'] = $this->
        app->share(function ($app) {
            $client = $app['elastic'];
            return new \Elastica\Search($client);
        });

        $this->app['schools'] = $this->
        app->share(function ($app) {
            /** @var \Elastica\Search $search */
            $search = $app['search'];
            $search->addIndex('schools')
                ->addType('school');
            return $search;
        });

        $this->app['guzzle'] = $this->
        app->share(function ($app) {
            $baseUri = $app['elastic.host'] . ':' . $app['elastic.port'];
            $stack = new HandlerStack();
            $stack->setHandler(new CurlHandler());
            $config = [
                'base_uri' => $baseUri,
                'exceptions' => false,
                'handler' => $stack,
            ];
            $client = new Client($config);
            return $client;
        });

        $this->app['db'] = $this->
        app->share(function ($app) {
            $db = Zend_Db::factory('pdo_mysql', [
                'username' => $app['db.username'],
                'password' => $app['db.password'],
                'host' => $app['db.host'],
                'dbname' => $app['db.dbname'],
            ]);
            $db->query('SET NAMES utf8');
            return $db;
        });

        $this->app['success'] = $this->
        app->share(function () {
            return new JsonResponse(['success' => true], 200);
        });
    }
}
