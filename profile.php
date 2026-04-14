<?php
require_once 'config.php';
require_login();

$user_id = $_SESSION['user_id'];

// Handle Profile Name Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_name') {
    $new_name = $_POST['name'] ?? '';
    if (!empty($new_name)) {
        $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->execute([$new_name, $user_id]);
        $success = "Name updated beautifully.";
    }
}

// Handle Unenrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'unenroll') {
    $plan_id = (int)($_POST['plan_id'] ?? 0);
    if ($plan_id) {
        $stmt = $pdo->prepare("DELETE FROM user_plan_enrollments WHERE user_id = ? AND plan_id = ?");
        $stmt->execute([$user_id, $plan_id]);
    }
}

// Fetch user data
$stmt = $pdo->prepare("SELECT name, role, total_xp FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$userProfile = $stmt->fetch();

// Fetch enrollments
$stmt = $pdo->prepare("
    SELECT upe.plan_id, tp.title 
    FROM user_plan_enrollments upe
    JOIN training_plans tp ON upe.plan_id = tp.id
    WHERE upe.user_id = ? AND upe.status = 'active'
");
$stmt->execute([$user_id]);
$enrollments = $stmt->fetchAll();

// Fetch workouts
$stmt = $pdo->prepare("SELECT id, type, duration, date_logged FROM workouts WHERE user_id = ? ORDER BY date_logged DESC LIMIT 10");
$stmt->execute([$user_id]);
$workouts = $stmt->fetchAll();

// Fetch badges
$stmt = $pdo->prepare("SELECT badge_name FROM badges WHERE user_id = ? ORDER BY earned_at DESC");
$stmt->execute([$user_id]);
$badges = $stmt->fetchAll();

require_once 'header.php';
?>

<div>
  <div class="page-header">
    <h1>My Profile</h1>
    <nav><a href="index.php" class="icon-link" style="color:#8b92ff;"><i class="fa-solid fa-arrow-left"></i> <span>Back home</span></a></nav>
  </div>
  
  <?php if (isset($success)): ?>
      <p style="color: #a5d6a7; margin-bottom: 1rem;"><?= htmlspecialchars($success) ?></p>
  <?php endif; ?>

  <div class="profile-details">
    <?php if (isset($_GET['edit'])): ?>
      <form method="POST" class="inline-form" style="padding: 0; background-color: transparent; box-shadow: none; border: none; margin: 0 0 1rem 0">
        <input type="hidden" name="action" value="update_name">
        <input type="text" name="name" value="<?= htmlspecialchars($userProfile['name']) ?>" required style="flex-grow: 1" />
        <button type="submit" class="icon-button" style="background-color: rgba(100,108,255,0.1); color: #8b92ff; border: 1px solid rgba(100,108,255,0.3); border-radius: 8px; padding: 0.8rem;"><i class="fa-solid fa-save"></i> Save</button>
        <a href="profile.php" class="icon-button" style="padding: 0.8rem; color: #ccc;"><i class="fa-solid fa-times"></i> Cancel</a>
      </form>
    <?php else: ?>
      <div class="profile-header" style="margin-bottom: 1rem;">
        <h2 style="margin: 0; font-size: 2rem;"><?= htmlspecialchars($userProfile['name']) ?></h2>
        <a href="?edit=1" class="icon-button" title="Edit Name" style="background: transparent; border: none; color: #8b92ff; padding: 0.4rem; box-shadow: none;"><i class="fa-solid fa-pencil-alt"></i></a>
      </div>
    <?php endif; ?>
    <p style="color: #aaa; margin: 0.5rem 0;">Role: <span style="color: #fff; font-weight: 500;"><?= htmlspecialchars($userProfile['role']) ?></span></p>
    <h3 style="margin-top: 1rem; color: #a6abff">Total XP: <?= htmlspecialchars($userProfile['total_xp']) ?></h3>
  </div>

  <div class="admin-section">
    <h3>My Active Plans</h3>
    <?php if (count($enrollments) > 0): ?>
      <div class="plans-list">
        <?php foreach ($enrollments as $enrollment): ?>
          <div class="plan-card">
            <h2><?= htmlspecialchars($enrollment['title']) ?></h2>
            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
              <a href="plans.php?id=<?= $enrollment['plan_id'] ?>" class="button-link icon-link"><i class="fa-solid fa-play"></i> <span>Continue Plan</span></a>
              <form method="POST" style="margin:0; padding:0; background:transparent; border:none; box-shadow:none;">
                <input type="hidden" name="action" value="unenroll">
                <input type="hidden" name="plan_id" value="<?= $enrollment['plan_id'] ?>">
                <button type="submit" class="icon-button" style="background-color: rgba(255, 107, 107, 0.1); color: #ff6b6b; border: 1px solid rgba(255, 107, 107, 0.3); padding: 0.8em 1.4em; border-radius: 8px; font-weight: bold; cursor:pointer;"><i class="fa-solid fa-times"></i> Unenroll</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p>You are not enrolled in any active plans.</p>
    <?php endif; ?>
  </div>

  <div class="admin-section">
    <h3>Recent Workouts</h3>
    <a href="workouts.php" class="button-link" style="margin-bottom: 1rem; display:inline-block;">+ Log Workout</a>
    <?php if (count($workouts) > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Duration</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($workouts as $w): ?>
            <tr>
              <td><?= date('M j, Y', strtotime($w['date_logged'])) ?></td>
              <td><?= htmlspecialchars($w['type']) ?></td>
              <td><?= floor($w['duration']/60) ?> min</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No workouts logged yet. Get to it!</p>
    <?php endif; ?>
  </div>

  <div class="badges-section admin-section" style="border-top: none; margin-top: 1rem">
    <h3>My Badges</h3>
    <?php if (count($badges) > 0): ?>
      <ul class="badge-list">
        <?php foreach ($badges as $badge): ?>
          <li class="badge" title="Awesome achievement!">
            <i class="fa-solid fa-award"></i>
            <span><?= htmlspecialchars($badge['badge_name']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No badges earned yet. Keep going!</p>
    <?php endif; ?>
  </div>

</div>

<?php require_once 'footer.php'; ?>
