<?php
require_once 'config.php';
require_login();

$user_id = $_SESSION['user_id'];
$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'log_workout') {
    $type = $_POST['type'] ?? '';
    // Expected duration in minutes
    $duration_minutes = (int)($_POST['duration'] ?? 0);
    $duration_seconds = $duration_minutes * 60; // We store it as seconds/minutes depending on design, old system stored seconds apparently, but let's assume we store seconds to match 'duration/60'
    
    if (empty($type) || $duration_seconds <= 0) {
        $error = "Please provide a valid workout type and duration.";
    } else {
        $stmt = $pdo->prepare("SELECT is_banned FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if ($user['is_banned']) {
            $error = "Account suspended. Cannot log workouts.";
        } else {
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("INSERT INTO workouts (user_id, type, duration) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $type, $duration_seconds]);
                
                $xpGained = (int)floor($duration_seconds / 4);
                $stmt = $pdo->prepare("UPDATE users SET total_xp = total_xp + ? WHERE id = ?");
                $stmt->execute([$xpGained, $user_id]);
                
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM workouts WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $count = $stmt->fetchColumn();
                
                $milestoneBadges = [1 => 'First Workout', 5 => '5-Workout Mark', 10 => '10 Workouts', 50 => '50 Workouts', 100 => '100 Workouts Club'];
                if (isset($milestoneBadges[$count])) {
                    $badgeName = $milestoneBadges[$count];
                    $checkBadge = $pdo->prepare("SELECT id FROM badges WHERE user_id = ? AND badge_name = ?");
                    $checkBadge->execute([$user_id, $badgeName]);
                    if (!$checkBadge->fetch()) {
                        $pdo->prepare("INSERT INTO badges (user_id, badge_name) VALUES (?, ?)")->execute([$user_id, $badgeName]);
                    }
                }
                
                $pdo->commit();
                $success = "Successfully logged $duration_minutes minutes of $type! Gained $xpGained XP.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Failed to log workout.";
            }
        }
    }
}

// Fetch Leaderboard (All-Time)
$stmt = $pdo->query("SELECT id, name, total_xp FROM users WHERE is_banned = 0 ORDER BY total_xp DESC LIMIT 10");
$leaderboard = $stmt->fetchAll();

require_once 'header.php';
?>

<div class="page-header">
  <h1><i class="fa-solid fa-trophy"></i> Workouts & Leaderboard</h1>
</div>

<div style="display:flex; gap: 2rem; flex-wrap: wrap;">
    <!-- Log Workout Form -->
    <div style="flex: 1; min-width: 300px;">
        <form method="POST" style="margin-top: 0;">
            <h2>Log a Workout</h2>
            <?php if ($error): ?>
                <p style="color: #ff6b6b;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p style="color: #a5d6a7;"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>
            <input type="hidden" name="action" value="log_workout">
            
            <div class="form-group">
                <label for="type">Workout Type</label>
                <input type="text" id="type" name="type" placeholder="e.g. Running, Weightlifting" required>
            </div>
            
            <div class="form-group" style="margin-top: 1rem;">
                <label for="duration">Duration (Minutes)</label>
                <input type="number" id="duration" name="duration" required min="1">
            </div>
            
            <button type="submit" class="button-link" style="border:none; cursor:pointer; margin-top: 1.5rem;"><i class="fa-solid fa-plus"></i> Submit Workout</button>
        </form>
    </div>

    <!-- Leaderboard -->
    <div class="admin-section" style="flex: 1; min-width: 300px; margin-top: 0; padding-top: 0; border: none;">
        <div class="profile-details" style="margin-top: 0; padding: 2rem;">
            <h2>Top Athletes</h2>
            <p style="color:#aaa; margin-bottom: 1.5rem;">All-Time XP Rankings</p>
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Athlete</th>
                        <th>XP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaderboard as $index => $player): ?>
                    <tr <?= $player['id'] == $user_id ? 'style="background-color: rgba(100,108,255,0.2)"' : '' ?>>
                        <td style="font-weight: bold; color: <?= $index == 0 ? '#ffd700' : ($index == 1 ? '#c0c0c0' : ($index == 2 ? '#cd7f32' : '#8b92ff')) ?>;">#<?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($player['name']) ?> <?= $player['id'] == $user_id ? '(You)' : '' ?></td>
                        <td><?= number_format($player['total_xp']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
