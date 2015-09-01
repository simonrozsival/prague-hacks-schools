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

    /**
     * @var \App\Model\Version
     */
    private $versionModel;

    public function __construct(\App\Model\School $schoolModel, \App\Model\SchoolDesign $schoolDesignModel, \App\Model\Version $versionModel)
    {
        $this->schoolModel = $schoolModel;
        $this->schoolDesignModel = $schoolDesignModel;
        $this->versionModel = $versionModel;
    }

    public function edit($schoolId, $email, $updatedDocument, $level)
    {
        // retrieve the actual school document from elastic
        $originalDocument = $this->schoolModel->get($schoolId);

        // check the level privileges - compare old and new versions, find all categories
        // incompatibilities and check if all of those are less or equal to user's level
        if (!$this->schoolDesignModel->isUpdateValid($originalDocument, $updatedDocument, $level)) {
            $e = new \App\Exception\School('Cannot edit data of higher level.', 400);
            throw $e;
        }

        // add it to version log
        $this->versionModel->addVersion($schoolId, $email, $originalDocument);

        // store the new document to elastic
        $this->schoolModel->update($schoolId, $updatedDocument);
    }
}
