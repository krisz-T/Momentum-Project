<?php require_once 'config.php'; require_once 'header.php'; ?>

<div class="auth-page-wrapper">
  <h1 class="brand-title">
    <i class="fa-solid fa-dumbbell" style="margin-right: 10px; vertical-align: middle;"></i>
    Momentum
  </h1>

  <div class="auth-container">
    <form action="auth.php" method="POST">
      <input type="hidden" name="action" value="login">
      <h1>Sign In</h1>
      <p>Sign in to track your progress</p>
      
      <?php if (isset($_GET['error'])): ?>
        <p style="color: #ff6b6b;"><?= htmlspecialchars($_GET['error']) ?></p>
      <?php endif; ?>
      
      <div>
        <label for="email">Email</label>
        <input id="email" name="email" type="email" required />
      </div>
      
      <div>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required />
      </div>
      
      <button type="submit" class="button-link" style="border:none; cursor:pointer;">Sign In</button>
    </form>
    
    <div class="auth-toggle" style="margin-top: 1rem; text-align: center;">
      <p>
        Don't have an account? 
        <a href="register.php" style="color: #646cff; text-decoration: underline;">Sign Up</a>
      </p>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>
