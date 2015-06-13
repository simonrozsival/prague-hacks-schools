<?php
namespace Hacks;

use Silex\Application;

class Subscription
{
    /**
     * @var \Silex\Application
     */
    protected $_app;

    public function __construct(Application $app)
    {
        $this->_app = $app;
    }

    public static function getId($schoolId, $email)
    {
        return $schoolId . '_' . md5($email);
    }

    public function insert($schoolId, $email)
    {
        $id = self::getId($schoolId, $email);

        $hash = sha1(openssl_random_pseudo_bytes(64));

        $document = new \Elastica\Document($id, [
            'school_id' => $schoolId,
            'email' => $email,
            'hash' => $hash,
        ]);
        /** @var \Elastica\Client $es */
        $es = $this->_app['elastic'];
        $esType = $es->getIndex('subscriptions')->getType('subscriptions');
        $response = $esType->addDocument($document);
        if ($response->getData()['ok'] === true) {
            return $this->_app->json([
                'success' => true,
                'cancel_token' => $hash,
            ]);
        } else {
            return $this->_app->json([
                'success' => false,
                'status' => $response->getStatus(),
                'msg' => sprintf('Subscription failed with status %s', $response->getStatus()),
            ]);
        }
    }

    public function testSubscription($schoolId, $email)
    {
        $id = Subscription::getId($schoolId, $email);

        /** @var \GuzzleHttp\Client $guzzle */
        $guzzle = $this->_app['guzzle'];
        return $guzzle->get('/subscriptions/subscriptions/' . $id);
    }
}
