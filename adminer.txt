<head>
    <title>Adminer</title>
    <link rel="icon" type="image/png" href="https://www.adminer.org/favicon.ico">
    <!-- Or for the PNG version -->
    <link rel="icon" type="image/png" href="https://www.adminer.org/static/images/favicon.png">
</head>
<?php
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 300);
// Enhanced error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo '<link rel="icon" type="image/x-icon" href="data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQEAYAAABPYyMiAAAABmJLR0T///////8JWPfcAAAACXBIWXMAAABIAAAASABGyWs+AAAAF0lEQVRIx2NgGAWjYBSMglEwCkbBSAcACAkBAQCq1wMAAAAASUVORK5CYII=">';
// Rest of your code...

// Start session and handle login/logout
session_start();

// Handle logout
if (isset($_GET['logout'])) {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
    
    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me', '', time() - 3600, '/');
    }
    
    header("Location: " . str_replace('?logout', '', $_SERVER['REQUEST_URI']));
    exit;
}

// Check if user is logged in
$is_logged_in = false;
if (isset($_SESSION['db_credentials'])) {
    $is_logged_in = true;
    $db_credentials = $_SESSION['db_credentials'];
} elseif (isset($_COOKIE['remember_me'])) {
    $remember_data = json_decode(base64_decode($_COOKIE['remember_me']), true);
    if (is_array($remember_data) && isset($remember_data['db_credentials'])) {
        $is_logged_in = true;
        $db_credentials = $remember_data['db_credentials'];
        $_SESSION['db_credentials'] = $db_credentials;
    }
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $host = $_POST['host'] ?? '';
    $db = $_POST['db'] ?? '';
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $charset = $_POST['charset'] ?? 'utf8mb4';
    $remember = isset($_POST['remember']);
    
    try {
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, $user, $pass, $options);
        
        $db_credentials = compact('host', 'db', 'user', 'pass', 'charset');
        $_SESSION['db_credentials'] = $db_credentials;
        
        if ($remember) {
            $remember_data = ['db_credentials' => $db_credentials];
            $cookie_value = base64_encode(json_encode($remember_data));
            setcookie('remember_me', $cookie_value, time() + (30 * 24 * 60 * 60), '/');
        }
        
        $is_logged_in = true;
    } catch (\PDOException $e) {
        $error = "Database connection failed: " . $e->getMessage();
    }
}

// If not logged in, show login form
if (!$is_logged_in) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Database Login</title>
        <style>
            body { font-family: sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; }
            .login-form { background: #f9f9f9; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            .form-group { margin-bottom: 15px; }
            label { display: block; margin-bottom: 5px; font-weight: bold; }
            input[type="text"], input[type="password"] { width: 100%; padding: 8px; box-sizing: border-box; }
            button { background: #007bff; color: white; border: none; padding: 10px 15px; cursor: pointer; }
            .error { color: red; margin-bottom: 15px; }
        </style>
    </head>
    <body>
        <h1>Database Login</h1>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" class="login-form">
            <input type="hidden" name="login" value="1">
            <div class="form-group">
                <label for="host">Host:</label>
                <input type="text" id="host" name="host" value="<?php echo htmlspecialchars($_POST['host'] ?? '127.0.0.1'); ?>" required>
            </div>
            <div class="form-group">
                <label for="db">Database Name:</label>
                <input type="text" id="db" name="db" value="<?php echo htmlspecialchars($_POST['db'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="user">Username:</label>
                <input type="text" id="user" name="user" value="<?php echo htmlspecialchars($_POST['user'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="pass">Password:</label>
                <input type="password" id="pass" name="pass" value="<?php echo htmlspecialchars($_POST['pass'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="charset">Charset:</label>
                <input type="text" id="charset" name="charset" value="<?php echo htmlspecialchars($_POST['charset'] ?? 'utf8mb4'); ?>">
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="remember" value="1" <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
                    Remember me (stay logged in)
                </label>
            </div>
            <button type="submit">Connect to Database</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

// If logged in, proceed with the enhanced database editor
extract($db_credentials);

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    unset($_SESSION['db_credentials']);
    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me', '', time() - 3600, '/');
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get current table and action
$current_table = $_GET['table'] ?? '';
$action = $_GET['action'] ?? 'tables';
$page = $_GET['page'] ?? 1;
$limit = $_GET['limit'] ?? 20;
$offset = ($page - 1) * $limit;
$edit_id = $_GET['id'] ?? '';

// Get all tables
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

// Process POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? $action;
    
    // Handle SQL query execution
    if ($action === 'execute_sql' && isset($_POST['sql_query'])) {
        $sql_query = $_POST['sql_query'];
        try {
            $stmt = $pdo->prepare($sql_query);
            $stmt->execute();
            
            if (stripos($sql_query, 'SELECT') === 0) {
                $result = $stmt->fetchAll();
                $_SESSION['sql_result'] = $result;
                $_SESSION['sql_columns'] = $result ? array_keys($result[0]) : [];
            } else {
                $_SESSION['message'] = "Query executed successfully. Affected rows: " . $stmt->rowCount();
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "SQL Error: " . $e->getMessage();
        }
        header("Location: ?action=sql&table=" . urlencode($current_table));
        exit;
    }
    
    // Handle table creation with advanced options
    if ($action === 'create_table' && isset($_POST['table_name'])) {
        $table_name = $_POST['table_name'];
        $columns = $_POST['columns'] ?? [];
        $primary_key = $_POST['primary_key'] ?? '';
        $indexes = $_POST['indexes'] ?? [];
        $foreign_keys = $_POST['foreign_keys'] ?? [];
        
        try {
            $sql = "CREATE TABLE `$table_name` (";
            $column_defs = [];
            
            foreach ($columns as $col) {
                if (!empty($col['name'])) {
                    $def = "`{$col['name']}` {$col['type']}";
                    
                    // Add length for certain types
                    if (in_array($col['type'], ['VARCHAR', 'CHAR', 'INT', 'BIGINT', 'DECIMAL']) && !empty($col['length'])) {
                        $def .= "({$col['length']})";
                    }
                    
                    if (!empty($col['collation'])) {
                        $def .= " COLLATE {$col['collation']}";
                    }
                    
                    if (!empty($col['null']) && $col['null'] === 'YES') {
                        $def .= " NULL";
                    } else {
                        $def .= " NOT NULL";
                    }
                    
                    if (!empty($col['default'])) {
                        $def .= " DEFAULT '{$col['default']}'";
                    }
                    
                    if (!empty($col['auto_increment']) && $col['auto_increment'] === 'YES') {
                        $def .= " AUTO_INCREMENT";
                    }
                    
                    if (!empty($col['comment'])) {
                        $def .= " COMMENT '{$col['comment']}'";
                    }
                    
                    $column_defs[] = $def;
                }
            }
            
            // Add primary key
            if (!empty($primary_key)) {
                $column_defs[] = "PRIMARY KEY (`$primary_key`)";
            }
            
            // Add indexes
            foreach ($indexes as $index) {
                if (!empty($index['name']) && !empty($index['columns'])) {
                    $index_type = !empty($index['type']) ? $index['type'] . ' ' : '';
                    $column_defs[] = "{$index_type}INDEX `{$index['name']}` (`{$index['columns']}`)";
                }
            }
            
            // Add foreign keys
            foreach ($foreign_keys as $fk) {
                if (!empty($fk['column']) && !empty($fk['ref_table']) && !empty($fk['ref_column'])) {
                    $on_delete = !empty($fk['on_delete']) ? " ON DELETE {$fk['on_delete']}" : '';
                    $on_update = !empty($fk['on_update']) ? " ON UPDATE {$fk['on_update']}" : '';
                    $column_defs[] = "FOREIGN KEY (`{$fk['column']}`) REFERENCES `{$fk['ref_table']}` (`{$fk['ref_column']}`){$on_delete}{$on_update}";
                }
            }
            
            $sql .= implode(', ', $column_defs) . ")";
            
            // Add table options
            $table_options = [];
            if (!empty($_POST['engine'])) {
                $table_options[] = "ENGINE={$_POST['engine']}";
            }
            if (!empty($_POST['charset'])) {
                $table_options[] = "DEFAULT CHARSET={$_POST['charset']}";
            }
            if (!empty($_POST['collation'])) {
                $table_options[] = "COLLATE={$_POST['collation']}";
            }
            if (!empty($_POST['comment'])) {
                $table_options[] = "COMMENT='{$_POST['comment']}'";
            }
            
            if (!empty($table_options)) {
                $sql .= " " . implode(' ', $table_options);
            }
            
            $pdo->exec($sql);
            $_SESSION['message'] = "Table '$table_name' created successfully";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error creating table: " . $e->getMessage();
        }
        header("Location: ?action=tables");
        exit;
    }
    
    // Handle alter table structure - IMPROVED VERSION
if ($action === 'alter_table' && $current_table) {
    try {
        $alter_statements = [];
        
        // Add columns
        if (isset($_POST['add_columns'])) {
            foreach ($_POST['add_columns'] as $col) {
                if (!empty($col['name'])) {
                    $def = "ADD COLUMN `{$col['name']}` {$col['type']}";
                    
                    if (!empty($col['null']) && $col['null'] === 'YES') {
                        $def .= " NULL";
                    } else {
                        $def .= " NOT NULL";
                    }
                    
                    if (!empty($col['default'])) {
                        $def .= " DEFAULT '{$col['default']}'";
                    }
                    
                    // Add AFTER clause if specified
                    if (!empty($col['after'])) {
                        $def .= " AFTER `{$col['after']}`";
                    }
                    
                    $alter_statements[] = $def;
                }
            }
        }
        
        // Modify columns
        if (isset($_POST['modify_columns'])) {
            foreach ($_POST['modify_columns'] as $col) {
                if (!empty($col['name'])) {
                    $new_name = !empty($col['new_name']) ? $col['new_name'] : $col['name'];
                    $def = "CHANGE COLUMN `{$col['name']}` `{$new_name}` {$col['type']}";
                    
                    if (!empty($col['null']) && $col['null'] === 'YES') {
                        $def .= " NULL";
                    } else {
                        $def .= " NOT NULL";
                    }
                    
                    if (!empty($col['default'])) {
                        $def .= " DEFAULT '{$col['default']}'";
                    } else {
                        $def .= " DEFAULT NULL";
                    }
                    
                    if (!empty($col['auto_increment']) && $col['auto_increment'] === 'YES') {
                        $def .= " AUTO_INCREMENT";
                    }
                    
                    $alter_statements[] = $def;
                }
            }
        }
        
        // Drop columns
        if (isset($_POST['drop_columns'])) {
            foreach ($_POST['drop_columns'] as $column_name) {
                if (!empty($column_name)) {
                    $alter_statements[] = "DROP COLUMN `$column_name`";
                }
            }
        }
        
        // Execute alter statements
        if (!empty($alter_statements)) {
            $sql = "ALTER TABLE `$current_table` " . implode(', ', $alter_statements);
            $pdo->exec($sql);
            $_SESSION['message'] = "Table structure updated successfully";
        } else {
            $_SESSION['message'] = "No changes were made";
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Error altering table: " . $e->getMessage();
    }
    header("Location: ?action=structure&table=" . urlencode($current_table));
    exit;
}
    
    // Handle trigger creation
    if ($action === 'create_trigger' && $current_table) {
        $trigger_name = $_POST['trigger_name'];
        $trigger_time = $_POST['trigger_time'];
        $trigger_event = $_POST['trigger_event'];
        $trigger_body = $_POST['trigger_body'];
        
        try {
            $sql = "CREATE TRIGGER `$trigger_name` $trigger_time $trigger_event ON `$current_table` 
                    FOR EACH ROW 
                    BEGIN
                        $trigger_body
                    END";
            $pdo->exec($sql);
            $_SESSION['message'] = "Trigger created successfully";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error creating trigger: " . $e->getMessage();
        }
        header("Location: ?action=triggers&table=" . urlencode($current_table));
        exit;
    }
    
    // Handle data operations (insert, update, delete)
    if (in_array($action, ['insert', 'update', 'delete']) && $current_table) {
        try {
            if ($action === 'insert' && isset($_POST['data'])) {
                $data = array_filter($_POST['data'], function($v) { return $v !== ''; });
                $columns = array_keys($data);
                $placeholders = array_fill(0, count($columns), '?');
                $sql = "INSERT INTO `$current_table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array_values($data));
                $_SESSION['message'] = "Record inserted successfully";
            }
            elseif ($action === 'update' && isset($_POST['data'], $_POST['primary_key'], $_POST['primary_value'])) {
                $data = array_filter($_POST['data'], function($v) { return $v !== ''; });
                $pk = $_POST['primary_key'];
                $pk_val = $_POST['primary_value'];
                
                $setParts = []; $params = [];
                foreach ($data as $col => $val) {
                    $setParts[] = "`$col` = ?";
                    $params[] = $val;
                }
                $params[] = $pk_val;
                $sql = "UPDATE `$current_table` SET " . implode(', ', $setParts) . " WHERE `$pk` = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $_SESSION['message'] = "Record updated successfully";
            }
            elseif ($action === 'delete' && isset($_POST['primary_key'], $_POST['primary_value'])) {
                $pk = $_POST['primary_key'];
                $pk_val = $_POST['primary_value'];
                $sql = "DELETE FROM `$current_table` WHERE `$pk` = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$pk_val]);
                $_SESSION['message'] = "Record deleted successfully";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
        header("Location: ?action=browse&table=" . urlencode($current_table));
        exit;
    }
    
    // Handle search
    if ($action === 'search' && isset($_POST['search_term']) && $current_table) {
        $search_term = '%' . $_POST['search_term'] . '%';
        $columns = $pdo->query("DESCRIBE `$current_table`")->fetchAll(PDO::FETCH_COLUMN);
        
        $conditions = [];
        foreach ($columns as $col) {
            $conditions[] = "`$col` LIKE ?";
        }
        $sql = "SELECT * FROM `$current_table` WHERE " . implode(' OR ', $conditions);
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_fill(0, count($columns), $search_term));
        $_SESSION['search_results'] = $stmt->fetchAll();
        header("Location: ?action=search&table=" . urlencode($current_table));
        exit;
    }
    
    // Handle import
    if ($action === 'import' && isset($_FILES['sql_file']) && $current_table) {
        $sql_content = file_get_contents($_FILES['sql_file']['tmp_name']);
        try {
            $pdo->exec($sql_content);
            $_SESSION['message'] = "SQL file imported successfully";
        } catch (Exception $e) {
            $_SESSION['error'] = "Import error: " . $e->getMessage();
        }
        header("Location: ?action=tables");
        exit;
    }
    
    // Handle export
    if ($action === 'export' && $current_table) {
        try {
            $data = $pdo->query("SELECT * FROM `$current_table`")->fetchAll();
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . $current_table . '_export.sql"');
            
            $output = "-- Export of table: $current_table\n";
            $output .= "-- Export time: " . date('Y-m-d H:i:s') . "\n\n";
            
            foreach ($data as $row) {
                $columns = array_keys($row);
                $values = array_map(function($value) use ($pdo) {
                    return $value === null ? 'NULL' : $pdo->quote($value);
                }, array_values($row));
                
                $output .= "INSERT INTO `$current_table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
            }
            
            echo $output;
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = "Export error: " . $e->getMessage();
            header("Location: ?action=export&table=" . urlencode($current_table));
            exit;
        }
    }
}

// Function to get triggers for a table
function getTriggers($pdo, $table) {
    try {
        $stmt = $pdo->prepare("SHOW TRIGGERS LIKE ?");
        $stmt->execute([$table]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

// Function to get foreign keys for a table
function getForeignKeys($pdo, $table) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                CONSTRAINT_NAME, 
                COLUMN_NAME, 
                REFERENCED_TABLE_NAME, 
                REFERENCED_COLUMN_NAME
            FROM 
                information_schema.KEY_COLUMN_USAGE 
            WHERE 
                TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = ? 
                AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        $stmt->execute([$table]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Manager</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; }
        .container { display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 250px; background: #2c3e50; color: white; overflow-y: auto; }
        .sidebar-header { padding: 20px; background: #34495e; border-bottom: 1px solid #34495e; }
        .sidebar h2 { font-size: 16px; margin-bottom: 10px; }
        .database-info { font-size: 12px; opacity: 0.8; }
        
        .nav-section { margin: 15px 0; }
        .nav-section h3 { padding: 10px 20px; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; }
        .nav-links a { display: block; padding: 10px 20px; color: #bdc3c7; text-decoration: none; border-left: 3px solid transparent; }
        .nav-links a:hover { background: #34495e; color: white; }
        .nav-links a.active { background: #34495e; color: #3498db; border-left-color: #3498db; }
        
        .table-list { max-height: 300px; overflow-y: auto; }
        .table-item { padding: 8px 20px; cursor: pointer; border-left: 3px solid transparent; }
        .table-item:hover { background: #34495e; }
        .table-item.active { background: #34495e; color: #3498db; border-left-color: #3498db; }
        
        /* Main Content */
        .main-content { flex: 1; padding: 20px; overflow-y: auto; }
        .header { background: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header h1 { margin-bottom: 10px; color: #2c3e50; }
        
        .content-area { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        
        /* Tabs */
        .tabs { display: flex; border-bottom: 1px solid #ddd; margin-bottom: 20px; flex-wrap: wrap; }
        .tab { padding: 10px 20px; cursor: pointer; border: 1px solid transparent; border-bottom: none; margin-right: 5px; border-radius: 3px 3px 0 0; }
        .tab.active { background: white; border-color: #ddd; color: #3498db; }
        
        /* Tables */
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: 600; }
        tr:hover { background: #f8f9fa; }
        
        /* Forms */
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; }
        button { background: #3498db; color: white; border: none; padding: 8px 15px; border-radius: 3px; cursor: pointer; }
        button:hover { background: #2980b9; }
        button.delete { background: #e74c3c; }
        button.delete:hover { background: #c0392b; }
        button.success { background: #27ae60; }
        button.success:hover { background: #219a52; }
        button.warning { background: #f39c12; }
        button.warning:hover { background: #e67e22; }
        
        /* Messages */
        .message { padding: 10px; margin: 10px 0; border-radius: 3px; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        /* SQL Editor */
        .sql-editor { width: 100%; height: 200px; font-family: monospace; }
        
        /* Pagination */
        .pagination { margin: 20px 0; display: flex; gap: 5px; }
        .pagination a { padding: 5px 10px; border: 1px solid #ddd; text-decoration: none; color: #3498db; }
        .pagination a.active { background: #3498db; color: white; }
        
        .logout-btn { 
            position: fixed; 
            top: 15px; 
            right: 15px; 
            background: #dc3545; 
            color: white; 
            padding: 8px 15px; 
            text-decoration: none; 
            border-radius: 4px;
            z-index: 1000;
        }
        .logout-btn:hover { background: #c82333; }
        
        .btn { display: inline-block; padding: 8px 15px; text-decoration: none; border-radius: 3px; margin: 2px; }
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-warning { background: #f39c12; color: white; }
        
        .advanced-options { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .advanced-options h4 { margin-top: 0; }
        
        .column-form { display: grid; grid-template-columns: 2fr 2fr 1fr 1fr 1fr 1fr 1fr; gap: 10px; align-items: end; margin-bottom: 10px; }
        .small-input { padding: 5px !important; }
    </style>
</head>
<body>
    <a href="?logout=1" class="logout-btn">Logout</a>
    
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Database Manager</h2>
                <div class="database-info">
                    <?php echo htmlspecialchars($db); ?><br>
                    <?php echo htmlspecialchars($host); ?>
                </div>
            </div>
            
            <div class="nav-section">
                <h3>Navigation</h3>
                <div class="nav-links">
                    <a href="?action=tables" class="<?php echo $action === 'tables' ? 'active' : ''; ?>">📊 Databases</a>
                    <a href="?action=sql" class="<?php echo $action === 'sql' ? 'active' : ''; ?>">⚙️ SQL Command</a>
                    <a href="?action=export&table=<?php echo urlencode($current_table); ?>" class="<?php echo $action === 'export' ? 'active' : ''; ?>">📤 Export</a>
                    <a href="?action=import" class="<?php echo $action === 'import' ? 'active' : ''; ?>">📥 Import</a>
                </div>
            </div>
            
            <div class="nav-section">
                <h3>Tables</h3>
                <div class="table-list">
                    <?php foreach ($tables as $table): ?>
                        <div class="table-item <?php echo $current_table === $table ? 'active' : ''; ?>" 
                             onclick="location.href='?action=browse&table=<?php echo urlencode($table); ?>'">
                            📋 <?php echo htmlspecialchars($table); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="nav-section">
                <div class="nav-links">
                    <a href="?action=create_table" class="<?php echo $action === 'create_table' ? 'active' : ''; ?>">➕ Create Table</a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>
                    <?php 
                    switch($action) {
                        case 'browse': echo "Browse Data: " . htmlspecialchars($current_table); break;
                        case 'structure': echo "Table Structure: " . htmlspecialchars($current_table); break;
                        case 'sql': echo "SQL Command"; break;
                        case 'search': echo "Search: " . htmlspecialchars($current_table); break;
                        case 'create_table': echo "Create New Table"; break;
                        case 'tables': echo "Database Tables"; break;
                        case 'export': echo "Export Data: " . htmlspecialchars($current_table); break;
                        case 'import': echo "Import Data"; break;
                        case 'edit': echo "Edit Record: " . htmlspecialchars($current_table); break;
                        case 'insert': echo "New Record: " . htmlspecialchars($current_table); break;
                        case 'triggers': echo "Triggers: " . htmlspecialchars($current_table); break;
                        case 'alter': echo "Alter Table: " . htmlspecialchars($current_table); break;
                        default: echo "Database Manager";
                    }
                    ?>
                </h1>
            </div>
            
            <!-- Messages -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="message success"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="message error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <div class="content-area">
                <?php
                // Display appropriate content based on action
                switch($action) {
                    case 'tables':
                        echo '<h2>Database Tables</h2>';
                        echo '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px;">';
                        foreach ($tables as $table) {
                            echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; text-align: center;">';
                            echo '<h3>' . htmlspecialchars($table) . '</h3>';
                            echo '<div style="margin-top: 10px;">';
                            echo '<a href="?action=browse&table=' . urlencode($table) . '" class="btn btn-primary">Browse</a>';
                            echo '<a href="?action=structure&table=' . urlencode($table) . '" class="btn btn-success">Structure</a>';
                            echo '</div>';
                            echo '</div>';
                        }
                        echo '</div>';
                        break;
                        
                                        case 'browse':
                    case 'structure':
                    case 'search':
                    case 'triggers':
                    case 'alter':
                        if ($current_table) {
                            echo '<div class="tabs">';
                            echo '<div class="tab ' . ($action === 'browse' ? 'active' : '') . '" onclick="location.href=\'?action=browse&table=' . urlencode($current_table) . '\'">Browse</div>';
                            echo '<div class="tab ' . ($action === 'structure' ? 'active' : '') . '" onclick="location.href=\'?action=structure&table=' . urlencode($current_table) . '\'">Structure</div>';
                            echo '<div class="tab ' . ($action === 'search' ? 'active' : '') . '" onclick="location.href=\'?action=search&table=' . urlencode($current_table) . '\'">Search</div>';
                            echo '<div class="tab ' . ($action === 'triggers' ? 'active' : '') . '" onclick="location.href=\'?action=triggers&table=' . urlencode($current_table) . '\'">Triggers</div>';
                            echo '<div class="tab ' . ($action === 'alter' ? 'active' : '') . '" onclick="location.href=\'?action=alter&table=' . urlencode($current_table) . '\'">Alter Table</div>';
                            echo '</div>';
                            
                            if ($action === 'browse') {
                                // Browse tab content
                                echo '<div style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">';
                                echo '<a href="?action=insert&table=' . urlencode($current_table) . '" class="btn btn-success">➕ New Item</a>';
                                echo '<form method="get" style="display: flex; gap: 10px;">';
                                echo '<input type="hidden" name="action" value="browse">';
                                echo '<input type="hidden" name="table" value="' . htmlspecialchars($current_table) . '">';
                                echo '<select name="limit" onchange="this.form.submit()">';
                                echo '<option value="10" ' . ($limit == 10 ? "selected" : "") . '>10 rows</option>';
                                echo '<option value="20" ' . ($limit == 20 ? "selected" : "") . '>20 rows</option>';
                                echo '<option value="50" ' . ($limit == 50 ? "selected" : "") . '>50 rows</option>';
                                echo '<option value="100" ' . ($limit == 100 ? "selected" : "") . '>100 rows</option>';
                                echo '</select>';
                                echo '</form>';
                                echo '</div>';
                                
                                $columns = $pdo->query("DESCRIBE `$current_table`")->fetchAll();
                                $primaryKey = "";
                                foreach ($columns as $col) {
                                    if ($col["Key"] === "PRI") {
                                        $primaryKey = $col["Field"];
                                        break;
                                    }
                                }
                                
                                $total = $pdo->query("SELECT COUNT(*) FROM `$current_table`")->fetchColumn();
                                $pages = ceil($total / $limit);
                                $data = $pdo->query("SELECT * FROM `$current_table` LIMIT $limit OFFSET $offset")->fetchAll();
                                
                                echo '<table>';
                                echo '<thead><tr>';
                                foreach ($columns as $col) {
                                    echo '<th>' . htmlspecialchars($col["Field"]) . '</th>';
                                }
                                echo '<th>Actions</th>';
                                echo '</tr></thead>';
                                echo '<tbody>';
                                foreach ($data as $row) {
                                    echo '<tr>';
                                    foreach ($columns as $col) {
                                        echo '<td>' . htmlspecialchars($row[$col["Field"]] ?? "") . '</td>';
                                    }
                                    echo '<td>';
                                    echo '<a href="?action=edit&table=' . urlencode($current_table) . '&id=' . urlencode($row[$primaryKey]) . '" class="btn btn-primary">Edit</a>';
                                    echo '<form method="post" style="display: inline;">';
                                    echo '<input type="hidden" name="action" value="delete">';
                                    echo '<input type="hidden" name="table" value="' . htmlspecialchars($current_table) . '">';
                                    echo '<input type="hidden" name="primary_key" value="' . htmlspecialchars($primaryKey) . '">';
                                    echo '<input type="hidden" name="primary_value" value="' . htmlspecialchars($row[$primaryKey]) . '">';
                                    echo '<button type="submit" class="delete" onclick="return confirm(\'Are you sure?\')">Delete</button>';
                                    echo '</form>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                                echo '</tbody></table>';
                                
                                if ($pages > 1) {
                                    echo '<div class="pagination">';
                                    for ($i = 1; $i <= $pages; $i++) {
                                        echo '<a href="?action=browse&table=' . urlencode($current_table) . '&page=' . $i . '&limit=' . $limit . '" class="' . ($i == $page ? 'active' : '') . '">' . $i . '</a>';
                                    }
                                    echo '</div>';
                                }
                                
                            } elseif ($action === 'structure') {
                                // Structure tab content
                                $structure = $pdo->query("DESCRIBE `$current_table`")->fetchAll();
                                $foreign_keys = getForeignKeys($pdo, $current_table);
                                
                                echo '<table>';
                                echo '<thead><tr>';
                                echo '<th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th>';
                                echo '</tr></thead>';
                                echo '<tbody>';
                                foreach ($structure as $col) {
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($col["Field"]) . '</td>';
                                    echo '<td>' . htmlspecialchars($col["Type"]) . '</td>';
                                    echo '<td>' . htmlspecialchars($col["Null"]) . '</td>';
                                    echo '<td>' . htmlspecialchars($col["Key"]) . '</td>';
                                    echo '<td>' . htmlspecialchars($col["Default"] ?? "NULL") . '</td>';
                                    echo '<td>' . htmlspecialchars($col["Extra"]) . '</td>';
                                    echo '</tr>';
                                }
                                echo '</tbody></table>';
                                
                                // Show foreign keys
                                if (!empty($foreign_keys)) {
                                    echo '<h3>Foreign Keys</h3>';
                                    echo '<table>';
                                    echo '<thead><tr><th>Name</th><th>Column</th><th>References</th></tr></thead>';
                                    echo '<tbody>';
                                    foreach ($foreign_keys as $fk) {
                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($fk['CONSTRAINT_NAME']) . '</td>';
                                        echo '<td>' . htmlspecialchars($fk['COLUMN_NAME']) . '</td>';
                                        echo '<td>' . htmlspecialchars($fk['REFERENCED_TABLE_NAME']) . '.' . htmlspecialchars($fk['REFERENCED_COLUMN_NAME']) . '</td>';
                                        echo '</tr>';
                                    }
                                    echo '</tbody></table>';
                                }
                                
                            } elseif ($action === 'search') {
                                // Search tab content
                                echo '<form method="post">';
                                echo '<input type="hidden" name="action" value="search">';
                                echo '<input type="hidden" name="table" value="' . htmlspecialchars($current_table) . '">';
                                echo '<div class="form-group">';
                                echo '<label>Search Term:</label>';
                                echo '<input type="text" name="search_term" required>';
                                echo '</div>';
                                echo '<button type="submit">Search</button>';
                                echo '</form>';
                                
                                if (isset($_SESSION["search_results"])) {
                                    echo '<h3>Search Results</h3>';
                                    $columns = $pdo->query("DESCRIBE `$current_table`")->fetchAll();
                                    echo '<table>';
                                    echo '<thead><tr>';
                                    foreach ($columns as $col) {
                                        echo '<th>' . htmlspecialchars($col["Field"]) . '</th>';
                                    }
                                    echo '</tr></thead>';
                                    echo '<tbody>';
                                    foreach ($_SESSION["search_results"] as $row) {
                                        echo '<tr>';
                                        foreach ($columns as $col) {
                                            echo '<td>' . htmlspecialchars($row[$col["Field"]] ?? "") . '</td>';
                                        }
                                        echo '</tr>';
                                    }
                                    echo '</tbody></table>';
                                    unset($_SESSION["search_results"]);
                                }
                                
                            } elseif ($action === 'triggers') {
                                // Triggers tab content
                                $triggers = getTriggers($pdo, $current_table);
                                
                                echo '<div style="margin-bottom: 20px;">';
                                echo '<button onclick="showTriggerForm()" class="btn btn-success">➕ Create Trigger</button>';
                                echo '</div>';
                                
                                echo '<div id="trigger-form" style="display: none; background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">';
                                echo '<h3>Create New Trigger</h3>';
                                echo '<form method="post">';
                                echo '<input type="hidden" name="action" value="create_trigger">';
                                echo '<input type="hidden" name="table" value="' . htmlspecialchars($current_table) . '">';
                                
                                echo '<div class="form-group">';
                                echo '<label>Trigger Name:</label>';
                                echo '<input type="text" name="trigger_name" required>';
                                echo '</div>';
                                
                                echo '<div class="form-group">';
                                echo '<label>Trigger Time:</label>';
                                echo '<select name="trigger_time" required>';
                                echo '<option value="BEFORE">BEFORE</option>';
                                echo '<option value="AFTER">AFTER</option>';
                                echo '</select>';
                                echo '</div>';
                                
                                echo '<div class="form-group">';
                                echo '<label>Trigger Event:</label>';
                                echo '<select name="trigger_event" required>';
                                echo '<option value="INSERT">INSERT</option>';
                                echo '<option value="UPDATE">UPDATE</option>';
                                echo '<option value="DELETE">DELETE</option>';
                                echo '</select>';
                                echo '</div>';
                                
                                echo '<div class="form-group">';
                                echo '<label>Trigger Body (SQL):</label>';
                                echo '<textarea name="trigger_body" rows="5" placeholder="BEGIN ... END" required></textarea>';
                                echo '</div>';
                                
                                echo '<button type="submit" class="btn btn-primary">Create Trigger</button>';
                                echo '<button type="button" onclick="hideTriggerForm()" class="btn btn-warning">Cancel</button>';
                                echo '</form>';
                                echo '</div>';
                                
                                if (!empty($triggers)) {
                                    echo '<h3>Existing Triggers</h3>';
                                    echo '<table>';
                                    echo '<thead><tr><th>Trigger</th><th>Event</th><th>Timing</th><th>Created</th></tr></thead>';
                                    echo '<tbody>';
                                    foreach ($triggers as $trigger) {
                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($trigger['Trigger']) . '</td>';
                                        echo '<td>' . htmlspecialchars($trigger['Event']) . '</td>';
                                        echo '<td>' . htmlspecialchars($trigger['Timing']) . '</td>';
                                        echo '<td>' . htmlspecialchars($trigger['Created']) . '</td>';
                                        echo '</tr>';
                                    }
                                    echo '</tbody></table>';
                                } else {
                                    echo '<p>No triggers found for this table.</p>';
                                }
                                
                            } elseif ($action === 'alter') {
                                // Alter table tab content - FIXED VERSION
                                $structure = $pdo->query("DESCRIBE `$current_table`")->fetchAll();
                                
                                echo '<h3>Alter Table Structure</h3>';
                                echo '<form method="post">';
                                echo '<input type="hidden" name="action" value="alter_table">';
                                echo '<input type="hidden" name="table" value="' . htmlspecialchars($current_table) . '">';
                         
                        echo '<h4>Modify Existing Columns</h4>';
                        echo '<p>Change the properties of existing columns:</p>';
                        
                        echo '<div id="modify-columns-container">';
                        foreach ($structure as $index => $col) {
                            $field = $col['Field'];
                            $type = $col['Type'];
                            $null = $col['Null'];
                            $default = $col['Default'] ?? '';
                            $extra = $col['Extra'];
                            
                            // Extract data type and length
                            $data_type = $type;
                            $length = '';
                            if (preg_match('/^(\w+)(\((\d+)\))?/', $type, $matches)) {
                                $data_type = $matches[1];
                                $length = $matches[3] ?? '';
                            }
                            
                            $is_auto_increment = strpos($extra, 'auto_increment') !== false;
                            
                            echo '<div class="column-form" style="background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px;">';
                            echo '<h4>Column: ' . htmlspecialchars($field) . '</h4>';
                            echo '<div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr 1fr; gap: 10px; align-items: end;">';
                            
                            // Current Name (readonly)
                            echo '<div>';
                            echo '<label>Current Name:</label>';
                            echo '<input type="text" value="' . htmlspecialchars($field) . '" readonly class="small-input">';
                            echo '<input type="hidden" name="modify_columns[' . $index . '][name]" value="' . htmlspecialchars($field) . '">';
                            echo '</div>';
                            
                            // New Name
                            echo '<div>';
                            echo '<label>New Name:</label>';
                            echo '<input type="text" name="modify_columns[' . $index . '][new_name]" value="' . htmlspecialchars($field) . '" placeholder="Leave unchanged" class="small-input">';
                            echo '</div>';
                            
                            // Data Type
                            echo '<div>';
                            echo '<label>Data Type:</label>';
                            echo '<select name="modify_columns[' . $index . '][type]" class="small-input">';
                            $types = ['INT', 'VARCHAR(255)', 'TEXT', 'DATE', 'DATETIME', 'TIMESTAMP', 'FLOAT', 'DOUBLE', 'DECIMAL(10,2)', 'BOOLEAN', 'BLOB', 'LONGTEXT'];
                            foreach ($types as $typeOption) {
                                $selected = $typeOption === $data_type . ($length ? "($length)" : '') ? 'selected' : '';
                                echo '<option value="' . $typeOption . '" ' . $selected . '>' . $typeOption . '</option>';
                            }
                            echo '</select>';
                            echo '</div>';
                            
                            // Null/Not Null
                            echo '<div>';
                            echo '<label>Nullable:</label>';
                            echo '<select name="modify_columns[' . $index . '][null]" class="small-input">';
                            echo '<option value="NO" ' . ($null === 'NO' ? 'selected' : '') . '>NOT NULL</option>';
                            echo '<option value="YES" ' . ($null === 'YES' ? 'selected' : '') . '>NULL</option>';
                            echo '</select>';
                            echo '</div>';
                            
                            // Default Value
                            echo '<div>';
                            echo '<label>Default Value:</label>';
                            echo '<input type="text" name="modify_columns[' . $index . '][default]" value="' . htmlspecialchars($default) . '" placeholder="NULL" class="small-input">';
                            echo '</div>';
                            
                            echo '</div>';
                            
                            // Advanced options
                            echo '<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: 10px;">';
                            
                            // Auto Increment
                            echo '<div>';
                            echo '<label>';
                            echo '<input type="checkbox" name="modify_columns[' . $index . '][auto_increment]" value="YES" ' . ($is_auto_increment ? 'checked' : '') . '>';
                            echo ' Auto Increment';
                            echo '</label>';
                            echo '</div>';
                            
                            // Column Comment
                            echo '<div>';
                            echo '<label>Comment:</label>';
                            echo '<input type="text" name="modify_columns[' . $index . '][comment]" placeholder="Column comment" class="small-input">';
                            echo '</div>';
                            
                            // Drop Column option
                            echo '<div>';
                            echo '<label>';
                            echo '<input type="checkbox" name="drop_columns[]" value="' . htmlspecialchars($field) . '">';
                            echo ' Drop this column';
                            echo '</label>';
                            echo '</div>';
                            
                            echo '</div>';
                            echo '</div>';
                        }
                        echo '</div>';
                        
                        echo '<h4>Add New Columns</h4>';
                        echo '<div id="add-columns-container">';
                        echo '<div class="column-form" style="background: #e8f4fd; padding: 15px; margin: 10px 0; border-radius: 5px;">';
                        echo '<h4>New Column</h4>';
                        echo '<div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr 1fr; gap: 10px; align-items: end;">';
                        
                        echo '<div>';
                        echo '<label>Column Name:</label>';
                        echo '<input type="text" name="add_columns[0][name]" placeholder="new_column" class="small-input">';
                        echo '</div>';
                        
                        echo '<div>';
                        echo '<label>Data Type:</label>';
                        echo '<select name="add_columns[0][type]" class="small-input">';
                        echo '<option value="VARCHAR(255)">VARCHAR(255)</option>';
                        echo '<option value="INT">INT</option>';
                        echo '<option value="TEXT">TEXT</option>';
                        echo '<option value="DATE">DATE</option>';
                        echo '<option value="DATETIME">DATETIME</option>';
                        echo '<option value="BOOLEAN">BOOLEAN</option>';
                        echo '</select>';
                        echo '</div>';
                        
                        echo '<div>';
                        echo '<label>Nullable:</label>';
                        echo '<select name="add_columns[0][null]" class="small-input">';
                        echo '<option value="NO">NOT NULL</option>';
                        echo '<option value="YES">NULL</option>';
                        echo '</select>';
                        echo '</div>';
                        
                        echo '<div>';
                        echo '<label>Default Value:</label>';
                        echo '<input type="text" name="add_columns[0][default]" placeholder="Optional" class="small-input">';
                        echo '</div>';
                        
                        echo '<div>';
                        echo '<label>After Column:</label>';
                        echo '<select name="add_columns[0][after]" class="small-input">';
                        echo '<option value="">- End of table -</option>';
                        foreach ($structure as $col) {
                            echo '<option value="' . htmlspecialchars($col['Field']) . '">' . htmlspecialchars($col['Field']) . '</option>';
                        }
                        echo '</select>';
                        echo '</div>';
                        
                        echo '</div>';
                        echo '<div style="margin-top: 10px;">';
                        echo '<button type="button" onclick="removeColumn(this)" class="btn btn-danger">Remove New Column</button>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                        
                        echo '<button type="button" onclick="addNewColumn()" class="btn btn-primary">Add Another New Column</button>';
                        
                        echo '<div style="margin-top: 20px;">';
                        echo '<button type="submit" class="btn btn-success">Apply Changes</button>';
                        echo '<a href="?action=structure&table=' . urlencode($current_table) . '" class="btn btn-warning">Cancel</a>';
                        echo '</div>';
                        echo '</form>';
                            }
                        } else {
                            echo '<p>Please select a table from the sidebar.</p>';
                        }
                        break;
                        
                    case 'edit':
                    case 'insert':
                        if ($current_table) {
                            $columns = $pdo->query("DESCRIBE `$current_table`")->fetchAll();
                            $primaryKey = "";
                            $editing_row = [];
                            
                            foreach ($columns as $col) {
                                if ($col["Key"] === "PRI") {
                                    $primaryKey = $col["Field"];
                                    break;
                                }
                            }
                            
                            if ($action === 'edit' && $edit_id) {
                                // Fetch the row to edit
                                $stmt = $pdo->prepare("SELECT * FROM `$current_table` WHERE `$primaryKey` = ?");
                                $stmt->execute([$edit_id]);
                                $editing_row = $stmt->fetch();
                                
                                if (!$editing_row) {
                                    echo '<div class="message error">Record not found!</div>';
                                    echo '<a href="?action=browse&table=' . urlencode($current_table) . '" class="btn btn-primary">Back to Browse</a>';
                                    break;
                                }
                            }
                            
                            echo '<h2>' . ($action === 'edit' ? 'Edit Record' : 'Add New Record') . '</h2>';
                            echo '<form method="post">';
                            echo '<input type="hidden" name="action" value="' . ($action === 'edit' ? 'update' : 'insert') . '">';
                            echo '<input type="hidden" name="table" value="' . htmlspecialchars($current_table) . '">';
                            
                            if ($action === 'edit') {
                                echo '<input type="hidden" name="primary_key" value="' . htmlspecialchars($primaryKey) . '">';
                                echo '<input type="hidden" name="primary_value" value="' . htmlspecialchars($edit_id) . '">';
                            }
                            
                            foreach ($columns as $col) {
                                $field = $col["Field"];
                                $value = $editing_row[$field] ?? '';
                                $is_auto_increment = strpos($col["Extra"], 'auto_increment') !== false;
                                
                                if ($action === 'insert' && $is_auto_increment) {
                                    continue; // Skip auto-increment fields for new records
                                }
                                
                                echo '<div class="form-group">';
                                echo '<label for="' . htmlspecialchars($field) . '">' . htmlspecialchars($field) . ':</label>';
                                
                                if ($action === 'edit' && $field === $primaryKey) {
                                    // Display primary key as read-only when editing
                                    echo '<input type="text" id="' . htmlspecialchars($field) . '" value="' . htmlspecialchars($value) . '" readonly>';
                                    echo '<input type="hidden" name="data[' . htmlspecialchars($field) . ']" value="' . htmlspecialchars($value) . '">';
                                } else {
                                    echo '<input type="text" id="' . htmlspecialchars($field) . '" name="data[' . htmlspecialchars($field) . ']" value="' . htmlspecialchars($value) . '"' . ($is_auto_increment ? ' readonly' : '') . '>';
                                }
                                
                                echo '</div>';
                            }
                            
                            echo '<div style="display: flex; gap: 10px;">';
                            echo '<button type="submit" class="success">' . ($action === 'edit' ? 'Update Record' : 'Add Record') . '</button>';
                            echo '<a href="?action=browse&table=' . urlencode($current_table) . '" class="btn btn-warning">Cancel</a>';
                            echo '</div>';
                            echo '</form>';
                        } else {
                            echo '<p>Please select a table from the sidebar.</p>';
                        }
                        break;
                        
                    case 'sql':
                        echo '<h2>SQL Command</h2>';
                        echo '<form method="post">';
                        echo '<input type="hidden" name="action" value="execute_sql">';
                        echo '<div class="form-group">';
                        echo '<label>SQL Query:</label>';
                        echo '<textarea name="sql_query" class="sql-editor" placeholder="Enter your SQL query here..." required>' . ($_POST["sql_query"] ?? "") . '</textarea>';
                        echo '</div>';
                        echo '<button type="submit">Execute</button>';
                        echo '</form>';
                        
                        if (isset($_SESSION["sql_result"])) {
                            echo '<h3>Results</h3>';
                            if (!empty($_SESSION["sql_result"])) {
                                echo '<table>';
                                echo '<thead><tr>';
                                foreach ($_SESSION["sql_columns"] as $col) {
                                    echo '<th>' . htmlspecialchars($col) . '</th>';
                                }
                                echo '</tr></thead>';
                                echo '<tbody>';
                                foreach ($_SESSION["sql_result"] as $row) {
                                    echo '<tr>';
                                    foreach ($_SESSION["sql_columns"] as $col) {
                                        echo '<td>' . htmlspecialchars($row[$col] ?? "") . '</td>';
                                    }
                                    echo '</tr>';
                                }
                                echo '</tbody></table>';
                            } else {
                                echo '<p>No results returned.</p>';
                            }
                            unset($_SESSION["sql_result"], $_SESSION["sql_columns"]);
                        }
                        break;
                        
                    case 'create_table':
                        echo '<h2>Create New Table</h2>';
                        echo '<form method="post">';
                        echo '<input type="hidden" name="action" value="create_table">';
                        
                        echo '<div class="form-group">';
                        echo '<label>Table Name:</label>';
                        echo '<input type="text" name="table_name" required>';
                        echo '</div>';
                        
                        echo '<div class="advanced-options">';
                        echo '<h4>Table Options</h4>';
                        echo '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">';
                        echo '<div><label>Engine:</label><select name="engine"><option value="InnoDB">InnoDB</option><option value="MyISAM">MyISAM</option></select></div>';
                        echo '<div><label>Charset:</label><input type="text" name="charset" value="utf8mb4"></div>';
                        echo '<div><label>Collation:</label><input type="text" name="collation" value="utf8mb4_unicode_ci"></div>';
                        echo '<div><label>Comment:</label><input type="text" name="comment"></div>';
                        echo '</div>';
                        echo '</div>';
                        
                        echo '<h3>Columns</h3>';
                        echo '<div id="columns-container">';
                        echo '<div class="column-form">';
                        echo '<input type="text" name="columns[0][name]" placeholder="Column name" class="small-input" required>';
                        echo '<select name="columns[0][type]" class="small-input" required>';
                        echo '<option value="INT">INT</option>';
                        echo '<option value="VARCHAR(255)">VARCHAR</option>';
                        echo '<option value="TEXT">TEXT</option>';
                        echo '<option value="DATE">DATE</option>';
                        echo '<option value="DATETIME">DATETIME</option>';
                        echo '<option value="BOOLEAN">BOOLEAN</option>';
                        echo '<option value="BIGINT">BIGINT</option>';
                        echo '<option value="DECIMAL">DECIMAL</option>';
                        echo '</select>';
                        echo '<input type="text" name="columns[0][length]" placeholder="Length" class="small-input">';
                        echo '<select name="columns[0][null]" class="small-input">';
                        echo '<option value="NO">NOT NULL</option>';
                        echo '<option value="YES">NULL</option>';
                        echo '</select>';
                        echo '<input type="text" name="columns[0][default]" placeholder="Default" class="small-input">';
                        echo '<select name="columns[0][auto_increment]" class="small-input">';
                        echo '<option value="NO">No</option>';
                        echo '<option value="YES">Auto Inc</option>';
                        echo '</select>';
                        echo '<input type="text" name="columns[0][comment]" placeholder="Comment" class="small-input">';
                        echo '<button type="button" onclick="removeColumn(this)">Remove</button>';
                        echo '</div>';
                        echo '</div>';
                        
                        echo '<button type="button" onclick="addColumnForm()" class="btn btn-primary">Add Column</button>';
                        
                        echo '<div class="advanced-options">';
                        echo '<h4>Primary Key</h4>';
                        echo '<select name="primary_key" id="primary_key_select">';
                        echo '<option value="">-- No Primary Key --</option>';
                        echo '</select>';
                        echo '</div>';
                        
                        echo '<div style="margin-top: 20px;">';
                        echo '<button type="submit" class="btn btn-success">Create Table</button>';
                        echo '</div>';
                        echo '</form>';
                        break;
                        
                    case 'import':
                        echo '<h2>Import SQL File</h2>';
                        echo '<form method="post" enctype="multipart/form-data">';
                        echo '<input type="hidden" name="action" value="import">';
                        echo '<div class="form-group">';
                        echo '<label>SQL File:</label>';
                        echo '<input type="file" name="sql_file" accept=".sql" required>';
                        echo '</div>';
                        echo '<button type="submit">Import</button>';
                        echo '</form>';
                        break;
                        
                    case 'export':
                        if ($current_table) {
                            echo '<h2>Export Table: ' . htmlspecialchars($current_table) . '</h2>';
                            echo '<p>Click the button below to export this table as an SQL file.</p>';
                            echo '<form method="post">';
                            echo '<input type="hidden" name="action" value="export">';
                            echo '<input type="hidden" name="table" value="' . htmlspecialchars($current_table) . '">';
                            echo '<button type="submit" class="success">Export SQL File</button>';
                            echo '</form>';
                        } else {
                            echo '<h2>Export Database</h2>';
                            echo '<p>Please select a table from the sidebar to export.</p>';
                        }
                        break;
                        
                    default:
                        echo '<h2>Database Manager</h2>';
                        echo '<p>Select an action from the sidebar.</p>';
                        break;
                }
                ?>
            </div>
        </div>
    </div>

    <script>
    function addNewColumn() {
    const container = document.getElementById('add-columns-container');
    const index = container.children.length;
    const html = `
        <div class="column-form" style="background: #e8f4fd; padding: 15px; margin: 10px 0; border-radius: 5px;">
            <h4>New Column</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr 1fr; gap: 10px; align-items: end;">
                <div>
                    <label>Column Name:</label>
                    <input type="text" name="add_columns[${index}][name]" placeholder="new_column" class="small-input">
                </div>
                <div>
                    <label>Data Type:</label>
                    <select name="add_columns[${index}][type]" class="small-input">
                        <option value="VARCHAR(255)">VARCHAR(255)</option>
                        <option value="INT">INT</option>
                        <option value="TEXT">TEXT</option>
                        <option value="DATE">DATE</option>
                        <option value="DATETIME">DATETIME</option>
                        <option value="BOOLEAN">BOOLEAN</option>
                    </select>
                </div>
                <div>
                    <label>Nullable:</label>
                    <select name="add_columns[${index}][null]" class="small-input">
                        <option value="NO">NOT NULL</option>
                        <option value="YES">NULL</option>
                    </select>
                </div>
                <div>
                    <label>Default Value:</label>
                    <input type="text" name="add_columns[${index}][default]" placeholder="Optional" class="small-input">
                </div>
                <div>
                    <label>After Column:</label>
                    <select name="add_columns[${index}][after]" class="small-input">
                        <option value="">- End of table -</option>
                        ${Array.from(document.querySelectorAll('input[name^="modify_columns"][name$="[name]"]')).map(input => 
                            `<option value="${input.value}">${input.value}</option>`
                        ).join('')}
                    </select>
                </div>
            </div>
            <div style="margin-top: 10px;">
                <button type="button" onclick="removeColumn(this)" class="btn btn-danger">Remove New Column</button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

function removeColumn(button) {
    button.closest('.column-form').remove();
    // Reindex the remaining columns
    const container = document.getElementById('add-columns-container');
    const forms = container.querySelectorAll('.column-form');
    forms.forEach((form, index) => {
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                input.setAttribute('name', name.replace(/\[\d+\]/, `[${index}]`));
            }
        });
    });
}
        function addColumnForm() {
            const container = document.getElementById('columns-container');
            const index = container.children.length;
            const html = `
                <div class="column-form">
                    <input type="text" name="columns[${index}][name]" placeholder="Column name" class="small-input" required>
                    <select name="columns[${index}][type]" class="small-input" required>
                        <option value="INT">INT</option>
                        <option value="VARCHAR(255)">VARCHAR</option>
                        <option value="TEXT">TEXT</option>
                        <option value="DATE">DATE</option>
                        <option value="DATETIME">DATETIME</option>
                        <option value="BOOLEAN">BOOLEAN</option>
                        <option value="BIGINT">BIGINT</option>
                        <option value="DECIMAL">DECIMAL</option>
                    </select>
                    <input type="text" name="columns[${index}][length]" placeholder="Length" class="small-input">
                    <select name="columns[${index}][null]" class="small-input">
                        <option value="NO">NOT NULL</option>
                        <option value="YES">NULL</option>
                    </select>
                    <input type="text" name="columns[${index}][default]" placeholder="Default" class="small-input">
                    <select name="columns[${index}][auto_increment]" class="small-input">
                        <option value="NO">No</option>
                        <option value="YES">Auto Inc</option>
                    </select>
                    <input type="text" name="columns[${index}][comment]" placeholder="Comment" class="small-input">
                    <button type="button" onclick="removeColumn(this)">Remove</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            updatePrimaryKeyOptions();
        }
        
        function removeColumn(button) {
            button.parentElement.remove();
            updatePrimaryKeyOptions();
        }
        
        function updatePrimaryKeyOptions() {
            const select = document.getElementById('primary_key_select');
            const columns = document.querySelectorAll('input[name^="columns"][name$="[name]"]');
            
            select.innerHTML = '<option value="">-- No Primary Key --</option>';
            columns.forEach(input => {
                if (input.value) {
                    const option = document.createElement('option');
                    option.value = input.value;
                    option.textContent = input.value;
                    select.appendChild(option);
                }
            });
        }
        
        function showTriggerForm() {
            document.getElementById('trigger-form').style.display = 'block';
        }
        
        function hideTriggerForm() {
            document.getElementById('trigger-form').style.display = 'none';
        }
        
        function confirmDelete() {
            return confirm('Are you sure you want to delete this record?');
        }
        
        // Initialize primary key options
        document.addEventListener('DOMContentLoaded', function() {
            updatePrimaryKeyOptions();
        });
    </script>
</body>
</html>
