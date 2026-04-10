<?php
// Arquivo para testar o webhook localmente

$webhook_url = "http://localhost/pix_api_mercadoLivre/webhook.php";

// Simular uma notificação do Mercado Pago
$notification = [
    "type" => "payment",
    "data" => [
        "id" => "123456789" // ID de pagamento de exemplo
    ]
];

$options = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($notification)
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($webhook_url, false, $context);

echo "Resposta do webhook: " . $response;
?>