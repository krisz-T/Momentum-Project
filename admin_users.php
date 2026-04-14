<?php
require_once 'config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $target_user_id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'];

    if ($target_user_id && $target_user_id !== $_SESSION['user_id']) {
        if ($action === 'ban') {
            $stmt = $pdo->prepare("UPDATE users SET is_banned = 1 WHERE id = ?");
            $stmt->execute([$target_user_id]);
            set_flash_message('success', 'User has been banned.');
        } elseif ($action === 'unban') {
            $stmt = $pdo->prepare("UPDATE users SET is_banned = 0 WHERE id = ?");
            $stmt->execute([$target_user_id]);
            set_flash_message('success', 'User ban lifted.');
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$target_user_id]);
            set_flash_message('success', 'User completely deleted from existence.');
        }
    } else {
        set_flash_message('error', 'Cannot perform action on yourself.');
    }
    
    header("Location: admin_users.php");
    exit;
}

$stmt = $pdo->query("SELECT id, name, email, role, is_banned, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

require_once 'header.php';
?>

<div class="page-header">
  <h1><i class="fa-solid fa-users"></i> Manage Athletes</h1>
  <nav><a href="admin.php" class="icon-link" style="color:#8b92ff;"><i class="fa-solid fa-arrow-left"></i> <span>Back to Control Panel</span></a></nav>
</div>

<div class="admin-section" style="margin-top: 0; padding-top: 0; border: none;">
  <table>
    <thead>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $user): ?>
      <tr>
        <td>
            <?= htmlspecialchars($user['name']) ?>
            <?php if ($user['role'] === 'Admin') echo " <span style='font-size:0.8em; color:#ffca28; padding:0.2em 0.5em; border: 1px solid rgba(255,202,40,0.3); border-radius:10px; background:rgba(255,202,40,0.1); margin-left:0.5rem;'>Admin</span>"; ?>
        </td>
        <td style="color:#aaa;"><?= htmlspecialchars($user['email']) ?></td>
        <td>
            <?php if ($user['is_banned']): ?>
                <span style="color:#ff6b6b;"><i class="fa-solid fa-ban"></i> Banned</span>
            <?php else: ?>
                <span style="color:#a5d6a7;">Active</span>
            <?php endif; ?>
        </td>
        <td class="action-cell">
          <div class="action-buttons">
            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                <form method="POST" style="margin:0; padding:0; background:transparent; border:none; box-shadow:none;">
                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                    <?php if ($user['is_banned']): ?>
                        <input type="hidden" name="action" value="unban">
                        <button type="submit" class="icon-button" title="Unban Athlete" onclick="return confirm('Restore this user account?')" style="padding: 0.5rem; background: rgba(165,214,167,0.1); color: #a5d6a7; border-radius: 6px;"><i class="fa-solid fa-check"></i></button>
                    <?php else: ?>
                        <input type="hidden" name="action" value="ban">
                        <button type="submit" class="icon-button" title="Ban Athlete" onclick="return confirm('Ban this user? They will not be able to log workouts.')" style="padding: 0.5rem; background: rgba(255,202,40,0.1); color: #ffca28; border-radius: 6px;"><i class="fa-solid fa-ban"></i></button>
                    <?php endif; ?>
                </form>
                
                <form method="POST" style="margin:0; padding:0; background:transparent; border:none; box-shadow:none;">
                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="icon-button" title="PERMANENTLY Delete Athlete" onclick="return confirm('WARNING: Permanently delete athlete and all data? Cannot be undone.')" style="padding: 0.5rem; background: rgba(255,107,107,0.1); color: #ff6b6b; border-radius: 6px;"><i class="fa-solid fa-trash"></i></button>
                </form>
            <?php else: ?>
                <span style="color:#555;">(You)</span>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once 'footer.php'; ?>
