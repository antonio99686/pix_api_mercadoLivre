<?php
// consultar_pagamentos_json.php
echo "<h2>📊 Pagamentos Processados</h2>";

$payments_file = __DIR__ . '/payments.json';

if (file_exists($payments_file)) {
    $payments = json_decode(file_get_contents($payments_file), true);
    
    if ($payments && count($payments) > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr style='background-color: #009ee3; color: white;'>";
        echo "<th>ID</th><th>Status</th><th>Valor</th><th>Email</th><th>Data</th>";
        echo "</tr>";
        
        foreach ($payments as $id => $data) {
            $status_color = [
                'approved' => '#4CAF50',
                'pending' => '#FF9800',
                'rejected' => '#F44336',
                'cancelled' => '#9E9E9E'
            ];
            $color = $status_color[$data['status']] ?? '#000';
            
            echo "<tr>";
            echo "<td>{$id}</td>";
            echo "<td style='color: {$color}; font-weight: bold;'>{$data['status']}</td>";
            echo "<td>R$ " . number_format($data['amount'], 2, ',', '.') . "</td>";
            echo "<td>{$data['payer_email']}</td>";
            echo "<td>{$data['updated_at']}</td>";
            echo "</tr>";
        }
        
        echo "rable>";
    } else {
        echo "Nenhum pagamento processado ainda.";
    }
} else {
    echo "Nenhum pagamento processado ainda.";
}
?>