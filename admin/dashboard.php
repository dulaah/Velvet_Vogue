<?php
session_start();
include "../db.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: admin_login.php");
    exit;
}

$adminName = $_SESSION['admin_name'] ?? 'Admin';

// ── STATS ──────────────────────────────────────
// Total revenue
$rev = $conn->query("SELECT COALESCE(SUM(total_amount),0) AS total FROM orders")->fetch_assoc()['total'];

// Total orders
$totalOrders = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'];

// Total users
$totalUsers  = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];

// Unread inquiries
$unreadInq   = $conn->query("SELECT COUNT(*) AS c FROM inquiries WHERE status='unread'")->fetch_assoc()['c'];

// Total products
$totalProds  = $conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'];

// Recent orders (last 8)
$recentOrders = $conn->query(
    "SELECT id, customer_name, total_amount, payment_method, created_at
     FROM orders ORDER BY created_at DESC LIMIT 8"
);

// Revenue last 7 days (for mini chart data)
$chartData = [];
for($i = 6; $i >= 0; $i--){
    $date  = date('Y-m-d', strtotime("-$i days"));
    $label = date('D', strtotime("-$i days"));
    $r = $conn->query("SELECT COALESCE(SUM(total_amount),0) AS d FROM orders WHERE DATE(created_at)='$date'")->fetch_assoc()['d'];
    $chartData[] = ['label'=>$label, 'val'=>(float)$r];
}
$maxChart = max(array_column($chartData,'val')) ?: 1;

// Top products by order quantity
$topProducts = $conn->query(
    "SELECT p.name, SUM(oi.quantity) AS sold
     FROM order_items oi
     JOIN products p ON p.id = oi.product_id
     GROUP BY oi.product_id ORDER BY sold DESC LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard | VV Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
<style>
:root{
    --bg:#f0f2f5;
    --white:#ffffff;
    --sidebar:#1e2330;
    --sidebar-hover:#272d3f;
    --accent:#4f6ef7;
    --accent-light:#eef1fe;
    --success:#16a34a;
    --success-light:#dcfce7;
    --warning:#d97706;
    --warning-light:#fef3c7;
    --danger:#e5534b;
    --danger-light:#fef2f2;
    --text:#1a1d23;
    --muted:#6b7280;
    --border:#e5e7eb;
    --radius:10px;
    --sidebar-w:240px;
}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh}

/* ── SIDEBAR ── */
.sidebar{
    width:var(--sidebar-w);
    background:var(--sidebar);
    position:fixed;top:0;left:0;bottom:0;
    display:flex;flex-direction:column;
    z-index:50;
    transition:transform 0.3s;
}
.sidebar-logo{
    padding:24px 20px;
    border-bottom:1px solid rgba(255,255,255,0.07);
    display:flex;align-items:center;gap:12px;
}
.logo-icon{
    width:36px;height:36px;
    background:var(--accent);
    border-radius:8px;
    display:flex;align-items:center;justify-content:center;
    font-size:16px;font-weight:700;color:white;
    flex-shrink:0;
}
.logo-text{font-family:'DM Serif Display',serif;font-size:17px;color:white}
.logo-text span{
    display:block;
    font-family:'DM Sans',sans-serif;
    font-size:9px;letter-spacing:2.5px;
    text-transform:uppercase;
    color:rgba(255,255,255,0.35);
    margin-top:1px;
}
.sidebar-nav{
    flex:1;padding:16px 12px;
    overflow-y:auto;
}
.nav-section-label{
    font-size:9px;letter-spacing:2.5px;text-transform:uppercase;
    color:rgba(255,255,255,0.25);font-weight:600;
    padding:12px 10px 6px;
}
.nav-item{
    display:flex;align-items:center;gap:11px;
    padding:10px 12px;border-radius:8px;
    font-size:13.5px;font-weight:500;
    color:rgba(255,255,255,0.6);
    text-decoration:none;
    margin-bottom:2px;
    transition:background 0.2s,color 0.2s;
    position:relative;
}
.nav-item:hover{background:var(--sidebar-hover);color:white}
.nav-item.active{background:var(--accent);color:white}
.nav-item .nav-icon{font-size:16px;width:20px;text-align:center;flex-shrink:0}
.nav-badge{
    margin-left:auto;
    background:var(--danger);
    color:white;
    font-size:10px;font-weight:600;
    padding:2px 7px;border-radius:99px;
    min-width:20px;text-align:center;
}
.sidebar-footer{
    padding:16px 20px;
    border-top:1px solid rgba(255,255,255,0.07);
    font-size:12.5px;
    color:rgba(255,255,255,0.4);
}
.sidebar-footer strong{display:block;color:rgba(255,255,255,0.8);font-size:13.5px;margin-bottom:2px}
.btn-logout{
    display:block;margin-top:12px;
    padding:9px 14px;
    background:rgba(229,83,75,0.15);
    border:1px solid rgba(229,83,75,0.3);
    color:#f87171;
    border-radius:7px;
    font-family:'DM Sans',sans-serif;
    font-size:13px;font-weight:500;
    text-align:center;
    text-decoration:none;
    transition:background 0.2s;
}
.btn-logout:hover{background:rgba(229,83,75,0.25)}

/* ── MAIN ── */
.main{
    margin-left:var(--sidebar-w);
    flex:1;
    display:flex;flex-direction:column;
    min-height:100vh;
}
.topbar{
    background:var(--white);
    padding:16px 32px;
    border-bottom:1px solid var(--border);
    display:flex;justify-content:space-between;align-items:center;
    position:sticky;top:0;z-index:40;
}
.topbar-title{
    font-family:'DM Serif Display',serif;
    font-size:22px;color:var(--text);
}
.topbar-title span{
    display:block;
    font-family:'DM Sans',sans-serif;
    font-size:12px;color:var(--muted);font-weight:400;
    margin-top:1px;
}
.topbar-right{display:flex;align-items:center;gap:14px}
.admin-avatar{
    width:36px;height:36px;
    background:var(--accent-light);
    border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-size:14px;font-weight:600;
    color:var(--accent);
}
.topbar-date{font-size:12.5px;color:var(--muted)}

.page{padding:28px 32px;flex:1}

/* ── STAT CARDS ── */
.stats-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:18px;
    margin-bottom:28px;
    animation:fadeUp 0.5s ease both;
}
@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}

.stat-card{
    background:var(--white);
    border-radius:var(--radius);
    padding:22px 24px;
    border:1px solid var(--border);
    display:flex;flex-direction:column;gap:10px;
    transition:box-shadow 0.2s,transform 0.2s;
}
.stat-card:hover{box-shadow:0 8px 24px rgba(0,0,0,0.08);transform:translateY(-2px)}
.stat-header{display:flex;justify-content:space-between;align-items:center}
.stat-label{font-size:12px;font-weight:500;color:var(--muted);letter-spacing:0.3px}
.stat-icon{
    width:36px;height:36px;border-radius:8px;
    display:flex;align-items:center;justify-content:center;
    font-size:17px;
}
.stat-val{font-size:28px;font-weight:700;color:var(--text);line-height:1}
.stat-sub{font-size:12px;color:var(--muted)}
.stat-sub .up{color:var(--success);font-weight:600}
.stat-sub .down{color:var(--danger);font-weight:600}

/* colours per card */
.ic-blue{background:var(--accent-light);color:var(--accent)}
.ic-green{background:var(--success-light);color:var(--success)}
.ic-yellow{background:var(--warning-light);color:var(--warning)}
.ic-red{background:var(--danger-light);color:var(--danger)}

/* ── GRID BOTTOM ── */
.bottom-grid{
    display:grid;
    grid-template-columns:1fr 340px;
    gap:20px;
    animation:fadeUp 0.5s 0.1s ease both;
}

/* ── CARD SHARED ── */
.card{
    background:var(--white);
    border-radius:var(--radius);
    border:1px solid var(--border);
    overflow:hidden;
}
.card-head{
    padding:18px 22px;
    border-bottom:1px solid var(--border);
    display:flex;justify-content:space-between;align-items:center;
}
.card-head h3{font-size:15px;font-weight:600;color:var(--text)}
.card-head a{font-size:12.5px;color:var(--accent);text-decoration:none;font-weight:500}
.card-head a:hover{text-decoration:underline}

/* ── CHART ── */
.chart-wrap{padding:20px 22px}
.chart-bars{
    display:flex;align-items:flex-end;gap:10px;
    height:120px;
}
.bar-col{flex:1;display:flex;flex-direction:column;align-items:center;gap:6px}
.bar{
    width:100%;border-radius:5px 5px 0 0;
    background:var(--accent-light);
    transition:background 0.2s;
    cursor:pointer;
    position:relative;
    min-height:4px;
}
.bar:hover{background:var(--accent)}
.bar:hover .bar-tip{display:block}
.bar-tip{
    display:none;
    position:absolute;top:-30px;left:50%;transform:translateX(-50%);
    background:var(--sidebar);color:white;
    font-size:10px;font-weight:600;
    padding:3px 7px;border-radius:4px;white-space:nowrap;
}
.bar-lbl{font-size:10px;color:var(--muted);font-weight:500}

/* ── RECENT ORDERS TABLE ── */
.table-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse}
thead th{
    padding:11px 16px;
    font-size:11px;letter-spacing:1.5px;text-transform:uppercase;
    color:var(--muted);font-weight:600;
    background:#fafafa;
    text-align:left;
    border-bottom:1px solid var(--border);
}
tbody td{
    padding:13px 16px;
    font-size:13.5px;
    border-bottom:1px solid #f3f4f6;
}
tbody tr:last-child td{border-bottom:none}
tbody tr:hover td{background:#fafafa}
.order-id{font-weight:600;color:var(--accent)}
.pay-badge{
    display:inline-block;padding:3px 10px;border-radius:99px;
    font-size:11px;font-weight:500;
}
.pay-card{background:#eef1fe;color:var(--accent)}
.pay-paypal{background:#fff3cd;color:#92400e}
.pay-cod{background:var(--success-light);color:var(--success)}

/* ── TOP PRODUCTS ── */
.top-list{padding:8px 0}
.top-item{
    display:flex;align-items:center;gap:14px;
    padding:12px 22px;
    border-bottom:1px solid #f3f4f6;
}
.top-item:last-child{border-bottom:none}
.top-rank{
    width:26px;height:26px;border-radius:50%;
    background:var(--accent-light);color:var(--accent);
    font-size:12px;font-weight:700;
    display:flex;align-items:center;justify-content:center;
    flex-shrink:0;
}
.top-rank.r1{background:var(--accent);color:white}
.top-name{flex:1;font-size:13.5px;font-weight:500}
.top-sold{font-size:12px;color:var(--muted);white-space:nowrap}
.top-bar-wrap{width:80px;height:5px;background:#f0f2f5;border-radius:99px;overflow:hidden}
.top-bar-fill{height:100%;background:var(--accent);border-radius:99px}

@media(max-width:1100px){.stats-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:900px){.bottom-grid{grid-template-columns:1fr}.main{margin-left:0}}
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">V</div>
        <div class="logo-text">Velvet Vogue <span>Admin Panel</span></div>
    </div>
    <nav class="sidebar-nav">
        <p class="nav-section-label">Main</p>
        <a href="dashboard.php" class="nav-item active">
            <span class="nav-icon">⬛</span> Dashboard
        </a>
        <a href="inquiries.php" class="nav-item">
            <span class="nav-icon">✉</span> Inquiries
            <?php if($unreadInq > 0): ?>
                <span class="nav-badge"><?= $unreadInq ?></span>
            <?php endif; ?>
        </a>
        <p class="nav-section-label">Store</p>
        <a href="../products.php" class="nav-item" target="_blank">
            <span class="nav-icon">🛍</span> View Store
        </a>
    </nav>
    <div class="sidebar-footer">
        <strong><?= htmlspecialchars($adminName) ?></strong>
        Administrator
        <a href="logout.php" class="btn-logout">Sign Out</a>
    </div>
</aside>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <div class="topbar-title">
            Dashboard
            <span>Welcome back, <?= htmlspecialchars($adminName) ?></span>
        </div>
        <div class="topbar-right">
            <span class="topbar-date"><?= date('l, F j, Y') ?></span>
            <div class="admin-avatar"><?= strtoupper(substr($adminName,0,1)) ?></div>
        </div>
    </div>

    <div class="page">

        <!-- STAT CARDS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Total Revenue</span>
                    <div class="stat-icon ic-green">💰</div>
                </div>
                <div class="stat-val">$<?= number_format($rev, 2) ?></div>
                <div class="stat-sub">All time earnings</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Total Orders</span>
                    <div class="stat-icon ic-blue">📦</div>
                </div>
                <div class="stat-val"><?= $totalOrders ?></div>
                <div class="stat-sub">All orders placed</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Customers</span>
                    <div class="stat-icon ic-yellow">👥</div>
                </div>
                <div class="stat-val"><?= $totalUsers ?></div>
                <div class="stat-sub">Registered users</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Unread Inquiries</span>
                    <div class="stat-icon ic-red">✉</div>
                </div>
                <div class="stat-val"><?= $unreadInq ?></div>
                <div class="stat-sub"><a href="inquiries.php" style="color:var(--accent);text-decoration:none">View all &rarr;</a></div>
            </div>
        </div>

        <!-- BOTTOM GRID -->
        <div class="bottom-grid">

            <!-- RECENT ORDERS -->
            <div class="card">
                <div class="card-head">
                    <h3>Recent Orders</h3>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if($recentOrders && $recentOrders->num_rows > 0): ?>
                            <?php while($o = $recentOrders->fetch_assoc()): ?>
                            <tr>
                                <td class="order-id">#<?= str_pad($o['id'],5,'0',STR_PAD_LEFT) ?></td>
                                <td><?= htmlspecialchars($o['customer_name']) ?></td>
                                <td><strong>$<?= number_format($o['total_amount'],2) ?></strong></td>
                                <td>
                                    <?php
                                        $pm = $o['payment_method'];
                                        $cls = $pm === 'card' ? 'pay-card' : ($pm === 'paypal' ? 'pay-paypal' : 'pay-cod');
                                        $lbl = $pm === 'card' ? 'Card' : ($pm === 'paypal' ? 'PayPal' : 'COD');
                                    ?>
                                    <span class="pay-badge <?= $cls ?>"><?= $lbl ?></span>
                                </td>
                                <td style="color:var(--muted);font-size:12.5px"><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:32px">No orders yet</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div style="display:flex;flex-direction:column;gap:20px">

                <!-- REVENUE CHART -->
                <div class="card">
                    <div class="card-head"><h3>Revenue (7 Days)</h3></div>
                    <div class="chart-wrap">
                        <div class="chart-bars">
                            <?php foreach($chartData as $d): ?>
                            <div class="bar-col">
                                <div class="bar" style="height:<?= max(4, round(($d['val']/$maxChart)*110)) ?>px">
                                    <span class="bar-tip">$<?= number_format($d['val'],0) ?></span>
                                </div>
                                <span class="bar-lbl"><?= $d['label'] ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- TOP PRODUCTS -->
                <div class="card">
                    <div class="card-head"><h3>Top Products</h3></div>
                    <div class="top-list">
                    <?php
                    $maxSold = 1;
                    $topArr  = [];
                    if($topProducts){
                        while($tp = $topProducts->fetch_assoc()) $topArr[] = $tp;
                        if(count($topArr)) $maxSold = max(array_column($topArr,'sold')) ?: 1;
                    }
                    if(count($topArr)):
                        foreach($topArr as $i => $tp):
                    ?>
                        <div class="top-item">
                            <div class="top-rank <?= $i===0?'r1':'' ?>"><?= $i+1 ?></div>
                            <span class="top-name"><?= htmlspecialchars($tp['name']) ?></span>
                            <div class="top-bar-wrap">
                                <div class="top-bar-fill" style="width:<?= round(($tp['sold']/$maxSold)*100) ?>%"></div>
                            </div>
                            <span class="top-sold"><?= $tp['sold'] ?> sold</span>
                        </div>
                    <?php endforeach; else: ?>
                        <p style="padding:24px;text-align:center;color:var(--muted);font-size:13px">No sales data yet</p>
                    <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

</body>
</html>