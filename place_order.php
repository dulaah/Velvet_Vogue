<?php
session_start();
include "db.php";

// Check if cart exists
$cart = $_SESSION['cart'] ?? [];
if(!$cart){
    die("Your cart is empty! <a href='products.php'>Shop now</a>");
}

// Collect customer info from POST
$fullName = $_POST['fullName'] ?? '';
$email    = $_POST['email'] ?? '';
$address  = $_POST['address'] ?? '';
$city     = $_POST['city'] ?? '';
$zip      = $_POST['zip'] ?? '';
$payment  = $_POST['payment'] ?? '';
$totalAmount = $_POST['totalAmount'] ?? 0;

// if(empty($fullName) || empty($email) || empty($address) || empty($city) || empty($zip) || empty($payment)){
//     echo "<div style='
//         background:#ffe0e0;
//         color:#c00;
//         padding:15px;
//         margin:20px;
//         border-radius:6px;
//         text-align:center;
//         font-weight:bold;
//     '>
//         All fields are required!
//     </div>";
//     exit;
// }

// Insert into orders table
$stmt = $conn->prepare("INSERT INTO orders (customer_name, email, address, city, zip, payment_method, total_amount, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("ssssssd", $fullName, $email, $address, $city, $zip, $payment, $totalAmount);
$stmt->execute();
$orderId = $stmt->insert_id;

// Insert each product into order_items table
foreach($cart as $pid => $qty){
    // Fetch product price from DB
    $productStmt = $conn->prepare("SELECT price FROM products WHERE id=?");
    $productStmt->bind_param("i",$pid);
    $productStmt->execute();
    $res = $productStmt->get_result();
    if($row = $res->fetch_assoc()){
        $price = $row['price'];
        $subtotal = $price * $qty;

        $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
        $itemStmt->bind_param("iiidd", $orderId, $pid, $qty, $price, $subtotal);
        $itemStmt->execute();
    }
}

// Clear cart session
unset($_SESSION['cart']);

// echo "<h2>Thank you! Your order has been placed successfully.</h2>";
echo "<p><a href='index.php'>Go back to Home</a></p>";
?>