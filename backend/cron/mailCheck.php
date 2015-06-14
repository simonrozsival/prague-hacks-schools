<?php
require_once __DIR__ . '/../vendor/autoload.php';

/*
* skript pro zarazeni do cronu a pravidelne (v radu minut) spousteni
*/
require_once '../src/ConfirmationEmail.php';
checkMails('prague.hacks.schools@gmail.com', 'prague-hacks');
