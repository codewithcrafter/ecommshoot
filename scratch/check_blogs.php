<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance()->getConnection();
$cols = $db->query('DESCRIBE blogs')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo $c['Field'] . ' (' . $c['Type'] . ")\n";
}

