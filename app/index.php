<?php
require 'vendor/autoload.php';

use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Models\WebhookModel;
use Src\Controllers\TokenController;
use Src\Controllers\ApiController;

//TODO обновление токена
$isTokenSet = TokenController::isTokenSet();
if (!$isTokenSet) {
    header("Location: auth.php");
    exit;
}

//Подпишемся на вебхук добавления сделки
$webhook = new WebhookModel();
$webhook->setDestination("https://" . $_SERVER["HTTP_HOST"] . "/lead.php")
    ->setSettings([
        'add_lead',
        'update_lead',
        'add_contact',
        'update_contact'
    ]);
try {
    $webhook = ApiController::getApiClient()->webhooks()->subscribe($webhook);
    echo ('Успешно подписан на входящий вебхук');
} catch (AmoCRMApiException $e) {
    print_r($e->getMessage());
    die;
}