<?php
// webhook.php - Arquivo para receber notificações do Mercado Pago

require __DIR__ . '/vendor/autoload.php';

// Configurar credenciais
MercadoPago\SDK::setAccessToken('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');

// Configuração de logging
define('LOG_FILE', __DIR__ . '/webhook_log.txt');
define('ERROR_LOG', __DIR__ . '/webhook_errors.txt');
define('PAYMENT_LOG', __DIR__ . '/payments_log.txt');

function writeLog($file, $message) {
    file_put_contents($file, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Método não permitido";
    exit;
}

// Receber notificação
$body = file_get_contents('php://input');
$notification = json_decode($body, true);

// Log da notificação recebida
writeLog(LOG_FILE, "Notificação recebida: " . $body);

// Verificar se é uma notificação válida
if (!$notification) {
    writeLog(ERROR_LOG, "JSON inválido: " . $body);
    http_response_code(400);
    echo "JSON inválido";
    exit;
}

// Processar diferentes tipos de notificação
switch ($notification['type'] ?? '') {
    case 'payment':
        processPaymentNotification($notification);
        break;
        
    case 'merchant_order':
        processMerchantOrderNotification($notification);
        break;
        
    case 'point_integration_ip':
        processPointNotification($notification);
        break;
        
    default:
        writeLog(LOG_FILE, "Tipo de notificação ignorado: " . ($notification['type'] ?? 'unknown'));
        http_response_code(200);
        echo "OK - Tipo não processado";
        break;
}

function processPaymentNotification($notification) {
    try {
        $payment_id = $notification['data']['id'];
        
        writeLog(PAYMENT_LOG, "Processando pagamento ID: " . $payment_id);
        
        // Buscar detalhes do pagamento
        $payment = MercadoPago\Payment::find_by_id($payment_id);
        
        if (!$payment || !$payment->id) {
            writeLog(ERROR_LOG, "Pagamento não encontrado: " . $payment_id);
            http_response_code(200); // Mudado para 200 para evitar reenvio
            echo "Pagamento não encontrado";
            return;
        }
        
        // Processar baseado no status
        $status = $payment->status;
        $amount = $payment->transaction_amount;
        $payment_method = $payment->payment_method_id;
        $payer_email = $payment->payer->email;
        
        writeLog(PAYMENT_LOG, "Pagamento {$payment_id} - Status: {$status}, Valor: {$amount}, Método: {$payment_method}");
        
        // Atualizar sistema baseado no status
        switch ($status) {
            case 'approved':
                // Pagamento aprovado
                updatePaymentStatus($payment_id, 'approved', $amount, $payer_email, $payment_method);
                
                // Ações para pagamento aprovado:
                // 1. Atualizar banco de dados
                // 2. Enviar email de confirmação
                // 3. Liberar acesso ao produto/serviço
                // 4. Gerar nota fiscal
                
                writeLog(PAYMENT_LOG, "✅ Pagamento {$payment_id} APROVADO com sucesso!");
                break;
                
            case 'pending':
                // Pagamento pendente
                updatePaymentStatus($payment_id, 'pending', $amount, $payer_email, $payment_method);
                writeLog(PAYMENT_LOG, "⏳ Pagamento {$payment_id} PENDENTE - Detalhe: " . ($payment->status_detail ?? 'N/A'));
                break;
                
            case 'rejected':
                // Pagamento rejeitado
                updatePaymentStatus($payment_id, 'rejected', $amount, $payer_email, $payment_method);
                writeLog(PAYMENT_LOG, "❌ Pagamento {$payment_id} REJEITADO - Motivo: " . ($payment->status_detail ?? 'N/A'));
                break;
                
            case 'cancelled':
                // Pagamento cancelado
                updatePaymentStatus($payment_id, 'cancelled', $amount, $payer_email, $payment_method);
                writeLog(PAYMENT_LOG, "⊘ Pagamento {$payment_id} CANCELADO");
                break;
                
            case 'refunded':
                // Pagamento estornado
                updatePaymentStatus($payment_id, 'refunded', $amount, $payer_email, $payment_method);
                writeLog(PAYMENT_LOG, "↺ Pagamento {$payment_id} ESTORNADO");
                break;
                
            default:
                writeLog(PAYMENT_LOG, "Status não tratado: {$status} para pagamento {$payment_id}");
                break;
        }
        
        // Retornar sucesso para o Mercado Pago
        http_response_code(200);
        echo "OK - Pagamento processado";
        
    } catch (Exception $e) {
        writeLog(ERROR_LOG, "Erro ao processar pagamento: " . $e->getMessage());
        http_response_code(200); // Mudado para 200 para evitar reenvio excessivo
        echo "Erro interno: " . $e->getMessage();
    }
}

function processMerchantOrderNotification($notification) {
    $order_id = $notification['data']['id'];
    writeLog(LOG_FILE, "Notificação de merchant_order recebida: " . $order_id);
    
    // Processar ordem de merchant (se necessário)
    http_response_code(200);
    echo "OK - Merchant order processada";
}

function processPointNotification($notification) {
    writeLog(LOG_FILE, "Notificação de point_integration_ip recebida");
    http_response_code(200);
    echo "OK - Point notification processada";
}

function updatePaymentStatus($payment_id, $status, $amount, $payer_email, $payment_method = null) {
    // Opção 1: Salvar em arquivo JSON (já implementado)
    $payments_file = __DIR__ . '/payments.json';
    $payments = [];
    
    if (file_exists($payments_file)) {
        $content = file_get_contents($payments_file);
        $payments = json_decode($content, true) ?? [];
    }
    
    $payments[$payment_id] = [
        'status' => $status,
        'amount' => $amount,
        'payer_email' => $payer_email,
        'payment_method' => $payment_method,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    file_put_contents($payments_file, json_encode($payments, JSON_PRETTY_PRINT));
    
    //Opção 2: Integração com banco de dados (descomente quando tiver o banco configurado)
 
    try {
        require_once __DIR__ . '/database.php';
        updatePaymentInDatabase($payment_id, $status, $amount, $payer_email);
    } catch (Exception $e) {
        writeLog(ERROR_LOG, "Erro no banco: " . $e->getMessage());
    }

}

// Função para verificar assinatura (segurança adicional)
function verifySignature($body, $signature, $x_request_id) {
    // Implementar verificação de assinatura se necessário
    // https://www.mercadopago.com.br/developers/pt/docs/your-integrations/notifications/webhooks#security
    
    // Exemplo básico (substitua pelo seu secret key)
    $secret = "seu_secret_key_aqui";
    $expected_signature = hash_hmac('sha256', $body, $secret);
    
    return hash_equals($expected_signature, $signature);
}
?>