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

    public function editAction(Request $request, $school_id, $edit_token)
    {
        $document = $request->getContent();

        // check the edit token
        $editRequestModel = new EditRequest($app);
        $editRequest = $editRequestModel->getByToken($edit_token);
        if (!$editRequest) {
            return $app->json([
                'success' => false,
                'msg' => "Invalid edit token.",
            ], 401);
        }

        $email = $editRequest['email'];

        if (!$editRequestModel->allowed($school_id, $email, $edit_token)) {
            return $app->json([
                'success' => false,
                'msg' => "Invalid edit token.",
            ], 401);
        }

        // get the user level
        $ownerModel = new Owner($app, $editRequestModel);
        $level = $ownerModel->getEditLevel($school_id, $email);

        // retrieve the actual school document from elastic
        $schoolModel = new School($app);
        $school = $schoolModel->get($school_id);

        // check the level privileges - compare old and new versions, find all categories
        // incompatibilities and check if all of those are less or equal to user's level
        $schoolDesignModel = new SchoolDesign($app);
        if (!$schoolDesignModel->isUpdateValid($school, $document, $level)) {
            return $app->json([
                'success' => false,
                'msg' => "Cannot edit data of higher level.",
                'school' => $school,
            ], 400);
        }

        // add it to version log
        (new Version($app))
            ->addVersion($school_id, $email, $school);

        // store the new document to elastic
        $schoolModel->update($school_id, $document);
        return $app['success'];
    }
}
