<?php
session_start();
if(isset($_POST['product_id'])){
    $pid = intval($_POST['product_id']);
    if(isset($_SESSION['cart'][$pid])){
        unset($_SESSION['cart'][$pid]);
    }
}
header("Location: cart.php");
exit;
?>