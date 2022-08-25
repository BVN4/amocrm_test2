<?php

include_once '../vendor/autoload.php';

require('Auth.php');

$ini = parse_ini_file('../api.ini');

$provider = new Auth([
	'clientId' => $ini['api_clientId'],
	'clientSecret' => $ini['api_clientSecret'],
	'redirectUri' => $ini['api_redirectUri'],
]);