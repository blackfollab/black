<?php
/**
 * Ultimate Database Manager - Complete phpMyAdmin Alternative
 * Single File, Full Featured MySQL Database Administration Tool
 * Version 2.0 - Professional Edition
 */

error_reporting(0);
if (ob_get_level() == 0) ob_start();
if (session_status() === PHP_SESSION_NONE) @session_start();
@ini_set('memory_limit', '512M');
@ini_set('max_execution_time', 600);

// ==================== AUTHENTICATION ====================
if (isset($_GET['logout'])) {
    unset($_SESSION['db_auth']);
    if (isset($_COOKIE['db_persist'])) @setcookie('db_persist', '', time() - 3600, '/');
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

$authenticated = false;
$db_config = null;

if (isset($_SESSION['db_auth'])) {
    $authenticated = true;
    $db_config = $_SESSION['db_auth'];
} elseif (isset($_COOKIE['db_persist'])) {
    $data = @json_decode(base64_decode($_COOKIE['db_persist']), true);
    if ($data && isset($data['config'], $data['hash'])) {
        $hash = hash_hmac('sha256', json_encode($data['config']), 'secret_2025');
        if (hash_equals($hash, $data['hash'])) {
            $authenticated = true;
            $db_config = $data['config'];
            $_SESSION['db_auth'] = $db_config;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $host = trim($_POST['server'] ?? 'localhost');
    $database = trim($_POST['database'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    try {
        $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
        $test = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $db_config = compact('host', 'database', 'username', 'password');
        $_SESSION['db_auth'] = $db_config;
        
        if (isset($_POST['permanent'])) {
            $cookie = ['config' => $db_config, 'hash' => hash_hmac('sha256', json_encode($db_config), 'secret_2025')];
            @setcookie('db_persist', base64_encode(json_encode($cookie)), time() + 315360000, '/');
        }
        
        $authenticated = true;
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    } catch (PDOException $e) {
        $error = 'Connection failed: ' . $e->getMessage();
    }
}

// ==================== LOGIN PAGE ====================
if (!$authenticated) {
    if (ob_get_level()) ob_clean();
    ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - Database Manager</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font: 14px Verdana, Arial, sans-serif; background: #2c5282; }
#login { max-width: 400px; margin: 100px auto; background: #fff; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); }
#heading { background: linear-gradient(135deg, #1e3c72, #2a5298); color: #fff; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
h1 { font-size: 24px; margin-bottom: 5px; }
.subtitle { font-size: 13px; opacity: 0.9; }
form { padding: 30px; }
table { width: 100%; }
th { text-align: right; padding: 8px 10px 8px 0; font-weight: normal; color: #555; }
td { padding: 8px 0; }
input[type="text"], input[type="password"] { width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 4px; font-size: 14px; }
input[type="text"]:focus, input[type="password"]:focus { outline: none; border-color: #2a5298; }
input[type="submit"] { width: 100%; padding: 12px; background: linear-gradient(135deg, #1e3c72, #2a5298); color: #fff; border: none; border-radius: 4px; font-size: 15px; font-weight: 600; cursor: pointer; margin-top: 10px; }
input[type="submit"]:hover { background: linear-gradient(135deg, #163254, #1e3c72); }
.error { background: #fee; color: #c33; padding: 12px; border-radius: 4px; margin-bottom: 15px; border-left: 4px solid #c33; }
label { display: flex; align-items: center; margin-top: 10px; cursor: pointer; }
input[type="checkbox"] { margin-right: 8px; }
</style>
</head>
<body>
<div id="login">
<div id="heading"><h1>Database Manager</h1><div class="subtitle">Professional MySQL Administration</div></div>
<form method="post">
<?php if (isset($error)): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<table>
<tr><th>Server:<td><input name="server" value="<?= htmlspecialchars($_POST['server'] ?? 'localhost') ?>" autocapitalize="off">
<tr><th>Username:<td><input name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" autocapitalize="off">
<tr><th>Password:<td><input type="password" name="password">
<tr><th>Database:<td><input name="database" value="<?= htmlspecialchars($_POST['database'] ?? '') ?>" autocapitalize="off">
</table>
<label><input type="checkbox" name="permanent"> Permanent login (10 years)</label>
<input type="submit" name="login" value="Login">
</form>
</div>
</body>
</html>
    <?php exit;
}

// ==================== DATABASE CONNECTION ====================
extract($db_config);
try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}

// ==================== HELPER FUNCTIONS ====================
function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function q($s) { global $pdo; return $pdo->quote($s); }
function redirect($msg = '', $type = 'success') {
    $_SESSION['msg'] = $msg;
    $_SESSION['msg_type'] = $type;
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . ($_GET['table'] ?? '') . (isset($_GET['table']) ? '?table=' . urlencode($_GET['table']) : ''));
    exit;
}

// ==================== GET PARAMETERS ====================
$table = $_GET['table'] ?? '';
$view = $_GET['view'] ?? '';
$action = $_GET['select'] ?? $_GET['edit'] ?? $_GET['insert'] ?? $_GET['create'] ?? $_GET['alter'] ?? $_GET['triggers'] ?? $_GET['sql'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 30;
$offset = ($page - 1) * $limit;

// ==================== GET DATABASE INFO ====================
$tables = $pdo->query("SHOW FULL TABLES")->fetchAll(PDO::FETCH_NUM);
$table_list = [];
$view_list = [];
foreach ($tables as $t) {
    if ($t[1] === 'VIEW') $view_list[] = $t[0];
    else $table_list[] = $t[0];
}
$all_triggers = $pdo->query("SHOW TRIGGERS")->fetchAll();
$all_procedures = $pdo->query("SHOW PROCEDURE STATUS WHERE Db = " . q($database))->fetchAll();
$all_functions = $pdo->query("SHOW FUNCTION STATUS WHERE Db = " . q($database))->fetchAll();
$all_events = $pdo->query("SHOW EVENTS")->fetchAll();

$message = $_SESSION['msg'] ?? '';
$msg_type = $_SESSION['msg_type'] ?? 'success';
unset($_SESSION['msg'], $_SESSION['msg_type']);

// ==================== POST ACTIONS ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // SQL QUERY EXECUTION
        if (isset($_POST['query'])) {
            $sql = trim($_POST['query']);
            if (stripos($sql, 'SELECT') === 0 || stripos($sql, 'SHOW') === 0 || stripos($sql, 'DESCRIBE') === 0) {
                $result = $pdo->query($sql);
                $query_result = $result->fetchAll();
                $message = count($query_result) . ' rows returned';
                $msg_type = 'success';
            } else {
                $affected = $pdo->exec($sql);
                redirect("Query OK, $affected rows affected");
            }
        }
        
        // INSERT RECORD
        elseif (isset($_POST['insert']) && $table) {
            $columns = $_POST['fields'] ?? [];
            $values = $_POST['values'] ?? [];
            $cols = [];
            $vals = [];
            foreach ($columns as $i => $col) {
                if ($values[$i] !== '') {
                    $cols[] = "`$col`";
                    $vals[] = $values[$i] === 'NULL' ? 'NULL' : q($values[$i]);
                }
            }
            if ($cols) {
                $pdo->exec("INSERT INTO `$table` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ")");
                redirect('Record inserted successfully');
            }
        }
        
        // UPDATE RECORD
        elseif (isset($_POST['update']) && $table) {
            $pk = $_POST['primary_key'];
            $pk_val = $_POST['pk_value'];
            $fields = $_POST['fields'] ?? [];
            $values = $_POST['values'] ?? [];
            $sets = [];
            foreach ($fields as $i => $field) {
                $sets[] = "`$field` = " . ($values[$i] === 'NULL' ? 'NULL' : q($values[$i]));
            }
            $pdo->exec("UPDATE `$table` SET " . implode(', ', $sets) . " WHERE `$pk` = " . q($pk_val));
            redirect('Record updated successfully');
        }
        
        // DELETE RECORDS
        elseif (isset($_POST['delete']) && $table) {
            $ids = $_POST['check'] ?? [];
            $pk = $_POST['pk'];
            if ($ids) {
                $placeholders = implode(',', array_map('Adminer\q', $ids));
                $pdo->exec("DELETE FROM `$table` WHERE `$pk` IN ($placeholders)");
                redirect(count($ids) . ' record(s) deleted');
            }
        }
        
        // TRUNCATE TABLE
        elseif (isset($_POST['truncate']) && $table) {
            $pdo->exec("TRUNCATE TABLE `$table`");
            redirect('Table truncated');
        }
        
        // DROP TABLE
        elseif (isset($_POST['drop_table']) && $table) {
            $pdo->exec("DROP TABLE `$table`");
            $table = '';
            redirect('Table dropped');
        }
        
        // CREATE TABLE
        elseif (isset($_POST['create_table'])) {
            $name = trim($_POST['table_name']);
            $cols = $_POST['columns'] ?? [];
            $pk = $_POST['primary_key'] ?? '';
            $engine = $_POST['engine'] ?? 'InnoDB';
            $collation = $_POST['collation'] ?? 'utf8mb4_unicode_ci';
            
            $defs = [];
            foreach ($cols as $col) {
                if (!$col['name']) continue;
                $def = "`{$col['name']}` {$col['type']}";
                if ($col['length']) $def .= "({$col['length']})";
                if ($col['unsigned']) $def .= " UNSIGNED";
                if ($col['null'] === 'NO') $def .= " NOT NULL";
                if ($col['default'] !== '') $def .= " DEFAULT " . ($col['default'] === 'NULL' ? 'NULL' : q($col['default']));
                if ($col['auto_increment']) $def .= " AUTO_INCREMENT";
                if ($col['comment']) $def .= " COMMENT " . q($col['comment']);
                $defs[] = $def;
            }
            if ($pk) $defs[] = "PRIMARY KEY (`$pk`)";
            
            $pdo->exec("CREATE TABLE `$name` (\n  " . implode(",\n  ", $defs) . "\n) ENGINE=$engine COLLATE=$collation");
            $table = $name;
            redirect('Table created successfully');
        }
        
        // ALTER TABLE
        elseif (isset($_POST['alter_table']) && $table) {
            $changes = [];
            
            // Modify columns
            if (isset($_POST['modify'])) {
                foreach ($_POST['modify'] as $col) {
                    if (!$col['name']) continue;
                    $def = "`{$col['name']}` {$col['type']}";
                    if ($col['length']) $def .= "({$col['length']})";
                    if ($col['unsigned']) $def .= " UNSIGNED";
                    if ($col['null'] === 'NO') $def .= " NOT NULL";
                    if ($col['default'] !== '') $def .= " DEFAULT " . ($col['default'] === 'NULL' ? 'NULL' : q($col['default']));
                    if ($col['auto_increment']) $def .= " AUTO_INCREMENT";
                    $changes[] = "MODIFY COLUMN $def";
                }
            }
            
            // Add columns
            if (isset($_POST['add'])) {
                foreach ($_POST['add'] as $col) {
                    if (!$col['name']) continue;
                    $def = "`{$col['name']}` {$col['type']}";
                    if ($col['length']) $def .= "({$col['length']})";
                    if ($col['unsigned']) $def .= " UNSIGNED";
                    if ($col['null'] === 'NO') $def .= " NOT NULL";
                    if ($col['default'] !== '') $def .= " DEFAULT " . ($col['default'] === 'NULL' ? 'NULL' : q($col['default']));
                    if ($col['after']) $def .= " AFTER `{$col['after']}`";
                    $changes[] = "ADD COLUMN $def";
                }
            }
            
            // Drop columns
            if (isset($_POST['drop_cols'])) {
                foreach ($_POST['drop_cols'] as $col) {
                    if ($col) $changes[] = "DROP COLUMN `$col`";
                }
            }
            
            // Rename table
            if (isset($_POST['new_name']) && $_POST['new_name'] && $_POST['new_name'] !== $table) {
                $changes[] = "RENAME TO `{$_POST['new_name']}`";
                $table = $_POST['new_name'];
            }
            
            // Change engine
            if (isset($_POST['engine'])) {
                $changes[] = "ENGINE={$_POST['engine']}";
            }
            
            // Change collation
            if (isset($_POST['collation'])) {
                $changes[] = "COLLATE={$_POST['collation']}";
            }
            
            if ($changes) {
                $pdo->exec("ALTER TABLE `$table` " . implode(', ', $changes));
                redirect('Table altered successfully');
            }
        }
        
        // CREATE INDEX
        elseif (isset($_POST['create_index']) && $table) {
            $name = $_POST['index_name'];
            $type = $_POST['index_type'];
            $columns = $_POST['index_columns'] ?? [];
            $cols = implode('`, `', array_filter($columns));
            if ($cols) {
                $sql = $type === 'PRIMARY' ? "ALTER TABLE `$table` ADD PRIMARY KEY (`$cols`)" : 
                       "CREATE " . ($type === 'UNIQUE' ? 'UNIQUE ' : '') . "INDEX `$name` ON `$table` (`$cols`)";
                $pdo->exec($sql);
                redirect('Index created successfully');
            }
        }
        
        // DROP INDEX
        elseif (isset($_POST['drop_index']) && $table) {
            $index = $_POST['index_name'];
            $sql = $index === 'PRIMARY' ? "ALTER TABLE `$table` DROP PRIMARY KEY" : "DROP INDEX `$index` ON `$table`";
            $pdo->exec($sql);
            redirect('Index dropped successfully');
        }
        
        // CREATE VIEW
        elseif (isset($_POST['create_view'])) {
            $name = trim($_POST['view_name']);
            $query = trim($_POST['view_select']);
            $pdo->exec("CREATE VIEW `$name` AS $query");
            redirect('View created successfully');
        }
        
        // DROP VIEW
        elseif (isset($_POST['drop_view']) && $view) {
            $pdo->exec("DROP VIEW `$view`");
            redirect('View dropped successfully');
        }
        
        // CREATE TRIGGER
        elseif (isset($_POST['create_trigger'])) {
            $name = trim($_POST['trigger_name']);
            $time = $_POST['trigger_time'];
            $event = $_POST['trigger_event'];
            $tbl = $_POST['trigger_table'];
            $body = trim($_POST['trigger_body']);
            $pdo->exec("CREATE TRIGGER `$name` $time $event ON `$tbl` FOR EACH ROW BEGIN $body END");
            redirect('Trigger created successfully');
        }
        
        // DROP TRIGGER
        elseif (isset($_POST['drop_trigger'])) {
            $name = $_POST['trigger_name'];
            $pdo->exec("DROP TRIGGER `$name`");
            redirect('Trigger dropped successfully');
        }
        
        // CREATE PROCEDURE
        elseif (isset($_POST['create_procedure'])) {
            $name = trim($_POST['proc_name']);
            $params = trim($_POST['proc_params']);
            $body = trim($_POST['proc_body']);
            $pdo->exec("CREATE PROCEDURE `$name` ($params) BEGIN $body END");
            redirect('Procedure created successfully');
        }
        
        // CREATE FUNCTION
        elseif (isset($_POST['create_function'])) {
            $name = trim($_POST['func_name']);
            $params = trim($_POST['func_params']);
            $returns = trim($_POST['func_returns']);
            $body = trim($_POST['func_body']);
            $pdo->exec("CREATE FUNCTION `$name` ($params) RETURNS $returns BEGIN $body END");
            redirect('Function created successfully');
        }
        
        // CREATE EVENT
        elseif (isset($_POST['create_event'])) {
            $name = trim($_POST['event_name']);
            $schedule = trim($_POST['event_schedule']);
            $body = trim($_POST['event_body']);
            $pdo->exec("CREATE EVENT `$name` ON SCHEDULE $schedule DO BEGIN $body END");
            redirect('Event created successfully');
        }
        
        // IMPORT SQL
        elseif (isset($_FILES['sql_file']) && $_FILES['sql_file']['error'] === 0) {
            $sql = file_get_contents($_FILES['sql_file']['tmp_name']);
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            $count = 0;
            foreach ($statements as $stmt) {
                if ($stmt) {
                    try { $pdo->exec($stmt); $count++; } catch (Exception $e) {}
                }
            }
            redirect("$count SQL statements executed");
        }
        
        // EXPORT TABLE
        elseif (isset($_POST['export']) && $table) {
            $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch();
            $sql = $create['Create Table'] . ";\n\n";
            $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll();
            foreach ($rows as $row) {
                $values = array_map(function($v) { return $v === null ? 'NULL' : q($v); }, array_values($row));
                $sql .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
            }
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . $table . '_' . date('Ymd_His') . '.sql"');
            echo $sql;
            exit;
        }
        
        // OPTIMIZE TABLE
        elseif (isset($_POST['optimize']) && $table) {
            $pdo->exec("OPTIMIZE TABLE `$table`");
            redirect('Table optimized');
        }
        
        // REPAIR TABLE
        elseif (isset($_POST['repair']) && $table) {
            $pdo->exec("REPAIR TABLE `$table`");
            redirect('Table repaired');
        }
        
        // CHECK TABLE
        elseif (isset($_POST['check']) && $table) {
            $result = $pdo->query("CHECK TABLE `$table`")->fetchAll();
            $msg = '';
            foreach ($result as $r) $msg .= "{$r['Table']}: {$r['Msg_text']}<br>";
            $_SESSION['msg'] = $msg;
            $_SESSION['msg_type'] = 'success';
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
        
    } catch (PDOException $e) {
        $message = 'Error: ' . $e->getMessage();
        $msg_type = 'error';
    }
}

// ==================== GET TABLE DATA ====================
$columns = [];
$data = [];
$total = 0;
$pk = 'id';
$indexes = [];
$table_status = null;

if ($table && in_array($table, $table_list)) {
    $columns = $pdo->query("SHOW FULL COLUMNS FROM `$table`")->fetchAll();
    $total = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    
    foreach ($columns as $col) {
        if ($col['Key'] === 'PRI') {
            $pk = $col['Field'];
            break;
        }
    }
    
    if (isset($_GET['select']) || (!isset($_GET['alter']) && !isset($_GET['insert']) && !isset($_GET['create']))) {
        $order = isset($_GET['order']) ? " ORDER BY `{$_GET['order']}`" . (isset($_GET['desc']) ? ' DESC' : ' ASC') : '';
        $data = $pdo->query("SELECT * FROM `$table`$order LIMIT $limit OFFSET $offset")->fetchAll();
    }
    
    if (isset($_GET['edit']) && isset($_GET['id'])) {
        $edit_data = $pdo->query("SELECT * FROM `$table` WHERE `$pk` = " . q($_GET['id']))->fetch();
    }
    
    $indexes = $pdo->query("SHOW INDEX FROM `$table`")->fetchAll();
    $table_status = $pdo->query("SHOW TABLE STATUS LIKE " . q($table))->fetch();
}

if (ob_get_level()) ob_clean();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($database) ?> - Database Manager</title>
<link rel="icon" type="image/png" href="https://www.adminer.org/favicon.ico">
    <!-- Or for the PNG version -->
    <link rel="icon" type="image/png" href="https://www.adminer.org/static/images/favicon.png">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font: 14px Verdana, Arial, sans-serif; background: #f8f9fa; color: #333; }

#heading { background: linear-gradient(135deg, #1e3c72, #2a5298); color: #fff; padding: 12px 15px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
#heading h1 { font-size: 18px; font-weight: normal; }
#heading a { color: #fff; text-decoration: none; padding: 6px 15px; background: rgba(255,255,255,0.2); border-radius: 4px; font-size: 13px; }
#heading a:hover { background: rgba(255,255,255,0.3); }

#content { display: flex; min-height: calc(100vh - 50px); }

#menu { width: 250px; background: #fff; border-right: 1px solid #ddd; padding: 15px 0; overflow-y: auto; flex-shrink: 0; }
#menu h3 { font-size: 11px; text-transform: uppercase; color: #999; padding: 8px 15px; margin-top: 15px; font-weight: 600; }
#menu h3:first-child { margin-top: 0; }
#menu a { display: block; padding: 8px 15px; color: #333; text-decoration: none; transition: all 0.2s; font-size: 13px; }
#menu a:hover { background: #f0f4f8; padding-left: 20px; }
#menu a.active { background: #2a5298; color: #fff; font-weight: 600; }
#menu .count { float: right; background: #e0e0e0; color: #666; padding: 2px 8px; border-radius: 10px; font-size: 11px; }

#main { flex: 1; padding: 20px; overflow-y: auto; max-width: calc(100vw - 250px); }

.message { padding: 12px 15px; border-radius: 4px; margin-bottom: 15px; }
.message.success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
.message.error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }

table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
thead { background: #2a5298; color: #fff; }
th { padding: 12px; text-align: left; font-weight: 600; font-size: 13px; white-space: nowrap; }
th a { color: #fff; text-decoration: none; }
th a:hover { text-decoration: underline; }
td { padding: 10px 12px; border-bottom: 1px solid #e9ecef; font-size: 13px; }
tbody tr:hover { background: #f8f9fa; }
tbody tr:last-child td { border-bottom: none; }

.number { text-align: right; font-family: 'Courier New', monospace; }
.null { color: #999; font-style: italic; }
.blob { color: #999; font-style: italic; }

h2 { font-size: 20px; margin-bottom: 15px; color: #2a5298; padding-bottom: 10px; border-bottom: 2px solid #e9ecef; }

.stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px; }
.stat { background: linear-gradient(135deg, #1e3c72, #2a5298); color: #fff; padding: 20px; border-radius: 6px; }
.stat-value { font-size: 32px; font-weight: 700; margin-bottom: 5px; }
.stat-label { font-size: 13px; opacity: 0.9; }

.actions { margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 8px; }
.btn { display: inline-block; padding: 8px 16px; background: #2a5298; color: #fff; text-decoration: none; border-radius: 4px; font-size: 13px; border: none; cursor: pointer; }
.btn:hover { background: #1e3c72; }
.btn-success { background: #28a745; }
.btn-success:hover { background: #218838; }
.btn-danger { background: #dc3545; }
.btn-danger:hover { background: #c82333; }
.btn-warning { background: #ffc107; color: #000; }
.btn-warning:hover { background: #e0a800; }
.btn-secondary { background: #6c757d; }
.btn-secondary:hover { background: #5a6268; }
.btn-small { padding: 4px 8px; font-size: 12px; }

textarea { width: 100%; min-height: 150px; padding: 10px; border: 2px solid #ddd; border-radius: 4px; font-family: 'Courier New', monospace; font-size: 13px; resize: vertical; }
textarea:focus { outline: none; border-color: #2a5298; }

input[type="text"], input[type="number"], select { padding: 8px; border: 2px solid #ddd; border-radius: 4px; font-size: 13px; }
input[type="text"]:focus, input[type="number"]:focus, select:focus { outline: none; border-color: #2a5298; }

.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; }

.pagination { margin-top: 20px; text-align: center; }
.pagination a { display: inline-block; padding: 6px 12px; margin: 0 2px; background: #fff; border: 1px solid #ddd; border-radius: 4px; color: #2a5298; text-decoration: none; }
.pagination a:hover { background: #f0f4f8; }
.pagination .current { background: #2a5298; color: #fff; border-color: #2a5298; }

.trigger, .routine, .event { background: #f8f9fa; padding: 15px; margin-bottom: 10px; border-left: 3px solid #2a5298; border-radius: 4px; }
.trigger-name, .routine-name, .event-name { font-weight: 600; color: #2a5298; margin-bottom: 5px; }
.trigger-meta, .routine-meta, .event-meta { font-size: 12px; color: #666; margin-bottom: 8px; }
.trigger-body, .routine-body, .event-body { background: #fff; padding: 10px; border-radius: 4px; font-family: 'Courier New', monospace; font-size: 12px; overflow-x: auto; white-space: pre-wrap; }

.column-editor { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 4px; border: 1px solid #ddd; }
.column-grid { display: grid; grid-template-columns: 2fr 1.5fr 1fr 1fr 1fr 1fr 2fr 0.5fr; gap: 8px; align-items: end; margin-bottom: 10px; }
.column-grid input, .column-grid select { padding: 6px; }

.info-box { background: #e7f3ff; border-left: 4px solid #2196F3; padding: 12px; margin-bottom: 15px; border-radius: 4px; }

.tab-nav { display: flex; gap: 5px; margin-bottom: 15px; border-bottom: 2px solid #e9ecef; }
.tab-nav a { padding: 10px 20px; text-decoration: none; color: #555; border-bottom: 3px solid transparent; margin-bottom: -2px; }
.tab-nav a:hover { color: #2a5298; }
.tab-nav a.active { color: #2a5298; border-bottom-color: #2a5298; font-weight: 600; }

input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; }

.overflow { overflow-x: auto; }

@media (max-width: 768px) {
    #content { flex-direction: column; }
    #menu { width: 100%; border-right: none; border-bottom: 1px solid #ddd; }
    #main { max-width: 100%; }
    .column-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<div id="heading">
    <h1><?= h($database) ?> @ <?= h($host) ?></h1>
    <div>
        <a href="?import=1">📥 Import</a>
        <a href="?logout">Logout</a>
    </div>
</div>

<div id="content">
    <div id="menu">
        <h3>Database</h3>
        <a href="?">📊 Overview</a>
        <a href="?sql=1">⚡ SQL Query</a>
        <a href="?create=1">➕ Create Table</a>
        <a href="?variables=1">⚙️ Variables</a>
        <a href="?import=1">📥 Import SQL</a>
        
        <h3>Tables (<?= count($table_list) ?>)</h3>
        <?php foreach ($table_list as $t): ?>
        <a href="?table=<?= urlencode($t) ?>" class="<?= $t === $table ? 'active' : '' ?>"><?= h($t) ?></a>
        <?php endforeach; ?>
        
        <?php if ($view_list): ?>
        <h3>Views (<?= count($view_list) ?>)</h3>
        <?php foreach ($view_list as $v): ?>
        <a href="?view=<?= urlencode($v) ?>" class="<?= $v === $view ? 'active' : '' ?>"><?= h($v) ?></a>
        <?php endforeach; ?>
        <?php endif; ?>
        
        <h3>Routines</h3>
        <a href="?triggers=1">Triggers <span class="count"><?= count($all_triggers) ?></span></a>
        <a href="?procedures=1">Procedures <span class="count"><?= count($all_procedures) ?></span></a>
        <a href="?functions=1">Functions <span class="count"><?= count($all_functions) ?></span></a>
        <a href="?events=1">Events <span class="count"><?= count($all_events) ?></span></a>
    </div>
    
    <div id="main">
        <?php if ($message): ?>
        <div class="message <?= $msg_type ?>"><?= $message ?></div>
        <?php endif; ?>
        
        <?php
        // ==================== PAGES ====================
        
        // IMPORT PAGE
        if (isset($_GET['import'])):
        ?>
            <h2>Import SQL File</h2>
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Select SQL File:</label>
                    <input type="file" name="sql_file" accept=".sql" required>
                </div>
                <button type="submit" class="btn btn-success">Import SQL File</button>
            </form>
            
        <?php
        // VARIABLES PAGE
        elseif (isset($_GET['variables'])):
            $variables = $pdo->query("SHOW VARIABLES")->fetchAll();
        ?>
            <h2>Server Variables</h2>
            <table>
                <thead>
                    <tr><th>Variable<th>Value</tr>
                </thead>
                <tbody>
                    <?php foreach ($variables as $v): ?>
                    <tr>
                        <td><?= h($v['Variable_name']) ?>
                        <td><?= h($v['Value']) ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
        <?php
        // SQL COMMAND PAGE
        elseif (isset($_GET['sql'])):
        ?>
            <h2>SQL Command</h2>
            <form method="post">
                <textarea name="query" placeholder="Enter SQL query..."><?= h($_POST['query'] ?? '') ?></textarea>
                <div style="margin-top: 10px;">
                    <button type="submit" class="btn btn-success">Execute</button>
                </div>
            </form>
            
            <?php if (isset($query_result)): ?>
            <h2 style="margin-top: 30px;">Results</h2>
            <?php if (count($query_result) > 0): ?>
            <div class="overflow">
                <table>
                    <thead>
                        <tr>
                            <?php foreach (array_keys($query_result[0]) as $col): ?>
                            <th><?= h($col) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($query_result as $row): ?>
                        <tr>
                            <?php foreach ($row as $val): ?>
                            <td><?= $val === null ? '<span class="null">NULL</span>' : h($val) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p>No results returned.</p>
            <?php endif; ?>
            <?php endif; ?>
            
        <?php
        // CREATE TABLE PAGE
        elseif (isset($_GET['create'])):
        ?>
            <h2>Create Table</h2>
            <form method="post">
                <div class="form-group">
                    <label>Table Name:</label>
                    <input type="text" name="table_name" required style="width: 300px;">
                </div>
                
                <h3 style="margin: 20px 0 10px;">Columns</h3>
                <div id="columns">
                    <div class="column-grid">
                        <div><strong>Name</strong></div>
                        <div><strong>Type</strong></div>
                        <div><strong>Length</strong></div>
                        <div><strong>Unsigned</strong></div>
                        <div><strong>Null</strong></div>
                        <div><strong>Auto Inc</strong></div>
                        <div><strong>Default</strong></div>
                        <div></div>
                    </div>
                    <div class="column-editor">
                        <div class="column-grid">
                            <input type="text" name="columns[0][name]" value="id" required>
                            <select name="columns[0][type]">
                                <option value="INT" selected>INT</option>
                                <option value="BIGINT">BIGINT</option>
                                <option value="VARCHAR">VARCHAR</option>
                                <option value="TEXT">TEXT</option>
                                <option value="DATE">DATE</option>
                                <option value="DATETIME">DATETIME</option>
                                <option value="DECIMAL">DECIMAL</option>
                                <option value="FLOAT">FLOAT</option>
                                <option value="DOUBLE">DOUBLE</option>
                                <option value="BOOLEAN">BOOLEAN</option>
                            </select>
                            <input type="text" name="columns[0][length]" value="11">
                            <input type="checkbox" name="columns[0][unsigned]" value="1" checked>
                            <select name="columns[0][null]">
                                <option value="NO">NOT NULL</option>
                                <option value="YES">NULL</option>
                            </select>
                            <input type="checkbox" name="columns[0][auto_increment]" value="1" checked>
                            <input type="text" name="columns[0][default]" placeholder="NULL or value">
                            <button type="button" onclick="removeColumn(this)" class="btn btn-danger btn-small">✕</button>
                        </div>
                        <input type="text" name="columns[0][comment]" placeholder="Comment" style="width: 100%; margin-top: 5px;">
                    </div>
                </div>
                <button type="button" onclick="addColumn()" class="btn btn-secondary">+ Add Column</button>
                
                <div class="form-group" style="margin-top: 20px;">
                    <label>Primary Key:</label>
                    <select name="primary_key" id="pk-select">
                        <option value="">-- No Primary Key --</option>
                        <option value="id" selected>id</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Engine:</label>
                    <select name="engine">
                        <option value="InnoDB" selected>InnoDB</option>
                        <option value="MyISAM">MyISAM</option>
                        <option value="MEMORY">MEMORY</option>
                        <option value="ARCHIVE">ARCHIVE</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Collation:</label>
                    <select name="collation">
                        <option value="utf8mb4_unicode_ci" selected>utf8mb4_unicode_ci</option>
                        <option value="utf8mb4_general_ci">utf8mb4_general_ci</option>
                        <option value="utf8_general_ci">utf8_general_ci</option>
                        <option value="latin1_swedish_ci">latin1_swedish_ci</option>
                    </select>
                </div>
                
                <button type="submit" name="create_table" class="btn btn-success">Create Table</button>
            </form>
            
            <script>
            let colIndex = 1;
            function addColumn() {
                const html = `
                <div class="column-editor">
                    <div class="column-grid">
                        <input type="text" name="columns[${colIndex}][name]" required>
                        <select name="columns[${colIndex}][type]">
                            <option value="INT">INT</option>
                            <option value="BIGINT">BIGINT</option>
                            <option value="VARCHAR" selected>VARCHAR</option>
                            <option value="TEXT">TEXT</option>
                            <option value="DATE">DATE</option>
                            <option value="DATETIME">DATETIME</option>
                            <option value="DECIMAL">DECIMAL</option>
                            <option value="FLOAT">FLOAT</option>
                            <option value="DOUBLE">DOUBLE</option>
                            <option value="BOOLEAN">BOOLEAN</option>
                        </select>
                        <input type="text" name="columns[${colIndex}][length]" value="255">
                        <input type="checkbox" name="columns[${colIndex}][unsigned]" value="1">
                        <select name="columns[${colIndex}][null]">
                            <option value="NO">NOT NULL</option>
                            <option value="YES" selected>NULL</option>
                        </select>
                        <input type="checkbox" name="columns[${colIndex}][auto_increment]" value="1">
                        <input type="text" name="columns[${colIndex}][default]" placeholder="NULL or value">
                        <button type="button" onclick="removeColumn(this)" class="btn btn-danger btn-small">✕</button>
                    </div>
                    <input type="text" name="columns[${colIndex}][comment]" placeholder="Comment" style="width: 100%; margin-top: 5px;">
                </div>`;
                document.getElementById('columns').insertAdjacentHTML('beforeend', html);
                colIndex++;
                updatePK();
            }
            
            function removeColumn(btn) {
                btn.closest('.column-editor').remove();
                updatePK();
            }
            
            function updatePK() {
                const select = document.getElementById('pk-select');
                const current = select.value;
                const inputs = document.querySelectorAll('input[name^="columns"][name$="[name]"]');
                select.innerHTML = '<option value="">-- No Primary Key --</option>';
                inputs.forEach(inp => {
                    if (inp.value) {
                        const opt = document.createElement('option');
                        opt.value = inp.value;
                        opt.textContent = inp.value;
                        if (inp.value === current) opt.selected = true;
                        select.appendChild(opt);
                    }
                });
            }
            
            document.addEventListener('input', e => {
                if (e.target.matches('input[name^="columns"][name$="[name]"]')) updatePK();
            });
            </script>
            
        <?php
        // TRIGGERS PAGE
        elseif (isset($_GET['triggers'])):
        ?>
            <h2>Triggers</h2>
            <div class="actions">
                <button onclick="showTriggerForm()" class="btn btn-success">+ Create Trigger</button>
            </div>
            
            <div id="trigger-form" style="display:none; background: #f8f9fa; padding: 20px; border-radius: 6px; margin-bottom: 20px;">
                <h3>Create New Trigger</h3>
                <form method="post">
                    <div class="form-group">
                        <label>Trigger Name:</label>
                        <input type="text" name="trigger_name" required style="width: 300px;">
                    </div>
                    <div class="form-group">
                        <label>Table:</label>
                        <select name="trigger_table" required style="width: 300px;">
                            <?php foreach ($table_list as $t): ?>
                            <option value="<?= h($t) ?>"><?= h($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Timing:</label>
                        <select name="trigger_time" required>
                            <option value="BEFORE">BEFORE</option>
                            <option value="AFTER">AFTER</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Event:</label>
                        <select name="trigger_event" required>
                            <option value="INSERT">INSERT</option>
                            <option value="UPDATE">UPDATE</option>
                            <option value="DELETE">DELETE</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Body:</label>
                        <textarea name="trigger_body" required placeholder="SET NEW.updated_at = NOW();"></textarea>
                    </div>
                    <button type="submit" name="create_trigger" class="btn btn-success">Create Trigger</button>
                    <button type="button" onclick="document.getElementById('trigger-form').style.display='none'" class="btn btn-secondary">Cancel</button>
                </form>
            </div>
            
            <?php if ($all_triggers): ?>
                <?php foreach ($all_triggers as $t): ?>
                <div class="trigger">
                    <div class="trigger-name"><?= h($t['Trigger']) ?></div>
                    <div class="trigger-meta">
                        <strong>Table:</strong> <?= h($t['Table']) ?> | 
                        <strong>Timing:</strong> <?= h($t['Timing']) ?> <?= h($t['Event']) ?>
                    </div>
                    <div class="trigger-body"><?= h($t['Statement']) ?></div>
                    <form method="post" style="margin-top: 10px;" onsubmit="return confirm('Drop this trigger?')">
                        <input type="hidden" name="trigger_name" value="<?= h($t['Trigger']) ?>">
                        <button type="submit" name="drop_trigger" class="btn btn-danger btn-small">Drop Trigger</button>
                    </form>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No triggers found.</p>
            <?php endif; ?>
            
            <script>
            function showTriggerForm() {
                document.getElementById('trigger-form').style.display = 'block';
            }
            </script>
            
        <?php
        // PROCEDURES PAGE
        elseif (isset($_GET['procedures'])):
        ?>
            <h2>Stored Procedures</h2>
            <div class="actions">
                <button onclick="showProcForm()" class="btn btn-success">+ Create Procedure</button>
            </div>
            
            <div id="proc-form" style="display:none; background: #f8f9fa; padding: 20px; border-radius: 6px; margin-bottom: 20px;">
                <h3>Create New Procedure</h3>
                <form method="post">
                    <div class="form-group">
                        <label>Procedure Name:</label>
                        <input type="text" name="proc_name" required style="width: 300px;">
                    </div>
                    <div class="form-group">
                        <label>Parameters:</label>
                        <input type="text" name="proc_params" placeholder="IN param1 INT, OUT param2 VARCHAR(255)" style="width: 100%;">
                    </div>
                    <div class="form-group">
                        <label>Body:</label>
                        <textarea name="proc_body" required placeholder="SELECT * FROM table;"></textarea>
                    </div>
                    <button type="submit" name="create_procedure" class="btn btn-success">Create Procedure</button>
                    <button type="button" onclick="document.getElementById('proc-form').style.display='none'" class="btn btn-secondary">Cancel</button>
                </form>
            </div>
            
            <?php if ($all_procedures): ?>
                <?php foreach ($all_procedures as $p): ?>
                <div class="routine">
                    <div class="routine-name"><?= h($p['Name']) ?></div>
                    <div class="routine-meta">
                        <strong>Type:</strong> <?= h($p['Type']) ?> | 
                        <strong>Created:</strong> <?= h($p['Created']) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No procedures found.</p>
            <?php endif; ?>
            
            <script>
            function showProcForm() {
                document.getElementById('proc-form').style.display = 'block';
            }
            </script>
            
        <?php
        // FUNCTIONS PAGE
        elseif (isset($_GET['functions'])):
        ?>
            <h2>Functions</h2>
            <div class="actions">
                <button onclick="showFuncForm()" class="btn btn-success">+ Create Function</button>
            </div>
            
            <div id="func-form" style="display:none; background: #f8f9fa; padding: 20px; border-radius: 6px; margin-bottom: 20px;">
                <h3>Create New Function</h3>
                <form method="post">
                    <div class="form-group">
                        <label>Function Name:</label>
                        <input type="text" name="func_name" required style="width: 300px;">
                    </div>
                    <div class="form-group">
                        <label>Parameters:</label>
                        <input type="text" name="func_params" placeholder="param1 INT, param2 VARCHAR(255)" style="width: 100%;">
                    </div>
                    <div class="form-group">
                        <label>Returns:</label>
                        <input type="text" name="func_returns" required placeholder="INT or VARCHAR(255)" style="width: 300px;">
                    </div>
                    <div class="form-group">
                        <label>Body:</label>
                        <textarea name="func_body" required placeholder="RETURN value;"></textarea>
                    </div>
                    <button type="submit" name="create_function" class="btn btn-success">Create Function</button>
                    <button type="button" onclick="document.getElementById('func-form').style.display='none'" class="btn btn-secondary">Cancel</button>
                </form>
            </div>
            
            <?php if ($all_functions): ?>
                <?php foreach ($all_functions as $f): ?>
                <div class="routine">
                    <div class="routine-name"><?= h($f['Name']) ?></div>
                    <div class="routine-meta">
                        <strong>Type:</strong> <?= h($f['Type']) ?> | 
                        <strong>Created:</strong> <?= h($f['Created']) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No functions found.</p>
            <?php endif; ?>
            
            <script>
            function showFuncForm() {
                document.getElementById('func-form').style.display = 'block';
            }
            </script>
            
        <?php
        // EVENTS PAGE
        elseif (isset($_GET['events'])):
        ?>
            <h2>Events</h2>
            <div class="actions">
                <button onclick="showEventForm()" class="btn btn-success">+ Create Event</button>
            </div>
            
            <div id="event-form" style="display:none; background: #f8f9fa; padding: 20px; border-radius: 6px; margin-bottom: 20px;">
                <h3>Create New Event</h3>
                <form method="post">
                    <div class="form-group">
                        <label>Event Name:</label>
                        <input type="text" name="event_name" required style="width: 300px;">
                    </div>
                    <div class="form-group">
                        <label>Schedule:</label>
                        <input type="text" name="event_schedule" required placeholder="EVERY 1 DAY or AT '2025-12-31 23:59:59'" style="width: 100%;">
                    </div>
                    <div class="form-group">
                        <label>Body:</label>
                        <textarea name="event_body" required placeholder="DELETE FROM logs WHERE created_at < NOW() - INTERVAL 30 DAY;"></textarea>
                    </div>
                    <button type="submit" name="create_event" class="btn btn-success">Create Event</button>
                    <button type="button" onclick="document.getElementById('event-form').style.display='none'" class="btn btn-secondary">Cancel</button>
                </form>
            </div>
            
            <?php if ($all_events): ?>
                <?php foreach ($all_events as $e): ?>
                <div class="event">
                    <div class="event-name"><?= h($e['Name']) ?></div>
                    <div class="event-meta">
                        <strong>Schedule:</strong> <?= h($e['Execute at'] ?: $e['Interval value'] . ' ' . $e['Interval field']) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No events found.</p>
            <?php endif; ?>
            
            <script>
            function showEventForm() {
                document.getElementById('event-form').style.display = 'block';
            }
            </script>
            
        <?php
        // TABLE VIEW
        elseif ($table):
        ?>
            <h2>Table: <?= h($table) ?></h2>
            
            <div class="tab-nav">
                <a href="?table=<?= urlencode($table) ?>" class="<?= !isset($_GET['alter']) && !isset($_GET['insert']) && !isset($_GET['edit']) ? 'active' : '' ?>">Browse</a>
                <a href="?table=<?= urlencode($table) ?>&alter=1" class="<?= isset($_GET['alter']) ? 'active' : '' ?>">Structure</a>
                <a href="?table=<?= urlencode($table) ?>&insert=1" class="<?= isset($_GET['insert']) ? 'active' : '' ?>">Insert</a>
            </div>
            
            <?php if (isset($_GET['alter'])): ?>
                <!-- ALTER TABLE PAGE -->
                <form method="post">
                    <div class="form-group">
                        <label>Table Name:</label>
                        <input type="text" name="new_name" value="<?= h($table) ?>" style="width: 300px;">
                    </div>
                    
                    <div class="form-group">
                        <label>Engine:</label>
                        <select name="engine">
                            <option value="InnoDB" <?= $table_status['Engine'] === 'InnoDB' ? 'selected' : '' ?>>InnoDB</option>
                            <option value="MyISAM" <?= $table_status['Engine'] === 'MyISAM' ? 'selected' : '' ?>>MyISAM</option>
                            <option value="MEMORY" <?= $table_status['Engine'] === 'MEMORY' ? 'selected' : '' ?>>MEMORY</option>
                            <option value="ARCHIVE" <?= $table_status['Engine'] === 'ARCHIVE' ? 'selected' : '' ?>>ARCHIVE</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Collation:</label>
                        <select name="collation">
                            <option value="utf8mb4_unicode_ci" <?= $table_status['Collation'] === 'utf8mb4_unicode_ci' ? 'selected' : '' ?>>utf8mb4_unicode_ci</option>
                            <option value="utf8mb4_general_ci" <?= $table_status['Collation'] === 'utf8mb4_general_ci' ? 'selected' : '' ?>>utf8mb4_general_ci</option>
                            <option value="utf8_general_ci" <?= $table_status['Collation'] === 'utf8_general_ci' ? 'selected' : '' ?>>utf8_general_ci</option>
                        </select>
                    </div>
                    
                    <h3 style="margin: 20px 0 10px;">Columns</h3>
                    <?php foreach ($columns as $i => $col): ?>
                    <div class="column-editor">
                        <div class="column-grid">
                            <input type="text" name="modify[<?= $i ?>][name]" value="<?= h($col['Field']) ?>" readonly>
                            <input type="text" name="modify[<?= $i ?>][type]" value="<?= h($col['Type']) ?>">
                            <input type="text" name="modify[<?= $i ?>][length]" placeholder="Length">
                            <input type="checkbox" name="modify[<?= $i ?>][unsigned]" <?= strpos($col['Type'], 'unsigned') !== false ? 'checked' : '' ?>>
                            <select name="modify[<?= $i ?>][null]">
                                <option value="NO" <?= $col['Null'] === 'NO' ? 'selected' : '' ?>>NOT NULL</option>
                                <option value="YES" <?= $col['Null'] === 'YES' ? 'selected' : '' ?>>NULL</option>
                            </select>
                            <input type="checkbox" name="modify[<?= $i ?>][auto_increment]" <?= $col['Extra'] === 'auto_increment' ? 'checked' : '' ?>>
                            <input type="text" name="modify[<?= $i ?>][default]" value="<?= h($col['Default']) ?>">
                            <label><input type="checkbox" name="drop_cols[]" value="<?= h($col['Field']) ?>"> Drop</label>
                        </div>
                        <div style="margin-top: 5px;">
                            <strong>Key:</strong> <?= h($col['Key']) ?> | 
                            <strong>Extra:</strong> <?= h($col['Extra']) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <h3 style="margin: 20px 0 10px;">Add Columns</h3>
                    <div id="add-cols"></div>
                    <button type="button" onclick="addAlterColumn()" class="btn btn-secondary">+ Add Column</button>
                    
                    <div style="margin-top: 20px;">
                        <button type="submit" name="alter_table" class="btn btn-success">Save Changes</button>
                    </div>
                </form>
                
                <h3 style="margin: 30px 0 10px;">Indexes</h3>
                <?php if ($indexes): ?>
                <table>
                    <thead>
                        <tr><th>Name<th>Columns<th>Type<th>Actions</tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grouped = [];
                        foreach ($indexes as $idx) {
                            $grouped[$idx['Key_name']][] = $idx;
                        }
                        foreach ($grouped as $name => $cols): 
                        ?>
                        <tr>
                            <td><?= h($name) ?>
                            <td><?= h(implode(', ', array_column($cols, 'Column_name'))) ?>
                            <td><?= $cols[0]['Non_unique'] ? ($cols[0]['Index_type'] === 'FULLTEXT' ? 'FULLTEXT' : 'INDEX') : 'UNIQUE' ?>
                            <td>
                                <form method="post" style="display:inline;" onsubmit="return confirm('Drop this index?')">
                                    <input type="hidden" name="index_name" value="<?= h($name) ?>">
                                    <button type="submit" name="drop_index" class="btn btn-danger btn-small">Drop</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
                
                <h4 style="margin: 15px 0 10px;">Create Index</h4>
                <form method="post" style="background: #f8f9fa; padding: 15px; border-radius: 4px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr 2fr auto; gap: 10px; align-items: end;">
                        <div>
                            <label>Index Name:</label>
                            <input type="text" name="index_name" required>
                        </div>
                        <div>
                            <label>Type:</label>
                            <select name="index_type">
                                <option value="INDEX">INDEX</option>
                                <option value="UNIQUE">UNIQUE</option>
                                <option value="PRIMARY">PRIMARY</option>
                            </select>
                        </div>
                        <div>
                            <label>Columns:</label>
                            <select name="index_columns[]" multiple required size="3">
                                <?php foreach ($columns as $col): ?>
                                <option value="<?= h($col['Field']) ?>"><?= h($col['Field']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <button type="submit" name="create_index" class="btn btn-success">Create</button>
                        </div>
                    </div>
                </form>
                
                <script>
                let addIndex = 0;
                function addAlterColumn() {
                    const html = `
                    <div class="column-editor">
                        <div class="column-grid">
                            <input type="text" name="add[${addIndex}][name]" placeholder="Column name" required>
                            <select name="add[${addIndex}][type]" required>
                                <option value="VARCHAR">VARCHAR</option>
                                <option value="INT">INT</option>
                                <option value="TEXT">TEXT</option>
                                <option value="DATE">DATE</option>
                                <option value="DATETIME">DATETIME</option>
                            </select>
                            <input type="text" name="add[${addIndex}][length]" placeholder="255">
                            <input type="checkbox" name="add[${addIndex}][unsigned]">
                            <select name="add[${addIndex}][null]">
                                <option value="YES">NULL</option>
                                <option value="NO">NOT NULL</option>
                            </select>
                            <input type="checkbox" name="add[${addIndex}][auto_increment]">
                            <input type="text" name="add[${addIndex}][default]" placeholder="Default">
                            <button type="button" onclick="this.closest('.column-editor').remove()" class="btn btn-danger btn-small">✕</button>
                        </div>
                        <select name="add[${addIndex}][after]" style="margin-top: 5px; width: 200px;">
                            <option value="">-- At end --</option>
                            <?php foreach ($columns as $c): ?>
                            <option value="<?= h($c['Field']) ?>">After <?= h($c['Field']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>`;
                    document.getElementById('add-cols').insertAdjacentHTML('beforeend', html);
                    addIndex++;
                }
                </script>
                
            <?php elseif (isset($_GET['insert']) || isset($_GET['edit'])): ?>
                <!-- INSERT/EDIT PAGE -->
                <form method="post">
                    <?php if (isset($_GET['edit'])): ?>
                    <input type="hidden" name="primary_key" value="<?= h($pk) ?>">
                    <input type="hidden" name="pk_value" value="<?= h($_GET['id']) ?>">
                    <?php endif; ?>
                    
                    <?php foreach ($columns as $i => $col): 
                        $value = isset($edit_data) ? $edit_data[$col['Field']] : '';
                    ?>
                    <div class="form-group">
                        <label><?= h($col['Field']) ?> <span style="color: #999;">(<?= h($col['Type']) ?>)</span></label>
                        <input type="hidden" name="fields[]" value="<?= h($col['Field']) ?>">
                        <?php if (strpos($col['Type'], 'text') !== false): ?>
                        <textarea name="values[]" rows="4"><?= h($value) ?></textarea>
                        <?php else: ?>
                        <input type="text" name="values[]" value="<?= h($value) ?>" style="width: 100%;">
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    
                    <button type="submit" name="<?= isset($_GET['edit']) ? 'update' : 'insert' ?>" class="btn btn-success">
                        <?= isset($_GET['edit']) ? 'Update' : 'Insert' ?> Record
                    </button>
                    <a href="?table=<?= urlencode($table) ?>" class="btn btn-secondary">Cancel</a>
                </form>
                
            <?php else: ?>
                <!-- BROWSE PAGE -->
                <div class="info-box">
                    <strong>Rows:</strong> <?= number_format($total) ?> | 
                    <strong>Engine:</strong> <?= h($table_status['Engine']) ?> | 
                    <strong>Collation:</strong> <?= h($table_status['Collation']) ?> | 
                    <strong>Size:</strong> <?= h($table_status['Data_length'] + $table_status['Index_length']) ?> bytes
                </div>
                
                <div class="actions">
                    <a href="?table=<?= urlencode($table) ?>&insert=1" class="btn btn-success">Insert Record</a>
                    <form method="post" style="display:inline;"><button type="submit" name="export" class="btn btn-primary">Export SQL</button></form>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Optimize table?')"><button type="submit" name="optimize" class="btn btn-warning">Optimize</button></form>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Repair table?')"><button type="submit" name="repair" class="btn btn-warning">Repair</button></form>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Check table?')"><button type="submit" name="check" class="btn btn-secondary">Check</button></form>
                    <form method="post" style="display:inline;" onsubmit="return confirm('TRUNCATE table? All data will be lost!')"><button type="submit" name="truncate" class="btn btn-danger">Truncate</button></form>
                    <form method="post" style="display:inline;" onsubmit="return confirm('DROP table? This cannot be undone!')"><button type="submit" name="drop_table" class="btn btn-danger">Drop Table</button></form>
                </div>
                
                <?php if ($data): ?>
                <form method="post" id="browse-form">
                    <input type="hidden" name="pk" value="<?= h($pk) ?>">
                    <div class="overflow">
                        <table>
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="check-all" onclick="toggleAll(this)"></th>
                                    <?php foreach ($columns as $col): ?>
                                    <th>
                                        <a href="?table=<?= urlencode($table) ?>&order=<?= urlencode($col['Field']) ?><?= isset($_GET['order']) && $_GET['order'] === $col['Field'] && !isset($_GET['desc']) ? '&desc=1' : '' ?>">
                                            <?= h($col['Field']) ?>
                                            <?php if (isset($_GET['order']) && $_GET['order'] === $col['Field']): ?>
                                                <?= isset($_GET['desc']) ? '↓' : '↑' ?>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <?php endforeach; ?>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $row): ?>
                                <tr>
                                    <td><input type="checkbox" name="check[]" value="<?= h($row[$pk]) ?>"></td>
                                    <?php foreach ($columns as $col): 
                                        $val = $row[$col['Field']];
                                        $is_number = preg_match('~int|decimal|float|double~', $col['Type']);
                                    ?>
                                    <td class="<?= $is_number ? 'number' : '' ?>">
                                        <?php if ($val === null): ?>
                                            <span class="null">NULL</span>
                                        <?php elseif (strpos($col['Type'], 'blob') !== false || strpos($col['Type'], 'binary') !== false): ?>
                                            <span class="blob">[BLOB - <?= strlen($val) ?> bytes]</span>
                                        <?php else: ?>
                                            <?= h(mb_substr($val, 0, 100)) ?><?= mb_strlen($val) > 100 ? '...' : '' ?>
                                        <?php endif; ?>
                                    </td>
                                    <?php endforeach; ?>
                                    <td>
                                        <a href="?table=<?= urlencode($table) ?>&edit=1&id=<?= urlencode($row[$pk]) ?>" class="btn btn-primary btn-small">Edit</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 15px;">
                        <button type="submit" name="delete" class="btn btn-danger" onclick="return confirm('Delete selected records?')">Delete Selected</button>
                    </div>
                </form>
                
                <?php 
                $total_pages = ceil($total / $limit);
                if ($total_pages > 1): 
                ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="?table=<?= urlencode($table) ?>&page=<?= $page - 1 ?>">« Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="?table=<?= urlencode($table) ?>&page=<?= $i ?>" class="<?= $i === $page ? 'current' : '' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <a href="?table=<?= urlencode($table) ?>&page=<?= $page + 1 ?>">Next »</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <p>No data in table.</p>
                <?php endif; ?>
                
                <script>
                function toggleAll(cb) {
                    document.querySelectorAll('input[name="check[]"]').forEach(c => c.checked = cb.checked);
                }
                </script>
            <?php endif; ?>
            
        <?php
        // VIEW
        elseif ($view):
            $view_def = $pdo->query("SHOW CREATE VIEW `$view`")->fetch();
        ?>
            <h2>View: <?= h($view) ?></h2>
            <div class="actions">
                <form method="post" style="display:inline;" onsubmit="return confirm('Drop this view?')">
                    <button type="submit" name="drop_view" class="btn btn-danger">Drop View</button>
                </form>
            </div>
            <div class="trigger-body"><?= h($view_def['Create View']) ?></div>
            
        <?php
        // DATABASE OVERVIEW
        else:
        ?>
            <h2>Database Overview</h2>
            
            <div class="stats">
                <div class="stat">
                    <div class="stat-value"><?= count($table_list) ?></div>
                    <div class="stat-label">Tables</div>
                </div>
                <div class="stat">
                    <div class="stat-value"><?= count($view_list) ?></div>
                    <div class="stat-label">Views</div>
                </div>
                <div class="stat">
                    <div class="stat-value"><?= count($all_triggers) ?></div>
                    <div class="stat-label">Triggers</div>
                </div>
                <div class="stat">
                    <div class="stat-value"><?= count($all_procedures) + count($all_functions) ?></div>
                    <div class="stat-label">Routines</div>
                </div>
            </div>
            
            <h3>Tables</h3>
            <table>
                <thead>
                    <tr>
                        <th>Table</th>
                        <th class="number">Rows</th>
                        <th>Engine</th>
                        <th>Collation</th>
                        <th class="number">Size</th>
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($table_list as $t): 
                        $status = $pdo->query("SHOW TABLE STATUS LIKE " . q($t))->fetch();
                        $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
                    ?>
                    <tr>
                        <td><strong><a href="?table=<?= urlencode($t) ?>" style="color: #2a5298; text-decoration: none;"><?= h($t) ?></a></strong></td>
                        <td class="number"><?= number_format($count) ?></td>
                        <td><?= h($status['Engine']) ?></td>
                        <td><?= h($status['Collation']) ?></td>
                        <td class="number"><?= number_format($status['Data_length'] + $status['Index_length']) ?></td>
                        <td><?= h($status['Comment']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
<?php ob_end_flush(); ?>
