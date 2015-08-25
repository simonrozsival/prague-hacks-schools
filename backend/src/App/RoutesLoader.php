<?php
/**
 * Created by PhpStorm.
 * User: tomas
 * Date: 9. 8. 2015
 * Time: 14:45
 */

namespace App;

use Silex\Application;

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
        $api->post('/subscribe', "controller.subscription:subscribeAction");
        $this->app->mount($this->app["api.endpoint"] . '/' . $this->app["api.version"], $api);
    }

    private function instantiateControllers()
    {

        $this->app['controller.subscription'] = $this->app->share(function () {
            return new Controller\Subscription($this->app['service.subscription']);
        });
    }
}
