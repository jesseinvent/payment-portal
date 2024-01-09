<?php

if (isset($_GET['verifyPayment'])) {
    $paymentRef = $_GET['verifyPayment'];

    $secretKey = "sk_test_14cbece9c8e5f00175959dd488ddd42c72135fb8";

    $userId = explode("_", $_GET['verifyPayment'])[0];
    $amount = 0;

    $url = "https://api.paystack.co/transaction/verify/" . $paymentRef;

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: Bearer " . $secretKey,
        "Cache-Control: no-cache",
    ));

    $response = curl_exec($ch);

    curl_close($ch);

    $result = json_decode($response, true);

    if ($result) {
        if ($result['data']) {
            if ($result['data']['status'] == "success") {
                $amount = $result['data']['amount'];
                $currency = $result['data']['currency'];

                if ($currency == "NGN") {
                    $amount = $amount / 100; // Divide by 100 to get the actual amount
                }

                echo "<h3>Payment Successful</h3>";
                echo "<p>Amount: " . $amount . "</p>";
                echo "<p>Currency: " . $currency . "</p>";

                // Do something with the amount, e.g. update the user's balance
            }

            if ($result['data']['status'] == "failed") {
                echo "<h3>Payment Failed</h3>";
            }

            if ($result['data']['status'] == "timeout") {
                echo "<h3>Payment Timeout</h3>";
            }

            if ($result['data']['status'] == "reversed") {
                echo "<h3>Payment Reversed</h3>";
            }

            if ($result['data']['status'] == "cancelled") {
                echo "<h3>Payment Cancelled</h3>";
            }
        }
    }
}
