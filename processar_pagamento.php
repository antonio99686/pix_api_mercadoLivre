<?php
// processar_pagamento.php
require_once __DIR__ . '/vendor/autoload.php';

// Configurar cabeçalho para JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configurar credenciais
MercadoPago\SDK::setAccessToken('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');

// Receber dados JSON
$input = json_decode(file_get_contents('php://input'), true);

// Log para debug
$log = date('Y-m-d H:i:s') . " - Dados recebidos: " . print_r($input, true) . "\n";
file_put_contents('debug_log.txt', $log, FILE_APPEND);

// Verificar se os dados foram recebidos
if (!$input) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Nenhum dado recebido'
    ]);
    exit;
}

// Verificar token
if (!isset($input['token']) || empty($input['token'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Token do cartão não fornecido'
    ]);
    exit;
}

try {
    // Criar pagamento
    $payment = new MercadoPago\Payment();
    $payment->transaction_amount = floatval($input['transaction_amount'] ?? 100);
    $payment->token = $input['token'];
    $payment->description = $input['description'] ?? "Produto Exemplo";
    $payment->installments = intval($input['installments'] ?? 1);
    $payment->payment_method_id = $input['payment_method_id'] ?? "master";
    
    // Configurar pagador
    $payer = new MercadoPago\Payer();
    $payer->email = $input['email'] ?? "cliente@exemplo.com";
    $payment->payer = $payer;
    
    // Salvar pagamento
    $payment->save();
    
    // Log do resultado
    $log = date('Y-m-d H:i:s') . " - Pagamento criado: ID={$payment->id}, Status={$payment->status}\n";
    file_put_contents('debug_log.txt', $log, FILE_APPEND);
    
    // Retornar resultado
    $response = [
        'status' => $payment->status,
        'id' => $payment->id,
        'status_detail' => $payment->status_detail ?? null,
        'message' => 'Pagamento processado'
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    $log = date('Y-m-d H:i:s') . " - Erro: " . $e->getMessage() . "\n";
    file_put_contents('error_log.txt', $log, FILE_APPEND);
    
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>