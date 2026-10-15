<?php
session_start();
include "../db.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: admin_login.php");
    exit;
}

$adminName  = $_SESSION['admin_name'] ?? 'Admin';

// ── MARK AS READ ───────────────────────────────
if(isset($_POST['mark_read'])){
    $id = intval($_POST['inq_id']);
    $conn->query("UPDATE inquiries SET status='read' WHERE id=$id");
    header("Location: inquiries.php");
    exit;
}

// ── MARK AS REPLIED ────────────────────────────
if(isset($_POST['mark_replied'])){
    $id = intval($_POST['inq_id']);
    $conn->query("UPDATE inquiries SET status='replied' WHERE id=$id");
    header("Location: inquiries.php");
    exit;
}

// ── DELETE ─────────────────────────────────────
if(isset($_POST['delete_inq'])){
    $id = intval($_POST['inq_id']);
    $conn->query("DELETE FROM inquiries WHERE id=$id");
    header("Location: inquiries.php");
    exit;
}

// ── FILTER ────────────────────────────────────
$filter  = $_GET['status'] ?? 'all';
$search  = trim($_GET['search'] ?? '');

$where = "WHERE 1=1";
if($filter !== 'all')         $where .= " AND status='" . $conn->real_escape_string($filter) . "'";
if($search !== '')            $where .= " AND (name LIKE '%" . $conn->real_escape_string($search) . "%' OR email LIKE '%" . $conn->real_escape_string($search) . "%' OR subject LIKE '%" . $conn->real_escape_string($search) . "%')";

$inquiries = $conn->query("SELECT * FROM inquiries $where ORDER BY created_at DESC");
$total     = $inquiries ? $inquiries->num_rows : 0;

// Counts for tabs
$allCount     = $conn->query("SELECT COUNT(*) AS c FROM inquiries")->fetch_assoc()['c'];
$unreadCount  = $conn->query("SELECT COUNT(*) AS c FROM inquiries WHERE status='unread'")->fetch_assoc()['c'];
$readCount    = $conn->query("SELECT COUNT(*) AS c FROM inquiries WHERE status='read'")->fetch_assoc()['c'];
$repliedCount = $conn->query("SELECT COUNT(*) AS c FROM inquiries WHERE status='replied'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Inquiries | VV Admin</title>
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

/* ── SIDEBAR (same as dashboard) ── */
.sidebar{width:var(--sidebar-w);background:var(--sidebar);position:fixed;top:0;left:0;bottom:0;display:flex;flex-direction:column;z-index:50}
.sidebar-logo{padding:24px 20px;border-bottom:1px solid rgba(255,255,255,0.07);display:flex;align-items:center;gap:12px}
.logo-icon{width:36px;height:36px;background:var(--accent);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;color:white;flex-shrink:0}
.logo-text{font-family:'DM Serif Display',serif;font-size:17px;color:white}
.logo-text span{display:block;font-family:'DM Sans',sans-serif;font-size:9px;letter-spacing:2.5px;text-transform:uppercase;color:rgba(255,255,255,0.35);margin-top:1px}
.sidebar-nav{flex:1;padding:16px 12px;overflow-y:auto}
.nav-section-label{font-size:9px;letter-spacing:2.5px;text-transform:uppercase;color:rgba(255,255,255,0.25);font-weight:600;padding:12px 10px 6px}
.nav-item{display:flex;align-items:center;gap:11px;padding:10px 12px;border-radius:8px;font-size:13.5px;font-weight:500;color:rgba(255,255,255,0.6);text-decoration:none;margin-bottom:2px;transition:background 0.2s,color 0.2s;position:relative}
.nav-item:hover{background:var(--sidebar-hover);color:white}
.nav-item.active{background:var(--accent);color:white}
.nav-item .nav-icon{font-size:16px;width:20px;text-align:center;flex-shrink:0}
.nav-badge{margin-left:auto;background:var(--danger);color:white;font-size:10px;font-weight:600;padding:2px 7px;border-radius:99px;min-width:20px;text-align:center}
.sidebar-footer{padding:16px 20px;border-top:1px solid rgba(255,255,255,0.07);font-size:12.5px;color:rgba(255,255,255,0.4)}
.sidebar-footer strong{display:block;color:rgba(255,255,255,0.8);font-size:13.5px;margin-bottom:2px}
.btn-logout{display:block;margin-top:12px;padding:9px 14px;background:rgba(229,83,75,0.15);border:1px solid rgba(229,83,75,0.3);color:#f87171;border-radius:7px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;text-align:center;text-decoration:none;transition:background 0.2s}
.btn-logout:hover{background:rgba(229,83,75,0.25)}

/* ── MAIN ── */
.main{margin-left:var(--sidebar-w);flex:1;display:flex;flex-direction:column;min-height:100vh}
.topbar{background:var(--white);padding:16px 32px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:40}
.topbar-title{font-family:'DM Serif Display',serif;font-size:22px;color:var(--text)}
.topbar-title span{display:block;font-family:'DM Sans',sans-serif;font-size:12px;color:var(--muted);font-weight:400;margin-top:1px}
.topbar-right{display:flex;align-items:center;gap:14px}
.admin-avatar{width:36px;height:36px;background:var(--accent-light);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:600;color:var(--accent)}
.topbar-date{font-size:12.5px;color:var(--muted)}

.page{padding:28px 32px;flex:1;animation:fadeUp 0.4s ease}
@keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}

/* ── TOOLBAR ── */
.toolbar{
    display:flex;justify-content:space-between;align-items:center;
    margin-bottom:20px;flex-wrap:wrap;gap:14px;
}
.filter-tabs{display:flex;gap:6px;flex-wrap:wrap}
.tab{
    padding:7px 16px;border-radius:99px;
    font-size:12.5px;font-weight:500;
    text-decoration:none;color:var(--muted);
    background:var(--white);border:1px solid var(--border);
    transition:all 0.2s;
    display:flex;align-items:center;gap:6px;
}
.tab:hover{border-color:var(--accent);color:var(--accent)}
.tab.active{background:var(--accent);color:white;border-color:var(--accent)}
.tab .tc{
    background:rgba(255,255,255,0.25);
    font-size:10px;font-weight:700;
    padding:1px 6px;border-radius:99px;
}
.tab:not(.active) .tc{background:var(--bg);color:var(--muted)}

.search-wrap{display:flex;gap:10px;align-items:center}
.search-input{
    padding:9px 14px;
    border:1.5px solid var(--border);border-radius:8px;
    font-family:'DM Sans',sans-serif;font-size:13.5px;
    color:var(--text);background:var(--white);
    outline:none;width:240px;
    transition:border-color 0.2s;
}
.search-input:focus{border-color:var(--accent)}
.btn-search{
    padding:9px 18px;background:var(--accent);color:white;border:none;
    border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;
    font-weight:500;cursor:pointer;transition:background 0.2s;
}
.btn-search:hover{background:#3d5ce6}

/* ── RESULTS INFO ── */
.results-info{
    font-size:12.5px;color:var(--muted);margin-bottom:16px;
}
.results-info strong{color:var(--text)}

/* ── INQUIRY CARDS ── */
.inq-list{display:flex;flex-direction:column;gap:12px}

.inq-card{
    background:var(--white);
    border:1px solid var(--border);
    border-radius:var(--radius);
    overflow:hidden;
    transition:box-shadow 0.2s;
}
.inq-card:hover{box-shadow:0 4px 16px rgba(0,0,0,0.07)}
.inq-card.unread{border-left:4px solid var(--accent)}
.inq-card.read{border-left:4px solid var(--border)}
.inq-card.replied{border-left:4px solid var(--success)}

.inq-head{
    padding:16px 20px;
    display:flex;justify-content:space-between;align-items:center;
    cursor:pointer;
    gap:12px;
}
.inq-head-left{display:flex;align-items:center;gap:14px;flex:1;min-width:0}
.inq-avatar{
    width:40px;height:40px;border-radius:50%;
    background:var(--accent-light);color:var(--accent);
    display:flex;align-items:center;justify-content:center;
    font-size:15px;font-weight:700;flex-shrink:0;
    text-transform:uppercase;
}
.inq-info{flex:1;min-width:0}
.inq-name{font-size:14px;font-weight:600;margin-bottom:2px}
.inq-email{font-size:12px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.inq-subject{font-size:13px;font-weight:500;flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.inq-meta{display:flex;align-items:center;gap:12px;flex-shrink:0}
.status-badge{
    display:inline-flex;align-items:center;gap:4px;
    padding:4px 10px;border-radius:99px;
    font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;
}
.badge-unread{background:var(--accent-light);color:var(--accent)}
.badge-read{background:#f3f4f6;color:var(--muted)}
.badge-replied{background:var(--success-light);color:var(--success)}
.inq-date{font-size:11.5px;color:var(--muted)}
.toggle-arrow{font-size:12px;color:var(--muted);transition:transform 0.25s}
.inq-card.open .toggle-arrow{transform:rotate(180deg)}

.inq-body{
    display:none;
    padding:0 20px 18px 74px;
    border-top:1px solid #f3f4f6;
}
.inq-card.open .inq-body{display:block}
.inq-message{
    font-size:13.5px;line-height:1.7;color:#374151;
    margin-top:14px;margin-bottom:16px;
    background:#fafafa;border-radius:8px;
    padding:14px 16px;
    border-left:3px solid var(--border);
}
.inq-actions{display:flex;gap:8px;flex-wrap:wrap}
.btn-action{
    padding:8px 16px;border-radius:7px;
    font-family:'DM Sans',sans-serif;font-size:12.5px;font-weight:500;
    cursor:pointer;border:1.5px solid;
    transition:all 0.2s;
}
.btn-read{color:var(--accent);border-color:var(--accent);background:white}
.btn-read:hover{background:var(--accent);color:white}
.btn-replied{color:var(--success);border-color:var(--success);background:white}
.btn-replied:hover{background:var(--success);color:white}
.btn-delete{color:var(--danger);border-color:var(--danger);background:white}
.btn-delete:hover{background:var(--danger);color:white}
.btn-email{
    display:inline-flex;align-items:center;gap:6px;
    padding:8px 16px;border-radius:7px;
    background:var(--accent-light);color:var(--accent);
    border:1.5px solid transparent;
    font-family:'DM Sans',sans-serif;font-size:12.5px;font-weight:500;
    text-decoration:none;transition:all 0.2s;
}
.btn-email:hover{background:var(--accent);color:white}

/* EMPTY STATE */
.empty-state{
    background:var(--white);border:1px solid var(--border);
    border-radius:var(--radius);padding:70px 20px;
    text-align:center;
}
.empty-icon{font-size:40px;margin-bottom:14px}
.empty-state h3{font-size:17px;font-weight:600;margin-bottom:8px}
.empty-state p{font-size:13px;color:var(--muted)}

@media(max-width:900px){.main{margin-left:0}.inq-body{padding-left:20px}}
@media(max-width:600px){.toolbar{flex-direction:column;align-items:flex-start}.search-wrap{width:100%}.search-input{width:100%}}
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
        <a href="dashboard.php" class="nav-item">
            <span class="nav-icon">⬛</span> Dashboard
        </a>
        <a href="inquiries.php" class="nav-item active">
            <span class="nav-icon">✉</span> Inquiries
            <?php if($unreadCount > 0): ?>
                <span class="nav-badge"><?= $unreadCount ?></span>
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
            Inquiries
            <span>Manage customer messages</span>
        </div>
        <div class="topbar-right">
            <span class="topbar-date"><?= date('l, F j, Y') ?></span>
            <div class="admin-avatar"><?= strtoupper(substr($adminName,0,1)) ?></div>
        </div>
    </div>

    <div class="page">

        <!-- TOOLBAR -->
        <div class="toolbar">
            <div class="filter-tabs">
                <a href="inquiries.php" class="tab <?= $filter==='all'?'active':'' ?>">All <span class="tc"><?= $allCount ?></span></a>
                <a href="inquiries.php?status=unread" class="tab <?= $filter==='unread'?'active':'' ?>">Unread <span class="tc"><?= $unreadCount ?></span></a>
                <a href="inquiries.php?status=read"   class="tab <?= $filter==='read'  ?'active':'' ?>">Read <span class="tc"><?= $readCount ?></span></a>
                <a href="inquiries.php?status=replied" class="tab <?= $filter==='replied'?'active':'' ?>">Replied <span class="tc"><?= $repliedCount ?></span></a>
            </div>
            <form method="GET" class="search-wrap">
                <?php if($filter !== 'all'): ?>
                    <input type="hidden" name="status" value="<?= htmlspecialchars($filter) ?>">
                <?php endif; ?>
                <input type="text" name="search" class="search-input"
                       placeholder="Search name, email, subject..."
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn-search">Search</button>
            </form>
        </div>

        <p class="results-info">Showing <strong><?= $total ?></strong> <?= $total === 1 ? 'inquiry' : 'inquiries' ?><?= $search ? ' for "<strong>'.htmlspecialchars($search).'</strong>"' : '' ?></p>

        <!-- INQUIRY CARDS -->
        <?php if($total > 0): ?>
        <div class="inq-list">
            <?php while($inq = $inquiries->fetch_assoc()):
                $statusClass = $inq['status'];
                $badgeClass  = 'badge-'.$inq['status'];
                $badgeLabel  = ucfirst($inq['status']);
                $initial     = strtoupper(substr($inq['name'],0,1));
            ?>
            <div class="inq-card <?= $statusClass ?>" id="card-<?= $inq['id'] ?>">
                <div class="inq-head" onclick="toggleCard(<?= $inq['id'] ?>)">
                    <div class="inq-head-left">
                        <div class="inq-avatar"><?= $initial ?></div>
                        <div class="inq-info">
                            <div class="inq-name"><?= htmlspecialchars($inq['name']) ?></div>
                            <div class="inq-email"><?= htmlspecialchars($inq['email']) ?></div>
                        </div>
                        <div class="inq-subject"><?= htmlspecialchars($inq['subject']) ?></div>
                    </div>
                    <div class="inq-meta">
                        <span class="status-badge <?= $badgeClass ?>"><?= $badgeLabel ?></span>
                        <span class="inq-date"><?= date('M d, Y', strtotime($inq['created_at'])) ?></span>
                        <span class="toggle-arrow">▼</span>
                    </div>
                </div>
                <div class="inq-body">
                    <div class="inq-message"><?= nl2br(htmlspecialchars($inq['message'])) ?></div>
                    <div class="inq-actions">
                        <a href="mailto:<?= htmlspecialchars($inq['email']) ?>?subject=Re: <?= urlencode($inq['subject']) ?>" class="btn-email">
                            ✉ Reply by Email
                        </a>
                        <?php if($inq['status'] !== 'read' && $inq['status'] !== 'replied'): ?>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="inq_id" value="<?= $inq['id'] ?>">
                            <button type="submit" name="mark_read" class="btn-action btn-read">Mark as Read</button>
                        </form>
                        <?php endif; ?>
                        <?php if($inq['status'] !== 'replied'): ?>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="inq_id" value="<?= $inq['id'] ?>">
                            <button type="submit" name="mark_replied" class="btn-action btn-replied">Mark as Replied</button>
                        </form>
                        <?php endif; ?>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this inquiry?')">
                            <input type="hidden" name="inq_id" value="<?= $inq['id'] ?>">
                            <button type="submit" name="delete_inq" class="btn-action btn-delete">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">✉</div>
            <h3>No inquiries found</h3>
            <p><?= $search ? 'Try a different search term.' : 'No messages in this category yet.' ?></p>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
function toggleCard(id){
    const card = document.getElementById('card-' + id);
    card.classList.toggle('open');
}
// Auto-open unread cards
document.querySelectorAll('.inq-card.unread').forEach(c => c.classList.add('open'));
</script>

</body>
</html>