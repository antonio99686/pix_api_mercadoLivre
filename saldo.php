<?php
// consultar_saldo_correto.php
// COLOQUE SEU TOKEN VÁLIDO AQUI
$access_token = 'APP_USR-4912927847899302-041000-2ba2be183a6476266a517087c0410e43-3327617184'; // Substitua pelo token correto

echo "<h2>💰 Consulta de Saldo Mercado Pago</h2>";

if ($access_token == 'APP_USR-4912927847899302-041000-2ba2be183a6476266a517087c0410e43-3327617184') {
    echo "<div style='color: red; padding: 10px; background: #ffebee; border-radius: 5px;'>";
    echo "⚠️ Você precisa substituir o token pelo seu token real!<br>";
    echo "Obtenha seu token em: https://www.mercadopago.com.br/developers/panel<br>";
    echo "</div>";
    exit;
}

// Testar token primeiro
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

if ($httpCode != 200) {
    echo "<div style='color: red; padding: 10px; background: #ffebee; border-radius: 5px;'>";
    echo "❌ Token inválido ou expirado!<br>";
    echo "HTTP Code: " . $httpCode . "<br>";
    echo "Por favor, gere um novo token no painel do Mercado Pago.<br>";
    echo "</div>";
    exit;
}

echo "✅ Token válido!<br><br>";

// Obter informações da conta
echo "<h3>📋 Informações da Conta</h3>";
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

if ($httpCode == 200) {
    $data = json_decode($response, true);
    echo "✅ Conta encontrada!<br>";
    echo "Usuário: " . ($data['nickname'] ?? 'N/A') . "<br>";
    echo "Email: " . ($data['email'] ?? 'N/A') . "<br>";
    echo "Tipo: " . ($data['account_type'] ?? 'N/A') . "<br>";
    echo "ID: " . ($data['id'] ?? 'N/A') . "<br>";
} else {
    echo "❌ Erro: HTTP $httpCode<br>";
}

// Obter pagamentos aprovados
echo "<h3>📊 Pagamentos Aprovados</h3>";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://api.mercadopago.com/v1/payments/search?status=approved&limit=10&sort=date_desc",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $access_token],
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $data = json_decode($response, true);
    $total = 0;
    
    if (isset($data['results']) && count($data['results']) > 0) {
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
        echo "<tr style='background-color: #009ee3; color: white;'>";
        echo "<th>ID</th><th>Valor</th><th>Método</th><th>Status</th><th>Data</th>";
        echo "<tr>";
        
        foreach ($data['results'] as $payment) {
            $total += $payment['transaction_amount'];
            echo "<tr>";
            echo "<td>" . $payment['id'] . "</td>";
            echo "<td>R$ " . number_format($payment['transaction_amount'], 2, ',', '.') . "</td>";
            echo "<td>" . strtoupper($payment['payment_method_id']) . "</td>";
            echo "<td>" . $payment['status'] . "</td>";
            echo "<td>" . date('d/m/Y H:i', strtotime($payment['date_created'])) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "<p><strong>💰 Total em pagamentos aprovados (últimos 10):</strong> R$ " . number_format($total, 2, ',', '.') . "</p>";
        
        // Salvar no banco de dados
        $conn = new mysqli("localhost", "root", "", "sentinelas");
        if (!$conn->connect_error) {
            // Criar tabela se não existir
            $conn->query("CREATE TABLE IF NOT EXISTS saldo_mercado_pago (
                id INT AUTO_INCREMENT PRIMARY KEY,
                saldo DECIMAL(10,2) NOT NULL,
                data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                tipo VARCHAR(50) DEFAULT 'consulta'
            )");
            
            $stmt = $conn->prepare("INSERT INTO saldo_mercado_pago (saldo, tipo) VALUES (?, 'pagamentos_aprovados')");
            $stmt->bind_param("d", $total);
            
            if ($stmt->execute()) {
                echo "<p style='color: green;'>✅ Saldo registrado no banco de dados!</p>";
            } else {
                echo "<p style='color: red;'>❌ Erro ao registrar: " . $conn->error . "</p>";
            }
            
            $stmt->close();
            $conn->close();
        } else {
            echo "<p style='color: orange;'>⚠️ Banco de dados não disponível</p>";
        }
        
    } else {
        echo "Nenhum pagamento aprovado encontrado.";
    }
} else {
    echo "❌ Erro ao consultar pagamentos: HTTP $httpCode<br>";
    echo "Resposta: " . $response . "<br>";
}

// Obter pagamentos pendentes
echo "<h3>⏳ Pagamentos Pendentes</h3>";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://api.mercadopago.com/v1/payments/search?status=pending&limit=5&sort=date_desc",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $access_token],
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $data = json_decode($response, true);
    $total_pending = 0;
    
    if (isset($data['results']) && count($data['results']) > 0) {
        foreach ($data['results'] as $payment) {
            $total_pending += $payment['transaction_amount'];
        }
        echo "<p>💰 Total pendente: R$ " . number_format($total_pending, 2, ',', '.') . "</p>";
    } else {
        echo "Nenhum pagamento pendente.";
    }
}
?>