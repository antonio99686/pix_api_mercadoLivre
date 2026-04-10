<?php
// testar_api.php - Script para testar a API do Mercado Pago
$access_token = 'APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529';

echo "<h2>Teste de API Mercado Pago</h2>";

// Teste 1: Verificar se o token é válido
echo "<h3>Teste 1: Verificar Token</h3>";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://api.mercadopago.com/v1/users/me",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $access_token],
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "<br>";
if ($httpCode == 200) {
    $data = json_decode($response, true);
    echo "✅ Token válido!<br>";
    echo "Usuário: " . ($data['nickname'] ?? 'N/A') . "<br>";
    echo "Email: " . ($data['email'] ?? 'N/A') . "<br>";
} else {
    echo "❌ Token inválido ou expirado<br>";
    echo "Resposta: " . $response . "<br>";
}

// Teste 2: Tentar endpoint de saldo
echo "<h3>Teste 2: Consultar Saldo</h3>";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://api.mercadopago.com/v1/mercadopago_account/balance",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $access_token],
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "<br>";
if ($httpCode == 200) {
    $data = json_decode($response, true);
    echo "✅ Saldo obtido com sucesso!<br>";
    echo "Saldo total: R$ " . number_format($data['total_balance'] ?? 0, 2, ',', '.') . "<br>";
    echo "<pre>" . print_r($data, true) . "</pre>";
} else {
    echo "❌ Erro ao obter saldo<br>";
    echo "Resposta: " . $response . "<br>";
}

// Teste 3: Listar pagamentos recentes (opcional)
echo "<h3>Teste 3: Listar Pagamentos Recentes</h3>";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://api.mercadopago.com/v1/payments/search?limit=5",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $access_token],
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "<br>";
if ($httpCode == 200) {
    $data = json_decode($response, true);
    $total = $data['paging']['total'] ?? 0;
    echo "✅ Total de pagamentos: " . $total . "<br>";
} else {
    echo "❌ Erro ao listar pagamentos<br>";
}
?>