<?php
namespace Hacks;

use Elastica\Type;
use Silex\Application;
use Nette\Utils\Json;

class School
{
    /**
     * @var \Silex\Application
     */
    protected $_app;

    public function __construct(Application $app)
    {
        $this->_app = $app;
    }

    /**
     * Get the school with given ID
     * @param int $id
     * @return array|null
     */
    public function get($id) {
    	/** @var \GuzzleHttp\Client $guzzle */
        $guzzle = $this->_app['guzzle'];

        $response = $guzzle->get('/schools/school/' . $id);
        if ($response->getStatusCode() == 200)
            return Json::decode(
            	$response->getBody()
            )->_source;
        else
            return NULL;
    }

    /**
     * Simple rewrite of the whole school
     * @param string $id
     * @param object $json
     */
    public function update($id, $jsonObject) {
        $document = new \Elastica\Document($id, $jsonObject);
        $response = $this->_getElasticType()->addDocument($document);

        return $response->getData()['created'] === TRUE;
    }

    /**
     * @return Type
     */
    protected function _getElasticType()
    {
        $es = $this->_app['elastic'];
        $esType = $es->getIndex('schools')->getType('school');
        return $esType;
    }
}
