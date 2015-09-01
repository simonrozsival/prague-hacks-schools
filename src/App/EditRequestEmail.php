<?php
/**
 * Created by PhpStorm.
 * User: tomasfejfar
 * Date: 27. 8. 2015
 * Time: 18:35
 */

namespace App;

class EditRequestEmail extends \Zend_Mail
{

    /**
     * EditRequestEmail constructor.
     */
    public function __construct($email, $token)
    {
        parent::__construct('utf-8');
        $this->addTo($email);
        $this->setBodyHtml($this->getEmailBody($token));
    }

    /**
     * @param $token
     */
    protected function getEmailBody($token)
    {
        return $token;
    }
}
