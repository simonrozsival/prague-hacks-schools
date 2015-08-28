<?php
namespace App\Model;

use Silex\Application;
use Nette\Utils\Json;

class Version
{
    const TABLE = 'version';

    /**
     * @var \Zend_Db_Adapter_Abstract
     */
    private $db;

    public function __construct(\Zend_Db_Adapter_Abstract $db)
    {
        $this->db = $db;
    }

    public function addVersion($schoolId, $email, $jsonObject)
    {
        $data = [
            'school_id' => $schoolId,
            'edited_by' => $email,
            'document' => Json::encode($jsonObject)
        ];
        $this->db->insert(self::TABLE, $data);
        return $this->db->lastInsertId();
    }
}
