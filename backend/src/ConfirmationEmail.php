<?php
namespace Hacks;

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

        $sql = $dbh->prepare("insert into tokens (token, subject, email_from) VALUES (?, ?, ?)");
        $sql->execute(array($this->token, $this->subject, $this->to));

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

    $sql = $dbh->prepare("select * from tokens where email_from = :email and token = :token and NOW() < date_add(dt, interval $limit minute)");
    $sql->bindParam(':token', $token, PDO::PARAM_STR);
    $sql->bindParam(':email', $email, PDO::PARAM_STR);
    $sql->execute();
    $res = $sql->fetchAll(PDO::FETCH_ASSOC);

    //print_r($res);

    if ($sql->rowCount() == 1) {
        $mbox = imap_open("{imap.gmail.com:993/ssl}", 'prague.hacks.schools', 'prague-hacks', null, 1,
            array('DISABLE_AUTHENTICATOR' => 'PLAIN')) or die("Can't connect to GMail: " . imap_last_error());

        $search = 'SUBJECT "'.$res[0]['subject'].'" FROM "'.$res[0]['email_from'].'"';
        $mails = imap_search($mbox, $search);

        if ($mails !== false) {
            foreach ($mails as $key => $id) {
                $headers = imap_headerinfo($mbox, $id);

                $from = $headers->from[0]->mailbox . '@' . $headers->from[0]->host;
                $subject = $headers->subject;

                $structure = imap_fetchstructure($mbox, $id);
                //$type = $this->get_mime_type($structure);

                // GET HTML BODY
                //$body = $this->get_part($connection, $i, "");

                $raw_body = imap_body($mbox, $id);

                $attachments = array();

                if (isset($structure->parts) && count($structure->parts)) {
                    for ($e = 0; $e < count($structure->parts); $e++) {
                        $attachments[$e] = array('is_attachment' => false, 'filename' => '', 'name' => '', 'attachment' => '');

                        if ($structure->parts[$e]->ifdparameters) {
                            foreach ($structure->parts[$e]->dparameters as $object) {
                                if (strtolower($object->attribute) == 'filename') {
                                    $attachments[$e]['is_attachment'] = true;
                                    $attachments[$e]['filename'] = $object->value;
                                } //if (strtolower($object->attribute) == 'filename')
                            } //foreach ($structure->parts[$e]->dparameters as $object)
                        } //if ($structure->parts[$e]->ifdparameters)

                        if ($structure->parts[$e]->ifparameters) {
                            foreach ($structure->parts[$e]->parameters as $object) {
                                if (strtolower($object->attribute) == 'name') {
                                    $attachments[$e]['is_attachment'] = true;
                                    $attachments[$e]['name'] = $object->value;
                                } //if (strtolower($object->attribute) == 'name')
                            } //foreach ($structure->parts[$e]->parameters as $object)
                        } //if ($structure->parts[$e]->ifparameters)

                        if ($attachments[$e]['is_attachment']) {
                            $attachments[$e]['attachment'] = @imap_fetchbody($mbox, $id, $e + 1);
                            if ($structure->parts[$e]->encoding == 3) {
                                // 3 = BASE64
                                $attachments[$e]['attachment'] = base64_decode($attachments[$e]['attachment']);
                            } //if ($structure->parts[$e]->encoding == 3)
                            elseif ($structure->parts[$e]->encoding == 4) {
                                // 4 = QUOTED-PRINTABLE
                                $attachments[$e]['attachment'] = quoted_printable_decode($attachments[$e]['attachment']);
                            } //elseif ($structure->parts[$e]->encoding == 4)
                        } //if ($attachments[$e]['is_attachment'])

                        if ($attachments[$e]['is_attachment']) {
                            $filename = $attachments[$e]['filename'];
                            $filename = $attachments[$e]['name'];
                            $filecontent = $attachments[$e]['attachment'];
                        } //if ($attachments[$e]['is_attachment'])
                    } //for ($e = 0; $e < count($structure->parts); $e++)
                } //if (isset($structure->parts) && count($structure->parts))



                echo "<pre>";
                echo "From: " . $headers->Unseen . "<br />";
                echo "From: " . $from . "<br />";
                echo "Cc: " . $cc . "<br />";
                echo "Subject: " . $subject . "<br />";
                echo "Content Type: " . $type . "<br />";
                echo "Body: " . $body . "<br />";


                $mail = new Zend_Mail();

                $mail->settype(Zend_Mime::MULTIPART_MIXED);

                for ($k = 0; $k < count($attachments); $k++) {
                    $filename = $attachments[$k]['name'];
                    $filecontent = $attachments[$k]['attachment'];

                    if ($filename && $filecontent) {
                        $file = $mail->createAttachment($filecontent);
                        $file->filename = $filename;
                    } //if ($filename && $filecontent)
                } //for ($k = 0; $k < count($attachments); $k++)


                $mail->setFrom($from);
                $mail->addTo('test@members.bigmanwalking.com');
                $mail->setSubject($subject);
                $mail->setBodyHtml($body);
                $mail->send();

                // imap_delete($mbox, $id);
            }
        }

        //imap_expunge($mbox);

        imap_close($mbox);
    }
}

if (IsSet($_GET['token']) && IsSet($_GET['email'])) {
    $token = $_GET['token'];
    $email = $_GET['email'];

    checkToken($token, $email, $limit_tokenu);
}

checkMails('prague.hacks.schools','prague-hacks');

checkToken('2764644c71449252355fbaf1fb16f1b026c8746f8d7cedfcbd59b63865d85418', 'jan.kasparek@gmail.com', 240);
