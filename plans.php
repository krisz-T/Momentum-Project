<?php
require_once 'config.php';
require_login();

$user_id = $_SESSION['user_id'];
$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'enroll') {
    $plan_id = (int)$_POST['plan_id'];
    
    // Check if already enrolled
    $stmt = $pdo->prepare("SELECT id FROM user_plan_enrollments WHERE user_id = ? AND plan_id = ? AND status = 'active'");
    $stmt->execute([$user_id, $plan_id]);
    
    if ($stmt->fetch()) {
        $error = "You are already enrolled in this plan.";
    } else {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO user_plan_enrollments (user_id, plan_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $plan_id]);
            
            // Check for Plan Starter badge
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_plan_enrollments WHERE user_id = ?");
            $stmt->execute([$user_id]);
            if ($stmt->fetchColumn() == 1) {
                // Award Badge
                $pdo->prepare("INSERT INTO badges (user_id, badge_name) VALUES (?, 'Plan Starter')")->execute([$user_id]);
            }
            $pdo->commit();
            $success = "Successfully enrolled! Let's get started.";
        } catch (\PDOException $e) {
            $pdo->rollBack();
            $error = "Failed to enroll. Please try again.";
        }
    }
}

// Fetch all plans
$stmt = $pdo->query("SELECT * FROM training_plans");
$plans = $stmt->fetchAll();

// Fetch this user's active enrollments to show disabled buttons
$stmt = $pdo->prepare("SELECT plan_id FROM user_plan_enrollments WHERE user_id = ? AND status = 'active'");
$stmt->execute([$user_id]);
$my_enrollments = $stmt->fetchAll(PDO::FETCH_COLUMN); // array of plan_ids

// If we are looking at a specific plan details
$view_plan_id = $_GET['id'] ?? null;
$view_plan = null;
if ($view_plan_id) {
    $stmt = $pdo->prepare("SELECT * FROM training_plans WHERE id = ?");
    $stmt->execute([$view_plan_id]);
    $view_plan = $stmt->fetch();
    
    // Attempt to fetch plan schedule
    if ($view_plan) {
        $stmt = $pdo->prepare("
            SELECT pw.*, GROUP_CONCAT(e.name SEPARATOR ', ') as exercises_list
            FROM plan_workouts pw
            LEFT JOIN workout_exercises we ON pw.id = we.plan_workout_id
            LEFT JOIN exercises e ON we.exercise_id = e.id
            WHERE pw.plan_id = ?
            GROUP BY pw.id
            ORDER BY pw.day_of_plan ASC
        ");
        $stmt->execute([$view_plan_id]);
        $view_plan['workouts'] = $stmt->fetchAll();
    }
}

require_once 'header.php';
?>

<?php if ($view_plan): ?>
  <div class="page-header">
    <h1><i class="fa-solid fa-clipboard-list"></i> <?= htmlspecialchars($view_plan['title']) ?></h1>
    <nav><a href="plans.php" class="icon-link" style="color:#8b92ff;"><i class="fa-solid fa-arrow-left"></i> <span>Back to Plans</span></a></nav>
  </div>
  
  <div class="profile-details">
      <p style="font-size: 1.2rem; line-height: 1.6; margin-bottom: 2rem;"><?= htmlspecialchars($view_plan['description']) ?></p>
      
      <?php if ($error): ?>
          <p style="color: #ff6b6b; font-weight:bold;"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>
      <?php if ($success): ?>
          <p style="color: #a5d6a7; font-weight:bold;"><?= htmlspecialchars($success) ?></p>
      <?php endif; ?>
      
      <?php if (in_array($view_plan['id'], $my_enrollments)): ?>
         <div class="enrolled-message"><i class="fa-solid fa-check-circle"></i> You are actively enrolled in this plan. Check your <a href="profile.php" style="color: #8b92ff; text-decoration: underline; margin-left: 5px;">Profile</a>.</div>
      <?php else: ?>
          <form method="POST" style="margin:0; padding:0; background:transparent; border:none; box-shadow:none;">
            <input type="hidden" name="action" value="enroll">
            <input type="hidden" name="plan_id" value="<?= $view_plan['id'] ?>">
            <button type="submit" class="button-link" style="border:none; cursor:pointer;"><i class="fa-solid fa-plus-circle"></i> Enroll in Plan</button>
          </form>
      <?php endif; ?>
  </div>
  
  <div class="workout-schedule">
      <h3 style="margin-bottom: 1rem;"><i class="fa-solid fa-calendar-alt"></i> Schedule</h3>
      <?php if (!empty($view_plan['workouts'])): ?>
          <?php foreach ($view_plan['workouts'] as $workout): ?>
              <div class="workout-schedule-item">
                  <span style="font-weight:bold; color: #8b92ff;">Day <?= $workout['day_of_plan'] ?></span> - 
                  <span style="font-weight: bold;"><?= htmlspecialchars($workout['workout_type']) ?></span> 
                  <span style="color:#aaa;">(<?= $workout['suggested_duration'] / 60 ?> mins)</span>
                  <?php if (!empty($workout['exercises_list'])): ?>
                      <p style="margin-top: 0.5rem; color:#ccc;"><i class="fa-solid fa-dumbbell"></i> <?= htmlspecialchars($workout['exercises_list']) ?></p>
                  <?php endif; ?>
              </div>
          <?php endforeach; ?>
      <?php else: ?>
          <p style="color: #aaa;">This plan has no scheduled workouts yet.</p>
      <?php endif; ?>
  </div>

<?php else: ?>
  <div class="page-header">
    <h1><i class="fa-solid fa-clipboard-list"></i> Training Plans</h1>
  </div>
  
  <div class="plans-list">
      <?php foreach ($plans as $plan): ?>
          <div class="plan-card">
              <h2><?= htmlspecialchars($plan['title']) ?></h2>
              <p><?= htmlspecialchars($plan['description']) ?></p>
              <span>Duration: <?= $plan['duration_weeks'] ?> Weeks</span>
              
              <div style="display: flex; gap: 1rem;">
                  <a href="plans.php?id=<?= $plan['id'] ?>" class="button-link" style="flex: 1;"><i class="fa-solid fa-eye"></i> View</a>
                  <?php if (in_array($plan['id'], $my_enrollments)): ?>
                      <button disabled class="button-link" style="flex: 1; background-color: #555; cursor: not-allowed;"><i class="fa-solid fa-check"></i> Enrolled</button>
                  <?php else: ?>
                      <form method="POST" style="margin:0; padding:0; background:transparent; border:none; box-shadow:none; flex: 1; display:flex;">
                        <input type="hidden" name="action" value="enroll">
                        <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                        <button type="submit" class="button-link" style="border:none; cursor:pointer; width: 100%;"><i class="fa-solid fa-plus-circle"></i> Enroll</button>
                      </form>
                  <?php endif; ?>
              </div>
          </div>
      <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
