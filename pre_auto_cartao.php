<?php
// processar_pagamento_correto.php
require_once 'vendor/autoload.php';

// Configure as credenciais do Mercado Pago
MercadoPago\SDK::setAccessToken('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Verificar se os dados vieram via JSON ou formulário
    if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
        $input = json_decode(file_get_contents('php://input'), true);
    } else {
        $input = $_POST;
    }
    
    // Validar dados necessários
    if (isset($input['token']) && isset($input['payment_method_id'])) {
        try {
            $payment = new MercadoPago\Payment();
            $payment->transaction_amount = $input['transaction_amount'] ?? 100;
            $payment->token = $input['token'];
            $payment->description = "Produto Exemplo";
            $payment->installments = $input['installments'] ?? 1;
            $payment->payment_method_id = $input['payment_method_id'];
            
            // Dados do pagador
            $payer = new MercadoPago\Payer();
            $payer->email = $input['email'] ?? "email@exemplo.com";
            
            // Adicionar dados do cartão se disponíveis
            if (isset($input['cardholderName'])) {
                $payer->name = $input['cardholderName'];
            }
            
            $payment->payer = $payer;
            
            // Processar pagamento
            $payment->save();
            
            // Retornar resultado
            if ($payment->status === 'approved') {
                echo "✅ Pagamento aprovado! ID: " . $payment->id;
            } else {
                echo "❌ Status: " . $payment->status . " - Detalhe: " . ($payment->status_detail ?? 'N/A');
            }
            
        } catch (Exception $e) {
            echo "❌ Erro ao processar: " . $e->getMessage();
        }
    } else {
        echo "❌ Dados do formulário não foram enviados corretamente.";
        echo "<br>Dados recebidos: <pre>";
        print_r($input);
        echo "</pre>";
    }
} else {
    // Mostrar formulário se acessado via GET
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Pagamento</title>
        <script src="https://sdk.mercadopago.com/js/v2"></script>
    </head>
    <body>
        <h2>Pagamento com Cartão</h2>
        <div id="card-form"></div>
        <div id="result"></div>
        
        <script>
            const mp = new MercadoPago('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab');
            
            const bricksBuilder = mp.bricks();
            
            bricksBuilder.create("cardPayment", "card-form", {
                initialization: {
                    amount: 100,
                },
                callbacks: {
                    onSubmit: async (cardFormData) => {
                        const response = await fetch(window.location.href, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                            },
                            body: JSON.stringify(cardFormData),
                        });
                        const result = await response.text();
                        document.getElementById('result').innerHTML = '<h3>Resultado:</h3><pre>' + result + '</pre>';
                    },
                    onError: (error) => {
                        console.error(error);
                    },
                },
            });
        </script>
    </body>
    </html>
    <?php
}
?>