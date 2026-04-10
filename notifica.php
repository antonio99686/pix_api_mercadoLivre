<?php
require_once __DIR__ . '/vendor/autoload.php';

// Configuração do banco de dados (exemplo com MySQL)
define('DB_HOST', 'localhost');
define('DB_NAME', 'joj7');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configure as credenciais do Mercado Pago
MercadoPago\SDK::setAccessToken('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');

function updatePaymentStatus($payment_id, $status, $transaction_amount = null) {
    try {
        // Exemplo de conexão com MySQL
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Verificar se o pagamento existe
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE payment_id = :payment_id");
        $stmt->execute([':payment_id' => $payment_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Atualizar pagamento existente
            $sql = "UPDATE payments SET status = :status, updated_at = NOW() WHERE payment_id = :payment_id";
            $params = [':status' => $status, ':payment_id' => $payment_id];
            
            if ($transaction_amount) {
                $sql = "UPDATE payments SET status = :status, amount = :amount, updated_at = NOW() WHERE payment_id = :payment_id";
                $params = [':status' => $status, ':amount' => $transaction_amount, ':payment_id' => $payment_id];
            }
        } else {
            // Inserir novo pagamento
            $sql = "INSERT INTO payments (payment_id, status, amount, created_at) VALUES (:payment_id, :status, :amount, NOW())";
            $params = [':payment_id' => $payment_id, ':status' => $status, ':amount' => $transaction_amount];
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return true;
        
    } catch (PDOException $e) {
        error_log("Erro no banco de dados: " . $e->getMessage());
        return false;
    }
}

// Verifique se o ID do pagamento foi fornecido
if (isset($_GET["id"]) && !empty($_GET["id"])) {
    $payment_id = filter_var($_GET["id"], FILTER_SANITIZE_NUMBER_INT);
    
    try {
        $payment = MercadoPago\Payment::find_by_id($payment_id);
        
        if ($payment && $payment->id) {
            // Atualizar status no banco de dados
            updatePaymentStatus($payment->id, $payment->status, $payment->transaction_amount);
            
            // Processar baseado no status
            if ($payment->status == 'approved') {
                // Pagamento aprovado - ações específicas
                
                // 1. Registrar transação
                $transaction_data = [
                    'payment_id' => $payment->id,
                    'amount' => $payment->transaction_amount,
                    'payment_method' => $payment->payment_method_id,
                    'payer_email' => $payment->payer->email,
                    'date_approved' => $payment->date_approved
                ];
                
                // 2. Enviar email de confirmação
                // mail($payment->payer->email, "Pagamento aprovado", "Seu pagamento foi aprovado!");
                
                // 3. Liberar acesso ao conteúdo/serviço
                
                // 4. Retornar resposta JSON para webhook
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'approved', 'payment_id' => $payment->id]);
                    exit;
                }
                
                echo "✅ Pagamento aprovado com sucesso!";
                
            } elseif ($payment->status == 'pending') {
                echo "⏳ Pagamento pendente. Código: " . $payment->status_detail;
            } elseif ($payment->status == 'rejected') {
                echo "❌ Pagamento rejeitado. Motivo: " . $payment->status_detail;
            } else {
                echo "Status: " . $payment->status;
            }
            
            // Exibir informações adicionais (opcional)
            echo "<br><br><strong>Detalhes:</strong><br>";
            echo "ID: " . $payment->id . "<br>";
            echo "Valor: R$ " . number_format($payment->transaction_amount, 2, ',', '.') . "<br>";
            echo "Método: " . strtoupper($payment->payment_method_id) . "<br>";
            echo "Email: " . $payment->payer->email . "<br>";
            
            // Se for PIX, mostrar QR Code
            if ($payment->payment_method_id == 'pix' && isset($payment->point_of_interaction->transaction_data)) {
                echo "<br><strong>QR Code PIX:</strong><br>";
                echo "<img src='{$payment->point_of_interaction->transaction_data->qr_code_base64}'><br>";
            }
            
        } else {
            echo "❌ Pagamento não encontrado. ID: {$payment_id}";
        }
        
    } catch (Exception $e) {
        echo "❌ Erro: " . $e->getMessage();
        error_log("Erro no webhook: " . $e->getMessage());
    }
    
} else {
    echo "❌ ID do pagamento não fornecido.";
}
?>  