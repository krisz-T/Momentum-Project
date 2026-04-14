<?php
require_once 'config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'create_plan') {
        $title = $_POST['title'] ?? '';
        $desc = $_POST['description'] ?? '';
        $duration = (int)($_POST['duration_weeks'] ?? 0);

        if (!empty($title) && $duration > 0) {
            $stmt = $pdo->prepare("INSERT INTO training_plans (title, description, duration_weeks) VALUES (?, ?, ?)");
            $stmt->execute([$title, $desc, $duration]);
            set_flash_message('success', 'Training Plan created successfully.');
        } else {
            set_flash_message('error', 'Title and valid duration are required.');
        }
        header("Location: admin_plans.php");
        exit;
        
    } elseif ($action === 'delete_plan') {
        $plan_id = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM training_plans WHERE id = ?");
        $stmt->execute([$plan_id]);
        set_flash_message('success', 'Training Plan permanently deleted.');
        header("Location: admin_plans.php");
        exit;
        
    } elseif ($action === 'add_workout') {
        $plan_id = (int)($_POST['plan_id'] ?? 0);
        $day = (int)($_POST['day_of_plan'] ?? 0);
        $type = $_POST['workout_type'] ?? '';
        $suggested_duration = (int)($_POST['suggested_duration'] ?? 0) * 60; // stored in seconds
        
        $stmt = $pdo->prepare("INSERT INTO plan_workouts (plan_id, day_of_plan, workout_type, suggested_duration) VALUES (?, ?, ?, ?)");
        $stmt->execute([$plan_id, $day, $type, $suggested_duration]);
        set_flash_message('success', 'Workout schedule node added to plan.');
        header("Location: admin_plans.php?manage=" . $plan_id);
        exit;
    }
}

// Check if we are managing a specific plan
$manage_id = $_GET['manage'] ?? null;
$managed_plan = null;
if ($manage_id) {
    $stmt = $pdo->prepare("SELECT * FROM training_plans WHERE id = ?");
    $stmt->execute([$manage_id]);
    $managed_plan = $stmt->fetch();
    
    // Fetch its scheduled workouts
    $stmt = $pdo->prepare("SELECT * FROM plan_workouts WHERE plan_id = ? ORDER BY day_of_plan ASC");
    $stmt->execute([$manage_id]);
    $managed_plan['workouts'] = $stmt->fetchAll();
}

$stmt = $pdo->query("SELECT * FROM training_plans ORDER BY created_at DESC");
$plans = $stmt->fetchAll();

require_once 'header.php';
?>

<div class="page-header">
  <h1><i class="fa-solid fa-clipboard-list"></i> Manage Training Plans</h1>
  <nav><a href="admin.php" class="icon-link" style="color:#8b92ff;"><i class="fa-solid fa-arrow-left"></i> <span>Back to Control Panel</span></a></nav>
</div>

<?php if ($managed_plan): ?>
<!-- Manage Specific Plan Context -->
<div style="background: rgba(25, 26, 35, 0.6); padding: 2rem; border-radius: 12px; margin-bottom: 2rem;">
    <h2>Editing: <?= htmlspecialchars($managed_plan['title']) ?></h2>
    <p style="color: #aaa;">Duration: <?= $managed_plan['duration_weeks'] ?> Weeks</p>
    
    <hr style="margin: 2rem 0; border-color: rgba(255,255,255,0.1);">
    
    <div style="display:flex; gap: 2rem; flex-wrap: wrap;">
        <!-- Add Workout Node -->
        <div style="flex: 1; min-width: 300px;">
            <h3>Add Workout Node</h3>
            <form method="POST" style="margin-top: 1rem; padding: 1.5rem; background: rgba(0,0,0,0.2);">
                <input type="hidden" name="action" value="add_workout">
                <input type="hidden" name="plan_id" value="<?= $managed_plan['id'] ?>">
                
                <div class="form-group" style="margin-top: 1rem;">
                    <label>Day of Plan</label>
                    <input type="number" name="day_of_plan" required min="1" max="<?= $managed_plan['duration_weeks'] * 7 ?>">
                </div>
                <div class="form-group" style="margin-top: 1rem;">
                    <label>Workout Type (Focus)</label>
                    <input type="text" name="workout_type" placeholder="e.g. Upper Body Strength" required>
                </div>
                <div class="form-group" style="margin-top: 1rem;">
                    <label>Suggested Duration (Minutes)</label>
                    <input type="number" name="suggested_duration" required min="1">
                </div>
                
                <button type="submit" class="button-link" style="border:none; cursor:pointer; margin-top: 1.5rem;"><i class="fa-solid fa-plus"></i> Add to Schedule</button>
            </form>
        </div>
        
        <!-- Schedule List -->
        <div style="flex: 2; min-width: 400px;">
            <h3>Current Schedule</h3>
            <?php if (empty($managed_plan['workouts'])): ?>
                <p style="color: #aaa; margin-top: 1rem;">No workouts scheduled yet. Add one to get started.</p>
            <?php else: ?>
                <div style="margin-top: 1rem;">
                    <?php foreach ($managed_plan['workouts'] as $workout): ?>
                    <div class="manage-plan-item" style="margin-bottom: 0.5rem; padding: 1rem;">
                        <div>
                            <span style="font-weight: bold; color: #8b92ff; margin-right: 1rem;">Day <?= $workout['day_of_plan'] ?></span>
                            <span><?= htmlspecialchars($workout['workout_type']) ?></span>
                            <span style="color: #aaa; font-size: 0.9em; margin-left: 1rem;"><i class="fa-regular fa-clock"></i> <?= $workout['suggested_duration'] / 60 ?> min</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Overview Lists and Creations -->
<div style="display:flex; gap: 2rem; flex-wrap: wrap;">

    <!-- Creation Form -->
    <div style="flex: 1; min-width: 300px;">
        <form method="POST" style="margin-top: 0; background: rgba(25, 26, 35, 0.8);">
            <h2><i class="fa-solid fa-plus"></i> New Training Plan</h2>
            <input type="hidden" name="action" value="create_plan">
            
            <div class="form-group" style="margin-top: 1rem;">
                <label>Plan Title</label>
                <input type="text" name="title" required>
            </div>
            
            <div class="form-group" style="margin-top: 1rem;">
                <label>Description</label>
                <textarea name="description" rows="4"></textarea>
            </div>
            
            <div class="form-group" style="margin-top: 1rem;">
                <label>Duration (Weeks)</label>
                <input type="number" name="duration_weeks" required min="1">
            </div>
            
            <button type="submit" class="button-link" style="border:none; cursor:pointer; margin-top: 1.5rem;"><i class="fa-solid fa-save"></i> Save Plan Template</button>
        </form>
    </div>

    <!-- Overview List -->
    <div class="admin-section" style="flex: 2; min-width: 400px; margin-top: 0; padding-top: 0; border: none;">
        <div class="manage-plans-list" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
            <?php foreach ($plans as $plan): ?>
            <div class="manage-plan-item" style="flex-direction: column; align-items: flex-start; padding: 1.5rem;">
                <h3 style="margin: 0 0 0.5rem 0; font-size: 1.2rem;"><?= htmlspecialchars($plan['title']) ?></h3>
                <span style="color: #8b92ff; font-weight: bold; font-size: 0.9em; margin-bottom: 0.5rem;"><i class="fa-regular fa-calendar"></i> <?= $plan['duration_weeks'] ?> Weeks</span>
                <p style="color: #aaa; font-size: 0.9rem; margin-bottom: 1rem; flex-grow: 1;"><?= nl2br(htmlspecialchars($plan['description'])) ?></p>
                
                <div style="display: flex; gap: 0.5rem; width: 100%;">
                    <a href="admin_plans.php?manage=<?= $plan['id'] ?>" class="manage-action-btn manage-edit-btn" style="flex: 1; text-align: center;"><i class="fa-solid fa-pencil"></i> Edit Plan</a>

                    <form method="POST" style="margin:0; padding:0; background:transparent; border:none; box-shadow:none; flex: 1; display: flex;">
                        <input type="hidden" name="id" value="<?= $plan['id'] ?>">
                        <input type="hidden" name="action" value="delete_plan">
                        <button type="submit" class="manage-action-btn manage-delete-btn" style="width: 100%; border:none; cursor:pointer;" onclick="return confirm('PERMANENTLY drop this plan and all associated enrollments?')"><i class="fa-solid fa-trash"></i> Drop</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php require_once 'footer.php'; ?>
