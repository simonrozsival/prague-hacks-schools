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

    public function unsubscribe($schoolId, $email, $cancelationToken)
    {
        if (!$token = $model->getSubscriptionToken($schoolId, $email)) {
            throw new \App\Exception\Subscription('Not subscribed.', 400);
        }
        if ($token != $cancelationToken) {
            throw new \App\Exception\Subscription('Invalid cancelation token', 400);
        }
        return $this->model->unsubscribe($cancelationToken);
    }
}
