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

class School
{
    /**
     * @var \App\Service\School
     */
    private $schoolService;

    public function __construct(\App\Service\School $schoolService)
    {
        $this->schoolService = $schoolService;
    }

    public function requestEditAction(Request $request, $schoolId)
    {
        $email = $request->get('email');
        $this->schoolService->requestEdit($schoolId, $email);
        return new JsonResponse(['success' => true]);
    }
}
