<?php
session_start();
include "db.php";

$cart = $_SESSION['cart'] ?? [];

if(!$cart){
    echo "Your cart is empty! <a href='products.php'>Shop now</a>";
    exit;
}

// Fetch product info from DB
$itemsList = [];
$totalAmount = 0;

foreach($cart as $pid => $qty){
    $stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id=?");
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $res = $stmt->get_result();
    if($row = $res->fetch_assoc()){
        $subtotal = $row['price'] * $qty;
        $totalAmount += $subtotal;
        $itemsList[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'image' => $row['image'],
            'qty' => $qty,
            'subtotal' => $subtotal
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Checkout | Velvet Vogue</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
/* Simple CSS for layout */
body{font-family:Arial;background:#f4f4f4;margin:0;padding:0}
header{background:black;color:white;padding:15px 50px;display:flex;justify-content:space-between;align-items:center}
nav a{color:white;text-decoration:none;margin-left:20px}
nav a:hover{color:#ff4081}
.container{max-width:900px;margin:40px auto;padding:20px}
.cart-table, .cart-table th, .cart-table td{border:1px solid #ddd;border-collapse:collapse;width:100%;background:white}
.cart-table th, .cart-table td{padding:12px;text-align:center}
.cart-table th{background:#222;color:white}
form{background:white;padding:20px;border-radius:10px;margin-top:20px}
form div{margin-bottom:15px}
form label{display:block;margin-bottom:5px}
form input, form select{width:100%;padding:8px}
.place-order-btn{padding:12px 25px;background:#ff4081;color:white;border:none;cursor:pointer;width:100%}
footer{background:black;color:white;text-align:center;padding:20px;margin-top:40px}
</style>
</head>
<body>

<header>
<h1>Velvet Vogue</h1>
<nav>
<a href="index.php">Home</a>
<a href="products.php">Shop</a>
<a href="cart.php">Cart (<?= array_sum($_SESSION['cart']) ?>)</a>
<a href="login.php">Login</a>
</nav>
</header>

<div class="container">
<h2>Checkout</h2>

<table class="cart-table">
    <thead>
        <tr>
            <th>Product</th>
            <th>Name</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($itemsList as $item): ?>
        <tr>
            <td><img src="<?= $item['image'] ?>" width="80"></td>
            <td><?= $item['name'] ?></td>
            <td>$<?= number_format($item['price'],2) ?></td>
            <td><?= $item['qty'] ?></td>
            <td>$<?= number_format($item['subtotal'],2) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h3>Total Amount: $<?= number_format($totalAmount,2) ?></h3>

<form action="place_order.php" method="post">
    <div>
        <label for="fullName">Full Name</label>
        <input type="text" name="fullName" id="fullName" required>
    </div>
    <div>
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>
    </div>
    <div>
        <label for="address">Shipping Address</label>
        <input type="text" name="address" id="address" required>
    </div>
    <div>
        <label for="city">City</label>
        <input type="text" name="city" id="city" required>
    </div>
    <div>
        <label for="zip">ZIP / Postal Code</label>
        <input type="text" name="zip" id="zip" required>
    </div>
    <div>
        <label for="payment">Payment Method</label>
        <select name="payment" id="payment" required>
            <option value="">Select Payment</option>
            <option value="card">Credit / Debit Card</option>
            <option value="paypal">PayPal</option>
            <option value="cod">Cash on Delivery</option>
        </select>
    </div>
    <input type="hidden" name="totalAmount" value="<?= $totalAmount ?>">
    <button type="submit" class="place-order-btn">Place Order</button>
</form>

</div>

<footer>
<p>&copy; 2026 Velvet Vogue | All Rights Reserved</p>
</footer>

</body>
</html>