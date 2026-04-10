<?php
require_once 'vendor/autoload.php'; // Caminho para o autoload do Mercado Pago SDK

// Configure as credenciais do Mercado Pago
MercadoPago\SDK::setAccessToken('APP_USR-5067385382129862-070111-7ebd87d96e82e1d80afd27f795bc2571-474362529');

// Crie uma preferência de pagamento
$preference = new MercadoPago\Preference();

$item = new MercadoPago\Item();
$item->title = 'Pagamento de mensalidade';
$item->quantity = 1;
$item->unit_price = 30.00;
$preference->items = array($item);

try {
    $preference->save();
} catch (Exception $e) {
    echo 'Erro ao criar preferência: ',  $e->getMessage(), "\n";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
</head>
<body>
    <?php if (isset($preference->init_point)): ?>
        <a href="<?php echo $preference->init_point; ?>">Pagar</a>
    <?php else: ?>
        <p>Erro ao gerar o link de pagamento. Por favor, tente novamente.</p>
    <?php endif; ?>
</body>
</html>
