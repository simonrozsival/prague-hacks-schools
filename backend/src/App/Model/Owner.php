<?php
namespace App\Model;

class Owner
{
    const TABLE = 'owner';

    const ANONYMOUS_LEVEL = 0;
    const ONE_TIME_EDITOR_LEVEL = 1;
    const OWNER_LEVEL = 2;

    /**
     * @var \Zend_Db_Adapter_Pdo_Mysql
     */
    protected $db;

    /**
     * @param \Zend_Db $db
     */
    public function __construct(\Zend_Db $db)
    {
        $this->db = $db;
    }

    public function getEditLevel($schoolId, $email)
    {
        $sql = $this->db->select()
            ->from(self::TABLE)
            ->where('school_id = ?', $schoolId)
            ->where('email = ?', $email)
            ->where('approved');
        return $this->db->fetchRow($sql) ? self::OWNER_LEVEL : self::ONE_TIME_EDITOR_LEVEL;
    }

    public function claimOwnership($schoolId, $email, $message)
    {
        $data = [
            'school_id' => $schoolId,
            'email' => $email,
            'message' => $message,
            'approved' => false,
        ];
        $this->db->insert(self::TABLE, $data);
        return $this->db->lastInsertId();
    }

    public function approve($schoolId, $email)
    {
        $data = array(
            'approved' => true,
        );
        return $this->db->update(self::TABLE, $data, array(
            'school_id = ?' => $schoolId,
            'email = ?' => $email,
        ));
    }
}
