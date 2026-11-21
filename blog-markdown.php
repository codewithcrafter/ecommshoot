<?php
/**
 * ECOMSHOOT - Public Raw Markdown Article Endpoint (Backward-Compatible Alias)
 * Delegates to the universal markdown-serve.php engine.
 */

if (!isset($_GET['path'])) {
    if (!empty($_GET['slug'])) {
        $_GET['path'] = 'blog/' . $_GET['slug'];
    } elseif (!empty($_GET['id'])) {
        $_GET['path'] = 'blog/' . (int)$_GET['id'];
    }
}

require __DIR__ . '/markdown-serve.php';
