<?php

class confirmationEmail {

    const ADRESA = 'http://www.example.com/confirm.php?token=';  // TODO vyplnit skutecnou adresu pro potvrzeni tokenem
    const FROM_EMAIL = 'admin@praguehacks.cz';

    public $to;

    private $subject;
    private $subject_encoded;
    private $body;
    private $token;

    function __construct() {
        $this->generateToken();
    }

    public function send($test = true) {

        $this->body = 'Dobrý den,<br>z vašeho emailu jsme obrželi žádost o rozeslaní zprávy s předmětem "' . $this->subject_encoded .
            '". Pokud chcete emaily opravdu rozeslat, klikněte na <a href="' . self::ADRESA . $this->token . '">tento link</a>. '.
        '<br><br>SchoolIn';

        if ($test) {
            echo $this->subject . '<br>' . $this->body . '<br><br>';
        } else {
            $mail = new \Zend_Mail('utf-8');
            $mail->setSubject('Potvrzení odeslání emailu');
            $mail->addTo($this->to);
            $mail->setFrom(self::FROM_EMAIL);
            $mail->setBodyHtml($this->body);
            $mail->send();
        }

        if (!$test) $this->saveTokenToDB();

        return true;
    }

    public function setSubject($sub) {
        $this->subject = $sub;
        $s = '';

        $elements = imap_mime_header_decode($sub);
        //print_r($elements);
        for ($i=0; $i<count($elements); $i++) {
            $s .= mb_convert_encoding($elements[$i]->text,
                                      'UTF-8',
                                      ($elements[$i]->charset == 'default' ? 'auto' : $elements[$i]->charset));
        }
        $this->subject_encoded = $s;

        return true;
    }

    private function generateToken() {
        $this->token = bin2hex(openssl_random_pseudo_bytes(32));
    }

    private function saveTokenToDB() {
        $dsn = 'mysql:host=localhost;dbname=schoolin';
        $username = 'schoolin';
        $password = 'LoremIpsum1';
        $options = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        );

        $dbh = new PDO($dsn, $username, $password, $options);

        $sql = $dbh->prepare("insert into tokens (token, subject) VALUES (?, ?)");
        $sql->execute(array($this->token, $this->subject));

        return true;
    }
}

function checkMail($user, $pass)
{
    $mbox = imap_open("{imap.gmail.com:993/ssl}", $user, $pass, null, 1,
                      array('DISABLE_AUTHENTICATOR' => 'PLAIN')) or die("Can't connect to GMail: " . imap_last_error());

    $mails = imap_search($mbox, 'UNSEEN');

    foreach($mails as $key => $id) {
        $mail_header = imap_headerinfo($mbox, $id);

        $confirmEmail = new confirmationEmail();
        $confirmEmail->to = $mail_header->from[0]->mailbox . '@' . $mail_header->from[0]->host;
        $confirmEmail->setSubject($mail_header->subject);
        $confirmEmail->send();

        //imap_delete($mbox, $key + 1);

    }

    imap_expunge($mbox);

    imap_close($mbox);

    return true;
}

echo '<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Document</title>
</head>
<body>';

echo '<pre>';
checkMail('prague.hacks.schools@gmail.com', 'prague-hacks');
echo '</pre>';

echo '</body></html>';
