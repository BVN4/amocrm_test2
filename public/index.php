<?php

include_once '../vendor/autoload.php';

use AmoCRM\OAuth2\Client\Provider\AmoCRM;

$ini = parse_ini_file('../api.ini');

$provider = new AmoCRM([
    'clientId' => $ini['api_clientId'],
    'clientSecret' => $ini['api_clientSecret'],
    'redirectUri' => $ini['api_redirectUri'],
]);

if (isset($_GET['code']) && $_GET['code']) {
    //Вызов функции setBaseDomain требуется для установки контектс аккаунта.
    if (isset($_GET['referer'])) {
        $provider->setBaseDomain($_GET['referer']);
    }

    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    //todo сохраняем access, refresh токены и привязку к аккаунту и возможно пользователю

    /** @var \AmoCRM\OAuth2\Client\Provider\AmoCRMResourceOwner $ownerDetails */
    $ownerDetails = $provider->getResourceOwner($token);

    printf('Hello, %s!', $ownerDetails->getName());
}