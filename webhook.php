<?php
// webhook.php
require __DIR__ . '/vendor/autoload.php';

// Permitir apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Método não permitido. Use POST.";
    exit;
}

// Configurar credenciais
MercadoPago\SDK::setAccessToken('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');

// Receber notificação
$body = file_get_contents('php://input');
$notification = json_decode($body, true);

// Log para debug
file_put_contents('webhook_log.txt', date('Y-m-d H:i:s') . " - Método: " . $_SERVER['REQUEST_METHOD'] . " - Body: " . $body . "\n", FILE_APPEND);

// Processar notificação
if (isset($notification['type']) && $notification['type'] == 'payment') {
    try {
        $payment = MercadoPago\Payment::find_by_id($notification['data']['id']);
        
        if ($payment && $payment->id) {
            // Atualizar status do pagamento no seu sistema
            $log = date('Y-m-d H:i:s') . " - Pagamento {$payment->id}: {$payment->status}\n";
            file_put_contents('payment_status.txt', $log, FILE_APPEND);
            
            echo "OK - Pagamento processado: " . $payment->status;
        } else {
            echo "Pagamento não encontrado";
        }
        
    } catch (Exception $e) {
        echo "Erro: " . $e->getMessage();
    }
} else {
    echo "OK - Notificação recebida";
}
?><?php
// webhook.php - Versão melhorada para processar pagamentos reais
require __DIR__ . '/vendor/autoload.php';

// Permitir apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Método não permitido. Use POST.";
    exit;
}

// Configurar credenciais
MercadoPago\SDK::setAccessToken('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');

// Receber notificação
$body = file_get_contents('php://input');
$notification = json_decode($body, true);

// Log para debug
file_put_contents('webhook_log.txt', date('Y-m-d H:i:s') . " - Notificação: " . $body . "\n", FILE_APPEND);

// Processar notificação de pagamento
if (isset($notification['type']) && $notification['type'] == 'payment') {
    try {
        $payment_id = $notification['data']['id'];
        
        // Buscar detalhes do pagamento
        $payment = MercadoPago\Payment::find_by_id($payment_id);
        
        if ($payment && $payment->id) {
            // Log do pagamento
            $log = date('Y-m-d H:i:s') . " - Pagamento ID: {$payment->id}, Status: {$payment->status}, Valor: {$payment->transaction_amount}\n";
            file_put_contents('payments_log.txt', $log, FILE_APPEND);
            
            // Processar baseado no status
            switch ($payment->status) {
                case 'approved':
                    // ✅ Pagamento aprovado - AÇÕES REAIS:
                    // 1. Atualizar banco de dados
                    // 2. Liberar acesso para o cliente
                    // 3. Enviar email de confirmação
                    // 4. Gerar nota fiscal
                    
                    $message = "Pagamento APROVADO - Cliente: {$payment->payer->email}, Valor: R$ {$payment->transaction_amount}";
                    file_put_contents('approved_payments.txt', date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
                    break;
                    
                case 'pending':
                    // ⏳ Pagamento pendente
                    $message = "Pagamento PENDENTE - ID: {$payment->id}, Detalhe: {$payment->status_detail}";
                    file_put_contents('pending_payments.txt', date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
                    break;
                    
                case 'rejected':
                    // ❌ Pagamento rejeitado
                    $message = "Pagamento REJEITADO - ID: {$payment->id}, Motivo: {$payment->status_detail}";
                    file_put_contents('rejected_payments.txt', date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
                    break;
            }
            
            echo "OK - Pagamento processado: " . $payment->status;
        } else {
            echo "Pagamento não encontrado";
        }
        
    } catch (Exception $e) {
        file_put_contents('webhook_errors.txt', date('Y-m-d H:i:s') . " - Erro: " . $e->getMessage() . "\n", FILE_APPEND);
        echo "Erro: " . $e->getMessage();
    }
    
} elseif (isset($notification['type']) && $notification['type'] == 'merchant_order') {
    // Processar ordem de merchant (se necessário)
    echo "OK - Merchant order recebida";
    
} else {
    // Outros tipos de notificação
    echo "OK - Notificação recebida: " . ($notification['type'] ?? 'tipo desconhecido');
}
?>