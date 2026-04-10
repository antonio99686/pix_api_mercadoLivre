<?php
// diagnostico_token.php
$access_token = 'APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529';

echo "<h2>🔍 Diagnóstico do Token</h2>";

// Verificar formato do token
if (strpos($access_token, 'APP_USR-') === 0) {
    echo "✅ Token de PRODUÇÃO<br>";
} elseif (strpos($access_token, 'TEST-') === 0) {
    echo "⚠️ Token de TESTE (Sandbox)<br>";
    echo "Tokens de teste têm funcionalidades limitadas!<br>";
} else {
    echo "❌ Formato de token inválido<br>";
}

echo "<br>Token: " . substr($access_token, 0, 20) . "...<br>";

// Teste simples de autenticação
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://api.mercadopago.com/v1/payment_methods",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $access_token],
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<br>Teste de autenticação (lista métodos de pagamento):<br>";
echo "HTTP Code: " . $httpCode . "<br>";

if ($httpCode == 200) {
    echo "✅ Token válido!<br>";
    $methods = json_decode($response, true);
    echo "Métodos disponíveis: " . count($methods) . "<br>";
} elseif ($httpCode == 401) {
    echo "❌ Token inválido ou expirado!<br>";
    echo "Você precisa gerar um novo token no painel do Mercado Pago.<br>";
} else {
    echo "⚠️ Resposta inesperada: " . $response . "<br>";
}
?>