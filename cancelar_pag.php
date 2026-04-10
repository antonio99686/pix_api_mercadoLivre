<?php
require_once 'vendor/autoload.php'; // Path to Mercado Pago SDK

// Replace with your actual Mercado Pago access token
MercadoPago\SDK::setAccessToken('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');

// Replace with the actual ID of the payment to be canceled
$payment_id = "474362529";

try {
    // Attempt to find the payment by ID
    $payment = MercadoPago\Payment::find_by_id($payment_id);

    if ($payment) {
        // If payment is found, update its status to "cancelled"
        $payment->status = "cancelled";
        $payment->update();
        echo "Pagamento cancelado.";
    } else {
        // Payment not found
        echo "Pagamento não encontrado com o ID fornecido.";
    }
} catch (Exception $e) {
    // Handle any exceptions that occur during the process
    echo 'Erro ao cancelar o pagamento: ', $e->getMessage();
}
?>
