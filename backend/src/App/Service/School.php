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

    public function __construct(\App\Model\School $schoolModel)
    {
        $this->schoolModel = $schoolModel;
    }

    public function requestEdit($schoolId, $email)
    {
        $editRequestId = $this->schoolModel->requestEdit($schoolId, $email);
        $this->sendEditLink($editRequestId);
    }
}
