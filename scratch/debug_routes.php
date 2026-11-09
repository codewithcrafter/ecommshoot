<?php
$res = file_get_contents('http://127.0.0.1:8000/blog/1');
echo "Length of /blog/1: " . strlen($res) . "\n";
echo "First 200 chars:\n" . substr($res, 0, 200) . "\n\n";

$resMd = file_get_contents('http://127.0.0.1:8000/blog/1.md');
echo "Length of /blog/1.md: " . strlen($resMd) . "\n";
echo "First 200 chars:\n" . substr($resMd, 0, 200) . "\n";

