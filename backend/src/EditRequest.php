<?php
namespace Hacks;

use Hacks\EditRequest\Email;
use Silex\Application;

class EditRequest
{
    const TABLE = 'edit_requests';
    /**
     * @var \Silex\Application
     */
    protected $_app;
    /**
     * @var \Zend_Db_Adapter_Pdo_Mysql
     */
    protected $_db;

    public function __construct(Application $app)
    {
        $this->_app = $app;
        $this->_db = $app['db'];
    }

    public function handleEditRequest($schoolId, $email)
    {
        if ($row = $this->getEditRequest($schoolId, $email)) {
            $this->removeEditRequest($row['id']);
        }
        $editRequestId = $this->createEditRequest($schoolId, $email);
        $this->sendEditLink($editRequestId);
    }

    public function getEditRequest($schoolId, $email)
    {
        $sql = $this->_db->select()
            ->from(self::TABLE)
            ->where('school_id = ?', $schoolId)
            ->where('email = ?', $email)
            ->where('valid_until > NOW()');
        return $this->_db->fetchRow($sql);
    }

    private function removeEditRequest($id)
    {
        return $this->_db->delete(self::TABLE, ['id = ?' => $id]);
    }

    private function createEditRequest($schoolId, $email)
    {
        $date = new \DateTime();
        $date->add(new \DateInterval('PT1H'));
        $data = [
            'school_id' => $schoolId,
            'email' => $email,
            'valid_until' => $date->format('Y-m-d H:i:s'),
            'token' => Util::generateRandomToken(),
        ];
        $this->_db->insert(self::TABLE, $data);
        return $this->_db->lastInsertId();
    }

    private function sendEditLink($editRequestId)
    {
        $sql = $this->_db->select()
            ->from(self::TABLE)
            ->where('id = ?', $editRequestId);
        if (!$row = $this->_db->fetchRow($sql)) {
            throw new \Exception(sprintf('Invalid RequestId "%s"', $editRequestId));
        }
        $mailModel = new Email();
        $mailModel->send($row['email'], $row['token']);
    }
}
