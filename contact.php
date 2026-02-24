<?php
session_start();
include "db.php";

$errors  = [];
$success = false;

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if(empty($name) || empty($email) || empty($subject) || empty($message)){
        $errors[] = "All fields are required.";
    }

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors[] = "Please enter a valid email address.";
    }

    if(empty($errors)){
        $stmt = $conn->prepare("INSERT INTO inquiries (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        if($stmt->execute()){
            $success = true;
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact | Velvet Vogue</title>
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
            background: var(--white);
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

        /* ─── PAGE BANNER ────────────────────────── */
        .page-banner {
            background: var(--black);
            text-align: center;
            padding: 130px 20px 60px;
            position: relative;
            overflow: hidden;
        }

        .page-banner::before {
            content: 'CONTACT';
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            font-family: 'Cormorant Garamond', serif;
            font-size: 170px;
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
            font-size: clamp(36px, 5vw, 62px);
            font-weight: 300;
            color: var(--white);
        }

        .page-banner h2 em {
            font-style: italic;
            color: var(--gold);
        }

        /* ─── MAIN LAYOUT ────────────────────────── */
        .container {
            flex: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 80px 80px 110px;
            display: grid;
            grid-template-columns: 1fr 580px;
            gap: 80px;
            align-items: start;
            animation: fadeUp 0.6s 0.2s ease both;
        }

        @keyframes fadeUp {
            from { transform: translateY(24px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }

        /* ─── LEFT INFO PANEL ────────────────────── */
        .contact-info .section-label {
            font-size: 10px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 12px;
            font-weight: 500;
        }

        .contact-info h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(32px, 3vw, 46px);
            font-weight: 300;
            line-height: 1.1;
            margin-bottom: 20px;
        }

        .contact-info p {
            font-size: 13px;
            line-height: 1.9;
            color: var(--muted);
            max-width: 360px;
            margin-bottom: 50px;
        }

        /* contact detail items */
        .contact-details {
            display: flex;
            flex-direction: column;
            gap: 28px;
            margin-bottom: 50px;
        }

        .contact-detail {
            display: flex;
            gap: 18px;
            align-items: flex-start;
        }

        .detail-icon {
            width: 42px; height: 42px;
            border: 1px solid rgba(201,169,110,0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 16px;
        }

        .detail-text .detail-label {
            font-size: 9px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--gold);
            font-weight: 500;
            margin-bottom: 5px;
        }

        .detail-text .detail-value {
            font-size: 13px;
            color: var(--black);
            line-height: 1.6;
        }

        /* social row */
        .social-row {
            display: flex;
            gap: 12px;
        }

        .social-btn {
            padding: 10px 20px;
            border: 1px solid var(--border);
            font-size: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--muted);
            text-decoration: none;
            font-weight: 500;
            transition: border-color 0.3s, color 0.3s, background 0.3s;
        }

        .social-btn:hover {
            border-color: var(--gold);
            color: var(--black);
            background: rgba(201,169,110,0.07);
        }

        /* ─── RIGHT FORM PANEL ───────────────────── */
        .form-panel {
            background: white;
            border: 1px solid var(--border);
            padding: 50px 44px;
        }

        .form-panel .form-eyebrow {
            font-size: 10px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 10px;
            font-weight: 500;
        }

        .form-panel .form-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 32px;
            font-weight: 300;
            margin-bottom: 30px;
        }

        /* ─── ERROR / SUCCESS ────────────────────── */
        .error {
            background: rgba(201,107,107,0.1);
            border-left: 3px solid var(--rose);
            color: var(--rose);
            padding: 11px 16px;
            font-size: 12px;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        .success-msg {
            background: rgba(201,169,110,0.1);
            border-left: 3px solid var(--gold);
            padding: 20px 20px;
            margin-bottom: 28px;
        }

        .success-msg h4 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 22px;
            font-weight: 400;
            color: var(--black);
            margin-bottom: 6px;
        }

        .success-msg p {
            font-size: 12px;
            color: var(--muted);
            letter-spacing: 0.5px;
            line-height: 1.7;
        }

        /* ─── FORM FIELDS ────────────────────────── */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 18px;
        }

        .form-group.full { grid-column: 1 / -1; }

        .form-group label {
            font-size: 10px;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--muted);
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 13px 16px;
            border: 1px solid var(--border);
            background: var(--white);
            color: var(--black);
            font-family: 'Montserrat', sans-serif;
            font-size: 13px;
            font-weight: 300;
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
            width: 100%;
            -webkit-appearance: none;
            appearance: none;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(201,169,110,0.08);
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: rgba(122,113,105,0.45);
            font-size: 12px;
        }

        .form-group select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%237a7169'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            padding-right: 40px;
            cursor: pointer;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 130px;
            line-height: 1.7;
        }

        /* ─── SUBMIT ─────────────────────────────── */
        .btn-send {
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
            margin-top: 6px;
        }

        .btn-send:hover {
            background: var(--gold);
            color: var(--black);
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
                padding: 60px 30px 80px;
                gap: 50px;
            }
        }

        @media (max-width: 600px) {
            header   { padding: 18px 24px; }
            nav      { display: none; }
            .form-row { grid-template-columns: 1fr; }
            .form-panel { padding: 36px 24px; }
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
        <a href="login.php">Login</a>
        <a href="contact.php" class="active">Contact</a>
    </nav>
</header>

<!-- PAGE BANNER -->
<div class="page-banner">
    <p class="eyebrow">We'd Love to Hear From You</p>
    <h2>Get in <em>Touch</em></h2>
</div>

<!-- MAIN -->
<div class="container">

    <!-- LEFT: INFO -->
    <div class="contact-info">
        <p class="section-label">Contact Us</p>
        <h3>Let's start a<br>conversation</h3>
        <p>Whether you have a question about an order, need styling advice, or just want to say hello — our team is here and happy to help.</p>

        <div class="contact-details">
            <div class="contact-detail">
                <div class="detail-icon">✉</div>
                <div class="detail-text">
                    <p class="detail-label">Email</p>
                    <p class="detail-value">hello@velvetvogue.com<br>support@velvetvogue.com</p>
                </div>
            </div>
            <div class="contact-detail">
                <div class="detail-icon">☎</div>
                <div class="detail-text">
                    <p class="detail-label">Phone</p>
                    <p class="detail-value">+1 (800) 123-4567<br>Mon – Fri, 9am – 6pm EST</p>
                </div>
            </div>
            <div class="contact-detail">
                <div class="detail-icon">◎</div>
                <div class="detail-text">
                    <p class="detail-label">Address</p>
                    <p class="detail-value">142 Fashion Avenue, Suite 8<br>New York, NY 10018</p>
                </div>
            </div>
        </div>

        <div class="social-row">
            <a href="#" class="social-btn">Instagram</a>
            <a href="#" class="social-btn">Pinterest</a>
            <a href="#" class="social-btn">TikTok</a>
        </div>
    </div>

    <!-- RIGHT: FORM -->
    <div class="form-panel">
        <p class="form-eyebrow">Send a Message</p>
        <h3 class="form-title">How can we help?</h3>

        <?php foreach($errors as $err): ?>
            <div class="error"><?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>

        <?php if($success): ?>
            <div class="success-msg">
                <h4>Message Sent!</h4>
                <p>Thank you for reaching out. We'll get back to you within 24 hours.</p>
            </div>
        <?php endif; ?>

        <?php if(!$success): ?>
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" name="name" id="name"
                           placeholder="Jane Doe"
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                           required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email"
                           placeholder="jane@example.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           required>
                </div>
            </div>

            <div class="form-group">
                <label for="subject">Subject</label>
                <select name="subject" id="subject" required>
                    <option value="">Select a topic</option>
                    <option value="Order Inquiry"      <?= ($_POST['subject'] ?? '') === 'Order Inquiry'      ? 'selected' : '' ?>>Order Inquiry</option>
                    <option value="Returns & Exchanges" <?= ($_POST['subject'] ?? '') === 'Returns & Exchanges' ? 'selected' : '' ?>>Returns & Exchanges</option>
                    <option value="Styling Advice"     <?= ($_POST['subject'] ?? '') === 'Styling Advice'     ? 'selected' : '' ?>>Styling Advice</option>
                    <option value="Wholesale"          <?= ($_POST['subject'] ?? '') === 'Wholesale'          ? 'selected' : '' ?>>Wholesale</option>
                    <option value="Other"              <?= ($_POST['subject'] ?? '') === 'Other'              ? 'selected' : '' ?>>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="message">Message</label>
                <textarea name="message" id="message"
                          placeholder="Write your message here..."
                          required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn-send">Send Message</button>
        </form>
        <?php endif; ?>
    </div>

</div>

<!-- FOOTER -->
<footer>
    <p>&copy; 2026 <span>Velvet Vogue</span> &mdash; All Rights Reserved</p>
</footer>

</body>
</html>