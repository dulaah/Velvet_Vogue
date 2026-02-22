<?php
session_start();
include "db.php";

$errors = [];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    if(empty($full_name) || empty($email) || empty($password)){
        $errors[] = "All fields are required.";
    }

    if($password !== $confirm_password){
        $errors[] = "Passwords do not match.";
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows > 0){
        $errors[] = "Email already registered.";
    }

    if(empty($errors)){
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $full_name, $email, $hashed_password);
        if($stmt->execute()){
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['full_name'] = $full_name;
            header("Location: index.html");
            exit;
        } else {
            $errors[] = "Registration failed. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Velvet Vogue</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body{ font-family: Arial; background:#f4f4f4; }
        .container{ max-width:400px; margin:50px auto; padding:20px; background:white; border-radius:10px; }
        input{ width:100%; padding:10px; margin:5px 0; }
        button{ width:100%; padding:12px; background:#ff4081; color:white; border:none; cursor:pointer; }
        .error{ background:#f8d7da; color:#721c24; padding:10px; margin-bottom:10px; border-radius:5px; }
        a{ color:#ff4081; text-decoration:none; }
    </style>
</head>
<body>

<div class="container">
    <h2>Register</h2>

    <?php foreach($errors as $err) echo "<div class='error'>$err</div>"; ?>

    <form method="POST">
        <input type="text" name="full_name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>

</body>
</html>