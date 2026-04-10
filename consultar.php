<?php
require_once 'vendor/autoload.php'; // Caminho para o autoload do Mercado Pago SDK

// Configure as credenciais do Mercado Pago
MercadoPago\SDK::setAccessToken('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');


// Id do pagamento a ser consultado
$payment_id = "ID_DO_PAGAMENTO";

// Consultar o pagamento
$payment = MercadoPago\Payment::find_by_id($payment_id);

// Exibir detalhes do pagamento
echo "Status: " . $payment->status . "<br>";
echo "Valor: " . $payment->transaction_amount . "<br>";
echo "Método de Pagamento: " . $payment->payment_method_id . "<br>";
echo "Email do Pagador: " . $payment->payer->email . "<br>";
?>
