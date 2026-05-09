<?php
// config/conekta.php
// Las llaves se leen del archivo .env - nunca escribas credenciales aqui directamente.

define('CONEKTA_PRIVATE_KEY', getenv('CONEKTA_PRIVATE_KEY') ?: '');
define('CONEKTA_PUBLIC_KEY',  getenv('CONEKTA_PUBLIC_KEY')  ?: '');
define('CONEKTA_ACCEPT',      'application/vnd.conekta-v2.2.0+json');
