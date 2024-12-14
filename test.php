<?php

require('assets/includes/config.php');

$mysqli = new mysqli($db_host, $db_username, $db_password,$db_name);

if (mysqli_connect_errno()) {
    exit(mysqli_connect_error());
}

file_put_contents('ccbill_postback_log.txt', print_r($_POST, true), FILE_APPEND);

//test data
// $transactionId = "04034"; 
// $responseCode  = "";
// $email         = "miakhalifa24@gmail.com";        
// $amount        = "500";       
// $subscriptionId = "234235"; 
// $username      = "26";      
// $user_id      = "26"; 

$transactionId = $_POST['transactionId'] ?? null; 
$responseCode  = $_POST['reasonForDecline'] ?? null; 
$email         = $_POST['email'] ?? null;        
$amount        = $_POST['accountingAmount'] ?? null;       
$subscriptionId = $_POST['subscription_id'] ?? null; 
$username      = $_POST['user_id'] ?? null;      
$user_id      = $_POST['user_id'] ?? null;      
 
if (empty($responseCode)) {
    file_put_contents('ccbill_approved_log.txt', "Payment approved: Email=$email, TransactionID=$transactionId, Amount=$amount, UserID=$username\n", FILE_APPEND);
    echo " Transaction was successful";

    if ($email !== null && $amount !== null) {
        $stmt = $mysqli->prepare("SELECT credits FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

		
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $currentCredits = $user['credits'];
            $newCredits = $currentCredits + (float)$amount;
            $updateStmt = $mysqli->prepare("UPDATE users SET credits = ? WHERE email = ?");
            $updateStmt->bind_param("ds", $newCredits, $email); 
            if ($updateStmt->execute()) {
                echo "<script>alert('Transaction successful! Credits have been updated.');</script>";;
                file_put_contents('ccbill_approved_log.txt', "Payment approved: Email Address=$email, TransactionID=$transactionId, Amount=$amount, NewCredits=$newCredits\n", FILE_APPEND);

                // New functionality: Insert into 'orders' table
                $order_type = 'credits';
                $order_gateway = 'CCBill';
                $order_status = 'success';
                $order_date = date('Y-m-d H:i:s');
                $raw_data = json_encode($_POST); // Storing raw POST data as JSON
                $order_proof = $transactionId; // Using transaction ID as order proof
                $order_title = 'Payment for Subscription';

                // Determine order_package based on amount
                $order_package = null;
                if ($amount == 10) {
                    $order_package = 1;
                } elseif ($amount == 25) {
                    $order_package = 2;
                } elseif ($amount == 50) {
                    $order_package = 3;
                } elseif ($amount == 100) {
                    $order_package = 4;
                } elseif ($amount == 250) {
                    $order_package = 5;
                } elseif ($amount == 500) {
                    $order_package = 6;
                }

                // Fetch the current max order_id (if needed for other purposes)
                // $result = $mysqli->query("SELECT MAX(order_id) AS max_id FROM orders");
                // $row = $result->fetch_assoc();
                // $order_id = $row['max_id'] == NULL ?  1 : $row['max_id'] + 1;
                $order_date = date('Y-m-d H:i:s');
                // Prepare the SQL query (without order_id, as it's AUTO_INCREMENT)
                $orderStmt = $mysqli->prepare("INSERT INTO orders (
                    user_id, 
                    order_type, 
                    order_package, 
                    order_gateway, 
                    order_status, 
                    order_date, 
                    raw_data, 
                    order_proof, 
                    order_proof_img, 
                    order_title, 
                    order_custom
                ) VALUES (?,?, ?, ?, ?, ?, ?, ?, NULL, ?, NULL)");

                // Check for preparation errors
                if (!$orderStmt) {
                    die("Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error);
                }
                // $order_package = $order_package-1;
                // Bind parameters
                $orderStmt->bind_param(
                    "iiissssss", // 9 placeholders
                    // $order_id,
                    $user_id,     // 1: Integer
                    $order_type,  // 2: String
                    $order_package, // 3: Integer
                    $order_gateway, // 4: String
                    $order_status,  // 5: String
                    $order_date,    // 6: String
                    $raw_data,      // 7: String
                    $order_proof,   // 8: String
                    $order_title    // 9: String
                );

                

                if ($orderStmt->execute()) {
                    file_put_contents('ccbill_approved_log.txt', "Order inserted successfully: UserID=$user_id, Amount=$amount, Package=$order_package\n", FILE_APPEND);
                } else {
                    file_put_contents('ccbill_error_log.txt', "Failed to insert order: UserID=$user_id, TransactionID=$transactionId\n", FILE_APPEND);
                }

            } else {
                echo "<script>alert('Transaction successful, but failed to update credits.');</script>";
                file_put_contents('ccbill_error_log.txt', "Failed to update credits for Email Address=$email, TransactionID=$transactionId\n", FILE_APPEND);
            }

            $updateStmt->close();
        } else {
            echo "<script>alert('Transaction successful, but user not found.');</script>";
            file_put_contents('ccbill_error_log.txt', "User not found: Username=$username, TransactionID=$transactionId\n", FILE_APPEND);
        }

        $stmt->close();
    }
    echo "SUCCESS";

} else {
    echo "<script>alert('Transaction failed! Reason: $responseCode');</script>";
    file_put_contents('ccbill_unknown_log.txt', "Unknown response: " . print_r($_POST, true), FILE_APPEND);
}