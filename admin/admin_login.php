<?php
session_start();
include "../db.php";

// Redirect if already logged in
if(isset($_SESSION['admin_id'])){
    header("Location: dashboard.php");
    exit;
}

$errors = [];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if(empty($email) || empty($password)){
        $errors[] = "All fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if($row = $res->fetch_assoc()){
            if(password_verify($password, $row['password'])){
                $_SESSION['admin_id']   = $row['id'];
                $_SESSION['admin_name'] = $row['name'];
                header("Location: dashboard.php");
                exit;
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "No admin account found with that email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login | Velvet Vogue</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
<style>
:root{
    --bg:#f0f2f5;
    --white:#ffffff;
    --sidebar:#1e2330;
    --accent:#4f6ef7;
    --accent-light:#eef1fe;
    --danger:#e5534b;
    --text:#1a1d23;
    --muted:#6b7280;
    --border:#e5e7eb;
    --radius:10px;
}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:var(--bg);min-height:100vh;display:flex;align-items:center;justify-content:center}

.login-wrap{
    display:grid;
    grid-template-columns:420px 380px;
    background:var(--white);
    border-radius:16px;
    overflow:hidden;
    box-shadow:0 20px 60px rgba(0,0,0,0.12);
    animation:fadeIn 0.5s ease;
}
@keyframes fadeIn{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}

/* LEFT PANEL */
.login-brand{
    background:var(--sidebar);
    padding:56px 48px;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
}
.brand-logo{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:48px;
}
.brand-logo .logo-icon{
    width:40px;height:40px;
    background:var(--accent);
    border-radius:10px;
    display:flex;align-items:center;justify-content:center;
    font-size:18px;color:white;font-weight:700;
}
.brand-logo .logo-text{
    font-family:'DM Serif Display',serif;
    font-size:20px;
    color:white;
    letter-spacing:0.5px;
}
.brand-logo .logo-text span{
    display:block;
    font-family:'DM Sans',sans-serif;
    font-size:10px;
    letter-spacing:3px;
    text-transform:uppercase;
    color:rgba(255,255,255,0.4);
    font-style:normal;
    margin-top:2px;
}
.brand-heading{
    font-family:'DM Serif Display',serif;
    font-size:32px;
    color:white;
    line-height:1.2;
    margin-bottom:16px;
}
.brand-heading em{font-style:italic;color:rgba(255,255,255,0.5)}
.brand-sub{
    font-size:13px;
    color:rgba(255,255,255,0.45);
    line-height:1.7;
    margin-bottom:48px;
}
.brand-stats{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:12px;
}
.stat-pill{
    background:rgba(255,255,255,0.06);
    border:1px solid rgba(255,255,255,0.08);
    border-radius:8px;
    padding:14px 16px;
}
.stat-pill .val{
    font-size:22px;
    font-weight:600;
    color:white;
    margin-bottom:4px;
}
.stat-pill .lbl{
    font-size:10px;
    letter-spacing:1.5px;
    text-transform:uppercase;
    color:rgba(255,255,255,0.35);
}

/* RIGHT PANEL */
.login-form-wrap{
    padding:56px 44px;
    display:flex;
    flex-direction:column;
    justify-content:center;
}
.form-eyebrow{
    font-size:10px;
    letter-spacing:3px;
    text-transform:uppercase;
    color:var(--accent);
    font-weight:600;
    margin-bottom:8px;
}
.form-title{
    font-family:'DM Serif Display',serif;
    font-size:28px;
    color:var(--text);
    margin-bottom:6px;
}
.form-sub{
    font-size:13px;
    color:var(--muted);
    margin-bottom:32px;
    line-height:1.6;
}
.error-box{
    background:#fef2f2;
    border:1px solid #fecaca;
    border-left:3px solid var(--danger);
    color:#991b1b;
    padding:11px 14px;
    font-size:12.5px;
    border-radius:6px;
    margin-bottom:10px;
}
.form-group{
    display:flex;
    flex-direction:column;
    gap:7px;
    margin-bottom:18px;
}
.form-group label{
    font-size:12px;
    font-weight:500;
    color:var(--text);
    letter-spacing:0.3px;
}
.form-group input{
    padding:12px 14px;
    border:1.5px solid var(--border);
    border-radius:8px;
    font-family:'DM Sans',sans-serif;
    font-size:14px;
    color:var(--text);
    background:var(--bg);
    outline:none;
    transition:border-color 0.2s,box-shadow 0.2s;
}
.form-group input:focus{
    border-color:var(--accent);
    background:white;
    box-shadow:0 0 0 3px rgba(79,110,247,0.1);
}
.form-group input::placeholder{color:#c0c4cc;font-size:13px}
.btn-login{
    width:100%;
    padding:13px;
    background:var(--accent);
    color:white;
    border:none;
    border-radius:8px;
    font-family:'DM Sans',sans-serif;
    font-size:14px;
    font-weight:600;
    cursor:pointer;
    letter-spacing:0.3px;
    transition:background 0.2s,transform 0.1s;
    margin-top:6px;
}
.btn-login:hover{background:#3d5ce6}
.btn-login:active{transform:scale(0.99)}
.back-link{
    margin-top:24px;
    text-align:center;
    font-size:12.5px;
    color:var(--muted);
}
.back-link a{color:var(--accent);text-decoration:none;font-weight:500}
.back-link a:hover{text-decoration:underline}

@media(max-width:820px){
    .login-wrap{grid-template-columns:1fr}
    .login-brand{display:none}
}
</style>
</head>
<body>
<div class="login-wrap">

    <!-- LEFT BRAND PANEL -->
    <div class="login-brand">
        <div>
            <div class="brand-logo">
                <div class="logo-icon">V</div>
                <div class="logo-text">
                    Velvet Vogue
                    <span>Admin Panel</span>
                </div>
            </div>
            <h2 class="brand-heading">Welcome<br>back, <em>Admin</em></h2>
            <p class="brand-sub">Manage your store, track orders, and stay on top of everything from one place.</p>
        </div>
        <div class="brand-stats">
            <div class="stat-pill"><div class="val">2</div><div class="lbl">Pages</div></div>
            <div class="stat-pill"><div class="val">100%</div><div class="lbl">Secure</div></div>
        </div>
    </div>

    <!-- RIGHT FORM PANEL -->
    <div class="login-form-wrap">
        <p class="form-eyebrow">Admin Access</p>
        <h2 class="form-title">Sign In</h2>
        <p class="form-sub">Enter your admin credentials to continue.</p>

        <?php foreach($errors as $err): ?>
            <div class="error-box"><?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email"
                       placeholder="admin@velvetvogue.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password"
                       placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login">Sign In to Dashboard</button>
        </form>

        <p class="back-link"><a href="../index.php">&larr; Back to Store</a></p>
    </div>

</div>
</body>
</html>