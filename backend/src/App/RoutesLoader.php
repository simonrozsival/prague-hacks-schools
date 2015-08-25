<?php
/**
 * Created by PhpStorm.
 * User: tomas
 * Date: 9. 8. 2015
 * Time: 14:45
 */

namespace App;

use App\Controller\Subscription;
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

    }

    private function instantiateControllers()
    {

        $this->app['controller.subscription'] = $this->app->share(function () {
            return new Subscription($this->app['subscription.service']);
        });
    }
}
