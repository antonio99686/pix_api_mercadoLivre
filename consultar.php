<?php
require_once 'vendor/autoload.php';

// Configure as credenciais do Mercado Pago
MercadoPago\SDK::setAccessToken('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');

class PaymentConsultant {
    private $access_token;
    
    public function __construct($access_token) {
        $this->access_token = $access_token;
        MercadoPago\SDK::setAccessToken($access_token);
    }
    
    public function getPaymentById($payment_id) {
        try {
            $payment = MercadoPago\Payment::find_by_id($payment_id);
            
            if (!$payment || !$payment->id) {
                return ['success' => false, 'message' => 'Pagamento não encontrado'];
            }
            
            return [
                'success' => true,
                'payment' => $payment,
                'data' => [
                    'id' => $payment->id,
                    'status' => $payment->status,
                    'amount' => $payment->transaction_amount,
                    'payment_method' => $payment->payment_method_id,
                    'payer_email' => $payment->payer->email,
                    'date_created' => $payment->date_created,
                    'description' => $payment->description,
                    'status_detail' => $payment->status_detail ?? null
                ]
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function displayPaymentDetails($payment_id) {
        $result = $this->getPaymentById($payment_id);
        
        if (!$result['success']) {
            echo "❌ " . $result['message'] . "<br>";
            return;
        }
        
        $payment = $result['payment'];
        $data = $result['data'];
        
        echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>";
        echo "<h2 style='color: #333;'>Detalhes do Pagamento</h2>";
        
        // Status com cor
        $status_color = [
            'approved' => 'green',
            'pending' => 'orange',
            'rejected' => 'red',
            'cancelled' => 'gray'
        ];
        
        $color = $status_color[$data['status']] ?? 'black';
        
        echo "<table style='width: 100%; border-collapse: collapse;'>";
        echo "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>ID:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>{$data['id']}</td></tr>";
        echo "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Status:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee; color: $color;'>{$data['status']}</td></tr>";
        echo "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Valor:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>R$ " . number_format($data['amount'], 2, ',', '.') . "</td></tr>";
        echo "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Método:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>{$data['payment_method']}</td></tr>";
        echo "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Email:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>{$data['payer_email']}</td></tr>";
        echo "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Data:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . date('d/m/Y H:i:s', strtotime($data['date_created'])) . "</td></tr>";
        
        if ($data['description']) {
            echo "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Descrição:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>{$data['description']}</td></tr>";
        }
        
        if ($data['status_detail']) {
            echo "<tr><td style='padding: 8px;'><strong>Detalhe:</strong></td><td style='padding: 8px;'>{$data['status_detail']}</td></tr>";
        }
        
        echo "</table>";
        
        // Exibir QR Code para PIX
        if ($payment->payment_method_id == 'pix' && isset($payment->point_of_interaction->transaction_data)) {
            echo "<h3>QR Code PIX</h3>";
            echo "<img src='{$payment->point_of_interaction->transaction_data->qr_code_base64}' style='max-width: 200px;'><br>";
            echo "<strong>Código Copia e Cola:</strong><br>";
            echo "<textarea rows='3' style='width: 100%;' readonly>{$payment->point_of_interaction->transaction_data->qr_code}</textarea>";
        }
        
        echo "</div>";
    }
}

// Usar a classe
$consultant = new PaymentConsultant('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');

// Substitua pelo ID real do pagamento
$payment_id = "ID_DO_PAGAMENTO";

$consultant->displayPaymentDetails($payment_id);
?>