<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class EditRequest
{
    /**
     * @var \App\Service\EditRequest
     */
    private $editRequestService;

    public function __construct(\App\Service\EditRequest $editRequestService)
    {
        $this->editRequestService = $editRequestService;
    }

    public function createAction(Request $request)
    {
        $email = $request->get('email');
        $schoolId = $request->get('school_id');
        if (!$email) {
            throw new \Exception('"email" not set');
        }
        if (!$schoolId) {
            throw new \Exception('"school_id" not set');
        }
        $this->editRequestService->requestEdit($schoolId, $email);
        return new JsonResponse(['success' => true]);
    }
}
