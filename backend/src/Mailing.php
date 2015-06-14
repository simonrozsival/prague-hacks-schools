<?php
namespace Hacks;

use Silex\Application;

class Mailing
{
    const TABLE = 'mailing';
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
    public function fetchSchoolIdByToken($token)
    {
        $sql = $this->_db->select()
            ->from(self::TABLE)
            ->where('token = ?', $token);
        $row = $this->_db->fetchRow($sql);
        if ($row) {
            return $row['school_id'];
        }
        return null;
    }

    public function fetchSubscribersByToken($token)
    {
        if (!$schoolId = $this->fetchSchoolIdByToken($token)) {
            return [];
        }
        $subscriptionsModel = new Subscription($this->_app);
        return $subscriptionsModel->getEmailsBySchoolId($schoolId);
    }
}
