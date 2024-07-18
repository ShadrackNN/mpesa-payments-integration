<?php
$callback_data = json_decode(file_get_contents('php://input'), true);

$transaction_id = $callback_data['Body']['stkCallback']['CheckoutRequestID'];
$result_code = $callback_data['Body']['stkCallback']['ResultCode'];
$result_desc = $callback_data['Body']['stkCallback']['ResultDesc'];

$conn = new mysqli('localhost', 'root', '', 'pos_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($result_code == 0) {
    $mpesa_receipt_number = $callback_data['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value'];
    $transaction_date = $callback_data['Body']['stkCallback']['CallbackMetadata']['Item'][3]['Value'];
    $phone_number = $callback_data['Body']['stkCallback']['CallbackMetadata']['Item'][4]['Value'];

    $stmt = $conn->prepare("UPDATE transactions SET transaction_status = 'Completed', mpesa_receipt_number = ?, transaction_date = ? WHERE transaction_id = ?");
    $stmt->bind_param('sss', $mpesa_receipt_number, $transaction_date, $transaction_id);
} else {
    $stmt = $conn->prepare("UPDATE transactions SET transaction_status = 'Failed', result_desc = ? WHERE transaction_id = ?");
    $stmt->bind_param('ss', $result_desc, $transaction_id);
}
$stmt->execute();

