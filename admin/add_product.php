<?php
session_start();
include "../db.php";
include "admin_guard.php";

$adminName = $_SESSION['admin_name'] ?? 'Admin';
$errors = [];
$values = [
    'name' => '',
    'price' => '',
    'image' => '',
    'category' => '',
];

if (empty($_SESSION['product_form_token'])) {
    $_SESSION['product_form_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $field => $value) {
        $submittedValue = $_POST[$field] ?? '';
        if (!is_string($submittedValue)) {
            $errors[] = 'Product fields must contain text values.';
            $submittedValue = '';
        }
        $values[$field] = trim($submittedValue);
    }

    $csrfToken = $_POST['csrf_token'] ?? null;
    if (!is_string($csrfToken) || !hash_equals($_SESSION['product_form_token'], $csrfToken)) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    }

    if ($values['name'] === '') {
        $errors[] = 'Product name is required.';
    } elseif (strlen($values['name']) > 255) {
        $errors[] = 'Product name must be 255 characters or fewer.';
    }

    if ($values['price'] === '' || !preg_match('/^\d+(?:\.\d{1,2})?$/D', $values['price']) || (float)$values['price'] > 99999999.99) {
        $errors[] = 'Enter a valid price between 0 and 99,999,999.99.';
    }

    if ($values['image'] === '') {
        $errors[] = 'An image URL or path is required.';
    } elseif (strlen($values['image']) > 500) {
        $errors[] = 'The image URL or path must be 500 characters or fewer.';
    }

    if ($values['category'] === '') {
        $errors[] = 'Category is required.';
    } elseif (strlen($values['category']) > 50) {
        $errors[] = 'Category must be 50 characters or fewer.';
    }

    if (!$errors) {
        $stmt = $conn->prepare("INSERT INTO products (name, price, image, category) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Product insert prepare failed: " . $conn->error);
            $errors[] = 'The product could not be saved. Please try again.';
        } else {
            $price = (float)$values['price'];
            $stmt->bind_param("sdss", $values['name'], $price, $values['image'], $values['category']);
            if ($stmt->execute()) {
                $stmt->close();
                unset($_SESSION['product_form_token']);
                header("Location: add_product.php?added=1");
                exit;
            }

            error_log("Product insert failed: " . $stmt->error);
            $errors[] = 'The product could not be saved. Please try again.';
            $stmt->close();
        }
    }
}

$added = isset($_GET['added']) && $_GET['added'] === '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Product | VV Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
<style>
:root{
    --bg:#f0f2f5;--white:#fff;--sidebar:#1e2330;--sidebar-hover:#272d3f;
    --accent:#4f6ef7;--accent-light:#eef1fe;--success:#16a34a;
    --success-light:#dcfce7;--danger:#e5534b;--danger-light:#fef2f2;
    --text:#1a1d23;--muted:#6b7280;--border:#e5e7eb;--radius:10px;
    --sidebar-w:240px;
}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh}
.sidebar{width:var(--sidebar-w);background:var(--sidebar);position:fixed;top:0;left:0;bottom:0;display:flex;flex-direction:column;z-index:50}
.sidebar-logo{padding:24px 20px;border-bottom:1px solid rgba(255,255,255,.07);display:flex;align-items:center;gap:12px}
.logo-icon{width:36px;height:36px;background:var(--accent);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;color:white;flex-shrink:0}
.logo-text{font-family:'DM Serif Display',serif;font-size:17px;color:white}
.logo-text span{display:block;font-family:'DM Sans',sans-serif;font-size:9px;letter-spacing:2.5px;text-transform:uppercase;color:rgba(255,255,255,.35);margin-top:1px}
.sidebar-nav{flex:1;padding:16px 12px;overflow-y:auto}
.nav-section-label{font-size:9px;letter-spacing:2.5px;text-transform:uppercase;color:rgba(255,255,255,.25);font-weight:600;padding:12px 10px 6px}
.nav-item{display:flex;align-items:center;gap:11px;padding:10px 12px;border-radius:8px;font-size:13.5px;font-weight:500;color:rgba(255,255,255,.6);text-decoration:none;margin-bottom:2px;transition:background .2s,color .2s}
.nav-item:hover{background:var(--sidebar-hover);color:white}
.nav-item.active{background:var(--accent);color:white}
.nav-icon{font-size:16px;width:20px;text-align:center;flex-shrink:0}
.sidebar-footer{padding:16px 20px;border-top:1px solid rgba(255,255,255,.07);font-size:12.5px;color:rgba(255,255,255,.4)}
.sidebar-footer strong{display:block;color:rgba(255,255,255,.8);font-size:13.5px;margin-bottom:2px}
.btn-logout{display:block;margin-top:12px;padding:9px 14px;background:rgba(229,83,75,.15);border:1px solid rgba(229,83,75,.3);color:#f87171;border-radius:7px;font-size:13px;font-weight:500;text-align:center;text-decoration:none}
.main{margin-left:var(--sidebar-w);flex:1;display:flex;flex-direction:column;min-height:100vh}
.topbar{background:var(--white);padding:16px 32px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:40}
.topbar-title{font-family:'DM Serif Display',serif;font-size:22px}
.topbar-title span{display:block;font-family:'DM Sans',sans-serif;font-size:12px;color:var(--muted);font-weight:400;margin-top:1px}
.topbar-right{display:flex;align-items:center;gap:14px}
.topbar-date{font-size:12.5px;color:var(--muted)}
.admin-avatar{width:36px;height:36px;background:var(--accent-light);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:600;color:var(--accent)}
.page{padding:28px 32px;flex:1}
.form-card{max-width:760px;background:var(--white);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden}
.card-heading{padding:20px 24px;border-bottom:1px solid var(--border)}
.card-heading h2{font-size:16px;font-weight:600;margin-bottom:5px}
.card-heading p,.field-hint{font-size:12.5px;color:var(--muted);line-height:1.5}
.product-form{padding:24px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.field{display:flex;flex-direction:column;gap:7px}
.field.full{grid-column:1/-1}
label{font-size:13px;font-weight:600}
.field input{width:100%;padding:11px 12px;border:1px solid var(--border);border-radius:7px;font:14px 'DM Sans',sans-serif;color:var(--text);outline:none}
.field input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(79,110,247,.1)}
.form-actions{display:flex;align-items:center;gap:14px;margin-top:24px}
.btn-submit{border:0;border-radius:7px;padding:11px 18px;background:var(--accent);color:white;font:600 13.5px 'DM Sans',sans-serif;cursor:pointer}
.btn-submit:hover{background:#3d5ce6}
.cancel-link{font-size:13px;color:var(--muted);text-decoration:none}
.cancel-link:hover{color:var(--text)}
.notice{max-width:760px;padding:12px 15px;border-radius:8px;margin-bottom:18px;font-size:13.5px}
.notice.success{background:var(--success-light);color:#166534;border:1px solid #bbf7d0}
.notice.error{background:var(--danger-light);color:#991b1b;border:1px solid #fecaca}
.notice ul{padding-left:18px}
@media(max-width:700px){.sidebar{width:200px}.main{margin-left:200px}.page{padding:20px}.topbar{padding:14px 20px}.form-grid{grid-template-columns:1fr}.field.full{grid-column:auto}}
@media(max-width:520px){.sidebar{position:static;width:100%;height:auto}.sidebar-nav{display:flex;flex-wrap:wrap;padding:8px}.sidebar-nav .nav-section-label{width:100%}.sidebar-footer{display:none}.main{margin-left:0}.sidebar-logo{padding:12px 16px}body{display:block}.topbar-date{display:none}}
</style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">V</div>
        <div class="logo-text">Velvet Vogue <span>Admin Panel</span></div>
    </div>
    <nav class="sidebar-nav">
        <p class="nav-section-label">Main</p>
        <a href="dashboard.php" class="nav-item"><span class="nav-icon">⬛</span> Dashboard</a>
        <a href="inquiries.php" class="nav-item"><span class="nav-icon">✉</span> Inquiries</a>
        <p class="nav-section-label">Store</p>
        <a href="add_product.php" class="nav-item active"><span class="nav-icon">＋</span> Add Product</a>
        <a href="../products.php" class="nav-item" target="_blank"><span class="nav-icon">🛍</span> View Store</a>
    </nav>
    <div class="sidebar-footer">
        <strong><?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?></strong>
        Administrator
        <a href="logout.php" class="btn-logout">Sign Out</a>
    </div>
</aside>
<main class="main">
    <div class="topbar">
        <div class="topbar-title">Add Product <span>Create a product for your store</span></div>
        <div class="topbar-right">
            <span class="topbar-date"><?= date('l, F j, Y') ?></span>
            <div class="admin-avatar"><?= htmlspecialchars(strtoupper(substr($adminName, 0, 1)), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>
    <section class="page">
        <?php if ($added): ?>
            <div class="notice success" role="status">Product added successfully.</div>
        <?php endif; ?>
        <?php if ($errors): ?>
            <div class="notice error" role="alert">
                <ul><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>
        <div class="form-card">
            <div class="card-heading">
                <h2>Product details</h2>
                <p>Enter the product name, price, image URL or path, and category.</p>
            </div>
            <form class="product-form" method="POST" action="add_product.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['product_form_token'], ENT_QUOTES, 'UTF-8') ?>">
                <div class="form-grid">
                    <div class="field full">
                        <label for="name">Product name</label>
                        <input id="name" name="name" type="text" maxlength="255" required value="<?= htmlspecialchars($values['name'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="field">
                        <label for="price">Price</label>
                        <input id="price" name="price" type="number" min="0" max="99999999.99" step="0.01" required value="<?= htmlspecialchars($values['price'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="field">
                        <label for="category">Category</label>
                        <input id="category" name="category" type="text" maxlength="50" required value="<?= htmlspecialchars($values['category'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="field full">
                        <label for="image">Image URL or path</label>
                        <input id="image" name="image" type="text" maxlength="500" required placeholder="https://example.com/product.jpg or images/product.jpg" value="<?= htmlspecialchars($values['image'], ENT_QUOTES, 'UTF-8') ?>">
                        <span class="field-hint">Provide a URL or a path relative to the store; the image file is not uploaded by this form.</span>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn-submit" type="submit">Add Product</button>
                    <a class="cancel-link" href="dashboard.php">Cancel</a>
                </div>
            </form>
        </div>
    </section>
</main>
</body>
</html>
