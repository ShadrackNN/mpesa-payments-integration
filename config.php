<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pos_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to initiate M-Pesa payment
function initiateMpesaPayment($msisdn, $amount) {
    // Your M-Pesa API credentials
    $consumerKey = "YOUR_CONSUMER_KEY";
    $consumerSecret = "YOUR_CONSUMER_SECRET";
    $shortCode = "YOUR_SHORT_CODE";
    $lipaNaMpesaOnlinePasskey = "YOUR_PASSKEY";
    $callbackUrl = "YOUR_CALLBACK_URL";

    // Generate a timestamp
    $timestamp = date("YmdHis");

    // Generate the password
    $password = base64_encode($shortCode . $lipaNaMpesaOnlinePasskey . $timestamp);

    // M-Pesa API endpoint
    $url = "https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest";

    // Prepare the request
    $data = [
        "BusinessShortCode" => $shortCode,
        "Password" => $password,
        "Timestamp" => $timestamp,
        "TransactionType" => "CustomerPayBillOnline",
        "Amount" => $amount,
        "PartyA" => $msisdn,
        "PartyB" => $shortCode,
        "PhoneNumber" => $msisdn,
        "CallBackURL" => $callbackUrl,
        "AccountReference" => "POS System",
        "TransactionDesc" => "Payment for POS System"
    ];

    $dataString = json_encode($data);

    // Get OAuth token
    $tokenUrl = "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
    $credentials = base64_encode($consumerKey . ":" . $consumerSecret);

    $headers = [
        "Authorization: Basic " . $credentials
    ];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $tokenUrl);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $curl_response = curl_exec($curl);
    $response = json_decode($curl_response);

    $accessToken = $response->access_token;

    // Make the M-Pesa request
    $headers = [
        "Content-Type: application/json",
        "Authorization: Bearer " . $accessToken
    ];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $dataString);

    $curl_response = curl_exec($curl);

    // Process the response
    $response = json_decode($curl_response, true);

    if (isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
        return [
            'status' => 'Success',
            'transaction_id' => $response['CheckoutRequestID']
        ];
    } else {
        return [
            'status' => 'Failure',
            'message' => isset($response['errorMessage']) ? $response['errorMessage'] : 'Unknown error'
        ];
    }
}
?>
