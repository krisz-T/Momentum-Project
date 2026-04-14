<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $stmt = $pdo->prepare("SELECT id, password_hash, role, is_banned FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['is_banned']) {
                $error = "This account has been banned.";
                header("Location: login.php?error=" . urlencode($error));
                exit;
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header("Location: profile.php");
            exit;
        } else {
            $error = "Invalid email or password.";
            header("Location: login.php?error=" . urlencode($error));
            exit;
        }
    }
    
    elseif ($action === 'register') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($name) || empty($email) || empty($password)) {
            header("Location: register.php?error=" . urlencode("All fields are required."));
            exit;
        }
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            header("Location: register.php?error=" . urlencode("Email is already in use."));
            exit;
        }
        
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, name, role) VALUES (?, ?, ?, 'User')");
        if ($stmt->execute([$email, $hash, $name])) {
            $new_user_id = $pdo->lastInsertId();
            $_SESSION['user_id'] = $new_user_id;
            $_SESSION['role'] = 'User';
            header("Location: profile.php");
            exit;
        } else {
            header("Location: register.php?error=" . urlencode("Registration failed. Please try again."));
            exit;
        }
    }
    
    elseif ($action === 'logout') {
        session_destroy();
        header("Location: login.php");
        exit;
    }
}
?>
