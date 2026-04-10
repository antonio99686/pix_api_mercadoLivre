<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

MercadoPago\SDK::setAccessToken('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');

try {
    $preference = new MercadoPago\Preference();
    
    $item = new MercadoPago\Item();
    $item->title = 'Mensalidade Referente a esse Mês';
    $item->quantity = 1;
    $item->currency_id = 'BRL';
    $item->unit_price = 30.00;
    
    $preference->items = array($item);
    
    // Configuração completa sem auto_return
    $preference->back_urls = array(
        'success' => 'http://localhost/pix_api_mercadoLivre/success.php',
        'failure' => 'http://localhost/pix_api_mercadoLivre/failure.php',
        'pending' => 'http://localhost/pix_api_mercadoLivre/pending.php'
    );
    
    $preference->save();
    
    if ($preference->id) {
        // Mostrar link em vez de redirecionar automaticamente
        echo "<h2>✅ Pagamento criado com sucesso!</h2>";
        echo "<p><strong>ID da Preferência:</strong> " . $preference->id . "</p>";
        echo "<p><strong>Link para pagamento:</strong> <a href='" . $preference->init_point . "' target='_blank'>Clique aqui para pagar</a></p>";
        echo "<p><strong>QR Code (se for PIX):</strong> " . ($preference->point_of_interaction->transaction_data->qr_code ?? 'N/A') . "</p>";
    } else {
        throw new Exception('Erro ao criar preferência');
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 20px; border: 1px solid red; margin: 20px;'>";
    echo "<h3>❌ Erro ao criar pagamento</h3>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    
    if (isset($preference->error)) {
        echo "<p><strong>Detalhes do erro:</strong></p>";
        echo "<pre>";
        print_r($preference->error);
        echo "</pre>";
    }
    echo "</div>";
}
?>  