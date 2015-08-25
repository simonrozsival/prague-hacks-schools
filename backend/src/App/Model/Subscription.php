<?php
namespace App\Model;

use Elastica\Document;
use Nette\Utils\Json;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;

class Subscription
{
    const TABLE = 'subscriptions';

    /**
     * @var \Zend_Db_Adapter_Pdo_Mysql
     */
    protected $_db;

    public function __construct(\Zend_Db_Adapter_Pdo_Mysql $db)
    {
        $this->_db = $db;
    }

    public static function getId($schoolId, $email)
    {
        return $schoolId . '_' . md5($email);
    }

    public function fetchEmailsBySchoolId($schoolId)
    {
        $sql = $this->_db->select()
            ->from(self::TABLE, array('email'))
            ->where('school_id = ?', $schoolId);
        return $this->_db->fetchCol($sql);
    }

    public function subscribe($schoolId, $email)
    {
        if ($token = $this->getSubscriptionToken($schoolId, $email)) {
            return $token;
        }
        $token = Util::generateRandomToken();
        $data = ['school_id' => $schoolId, 'email' => $email, 'cancel_token' => $token];
        $this->_db->insert(self::TABLE, $data);
        return $token;
    }

    public function getSubscriptionToken($schoolId, $email)
    {
        $sql = $this->_db->select()
            ->from(self::TABLE, array('cancel_token'))
            ->where('school_id = ?', $schoolId)
            ->where('email = ?', $email);
        return $this->_db->fetchOne($sql);
    }

    public function unsubscribe($token)
    {
        return $this->_db->delete(self::TABLE, [
            'cancel_token = ?' => $token,
        ]);
    }

    public function getEmailsBySchoolId($schoolId)
    {
        $sql = $this->_db->select()
            ->from(self::TABLE, ['email'])
            ->where('school_id = ?', $schoolId);
        return $this->_db->fetchCol($sql);
    }
}
