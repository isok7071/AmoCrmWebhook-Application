<?php
namespace Src\Controllers;

use AmoCRM\Client\AmoCRMApiClient;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Src\Controllers\EnvController;
use Src\Controllers\TokenController;

/**
 * Контроллер для управления запросами на API
 */
class ApiController
{
    /**
     * Возвращает готовый для использования APIClient AMoCRM
     * 
     * @throws \Exception
     * @return \AmoCRM\Client\AmoCRMApiClient
     */
    public static function getApiClient(): AmoCRMApiClient
    {
        $env = EnvController::getEnv();
        if (empty($env)) {
            throw new \Exception('Укажите переменные в .env');
        }

        if (!TokenController::isTokenSet()) {
            throw new \Exception('Token isn`t set');
        }

        $accessToken = TokenController::getToken();

        $apiClient = new AmoCRMApiClient($env['CLIENT_ID'], $env['CLIENT_SECRET'], $env['REDIRECT_URI']);
        $apiClient->setAccessToken(new AccessToken([
            'access_token' => $accessToken['accessToken'],
            'refresh_token' => $accessToken['refreshToken'],
            'expires' => $accessToken['expires'],
        ]))
        ->setAccountBaseDomain($accessToken['baseDomain'])
        ->onAccessTokenRefresh(
                function (AccessTokenInterface $accessToken, string $baseDomain) {
                    TokenController::setToken(
                        [
                            'accessToken' => $accessToken->getToken(),
                            'refreshToken' => $accessToken->getRefreshToken(),
                            'expires' => $accessToken->getExpires(),
                            'baseDomain' => $baseDomain,
                        ]
                    );
                }
            );
        return $apiClient;
    }
}