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

$formParams = [
	"name" => "Custom value",
	"type" => "multiselect",
	"code" => 322,
	"enums" => [
		[
			"value" => "True",
		],
		[
			"value" => "False",
		],
	]
];

try{
	$data = $provider->getHttpClient()
		->request('POST', $provider->urlAccount() . 'api/v4/contacts/custom_fields', [
			'headers' => $provider->getHeaders($accessToken),
			'form_params' => $formParams,
		]);

	$parsedBody = json_decode($data->getBody()->getContents(), true);
}catch(GuzzleHttp\Exception\GuzzleException $e){
	$parsedBody = json_decode($e->getResponse()->getBody()->getContents(), true);
	$error = $parsedBody['validation-errors'][0]['errors'][0];

	if($error['path'] === 'code' && $error['code'] === 'NotSupportedChoice'){
		exit('Кастомное поле уже добавлено');
	}
}