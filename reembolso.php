<?php
require_once 'vendor/autoload.php'; // Caminho para o autoload do Mercado Pago SDK

use MercadoPago\SDK;
use MercadoPago\Payment;
use MercadoPago\Refund;

// Configure as credenciais do Mercado Pago
SDK::setAccessToken('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');

// Id do pagamento a ser reembolsado
$payment_id = "474362529";

// Processar o reembolso
$payment = Payment::find_by_id($payment_id);

// Criar um novo objeto Refund
$refund = new Refund();

// Configurar o ID do pagamento diretamente na propriedade, se permitido pela classe
$refund->payment_id = $payment->id; // Isso depende da visibilidade da propriedade na classe Refund

// Configurar o valor total do reembolso
$refund->amount = $payment->transaction_amount;

// Salvar o reembolso
$refund->save();

// Verificar o status do reembolso
echo $refund->status;
?>
