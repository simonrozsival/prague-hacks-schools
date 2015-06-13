<?php
namespace Hacks;

use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;

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

        $cancelationToken = Util::generateRandomHash();

        $document = new \Elastica\Document($id, [
            'school_id' => $schoolId,
            'email' => $email,
            'cancelation_token' => $cancelationToken,
        ]);

        $response = $this->_getElasticType()->addDocument($document);
        if ($response->getData()['ok'] === true) {
            return $this->_app->json([
                'success' => true,
                'cancelation_token' => $cancelationToken,
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

    public function removeSubscription($schoolId, $email, $cancelationToken)
    {
        $response = $this->testSubscription($schoolId, $email);
        if ($response->getStatusCode() == 200) {
            $document = json_decode($response->getBody());
            if ($document->_source->cancelation_token == $cancelationToken) {
                $this->_getElasticType()->deleteIds([self::getId($schoolId, $email)]);
                return new JsonResponse(['success' => true]);
            } else {
                return new JsonResponse(['success' => false, 'msg' => sprintf('Cancelation token %s does not match', $cancelationToken)]);
            }
        } else {
            return new JsonResponse(['success' => false, 'msg' => 'No such subscription']);
        }
    }

    /**
     * @return \Elastica\Client
     */
    protected function _getElasticType()
    {
        $es = $this->_app['elastic'];
        $esType = $es->getIndex('subscriptions')->getType('subscriptions');
        return $esType;
    }
}
