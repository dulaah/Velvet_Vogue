<?php
session_start();
include "db.php";

if(!isset($_SESSION['user_id'])){
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT c.id as cart_id, p.id as product_id, p.name, p.price, p.image, c.quantity
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart = [];
while($row = $result->fetch_assoc()){
    $cart[] = $row;
}

echo json_encode($cart);
?>