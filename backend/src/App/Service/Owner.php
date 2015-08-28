<?php
namespace App\Service;

class Owner
{
    /**
     * @var \App\Model\Owner
     */
    private $ownerModel;

    /**
     * @param \App\Model\Owner $ownerModel
     */
    public function __construct(\App\Model\Owner $ownerModel)
    {
        $this->ownerModel = $ownerModel;
    }

    public function claimOwnership($schoolId, $email, $message)
    {
        return $this->ownerModel->claimOwnership($schoolId, $email, $message);
    }

    public function getEditLevel($schoolId, $email)
    {
        return $this->ownerModel->getEditLevel($schoolId, $email)
    }
}
