<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class Ownership
{
    /**
     * @var \App\Service\Owner
     */
    private $ownerService;

    public function __construct(\App\Service\Owner $ownerService)
    {
        $this->ownerService = $ownerService;
    }

    public function claimAction(Request $request)
    {
        $schoolId = $request->get('school_id');
        $email = $request->get('email');
        $message = $request->get('message');
        if (!$schoolId || !$email || !$message) {
            return new JsonResponse(['success' => false, 'msg' => 'SchoolId, Email or Message not set'], 400);
        }
        $this->ownerService->claimOwnership($schoolId, $email, $message);
        return new JsonResponse(['success' => true]);
    }
}
