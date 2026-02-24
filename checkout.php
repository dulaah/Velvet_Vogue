<?php
session_start();
include "db.php";

$cart = $_SESSION['cart'] ?? [];

if(!$cart){
    echo "Your cart is empty! <a href='products.php'>Shop now</a>";
    exit;
}

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
            'id'       => $row['id'],
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
<title>Checkout | Velvet Vogue</title>
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
        content: 'CHECKOUT';
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        font-family: 'Cormorant Garamond', serif;
        font-size: 160px;
        font-weight: 600;
        color: rgba(255,255,255,0.03);
        letter-spacing: 20px;
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

    /* ─── STEPS BAR ──────────────────────────── */
    .steps-bar {
        background: var(--cream);
        border-bottom: 1px solid var(--border);
        padding: 18px 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0;
    }

    .step {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 10px;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--muted);
        font-weight: 500;
    }

    .step.active { color: var(--black); }

    .step-num {
        width: 26px; height: 26px;
        border-radius: 50%;
        border: 1px solid var(--muted);
        display: flex; align-items: center; justify-content: center;
        font-size: 11px;
        font-weight: 500;
        color: var(--muted);
        flex-shrink: 0;
    }

    .step.active .step-num {
        background: var(--black);
        border-color: var(--black);
        color: var(--white);
    }

    .step.done .step-num {
        background: var(--gold);
        border-color: var(--gold);
        color: var(--black);
    }

    .step.done { color: var(--muted); }

    .step-line {
        width: 60px;
        height: 1px;
        background: var(--border);
        margin: 0 14px;
    }

    /* ─── MAIN LAYOUT ────────────────────────── */
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 70px 80px 110px;
        display: grid;
        grid-template-columns: 1fr 420px;
        gap: 50px;
        align-items: start;
        animation: fadeUp 0.6s 0.2s ease both;
    }

    @keyframes fadeUp {
        from { transform: translateY(24px); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }

    /* ─── SECTION TITLES ─────────────────────── */
    .section-label {
        font-size: 10px;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: var(--gold);
        margin-bottom: 10px;
        font-weight: 500;
    }

    .section-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 30px;
        font-weight: 300;
        margin-bottom: 30px;
        line-height: 1.1;
    }

    /* ─── SHIPPING FORM ──────────────────────── */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group.full { grid-column: 1 / -1; }

    .form-group label {
        font-size: 10px;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--muted);
        font-weight: 500;
    }

    .form-group input,
    .form-group select {
        padding: 13px 16px;
        border: 1px solid var(--border);
        background: white;
        color: var(--black);
        font-family: 'Montserrat', sans-serif;
        font-size: 13px;
        font-weight: 300;
        outline: none;
        transition: border-color 0.3s;
        -webkit-appearance: none;
        appearance: none;
        width: 100%;
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: var(--gold);
    }

    .form-group select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%237a7169'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 16px center;
        cursor: pointer;
        padding-right: 40px;
    }

    /* Payment icons row */
    .payment-icons {
        display: flex;
        gap: 10px;
        margin-top: 8px;
    }

    .pay-icon {
        padding: 6px 14px;
        border: 1px solid var(--border);
        font-size: 10px;
        letter-spacing: 1px;
        color: var(--muted);
        text-transform: uppercase;
        background: var(--cream);
    }

    /* ─── ORDER SUMMARY (RIGHT PANEL) ───────── */
    .order-panel {
        position: sticky;
        top: 100px;
    }

    .order-summary-box {
        background: var(--black);
        padding: 40px;
    }

    .order-summary-box .section-label { color: var(--gold); }

    .order-summary-box .section-title {
        color: var(--white);
        margin-bottom: 28px;
    }

    /* Item list */
    .order-items {
        display: flex;
        flex-direction: column;
        gap: 18px;
        margin-bottom: 28px;
    }

    .order-item {
        display: flex;
        gap: 14px;
        align-items: center;
    }

    .order-item img {
        width: 60px;
        height: 75px;
        object-fit: cover;
        flex-shrink: 0;
        display: block;
    }

    .order-item-info { flex: 1; }

    .order-item-name {
        font-family: 'Cormorant Garamond', serif;
        font-size: 17px;
        font-weight: 400;
        color: var(--white);
        margin-bottom: 4px;
    }

    .order-item-qty {
        font-size: 10px;
        letter-spacing: 1.5px;
        color: rgba(248,245,240,0.4);
        text-transform: uppercase;
    }

    .order-item-price {
        font-size: 13px;
        color: rgba(248,245,240,0.7);
        white-space: nowrap;
    }

    .summary-divider {
        border: none;
        border-top: 1px solid rgba(201,169,110,0.15);
        margin: 20px 0;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        letter-spacing: 1px;
        color: rgba(248,245,240,0.45);
        margin-bottom: 12px;
        text-transform: uppercase;
    }

    .summary-total-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-top: 20px;
        margin-bottom: 28px;
    }

    .summary-total-row .label {
        font-size: 10px;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: var(--gold);
        font-weight: 500;
    }

    .summary-total-row .amount {
        font-family: 'Cormorant Garamond', serif;
        font-size: 34px;
        font-weight: 400;
        color: var(--white);
    }

    /* ─── PLACE ORDER BTN ────────────────────── */
    .btn-place-order {
        width: 100%;
        padding: 18px;
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

    .btn-place-order:hover { opacity: 0.85; }

    .secure-note {
        margin-top: 16px;
        font-size: 10px;
        color: rgba(248,245,240,0.28);
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
    @media (max-width: 1000px) {
        .container {
            grid-template-columns: 1fr;
            padding: 50px 30px 80px;
        }
        .order-panel { position: static; }
    }

    @media (max-width: 600px) {
        header       { padding: 18px 24px; }
        nav          { display: none; }
        .steps-bar   { padding: 16px 20px; }
        .step-line   { width: 30px; }
        .form-grid   { grid-template-columns: 1fr; }
        .form-group.full { grid-column: 1; }
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
        <a href="cart.php">
            Cart
            <span class="cart-count"><?= array_sum($_SESSION['cart']) ?></span>
        </a>
        <a href="login.php">Login</a>
    </nav>
</header>

<!-- PAGE BANNER -->
<div class="page-banner">
    <p class="eyebrow">Almost There</p>
    <h2>Secure <em>Checkout</em></h2>
</div>

<!-- STEPS BAR -->
<div class="steps-bar">
    <div class="step done">
        <span class="step-num">✓</span>
        <span>Cart</span>
    </div>
    <div class="step-line"></div>
    <div class="step active">
        <span class="step-num">2</span>
        <span>Checkout</span>
    </div>
    <div class="step-line"></div>
    <div class="step">
        <span class="step-num">3</span>
        <span>Confirmation</span>
    </div>
</div>

<!-- MAIN LAYOUT -->
<div class="container">

    <!-- LEFT: SHIPPING FORM -->
    <div class="checkout-left">
        <p class="section-label">Step 2 of 3</p>
        <h2 class="section-title">Shipping & Payment</h2>

        <form action="place_order.php" method="post">
            <div class="form-grid">
                <div class="form-group full">
                    <label for="fullName">Full Name</label>
                    <input type="text" name="fullName" id="fullName" placeholder="Jane Doe" required>
                </div>

                <div class="form-group full">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" placeholder="jane@example.com" required>
                </div>

                <div class="form-group full">
                    <label for="address">Shipping Address</label>
                    <input type="text" name="address" id="address" placeholder="123 Main Street" required>
                </div>

                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" name="city" id="city" placeholder="New York" required>
                </div>

                <div class="form-group">
                    <label for="zip">ZIP / Postal Code</label>
                    <input type="text" name="zip" id="zip" placeholder="10001" required>
                </div>

                <div class="form-group full">
                    <label for="payment">Payment Method</label>
                    <select name="payment" id="payment" required>
                        <option value="">Select Payment Method</option>
                        <option value="card">Credit / Debit Card</option>
                        <option value="paypal">PayPal</option>
                        <option value="cod">Cash on Delivery</option>
                    </select>
                    <div class="payment-icons">
                        <span class="pay-icon">Visa</span>
                        <span class="pay-icon">Mastercard</span>
                        <span class="pay-icon">PayPal</span>
                        <span class="pay-icon">COD</span>
                    </div>
                </div>
            </div>

            <input type="hidden" name="totalAmount" value="<?= $totalAmount ?>">

            <!-- Place order btn only visible on mobile (inside form) -->
            <button type="submit" class="btn-place-order" style="margin-top:32px;display:none;" id="mobileBtn">
                Place Order &mdash; $<?= number_format($totalAmount >= 150 ? $totalAmount : $totalAmount + 9.99, 2) ?>
            </button>
        </form>
    </div>

    <!-- RIGHT: ORDER SUMMARY -->
    <div class="order-panel">
        <div class="order-summary-box">
            <p class="section-label">Your Order</p>
            <h3 class="section-title">Order Summary</h3>

            <div class="order-items">
                <?php foreach($itemsList as $item): ?>
                <div class="order-item">
                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                    <div class="order-item-info">
                        <p class="order-item-name"><?= htmlspecialchars($item['name']) ?></p>
                        <p class="order-item-qty">Qty: <?= $item['qty'] ?></p>
                    </div>
                    <span class="order-item-price">$<?= number_format($item['subtotal'], 2) ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <hr class="summary-divider">

            <div class="summary-row">
                <span>Subtotal</span>
                <span>$<?= number_format($totalAmount, 2) ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping</span>
                <span><?= $totalAmount >= 150 ? 'Free' : '$9.99' ?></span>
            </div>

            <div class="summary-total-row">
                <span class="label">Total</span>
                <span class="amount">
                    $<?= number_format($totalAmount >= 150 ? $totalAmount : $totalAmount + 9.99, 2) ?>
                </span>
            </div>

            <form action="place_order.php" method="post">
                <input type="hidden" name="totalAmount" value="<?= $totalAmount ?>">
                <!-- This submits the shipping form via JS -->
                <button type="button" class="btn-place-order" onclick="submitOrder()">
                    Place Order
                </button>
            </form>

            <p class="secure-note">SSL Encrypted &mdash; 256-bit Secure Checkout<br>Free returns within 30 days</p>
        </div>
    </div>

</div>

<!-- FOOTER -->
<footer>
    <p>&copy; 2026 <span>Velvet Vogue</span> &mdash; All Rights Reserved</p>
</footer>

<script>
    // Submit the shipping form when "Place Order" is clicked from the summary panel
    function submitOrder(){
     const form = document.querySelector('form[action="place_order.php"]');

    const fullName = document.getElementById("fullName").value.trim();
    const email    = document.getElementById("email").value.trim();
    const address  = document.getElementById("address").value.trim();
    const city     = document.getElementById("city").value.trim();
    const zip      = document.getElementById("zip").value.trim();
    const payment  = document.getElementById("payment").value;

    // Remove old messages
    let oldMsg = document.getElementById("formMessage");
    if(oldMsg) oldMsg.remove();

    const messageDiv = document.createElement("div");
    messageDiv.id = "formMessage";
    messageDiv.style.padding = "12px";
    messageDiv.style.marginBottom = "15px";
    messageDiv.style.borderRadius = "6px";
    messageDiv.style.textAlign = "center";
    messageDiv.style.fontWeight = "bold";

    // ❌ If empty fields
    if(fullName === "" || email === "" || address === "" || city === "" || zip === "" || payment === ""){
        
        messageDiv.style.background = "#ffe0e0";
        messageDiv.style.color = "#c00";
        messageDiv.innerText = "All fields are required!";
        
        form.prepend(messageDiv);
        return;
    }

    // ✅ Success message before submit
    messageDiv.style.background = "#e0ffe5";
    messageDiv.style.color = "#008000";
    messageDiv.innerText = "Order submitted successfully! Processing...";

    form.prepend(messageDiv);

    // Small delay so user can see message
    setTimeout(function(){
        form.submit();
    }, 1000);
};
   

    // Show mobile button on small screens
    function checkMobile(){
        document.getElementById('mobileBtn').style.display =
            window.innerWidth <= 1000 ? 'block' : 'none';
    }
    checkMobile();
    window.addEventListener('resize', checkMobile);
</script>

</body>
</html>