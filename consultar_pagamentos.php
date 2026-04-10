<?php
echo "<h2>📊 Logs de Pagamentos</h2>";

if (file_exists('payments_log.txt')) {
    echo "<pre>";
    echo file_get_contents('payments_log.txt');
    echo "</pre>";
} else {
    echo "Nenhum pagamento processado ainda.";
}

echo "<h3>✅ Pagamentos Aprovados:</h3>";
if (file_exists('approved_payments.txt')) {
    echo "<pre style='color: green;'>";
    echo file_get_contents('approved_payments.txt');
    echo "</pre>";
}

echo "<h3>⏳ Pagamentos Pendentes:</h3>";
if (file_exists('pending_payments.txt')) {
    echo "<pre style='color: orange;'>";
    echo file_get_contents('pending_payments.txt');
    echo "</pre>";
}

echo "<h3>❌ Pagamentos Rejeitados:</h3>";
if (file_exists('rejected_payments.txt')) {
    echo "<pre style='color: red;'>";
    echo file_get_contents('rejected_payments.txt');
    echo "</pre>";
}
?>