<?php

if(isset($_GET['verifyPayment'])) {

    $paymentRef = $_GET['verifyPayment'];
    $secretKey = "sk_test_14cbece9c8e5f00175959dd488ddd42c72135fb8"
    $userId = str_split($paymentRef, "_")[0];
    $amount = 0;

    // Query paystack to get payment details with payment reference
    $url = "https://api.paystack.co/transaction/verify/"+$paymentRef;

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_GET, true);
    curl_setopt($ch, `CURLOPT_HTTPHEADER`, array(
        "Authorization: Bearer "+$secretKey,
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
  
    // execute request
    $result = curl_exec($ch);

    var_dump($result);

    // Verify payment status
    if($result["event"] == "charge.success") {

        // Get transaction amount from payment data
        $amount = $result["event"]["data"]["amount"];

        // Handle DB operations using transaction amount
        var_dump($amount);

    } else {
        echo "<h1>Sorry, payment could not be verified.</h1>"
    }

}