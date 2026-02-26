<?php
require_once '../config/db.php';
require_once '../src/auth.php';
require_once '../includes/auth.php';

if (!isAuthenticated() || $_SESSION['role'] !== 'teacher') {
    header("Location: dashboard.php");
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $fullname = trim($_POST['fullname'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');

        if ($username === '' || $password === '' || $fullname === '') {
            $error = 'Username, password, and full name are required.';
        } else {
            $ok = register($username, $password, $fullname, $email, $phone, 'student');
            if ($ok) {
                $success = "Student \"$fullname\" added.";
            } else {
                $error = 'Username already exists or could not create student.';
            }
        }

    } elseif ($action === 'edit') {
        $id       = (int)($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $fullname = trim($_POST['fullname'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($id <= 0 || $username === '' || $fullname === '') {
            $error = 'Username and full name are required.';
        } else {
            // Ensure it's a student account
            $chk = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'student'");
            $chk->execute([$id]);
            if (!$chk->fetch()) {
                $error = 'Student not found.';
            } else {
                if ($password !== '') {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, fullname = ?, email = ?, phone = ? WHERE id = ?");
                    $stmt->execute([$username, $hashed, $fullname, $email, $phone, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, fullname = ?, email = ?, phone = ? WHERE id = ?");
                    $stmt->execute([$username, $fullname, $email, $phone, $id]);
                }
                $success = "Student \"$fullname\" updated.";
            }
        }

    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $chk = $pdo->prepare("SELECT fullname FROM users WHERE id = ? AND role = 'student'");
        $chk->execute([$id]);
        $s = $chk->fetch();
        if ($s) {
            $del = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $del->execute([$id]);
            $success = "Student \"{$s['fullname']}\" deleted.";
        } else {
            $error = 'Student not found.';
        }
    }
}

$students = $pdo->query("SELECT * FROM users WHERE role = 'student' ORDER BY fullname")->fetchAll();

// For edit modal pre-fill
$editStudent = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'student'");
    $stmt->execute([(int)$_GET['edit']]);
    $editStudent = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassDoc | Manage Students</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
<?php include '../includes/nav.php'; ?>
<div class="max-w-5xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Manage Students</h1>
        <button onclick="toggleForm('add-form')" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition">+ Add Student</button>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm mb-4"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="bg-green-50 text-green-600 p-4 rounded-xl text-sm mb-4"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Add Student Form -->
    <div id="add-form" class="hidden bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Add New Student</h2>
        <form method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <input type="hidden" name="action" value="add">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Username *</label>
                <input type="text" name="username" required class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-200">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Password *</label>
                <input type="password" name="password" required class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-200">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Full Name *</label>
                <input type="text" name="fullname" required class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-200">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Email</label>
                <input type="email" name="email" class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-200">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Phone</label>
                <input type="text" name="phone" class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-200">
            </div>
            <div class="sm:col-span-2 flex gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2 rounded-xl transition">Add Student</button>
                <button type="button" onclick="toggleForm('add-form')" class="text-gray-500 hover:text-gray-700 text-sm px-3 py-2">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Edit Student Form -->
    <?php if ($editStudent): ?>
    <div id="edit-form" class="bg-white rounded-2xl border border-blue-100 shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Edit Student: <?php echo htmlspecialchars($editStudent['fullname']); ?></h2>
        <form method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?php echo (int)$editStudent['id']; ?>">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Username *</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($editStudent['username']); ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-200">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">New Password (leave blank to keep)</label>
                <input type="password" name="password" class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-200">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Full Name *</label>
                <input type="text" name="fullname" value="<?php echo htmlspecialchars($editStudent['fullname']); ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-200">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($editStudent['email'] ?? ''); ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-200">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Phone</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($editStudent['phone'] ?? ''); ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-200">
            </div>
            <div class="sm:col-span-2 flex gap-3">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-5 py-2 rounded-xl transition">Save Changes</button>
                <a href="students-manage.php" class="text-gray-500 hover:text-gray-700 text-sm px-3 py-2">Cancel</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Student List -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <?php if (empty($students)): ?>
            <p class="text-gray-400 text-sm text-center py-10">No students yet.</p>
        <?php else: ?>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Name</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Username</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600 hidden sm:table-cell">Email</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600 hidden sm:table-cell">Phone</th>
                    <th class="text-right px-5 py-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($students as $s): ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3 font-medium text-gray-800"><?php echo htmlspecialchars($s['fullname']); ?></td>
                    <td class="px-5 py-3 text-gray-600">@<?php echo htmlspecialchars($s['username']); ?></td>
                    <td class="px-5 py-3 text-gray-500 hidden sm:table-cell"><?php echo htmlspecialchars($s['email'] ?? '—'); ?></td>
                    <td class="px-5 py-3 text-gray-500 hidden sm:table-cell"><?php echo htmlspecialchars($s['phone'] ?? '—'); ?></td>
                    <td class="px-5 py-3 text-right">
                        <a href="?edit=<?php echo (int)$s['id']; ?>" class="text-blue-500 hover:text-blue-700 font-medium mr-3">Edit</a>
                        <form method="POST" class="inline" onsubmit="return confirm('Delete student <?php echo htmlspecialchars(addslashes($s['fullname'])); ?>?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>">
                            <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<script>
function toggleForm(id) {
    const el = document.getElementById(id);
    el.classList.toggle('hidden');
}
<?php if ($editStudent): ?>
document.getElementById('edit-form').scrollIntoView({behavior: 'smooth'});
<?php endif; ?>
</script>
</body>
</html>
