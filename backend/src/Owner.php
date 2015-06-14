<?php
namespace Hacks;

use Silex\Application;

class Owner
{
    const TABLE = 'owner';

    const ANONYMOUS_LEVEL = 0;
    const ONE_TIME_EDITOR_LEVEL = 1;
    const OWNER_LEVEL = 2;
    /**
     * @var \Silex\Application
     */
    protected $_app;
    /**
     * @var \Zend_Db_Adapter_Pdo_Mysql
     */
    protected $_db;
    /**
     * @var \Hacks\EditRequest
     */
    protected $_editRequest;

    public function __construct(Application $app)
    {
        $this->_app = $app;
        $this->_db = $app['db'];
    }

    public function getEditLevel($schoolId, $email)
    {
        $sql = $this->_db->select()
            ->from(self::TABLE)
            ->where('school_id = ?', $schoolId)
            ->where('email = ?', $email)
            ->where('approved');
        return $this->_db->fetchRow($sql) ? self::OWNER_LEVEL : self::ONE_TIME_EDITOR_LEVEL;
    }

    public function claimOwnership($schoolId, $email, $message)
    {
        $data = [
            'school_id' => $schoolId,
            'email' => $email,
            'message' => $message,
            'approved' => FALSE
        ];
        $this->_db->insert(self::TABLE, $data);
        return $this->_db->lastInsertId();
    }

    public function approve($schoolId, $email)
    {
        $data = array(
            'approved' => TRUE
        );
        return $this->update($data, 'school_id = ? AND email = ?', $schoolId, $email);
    }
}
