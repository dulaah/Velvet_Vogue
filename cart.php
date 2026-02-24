<?php
session_start();
include "db.php";

$cart = $_SESSION['cart'] ?? [];
$totalAmount = 0;
$cartItems = [];

foreach($cart as $product_id => $qty){
    $stmt = $conn->prepare("SELECT name, price, image FROM products WHERE id=?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if($row = $res->fetch_assoc()){
        $subtotal = $row['price'] * $qty;
        $totalAmount += $subtotal;
        $cartItems[] = [
            'id'       => $product_id,
            'name'     => $row['name'],
            'price'    => $row['price'],
            'image'    => $row['image'],
            'qty'      => $qty,
            'subtotal' => $subtotal
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Shopping Cart | Velvet Vogue</title>
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
        --border: rgba(0,0,0,0.09);
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

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
    nav a:hover        { color: var(--gold); }
    nav a:hover::after { width: 100%; }
    nav a.active       { color: var(--gold); }
    nav a.active::after{ width: 100%; }

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

    /* ─── PAGE BANNER ────────────────────────── */
    .page-banner {
        background: var(--black);
        text-align: center;
        padding: 130px 20px 60px;
        position: relative;
        overflow: hidden;
    }

    .page-banner::before {
        content: 'CART';
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
        font-size: clamp(36px, 5vw, 60px);
        font-weight: 300;
        color: var(--white);
    }

    .page-banner h2 em {
        font-style: italic;
        color: var(--gold);
    }

    /* ─── CONTAINER ──────────────────────────── */
    .container {
        padding: 70px 80px 110px;
        max-width: 1300px;
        margin: 0 auto;
        animation: fadeUp 0.6s 0.2s ease both;
    }

    @keyframes fadeUp {
        from { transform: translateY(24px); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }

    /* ─── CART TABLE ─────────────────────────── */
    .cart-table-wrap {
        border: 1px solid var(--border);
        background: white;
        overflow: hidden;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead th {
        background: var(--black);
        color: var(--white);
        padding: 16px 20px;
        font-size: 10px;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        font-weight: 500;
        text-align: left;
    }

    thead th:last-child,
    tbody td:last-child {
        text-align: center;
    }

    thead th.center,
    tbody td.center {
        text-align: center;
    }

    tbody tr {
        border-bottom: 1px solid var(--border);
        transition: background 0.2s;
    }

    tbody tr:last-child { border-bottom: none; }

    tbody tr:hover { background: #faf8f5; }

    tbody td {
        padding: 22px 20px;
        font-size: 13px;
        vertical-align: middle;
    }

    .product-cell {
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .product-thumb {
        width: 80px;
        height: 100px;
        object-fit: cover;
        display: block;
        flex-shrink: 0;
    }

    .product-cell-info .product-name {
        font-family: 'Cormorant Garamond', serif;
        font-size: 20px;
        font-weight: 400;
        margin-bottom: 4px;
    }

    .product-cell-info .product-cat {
        font-size: 10px;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: var(--muted);
    }

    .price-cell {
        font-size: 13px;
        color: var(--muted);
        text-align: center;
    }

    .qty-cell {
        text-align: center;
    }

    .qty-badge {
        display: inline-block;
        background: var(--cream);
        padding: 6px 16px;
        font-size: 13px;
        font-weight: 500;
        letter-spacing: 1px;
        min-width: 46px;
        text-align: center;
    }

    .subtotal-cell {
        font-size: 14px;
        font-weight: 500;
        text-align: center;
        color: var(--black);
    }

    .remove-cell { text-align: center; }

    .remove-btn {
        background: none;
        border: 1px solid rgba(201, 107, 107, 0.4);
        color: var(--rose);
        cursor: pointer;
        font-family: 'Montserrat', sans-serif;
        font-size: 10px;
        letter-spacing: 2px;
        text-transform: uppercase;
        padding: 8px 16px;
        transition: background 0.3s, color 0.3s;
    }

    .remove-btn:hover {
        background: var(--rose);
        color: white;
        border-color: var(--rose);
    }

    /* ─── EMPTY CART ─────────────────────────── */
    .empty-cart {
        text-align: center;
        padding: 90px 20px;
    }

    .empty-cart h3 {
        font-family: 'Cormorant Garamond', serif;
        font-size: 38px;
        font-weight: 300;
        margin-bottom: 14px;
    }

    .empty-cart p {
        font-size: 12px;
        color: var(--muted);
        letter-spacing: 1px;
        margin-bottom: 32px;
    }

    .btn-shop {
        display: inline-block;
        padding: 15px 40px;
        border: 1px solid var(--black);
        background: transparent;
        color: var(--black);
        font-family: 'Montserrat', sans-serif;
        font-size: 10px;
        letter-spacing: 3px;
        text-transform: uppercase;
        font-weight: 500;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.3s, color 0.3s;
    }

    .btn-shop:hover {
        background: var(--black);
        color: var(--white);
    }

    /* ─── ORDER SUMMARY ──────────────────────── */
    .cart-footer {
        display: flex;
        justify-content: flex-end;
        margin-top: 36px;
        gap: 60px;
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .cart-actions {
        display: flex;
        align-items: center;
        gap: 14px;
        padding-top: 10px;
    }

    .btn-clear {
        background: none;
        border: 1px solid var(--border);
        color: var(--muted);
        padding: 12px 28px;
        font-family: 'Montserrat', sans-serif;
        font-size: 10px;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        cursor: pointer;
        transition: border-color 0.3s, color 0.3s;
    }

    .btn-clear:hover {
        border-color: var(--rose);
        color: var(--rose);
    }

    .order-summary {
        background: var(--black);
        padding: 40px 44px;
        min-width: 320px;
    }

    .order-summary h3 {
        font-family: 'Cormorant Garamond', serif;
        font-size: 26px;
        font-weight: 300;
        color: var(--white);
        margin-bottom: 28px;
        letter-spacing: 1px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        letter-spacing: 1px;
        color: rgba(248,245,240,0.5);
        margin-bottom: 14px;
        text-transform: uppercase;
    }

    .summary-divider {
        border: none;
        border-top: 1px solid rgba(201,169,110,0.2);
        margin: 20px 0;
    }

    .summary-total {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-bottom: 28px;
    }

    .summary-total .label {
        font-size: 10px;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: var(--gold);
        font-weight: 500;
    }

    .summary-total .amount {
        font-family: 'Cormorant Garamond', serif;
        font-size: 32px;
        font-weight: 400;
        color: var(--white);
    }

    .btn-checkout {
        width: 100%;
        padding: 16px;
        background: var(--gold);
        color: var(--black);
        border: none;
        font-family: 'Montserrat', sans-serif;
        font-size: 11px;
        letter-spacing: 3px;
        text-transform: uppercase;
        font-weight: 600;
        cursor: pointer;
        transition: opacity 0.3s;
    }

    .btn-checkout:hover { opacity: 0.85; }

    .summary-note {
        margin-top: 16px;
        font-size: 10px;
        color: rgba(248,245,240,0.3);
        text-align: center;
        letter-spacing: 1px;
        line-height: 1.8;
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
        header     { padding: 18px 30px; }
        .container { padding: 50px 24px 80px; }

        table, thead, tbody, th, td, tr { display: block; }
        thead { display: none; }

        tbody tr {
            border: 1px solid var(--border);
            margin-bottom: 16px;
            padding: 16px;
            background: white;
        }

        tbody td { padding: 10px 0; border: none; }

        .product-cell { flex-direction: row; }
        .price-cell, .qty-cell, .subtotal-cell, .remove-cell { text-align: left; }
        .qty-badge { display: inline-block; }

        .cart-footer { flex-direction: column; align-items: stretch; }
        .order-summary { min-width: auto; }
    }

    @media (max-width: 600px) {
        nav { display: none; }
    }
</style>
</head>
<body>

<!-- HEADER -->
<header>
    <a href="index.php" class="logo">Velvet <span>Vogue</span></a>
    <nav>
        <a href="index.php">Home</a>
        <a href="products.php">Shop</a>
        <a href="cart.php" class="active">
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
    <p class="eyebrow">Your Selection</p>
    <h2>Shopping <em>Cart</em></h2>
</div>

<!-- CONTAINER -->
<div class="container">

<?php if(empty($cartItems)): ?>
    <!-- EMPTY STATE -->
    <div class="empty-cart">
        <h3>Your cart is empty</h3>
        <p>Looks like you haven't added any pieces yet</p>
        <a href="products.php" class="btn-shop">Explore Collection</a>
    </div>

<?php else: ?>

    <!-- CART TABLE -->
    <div class="cart-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="center">Price</th>
                    <th class="center">Qty</th>
                    <th class="center">Subtotal</th>
                    <th class="center">Remove</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($cartItems as $item): ?>
            <tr>
                <td>
                    <div class="product-cell">
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-thumb">
                        <div class="product-cell-info">
                            <p class="product-name"><?= htmlspecialchars($item['name']) ?></p>
                        </div>
                    </div>
                </td>
                <td class="price-cell">$<?= number_format($item['price'], 2) ?></td>
                <td class="qty-cell"><span class="qty-badge"><?= $item['qty'] ?></span></td>
                <td class="subtotal-cell">$<?= number_format($item['subtotal'], 2) ?></td>
                <td class="remove-cell">
                    <form action="remove_from_cart.php" method="post">
                        <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                        <button type="submit" class="remove-btn">Remove</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- CART FOOTER -->
    <div class="cart-footer">

        <div class="cart-actions">
            <form action="clear_cart.php" method="post">
                <button type="submit" class="btn-clear">Clear Cart</button>
            </form>
            <a href="products.php" class="btn-shop">Continue Shopping</a>
        </div>

        <div class="order-summary">
            <h3>Order Summary</h3>

            <div class="summary-row">
                <span>Items (<?= $cartCount ?>)</span>
                <span>$<?= number_format($totalAmount, 2) ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping</span>
                <span><?= $totalAmount >= 150 ? 'Free' : '$9.99' ?></span>
            </div>

            <hr class="summary-divider">

            <div class="summary-total">
                <span class="label">Total</span>
                <span class="amount">
                    $<?= $totalAmount >= 150
                        ? number_format($totalAmount, 2)
                        : number_format($totalAmount + 9.99, 2) ?>
                </span>
            </div>

            <form action="checkout.php" method="post">
                <button type="submit" class="btn-checkout">Proceed to Checkout</button>
            </form>

            <p class="summary-note">
                Free shipping on orders over $150<br>
                Secure checkout &mdash; SSL encrypted
            </p>
        </div>

    </div>

<?php endif; ?>
</div>

<!-- FOOTER -->
<footer>
    <p>&copy; 2026 <span>Velvet Vogue</span> &mdash; All Rights Reserved</p>
</footer>

</body>
</html>