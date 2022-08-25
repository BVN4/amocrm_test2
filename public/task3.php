<?php

include 'init.php';
/** @var Auth $provider */
/** @var array $ini */

$accessToken = $provider->getToken();

$provider->setBaseDomain($accessToken->getValues()['baseDomain']);

while(true){
	$contacts = getContacts();
	$data = [];

	foreach($contacts as $contact){
		$data[] = [
			'id' => $contact['id'],
			'custom_fields_values' => [
				[
					'field_code' => $ini['custom_field_code'],
					'values' => factoryValue()
				]
			]
		];
	}

	try{
		$data = $provider->getHttpClient()
			->request('PATCH', $provider->urlAccount() . 'api/v4/contacts', [
				'headers' => $provider->getHeaders($accessToken),
				'json' => $data,
			]);

		$parsedBody = json_decode($data->getBody()->getContents(), true);
	}catch(GuzzleHttp\Exception\GuzzleException $e){
		var_dump((string)$e);
	}

	if(count($contacts) < 250)
		break;
}

/**
 * Возвращает записи контактов из API
 * @return array
 */
function getContacts(): array
{
	global $accessToken;
	global $provider;
	static $page = 0;
	++$page;

	$query = [
		'limit' => 250,
		'page' => $page
	];

	try{
		$data = $provider->getHttpClient()
			->request('GET', $provider->urlAccount() . 'api/v4/contacts', [
				'headers' => $provider->getHeaders($accessToken),
				'query' => $query,
			]);

		$parsedBody = json_decode($data->getBody()->getContents(), true);

		return $parsedBody['_embedded']['contacts'];
	}catch(GuzzleHttp\Exception\GuzzleException $e){
		var_dump((string)$e);
		return [];
	}
}

/**
 * Возвращает рандомное значение кастомного поля
 * @return array
 */
function factoryValue(): array
{
	try{
		$int = random_int(1, 18);
	}catch(Exception $e){
		$int = 1;
	}

	$values = [];

	if($int % 2 === 0) $values[] = ['value' => 'True'];
	if($int % 3 === 0) $values[] = ['value' => 'False'];

	if(count($values) === 0) $values[] = ['value' => 'False'];

	return $values;
}

