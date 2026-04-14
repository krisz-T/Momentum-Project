<?php
require_once 'config.php';

// If logged in, redirect to profile
if (isset($_SESSION['user_id'])) {
    header("Location: profile.php");
    exit;
}

require_once 'header.php';
?>

<div class="auth-page-wrapper" style="text-align: center;">
  <h1 class="brand-title" style="font-size: 4rem; margin-bottom: 0.5rem;">
    <i class="fa-solid fa-dumbbell" style="margin-right: 15px; vertical-align: middle;"></i>
    Momentum
  </h1>
  <p style="color: #ccc; font-size: 1.5rem; max-width: 600px; margin: 0 auto 3rem auto; line-height: 1.6;">
    Your brutalist gym tracker. <br> Stop thinking. Start lifting.
  </p>

  <div style="display: flex; gap: 1.5rem; justify-content: center;">
    <a href="login.php" class="button-link" style="padding: 1rem 2.5rem; font-size: 1.2rem;"><i class="fa-solid fa-sign-in-alt"></i> Sign In</a>
    <a href="register.php" class="button-link" style="padding: 1rem 2.5rem; font-size: 1.2rem; background-color: rgba(100, 108, 255, 0.1); border: 1px solid rgba(100, 108, 255, 0.4);"><i class="fa-solid fa-user-plus"></i> Create Account</a>
  </div>
</div>

<?php require_once 'footer.php'; ?>
