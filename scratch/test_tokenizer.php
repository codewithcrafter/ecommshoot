<?php
$sql = file_get_contents(__DIR__ . '/../database/production_safe_migration.sql');

function splitSqlStatements(string $sql): array {
    $queries = [];
    $current = '';
    $inString = false;
    $stringChar = '';
    $len = strlen($sql);
    
    for ($i = 0; $i < $len; $i++) {
        $char = $sql[$i];
        
        // Skip comments outside strings
        if (!$inString) {
            if ($char === '-' && ($sql[$i + 1] ?? '') === '-') {
                // Skip to end of line
                while ($i < $len && $sql[$i] !== "\n") {
                    $i++;
                }
                continue;
            }
            if ($char === '/' && ($sql[$i + 1] ?? '') === '*') {
                // Skip multi-line comment
                $i += 2;
                while ($i < $len && !($sql[$i] === '*' && ($sql[$i + 1] ?? '') === '/')) {
                    $i++;
                }
                $i++;
                continue;
            }
        }
        
        if ($inString) {
            if ($char === '\\') {
                $current .= $char . ($sql[++$i] ?? '');
                continue;
            }
            if ($char === $stringChar) {
                if (($sql[$i + 1] ?? '') === $stringChar) {
                    $current .= $char . $sql[++$i];
                    continue;
                }
                $inString = false;
            }
            $current .= $char;
        } else {
            if ($char === "'" || $char === '"') {
                $inString = true;
                $stringChar = $char;
                $current .= $char;
            } elseif ($char === ';') {
                $trimmed = trim($current);
                if (!empty($trimmed)) {
                    $queries[] = $trimmed;
                }
                $current = '';
            } else {
                $current .= $char;
            }
        }
    }
    $trimmed = trim($current);
    if (!empty($trimmed)) {
        $queries[] = $trimmed;
    }
    return $queries;
}

$stmts = splitSqlStatements($sql);
echo "Total parsed statements: " . count($stmts) . "\n";
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance()->getConnection();
$errors = [];
$db->exec("SET FOREIGN_KEY_CHECKS = 0");
foreach ($stmts as $idx => $stmt) {
    try {
        $db->exec($stmt);
    } catch (PDOException $e) {
        $errors[] = "Statement #$idx failed: " . substr($stmt, 0, 60) . "... Error: " . $e->getMessage();
    }
}
$db->exec("SET FOREIGN_KEY_CHECKS = 1");

if (empty($errors)) {
    echo "ALL " . count($stmts) . " STATEMENTS EXECUTED WITH ZERO ERRORS!\n";
} else {
    echo "Errors:\n" . implode("\n", $errors) . "\n";
}

