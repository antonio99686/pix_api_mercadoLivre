<?php
require_once 'vendor/autoload.php';

class MercadoPagoWebhook {
    private $access_token;
    
    public function __construct($access_token) {
        $this->access_token = $access_token;
        MercadoPago\SDK::setAccessToken($access_token);
    }
    
    public function createWebhook($url, $event_types = ['payment']) {
        $curl = curl_init();
        
        $data = array(
            "url" => $url,
            "event_types" => $event_types
        );
        
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.mercadopago.com/v1/webhooks',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->access_token,
                'Content-Type: application/json'
            ],
        ]);
        
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        return [
            'success' => ($http_code == 201 || $http_code == 200),
            'http_code' => $http_code,
            'response' => json_decode($response, true)
        ];
    }
    
    public function listWebhooks() {
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.mercadopago.com/v1/webhooks',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->access_token
            ],
        ]);
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        return json_decode($response, true);
    }
    
    public function deleteWebhook($webhook_id) {
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.mercadopago.com/v1/webhooks/' . $webhook_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->access_token
            ],
        ]);
        
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        return ($http_code == 200);
    }
}

// Usando a classe
try {
    $webhook_manager = new MercadoPagoWebhook('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');
    
    // Criar webhook
    $result = $webhook_manager->createWebhook('http://www.seusite.com/webhook');
    
    if ($result['success']) {
        echo "✅ Webhook criado!<br>";
        echo "ID: " . $result['response']['id'] . "<br>";
    } else {
        echo "❌ Erro ao criar webhook<br>";
        echo "Código HTTP: " . $result['http_code'] . "<br>";
    }
    
    // Listar webhooks existentes
    $webhooks = $webhook_manager->listWebhooks();
    echo "<pre>";
    print_r($webhooks);
    echo "</pre>";
    
} catch (Exception $e) {
    echo '❌ Erro: ' . $e->getMessage();
}
?>