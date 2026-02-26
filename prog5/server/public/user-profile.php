<?php
require_once '../config/db.php';
require_once '../src/auth.php';
require_once '../includes/auth.php';

if (!isAuthenticated()) {
    header("Location: login.php");
    exit();
}

$profileId = (int)($_GET['id'] ?? 0);
if (!$profileId) {
    header("Location: users.php");
    exit();
}

$stmt = $pdo->prepare("SELECT id, username, fullname, email, phone, avatar, role FROM users WHERE id = ?");
$stmt->execute([$profileId]);
$profileUser = $stmt->fetch();
if (!$profileUser) {
    header("Location: users.php");
    exit();
}

$myId = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'send') {
        $content = trim($_POST['content'] ?? '');
        if ($content === '') {
            $error = 'Message cannot be empty.';
        } else {
            $ins = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
            $ins->execute([$myId, $profileId, $content]);
            $success = 'Message sent.';
        }
    } elseif ($action === 'edit') {
        $msgId  = (int)($_POST['msg_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        // Only the sender can edit
        $chk = $pdo->prepare("SELECT sender_id FROM messages WHERE id = ?");
        $chk->execute([$msgId]);
        $msg = $chk->fetch();
        if ($msg && (int)$msg['sender_id'] === $myId && $content !== '') {
            $upd = $pdo->prepare("UPDATE messages SET content = ?, updated_at = NOW() WHERE id = ?");
            $upd->execute([$content, $msgId]);
            $success = 'Message updated.';
        } else {
            $error = 'Cannot edit this message.';
        }
    } elseif ($action === 'delete') {
        $msgId = (int)($_POST['msg_id'] ?? 0);
        $chk = $pdo->prepare("SELECT sender_id FROM messages WHERE id = ?");
        $chk->execute([$msgId]);
        $msg = $chk->fetch();
        if ($msg && (int)$msg['sender_id'] === $myId) {
            $del = $pdo->prepare("DELETE FROM messages WHERE id = ?");
            $del->execute([$msgId]);
            $success = 'Message deleted.';
        } else {
            $error = 'Cannot delete this message.';
        }
    }
    header("Location: user-profile.php?id=$profileId" . ($error ? '&err=1' : ''));
    exit();
}

$error = isset($_GET['err']) ? 'Action failed.' : '';

$messages = $pdo->prepare(
    "SELECT m.*, u.fullname AS sender_name, u.username AS sender_username, u.avatar AS sender_avatar
     FROM messages m JOIN users u ON m.sender_id = u.id
     WHERE m.receiver_id = ?
     ORDER BY m.created_at ASC"
);
$messages->execute([$profileId]);
$messages = $messages->fetchAll();

$av = $profileUser['avatar'] ?? '';
$avHtml = $av ? (filter_var($av, FILTER_VALIDATE_URL) ? htmlspecialchars($av) : htmlspecialchars('uploads/avatars/' . basename($av))) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassDoc | <?php echo htmlspecialchars($profileUser['fullname']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
<?php include '../includes/nav.php'; ?>
<div class="max-w-3xl mx-auto px-4 py-8">

    <!-- Profile Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6 flex items-center gap-5">
        <?php if ($avHtml): ?>
            <img src="<?php echo $avHtml; ?>" alt="avatar" class="w-20 h-20 rounded-full object-cover border-2 border-gray-100 shrink-0">
        <?php else: ?>
            <div class="w-20 h-20 rounded-full <?php echo $profileUser['role'] === 'teacher' ? 'bg-orange-100 text-orange-600' : 'bg-blue-100 text-blue-600'; ?> flex items-center justify-center text-3xl font-bold shrink-0">
                <?php echo htmlspecialchars(mb_substr($profileUser['fullname'], 0, 1)); ?>
            </div>
        <?php endif; ?>
        <div>
            <h1 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($profileUser['fullname']); ?></h1>
            <p class="text-gray-500 text-sm">@<?php echo htmlspecialchars($profileUser['username']); ?></p>
            <span class="inline-block mt-1 text-xs px-2 py-0.5 rounded-full <?php echo $profileUser['role'] === 'teacher' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700'; ?>">
                <?php echo $profileUser['role'] === 'teacher' ? 'Teacher' : 'Student'; ?>
            </span>
            <?php if ($profileUser['email']): ?><p class="text-sm text-gray-600 mt-2">✉️ <?php echo htmlspecialchars($profileUser['email']); ?></p><?php endif; ?>
            <?php if ($profileUser['phone']): ?><p class="text-sm text-gray-600">📱 <?php echo htmlspecialchars($profileUser['phone']); ?></p><?php endif; ?>
        </div>
    </div>

    <!-- Messages -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Messages</h2>

        <?php if ($error): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded-xl text-sm mb-4"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="bg-green-50 text-green-600 p-3 rounded-xl text-sm mb-4"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Message list -->
        <div class="space-y-4 mb-6">
            <?php if (empty($messages)): ?>
                <p class="text-gray-400 text-sm text-center py-4">No messages yet. Be the first to say something!</p>
            <?php endif; ?>
            <?php foreach ($messages as $m): ?>
            <?php
                $sav = $m['sender_avatar'] ?? '';
                $savHtml = $sav ? (filter_var($sav, FILTER_VALIDATE_URL) ? htmlspecialchars($sav) : htmlspecialchars('uploads/avatars/' . basename($sav))) : '';
                $isMine = ((int)$m['sender_id'] === $myId);
            ?>
            <div class="flex items-start gap-3" id="msg-<?php echo (int)$m['id']; ?>">
                <?php if ($savHtml): ?>
                    <img src="<?php echo $savHtml; ?>" alt="" class="w-8 h-8 rounded-full object-cover shrink-0 mt-1">
                <?php else: ?>
                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 text-sm font-bold shrink-0 mt-1">
                        <?php echo htmlspecialchars(mb_substr($m['sender_name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <div class="flex-1 min-w-0">
                    <div class="flex items-baseline gap-2">
                        <span class="font-semibold text-sm text-gray-800"><?php echo htmlspecialchars($m['sender_name']); ?></span>
                        <span class="text-xs text-gray-400"><?php echo htmlspecialchars($m['created_at']); ?><?php echo $m['updated_at'] ? ' (edited)' : ''; ?></span>
                    </div>
                    <!-- View mode -->
                    <div id="view-<?php echo (int)$m['id']; ?>">
                        <p class="text-gray-700 text-sm mt-1"><?php echo nl2br(htmlspecialchars($m['content'])); ?></p>
                        <?php if ($isMine): ?>
                        <div class="flex gap-2 mt-1">
                            <button onclick="startEdit(<?php echo (int)$m['id']; ?>)" class="text-xs text-blue-500 hover:underline">Edit</button>
                            <form method="POST" class="inline" onsubmit="return confirm('Delete this message?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="msg_id" value="<?php echo (int)$m['id']; ?>">
                                <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                    <!-- Edit mode -->
                    <?php if ($isMine): ?>
                    <div id="edit-<?php echo (int)$m['id']; ?>" class="hidden mt-1">
                        <form method="POST">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="msg_id" value="<?php echo (int)$m['id']; ?>">
                            <textarea name="content" rows="2" class="w-full border border-gray-200 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200"><?php echo htmlspecialchars($m['content']); ?></textarea>
                            <div class="flex gap-2 mt-1">
                                <button type="submit" class="text-xs bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-lg">Save</button>
                                <button type="button" onclick="cancelEdit(<?php echo (int)$m['id']; ?>)" class="text-xs text-gray-500 hover:underline">Cancel</button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Send message form (can't message yourself) -->
        <?php if ((int)$profileId !== $myId): ?>
        <form method="POST" class="border-t border-gray-100 pt-4">
            <input type="hidden" name="action" value="send">
            <label class="block text-sm font-medium text-gray-700 mb-1">Leave a message</label>
            <textarea name="content" rows="3" required placeholder="Write something…"
                class="w-full border border-gray-200 rounded-xl p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200 resize-none"></textarea>
            <button type="submit" class="mt-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2 rounded-xl transition">Send</button>
        </form>
        <?php endif; ?>
    </div>
</div>
<script>
function startEdit(id) {
    document.getElementById('view-' + id).classList.add('hidden');
    document.getElementById('edit-' + id).classList.remove('hidden');
}
function cancelEdit(id) {
    document.getElementById('edit-' + id).classList.add('hidden');
    document.getElementById('view-' + id).classList.remove('hidden');
}
</script>
</body>
</html>
