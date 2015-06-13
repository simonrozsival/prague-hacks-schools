<?php
namespace Hacks\EditRequest;

class Email
{
    const FROM_EMAIL = 'admin@praguehacks.cz';

    public function send($to, $token)
    {
        $mail = new \Zend_Mail('utf-8');
        $mail->setSubject('Odkaz pro editaci');
        $mail->addTo($to);
        $mail->setFrom(self::FROM_EMAIL);
        $mail->setBodyHtml($this->getBodyTemplate($token));
        $mail->send();
        return true;
    }

    public function getBodyTemplate($token)
    {
        // @todo switch to template
        return sprintf('<a href="%s">Editovat</a>', $token);
    }
}
