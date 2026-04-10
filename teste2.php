<?php
echo "Diretório atual: " . __DIR__ . "<br>";
echo "Caminho completo do autoload: " . __DIR__ . "/vendor/autoload.php" . "<br>";

if (file_exists(__DIR__ . "/vendor/autoload.php")) {
    echo "✅ vendor/autoload.php encontrado!<br>";
    require __DIR__ . "/vendor/autoload.php";
    echo "✅ SDK carregado com sucesso!";
} else {
    echo "❌ vendor/autoload.php NÃO encontrado!<br>";
    echo "Execute: composer require mercadopago/sdk";
}
?>