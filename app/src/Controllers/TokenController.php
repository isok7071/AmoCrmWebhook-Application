<?php
namespace Src\Controllers;

use Exception;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Dotenv\Exception\ExceptionInterface;

/**
 * Контроллер для управления oauth токенами
 */
class TokenController
{

    private const FILENAME = '.env.refresh';

    /**
     * Возращает установлен ли accessToken
     * @return bool
     */
    public static function isTokenSet(): bool
    {
        $tokens = self::getToken();
        if (empty($tokens['accessToken'])) {
            return false;
        }
        return true;
    }
    /**
     * Возвращает задан ли refreshToken
     * @return bool
     */
    public static function isRefreshTokenSet(): bool
    {
        $tokens = self::getToken();
        if (empty($tokens['refreshToken'])) {
            return false;
        }
        return true;
    }

    /**
     * Возвращает массив с accessToken, refreshToken, expires, baseDomain
     * 
     * @return array|null
     */
    public static function getToken(): array|null
    {
        $dotenv = new Dotenv();
        $dotenv->load(self::FILENAME);
        $tokens = [
            'accessToken' => $_ENV['accessToken'],
            'refreshToken' => $_ENV['refreshToken'],
            'expires' => $_ENV['expires'],
            'baseDomain' => $_ENV['baseDomain']
        ];
        return $tokens;
    }

    /**
     * Записывает информацию по токену из массива $token 
     * @param array $token
     * @return void
     */
    public static function setToken(array $token): void
    {
        try {
            $file = fopen(self::FILENAME, 'w+');

            foreach ($token as $key => $item) {
                fwrite($file, $key . '=' . $item . PHP_EOL, );
            }
            fclose($file);
        } catch(Exception $e){
            var_dump($e->getMessage());
            exit();
        }
    }
}