<?php
/**
 * Created by PhpStorm.
 * User: tomasfejfar
 * Date: 26. 8. 2015
 * Time: 10:18
 */

namespace App\Service;

class School
{
    /**
     * @var \App\Model\School
     */
    private $schoolModel;

    /**
     * @var \App\Model\SchoolDesign
     */
    private $schoolDesignModel;

    public function __construct(\App\Model\School $schoolModel, \App\Model\SchoolDesign $schoolDesignModel)
    {
        $this->schoolModel = $schoolModel;
        $this->schoolDesignModel = $schoolDesignModel;
    }

    public function edit($schoolId, $email, $document, $level)
    {
        // retrieve the actual school document from elastic
        $school = $this->schoolModel->get($schoolId);

        // check the level privileges - compare old and new versions, find all categories
        // incompatibilities and check if all of those are less or equal to user's level
        if (!$this->schoolDesignModel->isUpdateValid($school, $document, $level)) {
            $e = new \App\Exception\School('Cannot edit data of higher level.', 400);
            $e->setSchool($school);
            throw $e;
            return $app->json([
                'success' => false,
                'msg' => "Cannot edit data of higher level.",
                'school' => $school,
            ], 400);
        }

        // add it to version log
        (new Version($app))
            ->addVersion($schoolId, $email, $school);

        // store the new document to elastic
        $schoolModel->update($schoolId, $document);
    }
}
