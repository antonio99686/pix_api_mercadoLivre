<?php
require_once __DIR__ . '/vendor/autoload.php'; // Caminho para o autoload do Mercado Pago SDK

// Configure as credenciais do Mercado Pago
MercadoPago\SDK::setAccessToken('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');

// Verifique se o ID do pagamento foi fornecido via GET
if (isset($_GET["id"]) && !empty($_GET["id"])) {
    $payment_id = $_GET["id"];
    
    // Receba e verifique a notificação de pagamento (IPN)
    $payment = MercadoPago\Payment::find_by_id($payment_id);
    
    if ($payment) {
        if ($payment->status == 'approved') {
            // Atualize o status do pedido como pago ou faça outras ações necessárias
            echo "Pagamento aprovado!";
        } else {
            echo "Pagamento pendente ou cancelado.";
        }
    } else {
        echo "Pagamento não encontrado.";
    }
} else {
    echo "ID do pagamento não fornecido.";
}
?>
