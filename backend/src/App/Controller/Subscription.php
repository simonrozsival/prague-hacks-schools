<?php
/**
 * Created by PhpStorm.
 * User: tomasfejfar
 * Date: 25. 8. 2015
 * Time: 9:27
 */

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Subscription
{
    /**
     * @var \App\Service\Subscription
     */
    private $subscriptionService;

    /**
     * Subscription constructor.
     */
    public function __construct(\App\Service\Subscription $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function subscribeAction(Request $request)
    {
        // check params
        $schoolId = $request->get('school_id');
        $email = $request->get('email');
        if (!$schoolId || !$email) {
            return new JsonResponse(['success' => false, 'msg' => 'SchoolId or Email not set']);
        }

        $token = $this->subscriptionService->subscribe($schoolId, $email);
        return new JsonResponse([
            'success' => true,
            'cancel_token' => $token,
        ]);
    }
}
