<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php'; // <-- ADICIONE A BARRA ANTES DE vendor

MercadoPago\SDK::setAccessToken('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');

try {
    $preference = new MercadoPago\Preference();
    
    $item = new MercadoPago\Item();
    $item->title = 'Mensalidade Referente a esse Mês';
    $item->quantity = 1;
    $item->currency_id = 'BRL';
    $item->unit_price = 30.00;
    
    $preference->items = array($item);
    
    // Opcional: URLs de retorno (não obrigatório sem auto_return)
    $preference->back_urls = array(
        'success' => 'http://localhost/pix_api_mercadoLivre/success.php',
        'failure' => 'http://localhost/pix_api_mercadoLivre/failure.php',
        'pending' => 'http://localhost/pix_api_mercadoLivre/pending.php'
    );
    
    $preference->save();
    
    if ($preference->id) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Pagamento - Mensalidade</title>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    margin: 0;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                }
                .container {
                    background: white;
                    padding: 40px;
                    border-radius: 20px;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                    text-align: center;
                    max-width: 450px;
                    width: 90%;
                }
                h1 {
                    color: #333;
                    margin-bottom: 10px;
                }
                .amount {
                    font-size: 48px;
                    color: #4CAF50;
                    margin: 20px 0;
                    font-weight: bold;
                }
                .description {
                    color: #666;
                    margin-bottom: 30px;
                }
                .btn-pagar {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 15px 40px;
                    border: none;
                    border-radius: 50px;
                    font-size: 18px;
                    cursor: pointer;
                    text-decoration: none;
                    display: inline-block;
                    transition: transform 0.3s, box-shadow 0.3s;
                }
                .btn-pagar:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
                }
                .info {
                    margin-top: 30px;
                    color: #888;
                    font-size: 12px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Pagamento de Mensalidade</h1>
                <div class="amount">R$ 30,00</div>
                <div class="description">Mensalidade referente a este mês</div>
                <a href="<?php echo $preference->init_point; ?>" class="btn-pagar" target="_blank">
                    Pagar Agora
                </a>
                <div class="info">
                    <p>✓ Pagamento 100% seguro</p>
                    <p>✓ Aceita cartões de crédito, débito e PIX</p>
                    <p>✓ Parcele em até 12x</p>
                </div>
            </div>
        </body>
        </html>
        <?php
    } else {
        throw new Exception('Erro ao criar preferência');
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; text-align: center; padding: 20px;'>";
    echo "❌ Erro: " . $e->getMessage();
    echo "</div>";
}
?>