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

/* =========================
   FETCH PRODUCTS
========================= */
$search   = $_GET['search']   ?? '';
$category = $_GET['category'] ?? 'all';
$price    = $_GET['price']    ?? 'all';

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
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
    :root {
        --black:  #0a0a0a;
        --white:  #f8f5f0;
        --cream:  #ede9e1;
        --gold:   #c9a96e;
        --rose:   #c96b6b;
        --muted:  #7a7169;
    }

    * { margin:0; padding:0; box-sizing:border-box; }

    body {
        background: var(--white);
        color: var(--black);
        font-family: 'Montserrat', sans-serif;
        font-weight: 300;
        overflow-x: hidden;
    }

    /* ─── HEADER ─────────────────────────────── */
    header {
        position: fixed;
        top: 0; left: 0;
        width: 100%;
        z-index: 100;
        padding: 22px 60px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(10,10,10,0.92);
        backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(201,169,110,0.2);
        animation: slideDown 0.7s ease forwards;
    }

    @keyframes slideDown {
        from { transform: translateY(-100%); opacity: 0; }
        to   { transform: translateY(0);     opacity: 1; }
    }

    .logo {
        font-family: 'Cormorant Garamond', serif;
        font-size: 26px;
        font-weight: 600;
        letter-spacing: 4px;
        color: var(--white);
        text-transform: uppercase;
        text-decoration: none;
    }
    .logo span { color: var(--gold); }

    nav { display: flex; gap: 36px; align-items: center; }

    nav a {
        color: rgba(248,245,240,0.75);
        text-decoration: none;
        font-size: 11px;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        font-weight: 500;
        transition: color 0.3s;
        position: relative;
    }
    nav a::after {
        content: '';
        position: absolute;
        bottom: -4px; left: 0;
        width: 0; height: 1px;
        background: var(--gold);
        transition: width 0.3s ease;
    }
    nav a:hover           { color: var(--gold); }
    nav a:hover::after    { width: 100%; }
    nav a.active          { color: var(--gold); }
    nav a.active::after   { width: 100%; }

    .cart-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--rose);
        color: white;
        font-size: 9px;
        font-weight: 600;
        width: 18px; height: 18px;
        border-radius: 50%;
        margin-left: 5px;
        vertical-align: middle;
    }

    /* ─── PAGE HERO BANNER ───────────────────── */
    .page-banner {
        padding-top: 92px; /* header height */
        background: var(--black);
        text-align: center;
        padding-bottom: 60px;
        padding-top: 130px;
        position: relative;
        overflow: hidden;
    }

    .page-banner::before {
        content: 'SHOP';
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        font-family: 'Cormorant Garamond', serif;
        font-size: 220px;
        font-weight: 600;
        color: rgba(255,255,255,0.03);
        letter-spacing: 30px;
        white-space: nowrap;
        pointer-events: none;
    }

    .page-banner .eyebrow {
        font-size: 10px;
        letter-spacing: 5px;
        text-transform: uppercase;
        color: var(--gold);
        margin-bottom: 14px;
        font-weight: 500;
    }

    .page-banner h2 {
        font-family: 'Cormorant Garamond', serif;
        font-size: clamp(38px, 5vw, 64px);
        font-weight: 300;
        color: var(--white);
        line-height: 1.1;
    }

    .page-banner h2 em {
        font-style: italic;
        color: var(--gold);
    }

    /* ─── FILTERS ────────────────────────────── */
    .filter-bar {
        background: var(--cream);
        border-bottom: 1px solid rgba(0,0,0,0.08);
        padding: 22px 80px;
        position: sticky;
        top: 68px;
        z-index: 50;
        animation: fadeIn 0.6s 0.3s ease both;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .filter-form {
        display: flex;
        gap: 14px;
        align-items: center;
        flex-wrap: wrap;
    }

    .filter-form input,
    .filter-form select {
        padding: 10px 16px;
        border: 1px solid rgba(0,0,0,0.15);
        background: var(--white);
        color: var(--black);
        font-family: 'Montserrat', sans-serif;
        font-size: 11px;
        letter-spacing: 1px;
        outline: none;
        transition: border-color 0.3s;
        -webkit-appearance: none;
        appearance: none;
    }

    .filter-form input:focus,
    .filter-form select:focus {
        border-color: var(--gold);
    }

    .filter-form input {
        min-width: 220px;
    }

    .filter-form select {
        padding-right: 36px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%237a7169'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        cursor: pointer;
    }

    .filter-form .btn-filter {
        padding: 10px 30px;
        background: var(--black);
        color: var(--white);
        border: none;
        font-family: 'Montserrat', sans-serif;
        font-size: 10px;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.3s;
    }

    .filter-form .btn-filter:hover {
        background: var(--gold);
        color: var(--black);
    }

    .result-count {
        margin-left: auto;
        font-size: 10px;
        letter-spacing: 1.5px;
        color: var(--muted);
        text-transform: uppercase;
        white-space: nowrap;
    }

    /* ─── MAIN CONTENT ───────────────────────── */
    .container {
        padding: 70px 80px 110px;
    }

    /* ─── PRODUCT GRID ───────────────────────── */
    .products {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 36px 28px;
    }

    .product-card {
        background: transparent;
        cursor: pointer;
        animation: fadeUp 0.5s ease both;
    }

    @keyframes fadeUp {
        from { transform: translateY(24px); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }

    /* Stagger child cards */
    .product-card:nth-child(1)  { animation-delay: 0.05s; }
    .product-card:nth-child(2)  { animation-delay: 0.10s; }
    .product-card:nth-child(3)  { animation-delay: 0.15s; }
    .product-card:nth-child(4)  { animation-delay: 0.20s; }
    .product-card:nth-child(5)  { animation-delay: 0.25s; }
    .product-card:nth-child(6)  { animation-delay: 0.30s; }
    .product-card:nth-child(n+7){ animation-delay: 0.35s; }

    .product-img-wrap {
        position: relative;
        overflow: hidden;
        aspect-ratio: 3/4;
        background: var(--cream);
        margin-bottom: 18px;
    }

    .product-img-wrap img {
        width: 100%; height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.7s ease;
    }

    .product-card:hover .product-img-wrap img {
        transform: scale(1.06);
    }

    .product-overlay {
        position: absolute;
        inset: 0;
        background: rgba(10,10,10,0);
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 20px;
        gap: 10px;
        transition: background 0.4s ease;
    }

    .product-card:hover .product-overlay {
        background: rgba(10,10,10,0.3);
    }

    .overlay-form {
        display: flex;
        gap: 8px;
        transform: translateY(12px);
        opacity: 0;
        transition: transform 0.3s, opacity 0.3s;
    }

    .product-card:hover .overlay-form {
        transform: translateY(0);
        opacity: 1;
    }

    .qty-input {
        width: 54px;
        padding: 12px 10px;
        border: none;
        background: rgba(248,245,240,0.95);
        font-family: 'Montserrat', sans-serif;
        font-size: 12px;
        text-align: center;
        color: var(--black);
        outline: none;
    }

    .overlay-btn {
        flex: 1;
        padding: 12px 10px;
        background: var(--white);
        color: var(--black);
        border: none;
        font-family: 'Montserrat', sans-serif;
        font-size: 10px;
        letter-spacing: 2px;
        text-transform: uppercase;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.25s, color 0.25s;
        white-space: nowrap;
    }

    .overlay-btn:hover {
        background: var(--gold);
        color: var(--black);
    }

    .product-info {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-bottom: 5px;
    }

    .product-name {
        font-family: 'Cormorant Garamond', serif;
        font-size: 20px;
        font-weight: 400;
    }

    .product-price {
        font-size: 13px;
        color: var(--muted);
        font-weight: 400;
    }

    .product-category {
        font-size: 10px;
        letter-spacing: 1.5px;
        color: var(--muted);
        text-transform: uppercase;
    }

    /* ─── EMPTY STATE ────────────────────────── */
    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 100px 20px;
    }

    .empty-state h3 {
        font-family: 'Cormorant Garamond', serif;
        font-size: 36px;
        font-weight: 300;
        margin-bottom: 14px;
    }

    .empty-state p {
        font-size: 12px;
        color: var(--muted);
        letter-spacing: 1px;
    }

    /* ─── FOOTER ─────────────────────────────── */
    footer {
        background: var(--black);
        color: rgba(248,245,240,0.4);
        text-align: center;
        padding: 30px;
        font-size: 10px;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        border-top: 1px solid rgba(201,169,110,0.1);
    }

    footer span { color: var(--gold); }

    /* ─── RESPONSIVE ─────────────────────────── */
    @media (max-width: 900px) {
        header      { padding: 18px 30px; }
        .filter-bar { padding: 18px 30px; }
        .container  { padding: 50px 30px 80px; }
    }

    @media (max-width: 600px) {
        nav { display: none; }
        .result-count { display: none; }
    }
</style>
</head>
<body>

<!-- HEADER -->
<header>
    <a href="index.php" class="logo">Velvet <span>Vogue</span></a>
    <nav>
        <a href="index.php">Home</a>
        <a href="products.php" class="active">Shop</a>
        <a href="cart.php">
            Cart
            <?php $cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?>
            <?php if($cartCount > 0): ?>
                <span class="cart-count"><?= $cartCount ?></span>
            <?php endif; ?>
        </a>
        <a href="login.php">Login</a>
        <a href="contact.html">Contact</a>
    </nav>
</header>

<!-- PAGE BANNER -->
<div class="page-banner">
    <p class="eyebrow">Spring / Summer 2026</p>
    <h2>Shop Our <em>Collection</em></h2>
</div>

<!-- FILTER BAR -->
<div class="filter-bar">
    <form method="get" class="filter-form">
        <input type="text" name="search" placeholder="Search pieces..." value="<?= htmlspecialchars($search) ?>">

        <select name="category">
            <option value="all">All Categories</option>
            <option value="casual"  <?= $category=='casual' ?'selected':'' ?>>Casual</option>
            <option value="formal"  <?= $category=='formal' ?'selected':'' ?>>Formal</option>
        </select>

        <select name="price">
            <option value="all">All Prices</option>
            <option value="50"  <?= $price=='50' ?'selected':'' ?>>Under $50</option>
            <option value="100" <?= $price=='100'?'selected':'' ?>>Under $100</option>
        </select>

        <button type="submit" class="btn-filter">Filter</button>

        <span class="result-count">
            <?php
                $count = $result ? $result->num_rows : 0;
                echo $count . ' ' . ($count === 1 ? 'Piece' : 'Pieces') . ' Found';
            ?>
        </span>
    </form>
</div>

<!-- PRODUCTS -->
<div class="container">
    <div class="products">
    <?php if($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
        <div class="product-card">
            <div class="product-img-wrap">
                <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                <div class="product-overlay">
                    <form method="post" class="overlay-form">
                        <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                        <input type="number" name="quantity" value="1" min="1" class="qty-input">
                        <button type="submit" class="overlay-btn">Add to Cart</button>
                    </form>
                </div>
            </div>
            <div class="product-info">
                <h3 class="product-name"><?= htmlspecialchars($row['name']) ?></h3>
                <span class="product-price">$<?= number_format($row['price'], 2) ?></span>
            </div>
            <p class="product-category"><?= htmlspecialchars($row['category'] ?? '') ?></p>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <h3>No pieces found</h3>
            <p>Try adjusting your filters or search term</p>
        </div>
    <?php endif; ?>
    </div>
</div>

<!-- FOOTER -->
<footer>
    <p>&copy; 2026 <span>Velvet Vogue</span> &mdash; All Rights Reserved</p>
</footer>

</body>
</html>