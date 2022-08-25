<?php

use AmoCRM\OAuth2\Client\Provider\AmoCRM;
use League\OAuth2\Client\Token\AccessToken;

class Auth extends AmoCRM
{

	const TOKEN_FILE = DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'token_info.json';

	/**
	 * Обновляет токен
	 * @param $grant
	 * @param array $options
	 * @return AccessToken
	 */
	function updateToken($grant, array $options = []): AccessToken
	{
		try{
			$accessToken = $this->getAccessToken($grant, $options);

			if(!$accessToken->hasExpired()){
				$this->saveTokenCache($accessToken);
			}

			return $accessToken;
		}catch(Exception $e){
			die((string)$e);
		}
	}

	/**
	 * Берёт токен из кеша и проверяет его актуальность.
	 * Обновляет токен, если он истёк
	 * @return AccessToken
	 */
	function getToken(): AccessToken
	{
		$accessToken = $this->getTokenCache();

		if(!$accessToken->hasExpired()) return $accessToken;

		return $this->updateToken(new League\OAuth2\Client\Grant\RefreshToken(), [
			'refresh_token' => $accessToken->getRefreshToken(),
		]);
	}

	/**
	 * Сохраняет токен в кеш
	 * @param AccessToken $accessToken
	 * @return void
	 */
	function saveTokenCache(AccessToken $accessToken)
	{
		$data = [
			'accessToken' => $accessToken->getToken(),
			'refreshToken' => $accessToken->getRefreshToken(),
			'expires' => $accessToken->getExpires(),
			'baseDomain' => $this->getBaseDomain()
		];

		file_put_contents(self::TOKEN_FILE, json_encode($data));
	}

	/**
	 * Получает токен из кеша
	 * @return AccessToken
	 */
	function getTokenCache(): AccessToken
	{
		$accessToken = json_decode(file_get_contents(self::TOKEN_FILE), true);

		if (
			isset($accessToken)
			&& isset($accessToken['accessToken'])
			&& isset($accessToken['refreshToken'])
			&& isset($accessToken['expires'])
			&& isset($accessToken['baseDomain'])
		) {
			return new AccessToken([
				'access_token' => $accessToken['accessToken'],
				'refresh_token' => $accessToken['refreshToken'],
				'expires' => $accessToken['expires'],
				'baseDomain' => $accessToken['baseDomain'],
			]);
		} else {
			exit('Invalid access token ' . var_export($accessToken, true));
		}
	}

}