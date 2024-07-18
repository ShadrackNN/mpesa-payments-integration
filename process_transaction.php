<?php
require 'config.php';  // include your database connection here

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $msisdn = $_POST['msisdn'];
    $items = json_decode($_POST['items'], true);
    $totalAmount = 0;

    // Calculate total amount
    foreach ($items as $item) {
        $totalAmount += $item['total'];
    }

    // Process the transaction with M-Pesa API
    $mpesaResponse = initiateMpesaPayment($msisdn, $totalAmount);

    if ($mpesaResponse['status'] == 'Success') {
        $transaction_id = $mpesaResponse['transaction_id'];

        // Insert each product in the transactions table
        foreach ($items as $item) {
            $stmt = $conn->prepare("INSERT INTO transactions (product_id, amount, msisdn, transaction_status, transaction_id) VALUES (?, ?, ?, 'Pending', ?)");
            $stmt->bind_param("iisi", $item['id'], $item['total'], $msisdn, $transaction_id);
            $stmt->execute();
        }

        // Redirect or display success message
        echo "Transaction completed successfully.";
    } else {
        echo "Transaction failed.";
    }
}
