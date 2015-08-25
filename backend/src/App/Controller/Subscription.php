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
    public function subscribeAction(Request $request)
    {
        // check params
        $schoolId = $request->get('school_id');
        $email = $request->get('email');
        if (!$schoolId || !$email) {
            return new JsonResponse(['success' => false, 'msg' => 'SchoolId or Email not set']);
        }

        $model = new Subscription($app);
        $token = $model->subscribe($schoolId, $email);
        return $app->json([
            'success' => true,
            'cancel_token' => $token,
        ]);
    }
}

