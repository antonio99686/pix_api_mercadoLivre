<?php
require_once 'vendor/autoload.php'; // Caminho para o autoload do Mercado Pago SDK

// Configure as credenciais do Mercado Pago
MercadoPago\SDK::setAccessToken('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');

// Listar todos os pagamentos
$payment_search = MercadoPago\Payment::search();

if ($payment_search && isset($payment_search->results) && is_array($payment_search->results)) {
    foreach ($payment_search->results as $payment) {
        echo "ID do pagamento: " . $payment->id . "\n";
        echo "Status: " . $payment->status . "\n";
        echo "Valor: " . $payment->transaction_amount . "\n";
    }
} else {
    echo "Nenhum pagamento encontrado ou houve um erro na busca.";
}
?>
