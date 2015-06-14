<?php
namespace Hacks;

use Silex\Application;
use Nette\Utils\Json;

class SchoolDesign
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
     * Get the school design document
     * @return object
     */
    public function get() {
    	/** @var \GuzzleHttp\Client $guzzle */
        $guzzle = $this->_app['guzzle'];
        
        $response = $guzzle->get('/schools/design/document');

        if ($response->getStatusCode() == 200)
            return Json::decode(
                $response->getBody()
            )->_source;
        else
            return NULL;
    }

    public function isUpdateValid($old, $new, $level) {
        $design = $this->get();

        if ($design === NULL)
            return FALSE;

        try {

            foreach ($design->categories as $category) {
                // when the category level is higher than the user level is, check
                // if the categories are the same (both missing or identical)
                if ($category->level > $level) {
                    $key = $category->key;
                    if (isset($old->$key) && isset($new->$key)) {
                        if ($old->$key != $new->$key) {
                            return FALSE;
                        }
                    } else if (isset($old->$key) || isset($new->$key)) {
                        return FALSE;
                    }
                }
            }

        } catch (\Exception $e) {
            return FALSE;
        }

        return TRUE;
    }
}