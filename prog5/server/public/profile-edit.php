<?php
require_once '../config/db.php';
require_once '../src/auth.php';
require_once '../includes/auth.php';

if (!isAuthenticated()) {
    header("Location: login.php");
    exit();
}

$myId    = $_SESSION['user_id'];
$isTeacher = ($_SESSION['role'] === 'teacher');
$error   = '';
$success = '';

// Upload dir
$uploadDir = __DIR__ . '/uploads/avatars/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Only teachers can edit their own fullname
    $fullname = $isTeacher ? trim($_POST['fullname'] ?? '') : $_SESSION['fullname'];
    if ($isTeacher && $fullname === '') {
        $error = 'Full name cannot be empty.';
    }

    $avatar = $_SESSION['avatar'] ?? '';

    // Avatar from URL
    if (!$error && !empty($_POST['avatar_url'])) {
        $url = trim($_POST['avatar_url']);
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $avatar = $url;
        } else {
            $error = 'Invalid avatar URL.';
        }
    }

    // Avatar from file upload
    if (!$error && isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($_FILES['avatar_file']['tmp_name']);
        if (!in_array($mime, $allowed)) {
            $error = 'Avatar must be a JPEG, PNG, GIF, or WebP image.';
        } elseif ($_FILES['avatar_file']['size'] > 2 * 1024 * 1024) {
            $error = 'Avatar file must be under 2 MB.';
        } else {
            $ext      = pathinfo($_FILES['avatar_file']['name'], PATHINFO_EXTENSION);
            $filename = bin2hex(random_bytes(16)) . '.' . $ext;
            if (move_uploaded_file($_FILES['avatar_file']['tmp_name'], $uploadDir . $filename)) {
                // Remove old avatar file if it was a locally stored file
                if ($avatar && !filter_var($avatar, FILTER_VALIDATE_URL)) {
                    $oldFile = $uploadDir . basename($avatar);
                    if (is_file($oldFile)) {
                        @unlink($oldFile);
                    }
                }
                $avatar = $filename;
            } else {
                $error = 'Failed to save avatar file.';
            }
        }
    }

    if (!$error) {
        $stmt = $pdo->prepare("UPDATE users SET email = ?, phone = ?, avatar = ?, fullname = ? WHERE id = ?");
        $stmt->execute([$email, $phone, $avatar, $fullname, $myId]);

        // Refresh session
        $_SESSION['email']    = $email;
        $_SESSION['phone']    = $phone;
        $_SESSION['avatar']   = $avatar;
        $_SESSION['fullname'] = $fullname;
        $success = 'Profile updated successfully.';
    }
}

// Load fresh data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$myId]);
$me = $stmt->fetch();

$av = $me['avatar'] ?? '';
$avHtml = $av ? (filter_var($av, FILTER_VALIDATE_URL) ? htmlspecialchars($av) : htmlspecialchars('uploads/avatars/' . basename($av))) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassDoc | Edit Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
<?php include '../includes/nav.php'; ?>
<div class="max-w-xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Profile</h1>

    <?php if ($error): ?>
        <div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm mb-4"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="bg-green-50 text-green-600 p-4 rounded-xl text-sm mb-4"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <!-- Current avatar -->
        <div class="flex items-center gap-4 mb-6">
            <?php if ($avHtml): ?>
                <img src="<?php echo $avHtml; ?>" alt="avatar" class="w-20 h-20 rounded-full object-cover border-2 border-gray-200">
            <?php else: ?>
                <div class="w-20 h-20 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-3xl font-bold">
                    <?php echo htmlspecialchars(mb_substr($me['fullname'], 0, 1)); ?>
                </div>
            <?php endif; ?>
            <div>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($me['fullname']); ?></p>
                <p class="text-sm text-gray-500">@<?php echo htmlspecialchars($me['username']); ?></p>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data" class="space-y-5">
            <!-- Read-only: username -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Username (read-only)</label>
                <input type="text" value="<?php echo htmlspecialchars($me['username']); ?>" disabled
                    class="w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-xl text-gray-500 cursor-not-allowed">
            </div>

            <!-- Fullname: read-only for students, editable for teachers -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">
                    Full Name <?php echo !$isTeacher ? '(read-only for students)' : ''; ?>
                </label>
                <?php if ($isTeacher): ?>
                    <input type="text" name="fullname" value="<?php echo htmlspecialchars($me['fullname']); ?>" required
                        class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-200">
                <?php else: ?>
                    <input type="text" value="<?php echo htmlspecialchars($me['fullname']); ?>" disabled
                        class="w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-xl text-gray-500 cursor-not-allowed">
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($me['email'] ?? ''); ?>"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-200">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Phone</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($me['phone'] ?? ''); ?>"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-200">
            </div>

            <!-- Avatar from URL -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Avatar URL</label>
                <input type="text" name="avatar_url" placeholder="https://example.com/photo.jpg"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-200">
                <p class="text-xs text-gray-400 mt-1">Paste an image URL to use as your avatar.</p>
            </div>

            <!-- Avatar from file -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Upload Avatar (file overrides URL)</label>
                <input type="file" name="avatar_file" accept="image/*"
                    class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100">
                <p class="text-xs text-gray-400 mt-1">JPEG, PNG, GIF, WebP. Max 2 MB.</p>
            </div>

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition">
                Save Changes
            </button>
        </form>
    </div>
</div>
</body>
</html>
