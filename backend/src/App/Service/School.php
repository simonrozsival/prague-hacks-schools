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
}
