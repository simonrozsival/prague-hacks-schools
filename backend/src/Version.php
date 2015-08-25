<?php
namespace App;

use Silex\Application;
use Nette\Utils\Json;

class Version
{
    const TABLE = 'version';

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

    public function addVersion($schoolId, $email, $jsonObject)
    {
        $data = [
            'school_id' => $schoolId,
            'edited_by' => $email,
            'document' => Json::encode($jsonObject)
        ];
        $this->_db->insert(self::TABLE, $data);
        return $this->_db->lastInsertId();
    }
}
