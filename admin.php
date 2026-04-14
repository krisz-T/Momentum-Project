<?php
require_once 'config.php';
require_admin();

// Fetch summary metrics
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$totalUsers = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM training_plans");
$totalPlans = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM exercises");
$totalExercises = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM workouts");
$totalWorkouts = $stmt->fetchColumn();

require_once 'header.php';
?>

<div class="page-header">
  <h1><i class="fa-solid fa-tachometer-alt"></i> Control Panel</h1>
  <nav><a href="index.php" class="icon-link" style="color:#8b92ff;"><i class="fa-solid fa-arrow-left"></i> <span>Back home</span></a></nav>
</div>

<div class="admin-section" style="margin-top: 0; padding-top: 0; border: none;">
  <p style="color: #aaa; margin-bottom: 2rem;">Overview of application telemetry and management hubs.</p>
  
  <div class="plans-list" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
    
    <div class="plan-card" style="text-align: center; padding: 3rem 1rem;">
      <h2 style="font-size: 3rem; margin-bottom: 0; color: #646cff;"><i class="fa-solid fa-users"></i></h2>
      <h3 style="font-size: 2rem; margin: 1rem 0 0.5rem 0;"><?= $totalUsers ?></h3>
      <p>Registered Athletes</p>
      <a href="admin_users.php" class="button-link" style="margin-top: 1rem; width: 100%;">Manage Users</a>
    </div>

    <div class="plan-card" style="text-align: center; padding: 3rem 1rem;">
      <h2 style="font-size: 3rem; margin-bottom: 0; color: #a5d6a7;"><i class="fa-solid fa-clipboard-list"></i></h2>
      <h3 style="font-size: 2rem; margin: 1rem 0 0.5rem 0;"><?= $totalPlans ?></h3>
      <p>Training Plans</p>
      <a href="admin_plans.php" class="button-link" style="margin-top: 1rem; width: 100%; background-color: rgba(165,214,167,0.1); color: #a5d6a7; border: 1px solid rgba(165,214,167,0.3);">Manage Plans</a>
    </div>

    <div class="plan-card" style="text-align: center; padding: 3rem 1rem;">
      <h2 style="font-size: 3rem; margin-bottom: 0; color: #ffca28;"><i class="fa-solid fa-dumbbell"></i></h2>
      <h3 style="font-size: 2rem; margin: 1rem 0 0.5rem 0;"><?= $totalExercises ?></h3>
      <p>Exercise Library</p>
      <a href="admin_exercises.php" class="button-link" style="margin-top: 1rem; width: 100%; background-color: rgba(255,202,40,0.1); color: #ffca28; border: 1px solid rgba(255,202,40,0.3);">Manage Library</a>
    </div>

    <div class="plan-card" style="text-align: center; padding: 3rem 1rem;">
      <h2 style="font-size: 3rem; margin-bottom: 0; color: #ff6b6b;"><i class="fa-solid fa-fire"></i></h2>
      <h3 style="font-size: 2rem; margin: 1rem 0 0.5rem 0;"><?= $totalWorkouts ?></h3>
      <p>Workouts Logged</p>
    </div>

  </div>
</div>

<?php require_once 'footer.php'; ?>
