<?php

include 'init.php';
/** @var Auth $provider */

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

	echo '<script>window.location.href="https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'&request=true";</script>';
	exit;
}

echo '<br><a href="/task1.php">Задание 1</a> - Добавить в аккаунт 1000 сделок 1000 контактов и 1000 компаний через API, так что бы у каждой сделки была привязана компания и контакт.';
echo '<br><a href="/task2.php">Задание 2</a> - Добавить кастомное поле типа мультисписок в контакты через API.';
echo '<br><a href="/task3.php">Задание 3</a> - Обновить все контакты задав значение из этого мультисписка через API.';
echo '<br><a href="/task4.php">Задание 4</a> - Сделать выгрузку всех сделок контактов и компаний через API и вывести на экран, так что бы вместо id пользователей и кастомных полей отображались реальные названия полей и имена пользователей.';