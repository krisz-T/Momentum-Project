<?php
require_once 'config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'create') {
        $name = $_POST['name'] ?? '';
        $desc = $_POST['description'] ?? '';
        $video = $_POST['video_url'] ?? '';

        if (!empty($name)) {
            $stmt = $pdo->prepare("INSERT INTO exercises (name, description, video_url) VALUES (?, ?, ?)");
            $stmt->execute([$name, $desc, $video]);
            set_flash_message('success', 'Exercise added to library.');
        } else {
            set_flash_message('error', 'Exercise name cannot be empty.');
        }
    } elseif ($action === 'delete') {
        $exercise_id = (int)($_POST['id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM exercises WHERE id = ?");
            $stmt->execute([$exercise_id]);
            set_flash_message('success', 'Exercise permanently removed.');
        } catch (PDOException $e) {
            // It could be tied to a plan_workout due to RESTRICT constraint
            set_flash_message('error', 'Cannot delete exercise - it is currently actively used in a training plan.');
        }
    }
    
    header("Location: admin_exercises.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM exercises ORDER BY name ASC");
$exercises = $stmt->fetchAll();

require_once 'header.php';
?>

<div class="page-header">
  <h1><i class="fa-solid fa-dumbbell"></i> Exercise Library</h1>
  <nav><a href="admin.php" class="icon-link" style="color:#8b92ff;"><i class="fa-solid fa-arrow-left"></i> <span>Back to Control Panel</span></a></nav>
</div>

<div style="display:flex; gap: 2rem; flex-wrap: wrap;">

    <!-- Creation Form -->
    <div style="flex: 1; min-width: 300px;">
        <form method="POST" style="margin-top: 0; background: rgba(25, 26, 35, 0.8);">
            <h2><i class="fa-solid fa-plus"></i> New Exercise</h2>
            <input type="hidden" name="action" value="create">
            
            <div class="form-group" style="margin-top: 1rem;">
                <label for="name">Exercise Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group" style="margin-top: 1rem;">
                <label for="description">Description & Cues</label>
                <textarea id="description" name="description" rows="4"></textarea>
            </div>
            
            <div class="form-group" style="margin-top: 1rem;">
                <label for="video_url">Video Embed URL (Optional)</label>
                <input type="url" id="video_url" name="video_url" placeholder="https://youtube.com/embed/...">
            </div>
            
            <button type="submit" class="button-link" style="border:none; cursor:pointer; margin-top: 1.5rem;"><i class="fa-solid fa-save"></i> Save Exercise</button>
        </form>
    </div>

    <!-- Overview List -->
    <div class="admin-section" style="flex: 2; min-width: 400px; margin-top: 0; padding-top: 0; border: none;">
        <div class="manage-plans-list" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
            <?php foreach ($exercises as $ex): ?>
            <div class="manage-plan-item" style="flex-direction: column; align-items: flex-start; padding: 1.5rem;">
                <h3 style="margin: 0 0 0.5rem 0; font-size: 1.2rem;"><?= htmlspecialchars($ex['name']) ?></h3>
                <p style="color: #aaa; font-size: 0.9rem; margin-bottom: 1rem; flex-grow: 1;"><?= nl2br(htmlspecialchars($ex['description'])) ?></p>
                
                <div style="display: flex; gap: 0.5rem; width: 100%;">
                    <?php if (!empty($ex['video_url'])): ?>
                    <a href="<?= htmlspecialchars($ex['video_url']) ?>" target="_blank" class="manage-action-btn manage-edit-btn" style="flex: 1; text-align: center;"><i class="fa-solid fa-play"></i> Video</a>
                    <?php else: ?>
                    <span class="manage-action-btn" style="flex: 1; opacity: 0.5; color: #555; text-align: center; border: 1px dashed #444;"><i class="fa-solid fa-video-slash"></i> No Video</span>
                    <?php endif; ?>

                    <form method="POST" style="margin:0; padding:0; background:transparent; border:none; box-shadow:none; flex: 1; display: flex;">
                        <input type="hidden" name="id" value="<?= $ex['id'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="manage-action-btn manage-delete-btn" style="width: 100%; border:none; cursor:pointer;" onclick="return confirm('Delete this exercise from the library?')"><i class="fa-solid fa-trash"></i> Delete</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php require_once 'footer.php'; ?>
