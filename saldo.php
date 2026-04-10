<?php
require_once 'vendor/autoload.php'; // Carrega a biblioteca do Mercado Pago

// Configuração das credenciais do Mercado Pago
$clientId = '5067385382129862';
$clientSecret = 'EeYLbCJqTJ9sTn701QOyftxfOOIsgzv0';

try {
    // Configuração do ambiente (produção ou sandbox)
    $mp = new MP($clientId, $clientSecret);

    // Exemplo de consulta de saldo
    $resultado = $mp->get('/v1/account/balance');

    // Verifique se a resposta contém os dados esperados
    if (isset($resultado['response']['total_balance'])) {
        $saldo = $resultado['response']['total_balance'];

        // Conectar ao banco de dados (exemplo usando MySQLi)
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "sentinelas";

        $conn = new mysqli($servername, $username, $password, $dbname);

        // Verificar conexão
        if ($conn->connect_error) {
            die("Conexão falhou: " . $conn->connect_error);
        }

        // Exemplo de inserção do saldo no banco de dados
        $sql = "INSERT INTO saldo_mercado_pago (saldo) VALUES ('$saldo')";

        if ($conn->query($sql) === TRUE) {
            echo "Saldo inserido no banco de dados com sucesso.";
        } else {
            echo "Erro ao inserir saldo no banco de dados: " . $conn->error;
        }

        $conn->close();
    } else {
        echo "Erro: A resposta da API não contém a chave 'total_balance'.";
    }
} catch (MercadoPagoException $e) {
    echo "MercadoPagoException: " . $e->getMessage();
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}
?>
