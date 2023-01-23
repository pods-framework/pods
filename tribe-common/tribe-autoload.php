<?php
$common = __DIR__ . '/src';

require_once $common . '/Tribe/Autoloader.php';

$autoloader = Tribe__Autoloader::instance();
$autoloader->register_prefix( 'Tribe__', $common . '/Tribe' );
$autoloader->register_autoloader();
