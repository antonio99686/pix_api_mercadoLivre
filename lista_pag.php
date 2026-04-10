<?php
require_once 'vendor/autoload.php';

MercadoPago\SDK::setAccessToken('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');

function listPayments($limit = 20, $offset = 0) {
    $access_token = 'APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529';
    
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.mercadopago.com/v1/payments/search?limit={$limit}&offset={$offset}&sort=date_desc",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $access_token
        ],
    ]);
    
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($http_code == 200) {
        return json_decode($response, true);
    } else {
        return false;
    }
}

try {
    $result = listPayments(20);
    
    if ($result && isset($result['results']) && count($result['results']) > 0) {
        echo "<h3>📊 Lista de Pagamentos</h3>";
        echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #009ee3; color: white;'>";
        echo "<th>ID</th>";
        echo "<th>Status</th>";
        echo "<th>Valor</th>";
        echo "<th>Método</th>";
        echo "<th>Email</th>";
        echo "<th>Data</th>";
        echo "</tr>";
        
        foreach ($result['results'] as $payment) {
            $status_color = [
                'approved' => '#4CAF50',
                'pending' => '#FF9800',
                'rejected' => '#F44336',
                'cancelled' => '#9E9E9E'
            ];
            
            $color = $status_color[$payment['status']] ?? '#000000';
            
            echo "<tr>";
            echo "<td>{$payment['id']}</td>";
            echo "<td style='color: $color; font-weight: bold;'>{$payment['status']}</td>";
            echo "<td>R$ " . number_format($payment['transaction_amount'], 2, ',', '.') . "</td>";
            echo "<td>" . strtoupper($payment['payment_method_id']) . "</td>";
            echo "<td>{$payment['payer']['email']}</td>";
            echo "<td>" . date('d/m/Y H:i:s', strtotime($payment['date_created'])) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "<p><strong>Total encontrado:</strong> {$result['paging']['total']} pagamentos</p>";
        
    } else {
        echo "❌ Nenhum pagamento encontrado.";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>