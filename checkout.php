<?php
session_start();
include "db.php";

if(!isset($_SESSION['user_id'])){
    echo json_encode(["status"=>"error","message"=>"User not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];

if(isset($_POST['cart']) && isset($_POST['full_name']) && isset($_POST['email'])){
    $cart = $_POST['cart']; // JSON array of items
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $zip = $_POST['zip'];
    $payment = $_POST['payment'];

    // Calculate total
    $total_amount = 0;
    foreach($cart as $item){
        $total_amount += $item['price'] * $item['quantity'];
    }

    // Insert order
    $sqlOrder = "INSERT INTO orders (user_id, total_amount, order_status) VALUES (?, ?, 'Pending')";
    $stmt = $conn->prepare($sqlOrder);
    $stmt->bind_param("id", $user_id, $total_amount);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // Insert order items
    $sqlItem = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
    $stmtItem = $conn->prepare($sqlItem);

    foreach($cart as $item){
        $stmtItem->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
        $stmtItem->execute();
    }

    // Clear cart
    $sqlClear = "DELETE FROM cart WHERE user_id=?";
    $stmtClear = $conn->prepare($sqlClear);
    $stmtClear->bind_param("i", $user_id);
    $stmtClear->execute();

    echo json_encode(["status"=>"success","message"=>"Order placed successfully"]);
} else {
    echo json_encode(["status"=>"error","message"=>"Invalid request"]);
}
?>