<?php
require_once 'vendor/autoload.php';

// Configurar o token de acesso do Mercado Pago
MercadoPago\SDK::setAccessToken('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');

// Criar um item para a preferência
$item = new MercadoPago\Item();
$item->title = "Produto Exemplo"; // Título do produto
$item->quantity = 1; // Quantidade
$item->unit_price = 100; // Preço unitário

// Criar uma instância de preferência e adicionar o item
$preference = new MercadoPago\Preference();
$preference->items = array($item); // Adicionar o item à preferência

// Definir informações do pagador (comprador)
$payer = new MercadoPago\Payer();
$payer->email = "email@exemplo.com"; // E-mail do pagador

// Definir o pagador para a preferência
$preference->payer = $payer;

// Definir métodos de pagamento excluídos
$preference->payment_methods = array(
    "excluded_payment_methods" => array(
        array("id" => "amex") // Excluir método de pagamento Amex
    ),
    "excluded_payment_types" => array(
        array("id" => "ticket") // Excluir tipo de pagamento boleto
));

// Definir URLs de retorno
$preference->back_urls = array(
    "success" => "http://www.seusite.com/sucesso", // URL de sucesso
    "failure" => "http://www.seusite.com/erro",    // URL de erro
    "pending" => "http://www.seusite.com/pendente" // URL pendente
);

// Definir comportamento de retorno automático
$preference->auto_return = "approved"; // Auto retorno aprovado

// Salvar a preferência no Mercado Pago
$preference->save();

// Imprimir o ponto de inicialização (init_point) para o pagamento
echo $preference->init_point;
?>
