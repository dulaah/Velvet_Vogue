<?php
session_start();
include "db.php";

if(!isset($_SESSION['user_id'])){
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit;
}

if(isset($_POST['product_id']) && isset($_POST['quantity'])){
    $user_id = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    // Check if product already in cart
    $sqlCheck = "SELECT * FROM cart WHERE user_id=? AND product_id=?";
    $stmt = $conn->prepare($sqlCheck);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        // Update quantity
        $sqlUpdate = "UPDATE cart SET quantity = quantity + ? WHERE user_id=? AND product_id=?";
        $stmt2 = $conn->prepare($sqlUpdate);
        $stmt2->bind_param("iii", $quantity, $user_id, $product_id);
        $stmt2->execute();
        echo json_encode(["status" => "success", "message" => "Cart updated"]);
    } else {
        // Insert new row
        $sqlInsert = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt3 = $conn->prepare($sqlInsert);
        $stmt3->bind_param("iii", $user_id, $product_id, $quantity);
        $stmt3->execute();
        echo json_encode(["status" => "success", "message" => "Product added to cart"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
?>