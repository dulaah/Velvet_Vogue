<?php
session_start();
include "db.php";

$cart = $_SESSION['cart'] ?? [];
$totalAmount = 0;
$cartItems = [];

foreach($cart as $product_id => $qty){
    $stmt = $conn->prepare("SELECT name, price, image FROM products WHERE id=?");
    $stmt->bind_param("i",$product_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if($row = $res->fetch_assoc()){
        $subtotal = $row['price']*$qty;
        $totalAmount += $subtotal;
        $cartItems[] = [
            'id'=>$product_id,
            'name'=>$row['name'],
            'price'=>$row['price'],
            'image'=>$row['image'],
            'qty'=>$qty,
            'subtotal'=>$subtotal
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Shopping Cart | Velvet Vogue</title>
<style>
 
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f4f4;
        }

        header {
            background: black;
            color: white;
            padding: 15px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }

        .container {
            padding: 40px 50px;
        }

        h2 {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #222;
            color: white;
        }

        img {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }

        button {
            padding: 6px 12px;
            border: none;
            cursor: pointer;
        }

        .qty-btn {
            background: black;
            color: white;
        }

        .remove-btn {
            background: red;
            color: white;
        }

        .total-section {
            margin-top: 20px;
            text-align: right;
        }

        .checkout-btn {
            margin-top: 15px;
            padding: 12px 25px;
            background: #ff4081;
            color: white;
            border: none;
            cursor: pointer;
        }

        footer {
            background: black;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 40px;
        }

        @media(max-width: 768px){
            table, thead, tbody, th, td, tr {
                display: block;
            }

            th {
                display: none;
            }

            td {
                border: none;
                margin-bottom: 10px;
            }
        }
  
</style>
</head>
<body>
<header>
<h1>Velvet Vogue</h1>
<nav>
   <a href="index.php">Home</a>
        <a href="products.php">Shop</a>
        <a href="cart.php">Cart</a>
        <a href="login.php">Login</a>
        <a href="contact.html">Contact</a>
</nav>
</header>

<div class="container">
<h2>Your Shopping Cart</h2>
<table>
<thead>
<tr>
<th>Product</th>
<th>Name</th>
<th>Price</th>
<th>Quantity</th>
<th>Total</th>
<th>Remove</th>
</tr>
</thead>
<tbody>
<?php foreach($cartItems as $item): ?>
<tr>
<td><img src="<?= $item['image'] ?>" width="80" height="80"></td>
<td><?= $item['name'] ?></td>
<td>$<?= number_format($item['price'],2) ?></td>
<td><?= $item['qty'] ?></td>
<td>$<?= number_format($item['subtotal'],2) ?></td>
<td>
<form action="remove_from_cart.php" method="post">
<input type="hidden" name="product_id" value="<?= $item['id'] ?>">
<button type="submit">X</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<div class="total-section">
<h3>Total Amount: $<?= number_format($totalAmount,2) ?></h3>

<form action="checkout.php" method="post" style="display:inline;">
    <button type="submit">Proceed to Checkout</button>
</form>

<form action="clear_cart.php" method="post" style="display:inline;">
    <button type="submit">Clear Cart</button>
</form>
</div>
</div>
</body>
</html>