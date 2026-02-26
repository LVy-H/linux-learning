<?php
require_once '../config/db.php';
require_once '../src/auth.php';
require_once '../includes/auth.php';

if (!isAuthenticated()) {
    header("Location: login.php");
    exit();
}

$isTeacher = ($_SESSION['role'] === 'teacher');
$myId      = $_SESSION['user_id'];
$error     = '';
$success   = '';

$uploadAssignDir   = __DIR__ . '/uploads/assignments/';
$uploadSubmitDir   = __DIR__ . '/uploads/submissions/';
foreach ([$uploadAssignDir, $uploadSubmitDir] as $d) {
    if (!is_dir($d)) mkdir($d, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Teacher: upload assignment
    if ($action === 'upload' && $isTeacher) {
        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            $error = 'Title is required.';
        } elseif (!isset($_FILES['assignment_file']) || $_FILES['assignment_file']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Please choose a file to upload.';
        } else {
            $origName = basename($_FILES['assignment_file']['name']);
            $ext      = pathinfo($origName, PATHINFO_EXTENSION);
            $filename = bin2hex(random_bytes(16)) . ($ext ? ".$ext" : '');
            if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $uploadAssignDir . $filename)) {
                $ins = $pdo->prepare("INSERT INTO assignments (title, file_path, filename_orig, uploaded_by) VALUES (?, ?, ?, ?)");
                $ins->execute([$title, $filename, $origName, $myId]);
                $success = "Assignment \"$title\" uploaded.";
            } else {
                $error = 'Failed to save file.';
            }
        }

    // Student: submit for assignment
    } elseif ($action === 'submit' && !$isTeacher) {
        $assignId = (int)($_POST['assignment_id'] ?? 0);
        if ($assignId <= 0) {
            $error = 'Invalid assignment.';
        } elseif (!isset($_FILES['submit_file']) || $_FILES['submit_file']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Please choose a file to submit.';
        } else {
            $origName = basename($_FILES['submit_file']['name']);
            $ext      = pathinfo($origName, PATHINFO_EXTENSION);
            $filename = bin2hex(random_bytes(16)) . ($ext ? ".$ext" : '');
            if (move_uploaded_file($_FILES['submit_file']['tmp_name'], $uploadSubmitDir . $filename)) {
                $ins = $pdo->prepare("INSERT INTO submissions (assignment_id, student_id, file_path, filename_orig) VALUES (?, ?, ?, ?)");
                $ins->execute([$assignId, $myId, $filename, $origName]);
                $success = 'Submission uploaded successfully.';
            } else {
                $error = 'Failed to save submission file.';
            }
        }

    } elseif ($action === 'delete' && $isTeacher) {
        $assignId = (int)($_POST['assignment_id'] ?? 0);
        $chk = $pdo->prepare("SELECT file_path, title FROM assignments WHERE id = ?");
        $chk->execute([$assignId]);
        $asgn = $chk->fetch();
        if ($asgn) {
            // Delete associated submission files from disk before cascading DB delete
            $subs = $pdo->prepare("SELECT file_path FROM submissions WHERE assignment_id = ?");
            $subs->execute([$assignId]);
            foreach ($subs->fetchAll() as $sub) {
                @unlink($uploadSubmitDir . basename($sub['file_path']));
            }
            $del = $pdo->prepare("DELETE FROM assignments WHERE id = ?");
            $del->execute([$assignId]);
            @unlink($uploadAssignDir . basename($asgn['file_path']));
            $success = "Assignment \"{$asgn['title']}\" deleted.";
        } else {
            $error = 'Assignment not found.';
        }
    }
}

$assignments = $pdo->query(
    "SELECT a.*, u.fullname AS uploader FROM assignments a JOIN users u ON a.uploaded_by = u.id ORDER BY a.created_at DESC"
)->fetchAll();

// For each student, track which assignments they've submitted
$mySubmissions = [];
if (!$isTeacher) {
    $stmt = $pdo->prepare("SELECT assignment_id FROM submissions WHERE student_id = ?");
    $stmt->execute([$myId]);
    foreach ($stmt->fetchAll() as $row) {
        $mySubmissions[$row['assignment_id']] = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassDoc | Assignments</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
<?php include '../includes/nav.php'; ?>
<div class="max-w-5xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Assignments</h1>
        <?php if ($isTeacher): ?>
        <button onclick="toggleForm('upload-form')" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition">+ Upload Assignment</button>
        <?php endif; ?>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm mb-4"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="bg-green-50 text-green-600 p-4 rounded-xl text-sm mb-4"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Teacher upload form -->
    <?php if ($isTeacher): ?>
    <div id="upload-form" class="hidden bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Upload New Assignment</h2>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="upload">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Title *</label>
                <input type="text" name="title" required class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-200">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">File *</label>
                <input type="file" name="assignment_file" required
                    class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2 rounded-xl transition">Upload</button>
                <button type="button" onclick="toggleForm('upload-form')" class="text-gray-500 hover:text-gray-700 text-sm px-3 py-2">Cancel</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Assignment list -->
    <?php if (empty($assignments)): ?>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-10 text-center text-gray-400 text-sm">No assignments yet.</div>
    <?php else: ?>
    <div class="space-y-3">
        <?php foreach ($assignments as $a): ?>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($a['title']); ?></p>
                    <p class="text-xs text-gray-500 mt-0.5">Uploaded by <?php echo htmlspecialchars($a['uploader']); ?> · <?php echo htmlspecialchars($a['created_at']); ?></p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="uploads/assignments/<?php echo urlencode(basename($a['file_path'])); ?>"
                       download="<?php echo htmlspecialchars($a['filename_orig'] ?? basename($a['file_path'])); ?>"
                       class="text-xs bg-green-50 hover:bg-green-100 text-green-700 font-semibold px-3 py-1.5 rounded-lg transition">
                        ⬇ Download
                    </a>
                    <?php if ($isTeacher): ?>
                    <form method="POST" class="inline" onsubmit="return confirm('Delete this assignment and all its submissions?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="assignment_id" value="<?php echo (int)$a['id']; ?>">
                        <button type="submit" class="text-xs bg-red-50 hover:bg-red-100 text-red-600 font-semibold px-3 py-1.5 rounded-lg transition">Delete</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Student submission area -->
            <?php if (!$isTeacher): ?>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <?php if (isset($mySubmissions[$a['id']])): ?>
                    <p class="text-sm text-green-600 font-medium">✅ You have submitted for this assignment.</p>
                <?php else: ?>
                <details class="group">
                    <summary class="cursor-pointer text-sm text-blue-600 font-medium hover:text-blue-800 list-none flex items-center gap-1">
                        <span>Submit your work</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </summary>
                    <form method="POST" enctype="multipart/form-data" class="mt-3 flex items-end gap-3">
                        <input type="hidden" name="action" value="submit">
                        <input type="hidden" name="assignment_id" value="<?php echo (int)$a['id']; ?>">
                        <div class="flex-1">
                            <input type="file" name="submit_file" required
                                class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100">
                        </div>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition whitespace-nowrap">Upload</button>
                    </form>
                </details>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<script>
function toggleForm(id) {
    document.getElementById(id).classList.toggle('hidden');
}
</script>
</body>
</html>
