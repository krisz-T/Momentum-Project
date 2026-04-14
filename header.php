<?php
// header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Momentum</title>
  
  <link rel="stylesheet" href="style.css">
  
  <!-- Font Awesome for Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Modern Font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
  
  <style>
    body { font-family: 'Inter', sans-serif; background-color: #0f1016; color: rgba(255, 255, 255, 0.87); }
    .icon-button { background: none; border: none; cursor: pointer; color: inherit; }
  </style>
</head>
<body>
  <div id="root">
    <div class="container">
      <?php if (isset($_SESSION['user_id'])): ?>
      <header class="app-header">
        <div class="header-left">
          <a href="index.php" class="brand-link" style="text-decoration:none; color:white;">
            <i class="fa-solid fa-dumbbell"></i>
            <span>Momentum</span>
          </a>
        </div>
        <div class="header-right">
          <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
            <a href="admin.php" class="icon-link" title="Admin Dashboard"><i class="fa-solid fa-tachometer-alt"></i></a>
          <?php endif; ?>
          <a href="plans.php" class="icon-link" title="Training Plans"><i class="fa-solid fa-clipboard-list"></i></a>
          <a href="profile.php" class="icon-link" title="My Profile"><i class="fa-solid fa-user"></i></a>
          <span style="color: #888; margin: 0 0.5rem">|</span>
          <form action="auth.php" method="POST" style="margin:0; padding:0; background:transparent; display:inline; box-shadow:none; border:none; width:auto;">
            <input type="hidden" name="action" value="logout">
            <button type="submit" class="icon-button" title="Logout" style="padding: 0.4rem 0.8rem; font-size: 0.9rem; background-color: transparent; border: 1px solid rgba(255,107,107,0.3); color: #ff6b6b; border-radius:8px;">
              <i class="fa-solid fa-sign-out-alt"></i>
              <span>Logout</span>
            </button>
          </form>
        </div>
      </header>
      <?php endif; ?>

      <?php 
      if (isset($_SESSION['flash'])) {
          if ($err = get_flash_message('error')) {
              echo "<div style='background: rgba(255,107,107,0.1); border: 1px solid rgba(255,107,107,0.3); color: #ff6b6b; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; font-weight: bold;'><i class='fa-solid fa-circle-exclamation'></i> ".htmlspecialchars($err)."</div>";
          }
          if ($succ = get_flash_message('success')) {
              echo "<div style='background: rgba(100,108,255,0.1); border: 1px solid rgba(100,108,255,0.3); color: #a6abff; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; font-weight: bold;'><i class='fa-solid fa-check-circle'></i> ".htmlspecialchars($succ)."</div>";
          }
      }
      ?>
