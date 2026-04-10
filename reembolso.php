<?php
require_once 'vendor/autoload.php';

use MercadoPago\SDK;
use MercadoPago\Payment;

SDK::setAccessToken('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');

class PaymentRefund {
    private $access_token;
    
    public function __construct($access_token) {
        $this->access_token = $access_token;
        SDK::setAccessToken($access_token);
    }
    
    public function getPayment($payment_id) {
        try {
            $payment = Payment::find_by_id($payment_id);
            
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
                    'refunded_amount' => $payment->refunded_amount ?? 0,
                    'payment_method' => $payment->payment_method_id
                ]
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function fullRefund($payment_id) {
        try {
            $payment = Payment::find_by_id($payment_id);
            
            if (!$payment || !$payment->id) {
                return ['success' => false, 'message' => 'Pagamento não encontrado'];
            }
            
            if ($payment->status !== 'approved') {
                return ['success' => false, 'message' => 'Apenas pagamentos aprovados podem ser reembolsados'];
            }
            
            // Verificar valor já reembolsado
            $available_amount = $payment->transaction_amount - ($payment->refunded_amount ?? 0);
            
            if ($available_amount <= 0) {
                return ['success' => false, 'message' => 'Pagamento já foi totalmente reembolsado'];
            }
            
            $refund = new MercadoPago\Refund();
            $refund->payment_id = $payment->id;
            $refund->amount = $available_amount; // Reembolso do valor restante
            $refund->save();
            
            if ($refund->status == 'approved') {
                return [
                    'success' => true,
                    'message' => 'Reembolso total realizado',
                    'refund_id' => $refund->id,
                    'amount' => $refund->amount,
                    'status' => $refund->status
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Falha no reembolso',
                    'status' => $refund->status
                ];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function partialRefund($payment_id, $amount) {
        try {
            $payment = Payment::find_by_id($payment_id);
            
            if (!$payment || !$payment->id) {
                return ['success' => false, 'message' => 'Pagamento não encontrado'];
            }
            
            if ($payment->status !== 'approved') {
                return ['success' => false, 'message' => 'Apenas pagamentos aprovados podem ser reembolsados'];
            }
            
            // Verificar se o valor é válido
            if ($amount <= 0) {
                return ['success' => false, 'message' => 'Valor do reembolso deve ser maior que zero'];
            }
            
            $max_refund = $payment->transaction_amount - ($payment->refunded_amount ?? 0);
            
            if ($amount > $max_refund) {
                return ['success' => false, 'message' => "Valor excede o máximo permitido para reembolso: R$ {$max_refund}"];
            }
            
            $refund = new MercadoPago\Refund();
            $refund->payment_id = $payment->id;
            $refund->amount = $amount;
            $refund->save();
            
            if ($refund->status == 'approved') {
                return [
                    'success' => true,
                    'message' => 'Reembolso parcial realizado',
                    'refund_id' => $refund->id,
                    'amount' => $refund->amount,
                    'status' => $refund->status
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Falha no reembolso',
                    'status' => $refund->status
                ];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function listRefunds($payment_id) {
        try {
            $payment = Payment::find_by_id($payment_id);
            
            if (!$payment || !$payment->id) {
                return ['success' => false, 'message' => 'Pagamento não encontrado'];
            }
            
            if (!$payment->refunds || count($payment->refunds) == 0) {
                return ['success' => true, 'refunds' => [], 'message' => 'Nenhum reembolso encontrado'];
            }
            
            $refunds = [];
            foreach ($payment->refunds as $refund) {
                $refunds[] = [
                    'id' => $refund->id,
                    'amount' => $refund->amount,
                    'status' => $refund->status,
                    'date' => $refund->date_created ?? 'N/A'
                ];
            }
            
            return [
                'success' => true,
                'refunds' => $refunds,
                'total_refunded' => $payment->refunded_amount ?? 0
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

// Usar a classe
$refund_manager = new PaymentRefund('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');

$payment_id = "474362529"; // Substitua pelo ID real

// Verificar informações do pagamento
$payment_info = $refund_manager->getPayment($payment_id);

if ($payment_info['success']) {
    echo "<h2>Informações do Pagamento</h2>";
    echo "ID: " . $payment_info['data']['id'] . "<br>";
    echo "Status: " . $payment_info['data']['status'] . "<br>";
    echo "Valor original: R$ " . number_format($payment_info['data']['amount'], 2, ',', '.') . "<br>";
    echo "Valor já reembolsado: R$ " . number_format($payment_info['data']['refunded_amount'], 2, ',', '.') . "<br>";
    echo "Método: " . $payment_info['data']['payment_method'] . "<br>";
    
    // Listar reembolsos existentes
    $refunds = $refund_manager->listRefunds($payment_id);
    if ($refunds['success'] && count($refunds['refunds']) > 0) {
        echo "<h3>Reembolsos realizados:</h3>";
        foreach ($refunds['refunds'] as $refund) {
            echo "- ID: {$refund['id']}, Valor: R$ {$refund['amount']}, Status: {$refund['status']}<br>";
        }
    }
    
    echo "<hr>";
    
    // Realizar reembolso total
    $result = $refund_manager->fullRefund($payment_id);
    
    if ($result['success']) {
        echo "<h3 style='color: green;'>✅ " . $result['message'] . "</h3>";
        echo "ID do reembolso: " . $result['refund_id'] . "<br>";
        echo "Valor reembolsado: R$ " . number_format($result['amount'], 2, ',', '.') . "<br>";
    } else {
        echo "<h3 style='color: red;'>❌ " . $result['message'] . "</h3>";
        if (isset($result['status'])) {
            echo "Status: " . $result['status'] . "<br>";
        }
    }
    
} else {
    echo "❌ " . $payment_info['message'];
}
?>