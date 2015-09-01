<?php
namespace App\Model;

class EditRequest
{
    const TABLE = 'edit_requests';
    /**
     * @var \Zend_Db_Adapter_Pdo_Mysql
     */
    protected $db;

    /**
     * @param \Zend_Db $db
     */
    public function __construct(\Zend_Db_Adapter_Abstract $db)
    {
        $this->db = $db;
    }

    public function handleEditRequest($schoolId, $email)
    {
        if ($row = $this->getEditRequest($schoolId, $email)) {
            $this->removeEditRequest($row['id']);
        }
        $editRequestId = $this->createEditRequest($schoolId, $email);
        $this->sendEditLink($editRequestId);
    }



    public function getByToken($token) {
        $sql = $this->db->select()
            ->from(self::TABLE)
            ->where('token = ?', $token);
        return $this->db->fetchRow($sql);
    }

    public function allowed($schoolId, $email, $token) {
        if ($row = $this->getEditRequest($schoolId, $email))
            return $row['token'] == $token;
        return FALSE;
    }

    public function removeEditRequest($id)
    {
        return $this->db->delete(self::TABLE, ['id = ?' => $id]);
    }

    public function createEditRequest($schoolId, $email)
    {
        $date = new \DateTime();
        $date->add(new \DateInterval('PT1H'));
        $data = [
            'school_id' => $schoolId,
            'email' => $email,
            'valid_until' => $date->format('Y-m-d H:i:s'),
            'token' => \App\Util::generateRandomToken(),
        ];
        $this->db->insert(self::TABLE, $data);
        return $this->db->lastInsertId();
    }

    public function sendEditLink($editRequestId)
    {
        $sql = $this->db->select()
            ->from(self::TABLE)
            ->where('id = ?', $editRequestId);
        if (!$row = $this->db->fetchRow($sql)) {
            throw new \Exception(sprintf('Invalid RequestId "%s"', $editRequestId));
        }
        $mailModel = new \App\EditRequestEmail($row['email'], $row['token']);
        $mailModel->send();
    }

    public function getEditRequest($schoolId, $email)
    {
        $sql = $this->db->select()
            ->from(self::TABLE)
            ->where('school_id = ?', $schoolId)
            ->where('email = ?', $email)
            ->where('valid_until > NOW()');
        return $this->db->fetchRow($sql);
    }
}
