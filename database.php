<?php
require_once __DIR__ . '/config.php';

function updatePaymentInDatabase($payment_id, $status, $amount, $payer_email) {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE payment_id = :payment_id");
        $stmt->execute([':payment_id' => $payment_id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $sql = "UPDATE payments SET status = :status, updated_at = NOW() WHERE payment_id = :payment_id";
        } else {
            $sql = "INSERT INTO payments (payment_id, status, amount, payer_email, created_at) 
                    VALUES (:payment_id, :status, :amount, :payer_email, NOW())";
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':payment_id' => $payment_id,
            ':status' => $status,
            ':amount' => $amount,
            ':payer_email' => $payer_email
        ]);
        
        return true;
        
    } catch (PDOException $e) {
        file_put_contents(__DIR__ . '/logs/db_errors.txt', date('Y-m-d H:i:s') . " - " . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    }
}
?>