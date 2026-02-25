<?php
// Include this at the top of every admin page
if(!isset($_SESSION['admin_id'])){
    header("Location: admin_login.php");
    exit;
}