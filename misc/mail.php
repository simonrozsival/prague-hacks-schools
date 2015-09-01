<?php
$username = 'prague.hacks.schools@gmail.com';
$password = 'prague-hacks';
$hostname = '{imap.gmail.com:993/ssl}INBOX';
$inbox = imap_open($hostname, $username, $password, null, 1, array('DISABLE_AUTHENTICATOR' => 'PLAIN'))
    || die('Cannot connect to Gmail: ' . imap_last_error());
$check = imap_check($inbox) || die(imap_last_error());
var_dump($check);
$mails = imap_fetch_overview($inbox, '1,2,3,4');
var_dump($mails);
die('connected');
