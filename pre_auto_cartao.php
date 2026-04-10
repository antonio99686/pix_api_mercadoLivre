<?php
require_once 'vendor/autoload.php'; // Caminho para o autoload do Mercado Pago SDK

// Configure as credenciais do Mercado Pago
MercadoPago\SDK::setAccessToken('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');

// Verificar se os dados do formulário foram enviados
if (isset($_POST['token']) && isset($_POST['payment_method_id'])) {
    // Dados da pré-autorização de cartão
    $card_data = array(
        "token" => $_POST['token'],
        "description" => "Produto Exemplo",
        "installments" => 1,
        "payer" => array(
            "email" => "email@exemplo.com"
        ),
        "payment_method_id" => $_POST['payment_method_id']
    );

    $payment = new MercadoPago\Payment();
    $payment->transaction_amount = 100;
    $payment->token = $card_data["token"];
    $payment->description = $card_data["description"];
    $payment->installments = $card_data["installments"];
    $payment->payer = $card_data["payer"];
    $payment->payment_method_id = $card_data["payment_method_id"];
    $payment->save();

    echo $payment->status;
} else {
    echo "Dados do formulário não foram enviados corretamente.";
}
?>
