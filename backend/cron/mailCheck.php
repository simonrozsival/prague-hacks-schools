<?php
namespace Hacks;

require_once __DIR__ . '/../vendor/autoload.php';

namespace Hacks\ConfirmationEmail;

/*
* skript pro zarazeni do cronu a pravidelne (v radu minut) spousteni
*/

\Hacks\checkMails('prague.hacks.schools@gmail.com', 'prague-hacks');
