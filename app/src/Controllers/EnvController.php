<?php
namespace Src\Controllers;

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Dotenv\Exception\ExceptionInterface;

/**
 * Контроллер для управления файлов .env
 */
class EnvController
{
    private const FILENAME = '.env';
    /**
     * Возвращает массив env переменных CLIENT_ID, CLIENT_SECRET, REDIRECT_URI
     * 
     * @return array|null
     */
    public static function getEnv(): array|null
    {
        try {
            $dotenv = new Dotenv();
            $dotenv->load(self::FILENAME);

            $env = [
                'CLIENT_ID' => $_ENV['CLIENT_ID'],
                'CLIENT_SECRET' => $_ENV['CLIENT_SECRET'],
                'REDIRECT_URI' => $_ENV['REDIRECT_URI'],
            ];
        } catch (ExceptionInterface $e) {
            var_dump($e->getMessage());
            return null;
        }
        return $env;
    }
}