<?php

$rootDir = '/home/';
$currentPath = isset($_GET['path']) ? $_GET['path'] : '';
$fullPath = realpath($rootDir . $currentPath);

if ($fullPath === false || strpos($fullPath, $rootDir) !== 0) {
    $fullPath = $rootDir;
    $currentPath = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'save':
                if (is_file($fullPath)) {
                    file_put_contents($fullPath, $_POST['content']);
                    header('Location: ?path=' . urlencode($currentPath));
                    exit;
                }
                break;
            case 'delete':
                if (file_exists($fullPath) && $fullPath !== $rootDir) {
                    if (is_dir($fullPath)) {
                        deleteDirectory($fullPath);
                    } else {
                        unlink($fullPath);
                    }
                    $parentPath = dirname($currentPath);
                    header('Location: ?path=' . urlencode($parentPath));
                    exit;
                }
                break;
            case 'create':
                $newFileName = $_POST['new_file_name'];
                $newFilePath = $fullPath . '/' . $newFileName;
                if (!empty($newFileName) && !file_exists($newFilePath)) {
                    file_put_contents($newFilePath, '');
                    header('Location: ?path=' . urlencode($currentPath));
                    exit;
                }
                break;
        }
    }
}

if (is_file($fullPath)) {
    echo "<h2>Editing: " . htmlspecialchars(basename($fullPath)) . "</h2>";
    echo "<a href='?path=" . urlencode(dirname($currentPath)) . "'>&larr; Back to Directory</a>";
    $content = file_get_contents($fullPath);
    echo "<form method='post'>";
    echo "<input type='hidden' name='action' value='save'>";
    echo "<textarea name='content' style='width:100%; height:400px;'>" . htmlspecialchars($content) . "</textarea>";
    echo "<br><button type='submit'>Save</button>";
    echo "</form>";
    echo "<form method='post' onsubmit=\"return confirm('Are you sure you want to delete this file?');\" style='margin-top:10px;'>";
    echo "<input type='hidden' name='action' value='delete'>";
    echo "<button type='submit' style='background-color:red; color:white;'>Delete File</button>";
    echo "</form>";
    exit;
}

echo "<h1>File Browser: " . htmlspecialchars(str_replace($rootDir, '', $fullPath)) . "</h1>";
echo "<form method='post' style='margin-bottom:20px;'>";
echo "<input type='hidden' name='action' value='create'>";
echo "Create New File: <input type='text' name='new_file_name' placeholder='e.g., new_file.txt'>";
echo "<button type='submit'>Create</button>";
echo "</form>";
echo "<ul>";

if ($fullPath !== $rootDir) {
    $parentDir = dirname($currentPath);
    echo "<li><a href='?path=" . urlencode($parentDir) . "'>.. (Parent Directory)</a></li>";
}

$items = scandir($fullPath);
if ($items !== false) {
    foreach ($items as $item) {
        if ($item !== '.' && $item !== '..') {
            $itemRelative = ($currentPath ? $currentPath . '/' : '') . $item;
            $itemFullPath = $rootDir . $itemRelative;
            if (is_dir($itemFullPath)) {
                echo "<li><a href='?path=" . urlencode($itemRelative) . "'>" . htmlspecialchars($item) . "</a>";
                echo " <form method='post' style='display:inline;' onsubmit=\"return confirm('Are you sure you want to delete the directory and its contents?');\">";
                echo "<input type='hidden' name='path' value='" . htmlspecialchars($itemRelative) . "'>";
                echo "<input type='hidden' name='action' value='delete'>";
                echo "<button type='submit' style='background-color:red; color:white; border:none;'>X</button></form></li>";
            } else {
                echo "<li><a href='?path=" . urlencode($itemRelative) . "'>" . htmlspecialchars($item) . "</a>";
                echo " <form method='post' style='display:inline;' onsubmit=\"return confirm('Are you sure you want to delete this file?');\">";
                echo "<input type='hidden' name='path' value='" . htmlspecialchars($itemRelative) . "'>";
                echo "<input type='hidden' name='action' value='delete'>";
                echo "<button type='submit' style='background-color:red; color:white; border:none;'>X</button></form></li>";
            }
        }
    }
} else {
    echo "<li>Error: Could not read directory contents.</li>";
}

echo "</ul>";

function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? deleteDirectory("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}
?>
