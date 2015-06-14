<?php

require_once __DIR__ . '/../vendor/autoload.php';

$limit_tokenu = 240; // minuty

class Database {

    public function connect() {
        $dsn = 'mysql:host=localhost;dbname=schoolin';
        $username = 'root';
        $password = 'LoremIpsum1';
        $options = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        );

        return new PDO($dsn, $username, $password, $options);
    }

}

class ConfirmationEmail {

    const ADRESA = 'http://www.example.com/ConfirmationEmail.php?token=';  // TODO vyplnit skutecnou adresu pro potvrzeni tokenem
    const FROM_EMAIL = 'prague.hacks.schools@google.com';

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
            '" target="_blank">tento link</a> pro potvrzení.<br><br>SchoolIn';
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

        for ($i=0; $i<count($elements); $i++) {
            $s .= mb_convert_encoding($elements[$i]->text,
                                      'UTF-8', // from
                                      ($elements[$i]->charset == 'default' ? 'auto' : $elements[$i]->charset)); // to
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

    public function checkSenderEmail() {
        $db = new Database();
        $dbh = $db->connect();

        $sql = $dbh->prepare("select * from owner where approved = 1 and email = ?");
        $sql->execute(array($this->to));

        return ($sql->rowCount() == 1);
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

        if ( $confirmEmail->checkSenderEmail() ) {

            $confirmEmail->edate = $mail_header->Date;
            $confirmEmail->setSubject($mail_header->subject);
            $confirmEmail->send();
            echo 'Known recipient ' . $confirmEmail->to . PHP_EOL;
            imap_setflag_full($mbox, "$id", "\\Seen");

        } else {
            echo 'Unknown recipient ' . $confirmEmail->to . PHP_EOL;
            imap_delete($mbox, $id);

        }
    }

    imap_expunge($mbox);
    imap_close($mbox);

    return true;
}

function getSubscribersEmails($email) {
    $db = new Database();
    $dbh = $db->connect();

    $sql = $dbh->prepare("select * from owner o
                          left join subscriptions s on s.school_id = o.school_id
                          where o.email = :email and approved = 1");
    $sql->bindParam(':email', $email, PDO::PARAM_STR);
    $sql->execute();

    $bcc = [];
    while ($res = $sql->fetch(PDO::FETCH_ASSOC)) {
        $bcc[] = $res['email'];
    }

    return $bcc;
}

function checkToken($token, $email, $limit) {

    function naplnSablonu($body) {
        $head = '<!doctype html><html lang="cs"><head><meta charset="UTF-8"><title>Email od naseskola.cz</title></head><body>';

        $structured_body = $body; // TODO dle navrhu vytvorit sablonu a vlozit do toho text z poslaneho emailu

        $tail = '</body></html>';

        return $head . $structured_body . $tail;
    }

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

                $raw_body = imap_body($mbox, $id);

                $mail = new \Zend_Mail('utf-8');
                $mail->setFrom($from);
                $bcc = getSubscribersEmails($res[0]['email_from']);
                $mail->addBcc( $bcc );
                $mail->setSubject($subject);
                $mail->setBodyHtml( naplnSablonu($raw_body) );
                //if (count($bcc)>0) $mail->send();

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

//checkMails('prague.hacks.schools','prague-hacks');

/*checkToken('2ef240346fb723db37a8dc48cddb495aacaea1b4c58e6f6cc4800151f5f350ed', 'jan.kasparek@gmail.com', 240);
checkToken('23045972f8a1d296a71787e1bdf396c3b6b4bc0806be7819389224d3271a624c', 'no-reply@accounts.google.com', 240);*/
