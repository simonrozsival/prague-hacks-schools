<?php
namespace Hacks;

require_once __DIR__ . '/../vendor/autoload.php';

use PDO;

$limit_tokenu = 30; // minuty

class Database {

    public function connect() {
        $dsn = 'mysql:host=localhost;dbname=schoolin';
        $username = 'schoolin';
        $password = 'LoremIpsum1';
        $options = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        );

        return new PDO($dsn, $username, $password, $options);
    }

}

class ConfirmationEmail {

    const ADRESA = 'http://www.example.com/confirm.php?token=';  // TODO vyplnit skutecnou adresu pro potvrzeni tokenem
    const FROM_EMAIL = 'admin@praguehacks.cz';

    public $to;
    public $edate;

    private $subject;
    private $subject_encoded;
    private $body;
    private $token;

    function __construct() {
        $this->generateToken();
    }

    public function send($test = true) {

        $this->body = 'Dobrý den,<br>z vašeho emailu jsme obrželi žádost o rozeslaní zprávy s předmětem "' . $this->subject_encoded .
            '". Pokud chcete emaily opravdu rozeslat, klikněte na <a href="' . self::ADRESA . $this->token . '&email='.$this->to.
            '">tento link</a>. '.
        '<br><br>SchoolIn';
        if ($test) {
            echo $this->body;
        } else {
            $mail = new \Zend_Mail('utf-8');
            $mail->setSubject('Potvrzení odeslání emailu');
            $mail->addTo($this->to);
            $mail->setFrom(self::FROM_EMAIL);
            $mail->setBodyHtml($this->body);
            $mail->send();
        }
        $this->saveTokenToDB();

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
        $db = new Database();
        $dbh = $db->connect();

        $sql = $dbh->prepare("insert into tokens (token, subject, email_from, email_date) VALUES (?, ?, ?, ?)");
        $sql->execute(array($this->token, $this->subject, $this->to, $this->edate));

        return true;
    }
}

function checkMails($user, $pass)
{
    $mbox = imap_open("{imap.gmail.com:993/ssl}", $user, $pass, null, 1,
                      array('DISABLE_AUTHENTICATOR' => 'PLAIN')) or die("Can't connect to GMail: " . imap_last_error());

    $mails = imap_search($mbox, 'UNSEEN');

    if ($mails !== false)
    foreach($mails as $key => $id) {
        $mail_header = imap_headerinfo($mbox, $id);
        $confirmEmail = new ConfirmationEmail();
        $confirmEmail->to = $mail_header->from[0]->mailbox . '@' . $mail_header->from[0]->host;
        $confirmEmail->edate = $mail_header->Date;
        $confirmEmail->setSubject($mail_header->subject);
        $confirmEmail->send();

        imap_setflag_full($mbox, "$id", "\\Seen");
    }

    imap_close($mbox);

    return true;
}

function checkToken($token, $email, $limit) {
    $db = new Database();
    $dbh = $db->connect();

    $sql = $dbh->prepare("select * from tokens where email_from = :email and token = :token and NOW() <= date_add(dt, interval $limit minute)");
    $sql->bindParam(':token', $token, PDO::PARAM_STR);
    $sql->bindParam(':email', $email, PDO::PARAM_STR);
    $sql->execute();
    $res = $sql->fetchAll(PDO::FETCH_ASSOC);

    if ($sql->rowCount() == 1) {
        $mbox = imap_open("{imap.gmail.com:993/ssl}", 'prague.hacks.schools', 'prague-hacks', null, 1,
            array('DISABLE_AUTHENTICATOR' => 'PLAIN')) or die("Can't connect to GMail: " . imap_last_error());

        $search = 'FROM "'.$res[0]['email_from'].'" ON "'.$res[0]['email_date'] .'"';

        $mails = imap_search($mbox, $search);

        if ($mails !== false) {
            foreach ($mails as $key => $id) {
                $headers = imap_headerinfo($mbox, $id);

                $from = $headers->from[0]->mailbox . '@' . $headers->from[0]->host;
                $subject = $headers->subject;

                //$structure = imap_fetchstructure($mbox, $id);

                $raw_body = imap_body($mbox, $id);

                $mail = new \Zend_Mail('utf-8');
                $mail->setFrom($from);
                $mail->addTo('neco@example.com');
//                $mail->addBcc( getMails($res[0]['email_from']) );
                $mail->setSubject($subject);
                $mail->setBodyText($raw_body);
                //$mail->send();

                //imap_delete($mbox, $id);
            }
        }

        imap_expunge($mbox);
        imap_close($mbox);
    }
}

if (IsSet($_GET['token']) && IsSet($_GET['email'])) {
    $token = $_GET['token'];
    $email = $_GET['email'];

    checkToken($token, $email, $limit_tokenu);
}

checkMails('prague.hacks.schools','prague-hacks');

checkToken('1fe1d2bc7da8591ff2f9cd990b3cbee63eba4782483f8868073acd73a9de23c5', 'jan.kasparek@gmail.com', 240);
checkToken('dec9c291a348ab95a9eda3c755e9b24bba14e19aaf15e1b0b855388510d436c0', 'no-reply@accounts.google.com', 240);
