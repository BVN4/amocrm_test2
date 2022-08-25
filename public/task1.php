<?php

include_once '../vendor/autoload.php';

require('Auth.php');

$ini = parse_ini_file('../api.ini');

$provider = new Auth([
	'clientId' => $ini['api_clientId'],
	'clientSecret' => $ini['api_clientSecret'],
	'redirectUri' => $ini['api_redirectUri'],
]);

$accessToken = $provider->getToken();

$provider->setBaseDomain($accessToken->getValues()['baseDomain']);

/**
 * Создание экземпляра контакта
 * @param $i
 * @return string[]
 */
function factoryContact($i): array
{
	return [
		"first_name" => "Contact ".$i,
	];
}

/**
 * Создание экземпляра компании
 * @param $i
 * @return string[]
 */
function factoryCompany($i): array
{
	return [
		"name" => "Company ".$i,
	];
}

/**
 * Создание экземпляра сделки
 * @param $i
 * @return array
 */
function factoryLead($i): array
{
	return [
		"name" => "Lead ".$i,
		"_embedded" => [
			"contacts" => [factoryContact($i)],
			"companies" => [factoryCompany($i)],
		],
	];
}


$i = 0;
$leads = [];

while($i < 1000){
	++$i;

	$leads[] = factoryLead($i);
	echo '<br>'.factoryLead($i)['name'];

	if($i % 50 === 0){
		echo '<br><br>';
		try {
			$data = $provider->getHttpClient()
				->request('POST', $provider->urlAccount() . 'api/v4/leads/complex', [
					'headers' => $provider->getHeaders($accessToken),
					'form_params' => $leads,
				]);

			$parsedBody = json_decode($data->getBody()->getContents(), true);
			echo '-- Added leads --';
			$leads = [];
		} catch (GuzzleHttp\Exception\GuzzleException $e) {
			var_dump((string)$e);
		}
		echo '<br>';
	}
}