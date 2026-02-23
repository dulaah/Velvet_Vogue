<?php
session_start();
include "db.php";

/* =========================
   ADD TO CART
========================= */
if(isset($_POST['product_id'])){
    $pid = intval($_POST['product_id']);
    $qty = intval($_POST['quantity']);
    if($qty < 1) $qty = 1;

    if(!isset($_SESSION['cart'])){
        $_SESSION['cart'] = [];
    }

    if(isset($_SESSION['cart'][$pid])){
        $_SESSION['cart'][$pid] += $qty;
    } else {
        $_SESSION['cart'][$pid] = $qty;
    }

    header("Location: products.php");
    exit;
}

/* =========================f
   FETCH PRODUCTS
========================= */
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? 'all';
$price = $_GET['price'] ?? 'all';

$sql = "SELECT * FROM products WHERE 1=1";

if($search != ''){
    $sql .= " AND name LIKE '%".$conn->real_escape_string($search)."%'";
}

if($category != 'all'){
    $sql .= " AND category='".$conn->real_escape_string($category)."'";
}

if($price != 'all'){
    $sql .= " AND price <= ".intval($price);
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Shop | Velvet Vogue</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial}
body{background:#f4f4f4}
header{background:black;color:white;padding:15px 50px;display:flex;justify-content:space-between;align-items:center}
nav a{color:white;text-decoration:none;margin-left:20px}
nav a:hover{color:#ff4081}
.container{padding:40px 50px}
.products{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px}
.product-card{background:white;padding:15px;border-radius:10px;box-shadow:0 4px 8px rgba(0,0,0,0.1)}
.product-card img{width:100%;height:250px;object-fit:cover;border-radius:8px}
.product-card h3{margin:10px 0}
.product-card p{color:#666}
.product-card button{margin-top:10px;padding:10px;width:100%;border:none;background:black;color:white;cursor:pointer}
.filters{display:flex;gap:15px;margin-bottom:30px;flex-wrap:wrap}
.filters input,.filters select{padding:8px}
footer{background:black;color:white;text-align:center;padding:20px;margin-top:40px}
</style>
</head>
<body>

<header>
<h1>Velvet Vogue</h1>
<nav>
<a href="index.php">Home</a>
<a href="products.php">Shop</a>
<a href="cart.php">Cart (<?= isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0 ?>)</a>
<a href="login.php">Login</a>

   
        <a href="contact.html">Contact</a>
</nav>
</header>

<div class="container">
<h2>Shop Our Collection</h2>

<form method="get" class="filters">
    <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
    
    <select name="category">
        <option value="all">All Categories</option>
        <option value="casual" <?= $category=='casual'?'selected':'' ?>>Casual</option>
        <option value="formal" <?= $category=='formal'?'selected':'' ?>>Formal</option>
    </select>

    <select name="price">
        <option value="all">All Prices</option>
        <option value="50" <?= $price=='50'?'selected':'' ?>>Below $50</option>
        <option value="100" <?= $price=='100'?'selected':'' ?>>Below $100</option>
    </select>

    <button type="submit">Filter</button>
</form>

<div class="products">
<?php while($row = $result->fetch_assoc()): ?>
    <div class="product-card">
        <img src="<?= $row['image'] ?>" alt="<?= $row['name'] ?>">
        <h3><?= $row['name'] ?></h3>
        <p>$<?= number_format($row['price'],2) ?></p>

        <form method="post">
            <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
            <input type="number" name="quantity" value="1" min="1" style="width:60px;">
            <button type="submit">Add to Cart</button>
        </form>
    </div>
<?php endwhile; ?>
</div>
</div>

<footer>
<p>&copy; 2026 Velvet Vogue | All Rights Reserved</p>
</footer>
</body>
</html>