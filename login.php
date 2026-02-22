<?php
session_start();
include "db.php";

$errors = [];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = trim($_POST['email']);
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
                $_SESSION['user_id'] = $id;
                $_SESSION['full_name'] = $full_name;
                header("Location: index.html");
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
    <h2>Login</h2>

    <?php foreach($errors as $err) echo "<div class='error'>$err</div>"; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a></p>
</div>

</body>
</html>