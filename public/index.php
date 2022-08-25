<?php

include_once '../vendor/autoload.php';

require('Auth.php');

$ini = parse_ini_file('../api.ini');

$provider = new Auth([
    'clientId' => $ini['api_clientId'],
    'clientSecret' => $ini['api_clientSecret'],
    'redirectUri' => $ini['api_redirectUri'],
]);

if(isset($_GET['referer'])){
    $provider->setBaseDomain($_GET['referer']);
}

if(!isset($_GET['code'])){
	/** Просто отображаем кнопку авторизации */
	echo '<div>
		<script
			class="amocrm_oauth"
			charset="utf-8"
			data-client-id="' . $provider->getClientId() . '"
			data-title="Установить интеграцию"
			data-compact="false"
			data-class-name="className"
			data-color="default"
			data-state="' . $_SESSION['oauth2state'] . '"
			data-error-callback="handleOauthError"
			src="https://www.amocrm.ru/auth/button.min.js"
		></script>
		</div>';
	echo '<script>
	handleOauthError = function(event) {
		alert(\'ID клиента - \' + event.client_id + \' Ошибка - \' + event.error);
	}
	</script>';
	die;
}elseif(!isset($_GET['request'])){
	$provider->updateToken(new League\OAuth2\Client\Grant\AuthorizationCode(), [
		'code' => $_GET['code'],
	]);

	echo '<script>window.location.href="https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'&request=action";</script>';
	exit;
}

$accessToken = $provider->getToken();