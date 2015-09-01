<?php
/**
 * Created by PhpStorm.
 * User: tomas
 * Date: 9. 8. 2015
 * Time: 14:45
 */

namespace App;

use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;

class RoutesLoader
{

    /**
     * RoutesLoader constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->instantiateControllers();
    }

    public function bindRoutesToControllers()
    {
        $api = $this->app["controllers_factory"];

        $api->get('/', function () {
            return new JsonResponse(['msg' => 'Hello, world!']);
        });

        $api->post('/subscribe', 'controller.subscription:subscribeAction');
        $api->post('/unsubscribe', 'controller.subscription:unsubscribeAction');

        $api->post('/request-edit', 'controller.editRequest:createAction');
        $api->post('/claim-ownership', 'controller.ownership:claimAction');

        $api->post('/api/school/{schoolId}/edit/{editToken}', 'controller.school:editAction');

        $this->app->mount($this->app["api.endpoint"] . '/' . $this->app["api.version"], $api);
    }

    private function instantiateControllers()
    {
        $this->app['controller.subscription'] = $this->app->share(function () {
            return new Controller\Subscription($this->app['service.subscription']);
        });

        $this->app['controller.school'] = $this->app->share(function () {
            return new Controller\School($this->app['service.school']);
        });

        $this->app['controller.editRequest'] = $this->app->share(function () {
            return new Controller\EditRequest($this->app['service.editRequest']);
        });
    }
}
