<?php
session_start();
include "db.php";

$errors = [];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if(empty($email) || empty($password)){
        $errors[] = "All fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, full_name, password FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if($stmt->num_rows > 0){
            $stmt->bind_result($id, $full_name, $hashed_password);
            $stmt->fetch();

            if(password_verify($password, $hashed_password)){
                $_SESSION['user_id']   = $id;
                $_SESSION['full_name'] = $full_name;
                header("Location: index.php");
                exit;
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "Email not registered.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Velvet Vogue</title>
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
            --border: rgba(0,0,0,0.10);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Montserrat', sans-serif;
            font-weight: 300;
            background: var(--black);
            color: var(--black);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
            background: rgba(10,10,10,0.85);
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

        /* ─── FULL PAGE SPLIT ────────────────────── */
        .page-wrap {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
        }

        /* ─── LEFT IMAGE PANEL ───────────────────── */
        .image-panel {
            position: relative;
            overflow: hidden;
            background: var(--black);
        }

        .image-panel img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            opacity: 0.7;
            animation: zoomOut 10s ease forwards;
        }

        @keyframes zoomOut {
            from { transform: scale(1.06); }
            to   { transform: scale(1); }
        }

        .image-panel-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to right,
                rgba(10,10,10,0.2) 0%,
                rgba(10,10,10,0.55) 100%
            );
        }

        .image-panel-text {
            position: absolute;
            bottom: 60px;
            left: 50px;
            right: 50px;
            z-index: 2;
            animation: fadeUp 1s 0.4s ease both;
        }

        @keyframes fadeUp {
            from { transform: translateY(20px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }

        .image-panel-text .eyebrow {
            font-size: 10px;
            letter-spacing: 5px;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 14px;
            font-weight: 500;
        }

        .image-panel-text h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(32px, 3.5vw, 52px);
            font-weight: 300;
            color: var(--white);
            line-height: 1.1;
        }

        .image-panel-text h2 em {
            font-style: italic;
            color: var(--gold);
        }

        /* ─── RIGHT FORM PANEL ───────────────────── */
        .form-panel {
            background: var(--white);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 100px 70px;
            animation: fadeIn 0.8s 0.3s ease both;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(20px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        .form-inner {
            width: 100%;
            max-width: 380px;
        }

        .form-eyebrow {
            font-size: 10px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 10px;
            font-weight: 500;
        }

        .form-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 42px;
            font-weight: 300;
            margin-bottom: 8px;
            line-height: 1.1;
        }

        .form-subtitle {
            font-size: 12px;
            color: var(--muted);
            letter-spacing: 0.5px;
            margin-bottom: 40px;
            line-height: 1.7;
        }

        /* ─── ERROR MESSAGES ─────────────────────── */
        .error {
            background: rgba(201,107,107,0.1);
            border-left: 3px solid var(--rose);
            color: var(--rose);
            padding: 11px 16px;
            font-size: 12px;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        /* ─── FORM FIELDS ────────────────────────── */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
        }

        .form-group label {
            font-size: 10px;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--muted);
            font-weight: 500;
        }

        .form-group input {
            padding: 14px 16px;
            border: 1px solid var(--border);
            background: white;
            color: var(--black);
            font-family: 'Montserrat', sans-serif;
            font-size: 13px;
            font-weight: 300;
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
            width: 100%;
        }

        .form-group input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(201,169,110,0.08);
        }

        .form-group input::placeholder {
            color: rgba(122,113,105,0.5);
            font-size: 12px;
        }

        /* ─── FORGOT LINK ────────────────────────── */
        .form-meta {
            display: flex;
            justify-content: flex-end;
            margin-top: -12px;
            margin-bottom: 28px;
        }

        .form-meta a {
            font-size: 11px;
            color: var(--muted);
            text-decoration: none;
            letter-spacing: 0.5px;
            border-bottom: 1px solid transparent;
            transition: color 0.3s, border-color 0.3s;
        }

        .form-meta a:hover {
            color: var(--black);
            border-color: var(--black);
        }

        /* ─── SUBMIT BUTTON ──────────────────────── */
        .btn-login {
            width: 100%;
            padding: 16px;
            background: var(--black);
            color: var(--white);
            border: none;
            font-family: 'Montserrat', sans-serif;
            font-size: 11px;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s, color 0.3s;
            margin-bottom: 24px;
        }

        .btn-login:hover {
            background: var(--gold);
            color: var(--black);
        }

        /* ─── DIVIDER ────────────────────────────── */
        .divider {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 24px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .divider span {
            font-size: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--muted);
            white-space: nowrap;
        }

        /* ─── REGISTER LINK ──────────────────────── */
        .register-link {
            text-align: center;
            font-size: 12px;
            color: var(--muted);
            letter-spacing: 0.5px;
        }

        .register-link a {
            color: var(--black);
            font-weight: 500;
            text-decoration: none;
            border-bottom: 1px solid var(--black);
            padding-bottom: 1px;
            transition: color 0.3s, border-color 0.3s;
        }

        .register-link a:hover {
            color: var(--gold);
            border-color: var(--gold);
        }

        /* ─── FOOTER ─────────────────────────────── */
        footer {
            background: var(--black);
            color: rgba(248,245,240,0.4);
            text-align: center;
            padding: 28px;
            font-size: 10px;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            border-top: 1px solid rgba(201,169,110,0.1);
        }

        footer span { color: var(--gold); }

        /* ─── RESPONSIVE ─────────────────────────── */
        @media (max-width: 900px) {
            header { padding: 18px 30px; }

            .page-wrap { grid-template-columns: 1fr; }

            .image-panel { display: none; }

            .form-panel {
                padding: 120px 36px 60px;
                min-height: 100vh;
                background: var(--white);
            }
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
        <a href="cart.php">Cart</a>
        <a href="login.php" class="active">Login</a>
        <a href="contact.html">Contact</a>
    </nav>
</header>

<!-- SPLIT PAGE -->
<div class="page-wrap">

    <!-- LEFT: IMAGE -->
    <div class="image-panel">
        <img src="https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=1200&q=80" alt="Velvet Vogue Fashion">
        <div class="image-panel-overlay"></div>
        <div class="image-panel-text">
            <p class="eyebrow">Welcome Back</p>
            <h2>Style is a<br><em>way of living</em></h2>
        </div>
    </div>

    <!-- RIGHT: FORM -->
    <div class="form-panel">
        <div class="form-inner">

            <p class="form-eyebrow">Member Access</p>
            <h2 class="form-title">Sign In</h2>
            <p class="form-subtitle">Welcome back. Enter your details to continue.</p>

            <?php if(!empty($errors)): ?>
                <?php foreach($errors as $err): ?>
                    <div class="error"><?= htmlspecialchars($err) ?></div>
                <?php endforeach; ?>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" placeholder="jane@example.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="••••••••" required>
                </div>

                <div class="form-meta">
                    <a href="forgot_password.php">Forgot password?</a>
                </div>

                <button type="submit" class="btn-login">Sign In</button>
            </form>

            <div class="divider"><span>New to Velvet Vogue?</span></div>

            <p class="register-link">
                Don't have an account? <a href="register.php">Create one here</a>
            </p>

        </div>
    </div>

</div>

<!-- FOOTER -->
<footer>
    <p>&copy; 2026 <span>Velvet Vogue</span> &mdash; All Rights Reserved</p>
</footer>

</body>
</html>