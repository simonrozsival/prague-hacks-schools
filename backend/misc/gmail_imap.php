<?php

class confirmationEmail {

    public $subject;
    public $email;

    private $body;
    private $token;
    private $adresa = 'http://www.example.com/confirm.php?token='; // TODO vyplnit skutecnou adresu pro poytvrzeni tokenem

    function __construct() {
        $this->generateToken();
    }

    public function send($test = true) {
        $this->body = 'Z vaseho emailu jsme obrzeli zadost o rozeslani zpravy s predmetem "' . $this->subject .
            '". Pokud chcete emaily rozeslat, klikne na <a href="' . $this->adresa . '/' . $this->token . '">tento link</a>';

        if ($test) {
            echo $this->subject . '<br>' . $this->body . '<br><br>';
        } else {
            mail($this->email, $this->subject, $this->body);
        }
        return null;
    }

    private function generateToken() {
        $this->token = 'isufiauaivpoaeircimruaoiuoamitoir'; // TODO generovani tokenu
    }
}

function checkMail($user, $pass)
{
/*
 * nacist neprectene emaily
 * poslat zpet potvrzovaci email s linkem
 * po kliknuti na link overit token
 * rozeslat puvodni mail na emaily evidovane u adresy odesilatele
 * */
    $mbox = imap_open("{imap.gmail.com:993/ssl}", $user, $pass, null, 1,
                      array('DISABLE_AUTHENTICATOR' => 'PLAIN')) or die("Can't connect to GMail: " . imap_last_error());

    $mails = imap_search($mbox, 'UNSEEN');

    foreach($mails as $key => $id) {
        $confirmEmail = new confirmationEmail();
        $mail_header = imap_headerinfo($mbox, $id);
        // print_r($mail_header);
        $confirmEmail->email = $mail_header->from[0]->mailbox . '@' . $mail_header->from[0]->host;
        $confirmEmail->subject = $mail_header->subject;

        $confirmEmail->send();

        // sendConfirmationEmail($email, $subject);

        //imap_delete($mbox, $key + 1);

    }

    imap_expunge($mbox);

    imap_close($mbox);

    return null;
}

echo '<pre>';
checkMail('prague.hacks.schools@gmail.com', 'prague-hacks');
echo '</pre>';
