<?php
/**
 * Created by PhpStorm.
 * User: tomasfejfar
 * Date: 26. 8. 2015
 * Time: 10:18
 */

namespace App\Service;

class EditRequest
{
    /**
     * @var \App\Model\EditRequest
     */
    private $editRequestModel;

    public function __construct(\App\Model\EditRequest $editRequestModel)
    {
        $this->editRequestModel = $editRequestModel;
    }

    public function requestEdit($schoolId, $email)
    {
        if ($row = $this->editRequestModel->getEditRequest($schoolId, $email)) {
            $this->editRequestModel->removeEditRequest($row['id']);
        }
        $editRequestId = $this->editRequestModel->createEditRequest($schoolId, $email);
        $this->editRequestModel->sendEditLink($editRequestId);
    }

    public function getEditRequest($edit_token, $school_id, $email)
    {
        $editRequest = $this->editRequestModel->getByToken($edit_token);
        if (!$editRequest) {
            return $app->json([
                'success' => false,
                'msg' => "Invalid edit token.",
            ], 401);
        }

        if (!$this->editRequestModel->allowed($school_id, $email, $edit_token)) {
            return $app->json([
                'success' => false,
                'msg' => "Invalid edit token.",
            ], 401);
        }

        return $editRequest;
    }
}