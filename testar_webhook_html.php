<!DOCTYPE html>
<html>
<head>
    <title>Testar Webhook</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        textarea { width: 100%; height: 100px; }
        button { padding: 10px 20px; margin-top: 10px; }
    </style>
</head>
<body>
    <h1>Teste de Webhook Mercado Pago</h1>
    
    <form method="post" action="webhook.php" target="_blank">
        <h3>Testar com ID Real:</h3>
        <input type="text" name="id" placeholder="ID do pagamento" style="width: 300px; padding: 5px;">
        <button type="submit">Enviar GET (não funciona)</button>
    </form>
    
    <hr>
    
    <h3>Simular Webhook POST:</h3>
    <div>
        <label>ID do Pagamento:</label>
        <input type="text" id="payment_id" placeholder="Ex: 474362529" style="width: 300px; padding: 5px;">
        <button onclick="testWebhook()">Enviar Simulação POST</button>
    </div>
    
    <div id="result" style="margin-top: 20px; padding: 10px; border: 1px solid #ccc; display: none;">
        <h3>Resposta:</h3>
        <pre id="response"></pre>
    </div>
    
    <script>
        function testWebhook() {
            const paymentId = document.getElementById('payment_id').value;
            if (!paymentId) {
                alert('Digite um ID de pagamento');
                return;
            }
            
            const notification = {
                type: "payment",
                data: {
                    id: paymentId
                }
            };
            
            fetch('webhook.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(notification)
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('response').textContent = data;
                document.getElementById('result').style.display = 'block';
            })
            .catch(error => {
                document.getElementById('response').textContent = 'Erro: ' + error;
                document.getElementById('result').style.display = 'block';
            });
        }
    </script>
    
    <hr>
    
    <h3>Últimos Logs:</h3>
    <pre>
    <?php
    if (file_exists('webhook_log.txt')) {
        echo htmlspecialchars(file_get_contents('webhook_log.txt'));
    } else {
        echo "Nenhum log encontrado.";
    }
    ?>
    </pre>
</body>
</html>