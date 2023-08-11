<?php
//Файл обработчик авторизации
require 'vendor/autoload.php';

use AmoCRM\Client\AmoCRMApiClient;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Src\Controllers\ApiController;
use Src\Controllers\TokenController;
use Src\Controllers\EnvController;

$isTokenSet = TokenController::isTokenSet();

[
    'CLIENT_ID' => $clientId,
    'CLIENT_SECRET' => $clientSecret,
    'REDIRECT_URI' => $redirectUri
] = EnvController::getEnv();
[
    'refreshToken' => $refreshToken,
    'expires' => $expires,
    'baseDomain' => $baseDomain
] = TokenController::getToken();

$apiClient = new AmoCRMApiClient($clientId, $clientSecret, $redirectUri);
$oauthClient = $apiClient->getOAuthClient();

if (isset($_GET['referer'])) {
    $apiClient->setAccountBaseDomain($_GET['referer']);
}

if (isset($_GET['code'])) {
    try {
        $accessToken = $oauthClient->getAccessTokenByCode($_GET['code']);

        TokenController::setToken([
            'accessToken' => $accessToken->getToken(),
            'refreshToken' => $accessToken->getRefreshToken(),
            'expires' => $accessToken->getExpires(),
            'baseDomain' => $apiClient->getAccountBaseDomain()
        ]);
        header("Location: /");
        exit;
    } catch (IdentityProviderException $e) {
        exit($e->getMessage());
    }
} elseif (TokenController::isRefreshTokenSet()) {
    try {
        $client = ApiController::getApiClient();
        $accessToken = $oauthClient->getAccessTokenByRefreshToken(
            $client->getAccessToken(),
        );

        TokenController::setToken([
            'accessToken' => $accessToken->getToken(),
            'refreshToken' => $accessToken->getRefreshToken(),
            'expires' => $accessToken->getExpires(),
            'baseDomain' => $apiClient->getAccountBaseDomain()
        ]);

        header("Location: /");
        exit;
    } catch (IdentityProviderException $e) {
        exit($e->getMessage());
    }
} else {
    // Если нет кода и refreshToken, перенаправляем на страницу авторизации
    $authorizationUrl = $oauthClient->getAuthorizeUrl();
    header("Location: {$authorizationUrl}");
    exit;
}