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

    /**
     * @var \App\Service\EditRequest
     */
    private $editRequestService;

    /**
     * @var \App\Service\Owner
     */
    private $ownerService;

    /**
     * @param \App\Service\School $schoolService
     * @param \App\Service\EditRequest $editRequestService
     * @param \App\Service\Owner $ownerService
     */
    public function __construct(\App\Service\School $schoolService, \App\Service\EditRequest $editRequestService, \App\Service\Owner $ownerService)
    {
        $this->schoolService = $schoolService;
        $this->editRequestService = $editRequestService;
        $this->ownerService = $ownerService;
    }

    public function editAction(Request $request, $schoolId, $editToken)
    {
        $document = $request->getContent();


        // check the edit token
        $editRequest = $this->editRequestService->getEditRequest($editToken, $schoolId, $email);
        $email = $editRequest['email'];

        $level = $this->ownerService->getEditLevel($schoolId, $email);

        $this->schoolService->edit($schoolId, $email, $document, $level);


        return $app['success'];
    }
}
