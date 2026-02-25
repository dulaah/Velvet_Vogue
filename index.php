<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Velvet Vogue | Express Your Style</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --black: #0a0a0a;
            --white: #f8f5f0;
            --cream: #ede9e1;
            --gold: #c9a96e;
            --rose: #c96b6b;
            --muted: #7a7169;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--white);
            color: var(--black);
            font-family: 'Montserrat', sans-serif;
            font-weight: 300;
            overflow-x: hidden;
        }

        /* ─── HEADER ─────────────────────────────── */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 100;
            padding: 22px 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(10,10,10,0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(201,169,110,0.2);
            animation: slideDown 0.8s ease forwards;
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
        }

        .logo span {
            color: var(--gold);
        }

        nav {
            display: flex;
            gap: 36px;
            align-items: center;
        }

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
            bottom: -4px;
            left: 0;
            width: 0;
            height: 1px;
            background: var(--gold);
            transition: width 0.3s ease;
        }

        nav a:hover { color: var(--gold); }
        nav a:hover::after { width: 100%; }

        /* ─── HERO ───────────────────────────────── */
        .hero {
            height: 100vh;
            position: relative;
            display: flex;
            align-items: flex-end;
            overflow: hidden;
        }

        .hero-bg {
            position: absolute;
            inset: 0;
            background: url("https://images.unsplash.com/photo-1520975916090-3105956dac38?w=1800&q=80") center center / cover no-repeat;
            transform: scale(1.05);
            animation: zoomOut 8s ease forwards;
        }

        @keyframes zoomOut {
            from { transform: scale(1.08); }
            to   { transform: scale(1); }
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to top,
                rgba(10,10,10,0.85) 0%,
                rgba(10,10,10,0.35) 50%,
                rgba(10,10,10,0.1) 100%
            );
        }

        .hero-content {
            position: relative;
            z-index: 2;
            padding: 0 80px 90px;
            max-width: 700px;
            animation: fadeUp 1.2s 0.4s ease both;
        }

        @keyframes fadeUp {
            from { transform: translateY(40px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }

        .hero-eyebrow {
            font-size: 11px;
            letter-spacing: 5px;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 20px;
            font-weight: 500;
        }

        .hero h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(52px, 7vw, 88px);
            font-weight: 300;
            line-height: 1.05;
            color: var(--white);
            margin-bottom: 28px;
        }

        .hero h2 em {
            font-style: italic;
            color: var(--gold);
        }

        .hero p {
            font-size: 13px;
            letter-spacing: 1px;
            color: rgba(248,245,240,0.65);
            margin-bottom: 40px;
            max-width: 420px;
            line-height: 1.9;
        }

        .btn-primary {
            display: inline-block;
            padding: 16px 44px;
            border: 1px solid var(--gold);
            background: transparent;
            color: var(--gold);
            font-family: 'Montserrat', sans-serif;
            font-size: 11px;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s, color 0.3s;
        }

        .btn-primary:hover {
            background: var(--gold);
            color: var(--black);
        }

        .hero-scroll {
            position: absolute;
            bottom: 40px;
            right: 80px;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(248,245,240,0.4);
            font-size: 10px;
            letter-spacing: 3px;
            text-transform: uppercase;
            animation: fadeUp 1.2s 0.9s ease both;
        }

        .hero-scroll::before {
            content: '';
            display: block;
            width: 40px;
            height: 1px;
            background: var(--gold);
        }

        /* ─── MARQUEE ────────────────────────────── */
        .marquee-wrap {
            background: var(--black);
            padding: 18px 0;
            overflow: hidden;
            border-top: 1px solid rgba(201,169,110,0.15);
            border-bottom: 1px solid rgba(201,169,110,0.15);
        }

        .marquee-track {
            display: flex;
            gap: 60px;
            animation: marquee 18s linear infinite;
            width: max-content;
        }

        @keyframes marquee {
            from { transform: translateX(0); }
            to   { transform: translateX(-50%); }
        }

        .marquee-item {
            font-family: 'Cormorant Garamond', serif;
            font-size: 15px;
            font-style: italic;
            color: rgba(201,169,110,0.6);
            letter-spacing: 3px;
            white-space: nowrap;
        }

        .marquee-dot {
            color: var(--rose);
            margin: 0 10px;
        }

        /* ─── NEW ARRIVALS ───────────────────────── */
        .section {
            padding: 110px 80px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 60px;
        }

        .section-label {
            font-size: 10px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 12px;
            font-weight: 500;
        }

        .section-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(36px, 4vw, 54px);
            font-weight: 300;
            line-height: 1.1;
        }

        .section-link {
            font-size: 10px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--muted);
            text-decoration: none;
            border-bottom: 1px solid var(--muted);
            padding-bottom: 2px;
            transition: color 0.3s, border-color 0.3s;
        }

        .section-link:hover {
            color: var(--black);
            border-color: var(--black);
        }

        /* ─── PRODUCT GRID ───────────────────────── */
        .products {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        .product-card {
            background: transparent;
            cursor: pointer;
        }

        .product-img-wrap {
            position: relative;
            overflow: hidden;
            aspect-ratio: 3/4;
            background: var(--cream);
            margin-bottom: 20px;
        }

        .product-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.7s ease;
            display: block;
        }

        .product-card:hover img {
            transform: scale(1.06);
        }

        .product-badge {
            position: absolute;
            top: 18px;
            left: 18px;
            background: var(--rose);
            color: white;
            font-size: 9px;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 5px 12px;
            font-weight: 500;
        }

        .product-overlay {
            position: absolute;
            inset: 0;
            background: rgba(10,10,10,0);
            display: flex;
            align-items: flex-end;
            padding: 24px;
            transition: background 0.4s ease;
        }

        .product-card:hover .product-overlay {
            background: rgba(10,10,10,0.35);
        }

        .overlay-btn {
            width: 100%;
            padding: 14px;
            background: var(--white);
            color: var(--black);
            border: none;
            font-family: 'Montserrat', sans-serif;
            font-size: 10px;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            font-weight: 500;
            cursor: pointer;
            transform: translateY(10px);
            opacity: 0;
            transition: transform 0.3s, opacity 0.3s;
        }

        .product-card:hover .overlay-btn {
            transform: translateY(0);
            opacity: 1;
        }

        .product-info {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
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

        .product-sub {
            font-size: 10px;
            letter-spacing: 1.5px;
            color: var(--muted);
            text-transform: uppercase;
            margin-top: 5px;
        }

        /* ─── EDITORIAL STRIP ────────────────────── */
        .editorial {
            padding: 0 80px 110px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            min-height: 520px;
        }

        .editorial-img {
            height: 520px;
            overflow: hidden;
        }

        .editorial-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.7s ease;
        }

        .editorial:hover .editorial-img img {
            transform: scale(1.04);
        }

        .editorial-text {
            background: var(--black);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 70px;
        }

        .editorial-text .section-label { color: var(--gold); }

        .editorial-text h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 46px;
            font-weight: 300;
            color: var(--white);
            line-height: 1.1;
            margin-bottom: 24px;
        }

        .editorial-text p {
            font-size: 12px;
            line-height: 2;
            color: rgba(248,245,240,0.55);
            margin-bottom: 40px;
            max-width: 340px;
        }

        .btn-outline-light {
            display: inline-block;
            padding: 15px 38px;
            border: 1px solid rgba(201,169,110,0.5);
            color: var(--gold);
            font-family: 'Montserrat', sans-serif;
            font-size: 10px;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-weight: 500;
            cursor: pointer;
            background: transparent;
            align-self: flex-start;
            transition: all 0.3s;
        }

        .btn-outline-light:hover {
            background: var(--gold);
            color: var(--black);
            border-color: var(--gold);
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

        footer span {
            color: var(--gold);
        }

        /* ─── RESPONSIVE ──────────────────────────── */
        @media (max-width: 900px) {
            header { padding: 18px 30px; }
            .hero-content { padding: 0 40px 70px; }
            .section { padding: 70px 30px; }
            .products { grid-template-columns: 1fr 1fr; }
            .editorial { grid-template-columns: 1fr; padding: 0 30px 70px; }
            .editorial-img { height: 320px; }
            .editorial-text { padding: 50px 36px; }
            .section-header { flex-direction: column; align-items: flex-start; gap: 16px; }
        }

        @media (max-width: 600px) {
            nav { display: none; }
            .products { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>

<!-- HEADER -->
<header>
    <div class="logo">Velvet <span>Vogue</span></div>
    <nav>
        <a href="index.php">Home</a>
        <a href="products.php">Shop</a>
        <a href="cart.php">Cart</a>
        <a href="login.php">Login</a>
        <a href="contact.php">Contact</a>
    </nav>
</header>

<!-- HERO -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <p class="hero-eyebrow">New Collection — Spring 2026</p>
        <h2>Discover<br>Your <em>Unique</em><br>Style</h2>
        <p>Curated fashion for those who see clothing as more than fabric — as language, identity, and art.</p>
        <button class="btn-primary" onclick="goToShop()">Explore Collection</button>
    </div>
    <div class="hero-scroll">Scroll to explore</div>
</section>

<!-- MARQUEE -->
<div class="marquee-wrap">
    <div class="marquee-track">
        <span class="marquee-item">N 2026 <span class="marquee-dot">✦</span></span>
        <span class="marquee-item">Elevated Essentew Arrivals <span class="marquee-dot">✦</span></span>
        <span class="marquee-item">Spring / Summerials <span class="marquee-dot">✦</span></span>
        <span class="marquee-item">Free Shipping Over $150 <span class="marquee-dot">✦</span></span>
        <span class="marquee-item">New Arrivals <span class="marquee-dot">✦</span></span>
        <span class="marquee-item">Spring / Summer 2026 <span class="marquee-dot">✦</span></span>
        <span class="marquee-item">Elevated Essentials <span class="marquee-dot">✦</span></span>
        <span class="marquee-item">Free Shipping Over $150 <span class="marquee-dot">✦</span></span>
    </div>
</div>

<!-- NEW ARRIVALS -->
<section class="section">
    <div class="section-header">
        <div>
            <p class="section-label">Just Landed</p>
            <h2 class="section-title">New Arrivals</h2>
        </div>
        <a href="products.php" class="section-link">View All Pieces</a>
    </div>

    <div class="products">
        <div class="product-card">
            <div class="product-img-wrap">
                <span class="product-badge">New</span>
                <img src="https://img-lcwaikiki.mncdn.com/mnpadding/1200/1600/ffffff/pim/productimages/20251/7674644/v1/l_20251-s5bd58z8-h45-105-81-90-187_a.jpg" alt="Casual Denim Jacket">
                
            </div>
            <div class="product-info">
                <h3 class="product-name">Casual Denim Jacket</h3>
                <span class="product-price">$49.99</span>
            </div>
            <p class="product-sub">Outerwear</p>
        </div>

        <div class="product-card">
            <div class="product-img-wrap">
                <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=600&q=80" alt="Elegant Formal Dress">
                
            </div>
            <div class="product-info">
                <h3 class="product-name">Elegant Formal Dress</h3>
                <span class="product-price">$79.99</span>
            </div>
            <p class="product-sub">Dresses</p>
        </div>

        <div class="product-card">
            <div class="product-img-wrap">
                <span class="product-badge" style="background:var(--gold);color:var(--black);">Popular</span>
                <img src="https://images.unsplash.com/photo-1523381210434-271e8be1f52b?w=600&q=80" alt="Classic White Shirt">
                
            </div>
            <div class="product-info">
                <h3 class="product-name">Classic White Shirt</h3>
                <span class="product-price">$29.99</span>
            </div>
            <p class="product-sub">Tops</p>
        </div>
    </div>
</section>

<!-- EDITORIAL STRIP -->
<div class="editorial">
    <div class="editorial-img">
        <img src="https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=900&q=80" alt="Editorial">
    </div>
    <div class="editorial-text">
        <p class="section-label">Editorial</p>
        <h3>Style Is a<br>State of Mind</h3>
        <p>From boardroom to weekend — our curated collections carry you through every chapter with effortless grace and considered craft.</p>
        <button class="btn-outline-light" onclick="goToShop()">Shop the Edit</button>
    </div>
</div>

<!-- FOOTER -->
<footer>
    <p>&copy; 2026 <span>Velvet Vogue</span> &mdash; All Rights Reserved</p>
</footer>

<script>
    function goToShop(){
        window.location.href = "products.php";
    }
</script>

</body>
</html>