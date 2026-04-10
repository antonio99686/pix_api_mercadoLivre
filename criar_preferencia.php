<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

// Configurar credenciais
MercadoPago\SDK::setAccessToken('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');

// Criar preferência de pagamento
$preference = new MercadoPago\Preference();

$item = new MercadoPago\Item();
$item->title = 'Mensalidade Referente a esse Mês ';
$item->quantity = 1;
$item->currency_id = 'BRL';
$item->unit_price = 30.00;

$preference->items = array($item);
$preference->save();

// Redirecionar para a URL de pagamento
echo $preference->init_point;

