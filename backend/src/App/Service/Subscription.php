<?php
/**
 * Created by PhpStorm.
 * User: tomasfejfar
 * Date: 25. 8. 2015
 * Time: 9:31
 */

namespace App\Service;

class Subscription
{
    /**
     * @var \App\Model\Subscription
     */
    private $model;

    /**
     * Subscription constructor.
     */
    public function __construct(\App\Model\Subscription $model)
    {
        $this->model = $model;
    }

    public function subscribe($schoolId, $email)
    {
        return $this->model->subscribe($schoolId, $email);
    }
}
