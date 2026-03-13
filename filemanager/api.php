<?php
/**
 * DC Filemanager – API (list + upload).
 */
header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/config.php';
$root   = rtrim(str_replace('\\', '/', $config['root_dir']), '/');
$max    = (int) $config['max_size'];
$allowed = $config['allowed'];
$mimes  = $config['mimes'];

if (!is_dir($root)) {
    @mkdir($root, 0755, true);
}

function safePath($base, $sub) {
    $sub = trim($sub, '/');
    $sub = str_replace(['../', '..\\'], '', $sub);
    if ($sub !== '' && preg_match('#\.\.#', $sub)) {
        return null;
    }
    $path = $sub === '' ? $base : $base . '/' . $sub;
    $realBase = realpath($base);
    $realPath = realpath($path);
    if ($realPath === false) {
        $parent = $sub === '' ? $base : $base . '/' . implode('/', array_slice(explode('/', $sub), 0, -1));
        return realpath($parent) !== false ? rtrim($path, '/') : null;
    }
    return strpos($realPath, $realBase) === 0 ? $realPath : null;
}

function jsonOut($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
}

$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';

if ($action === 'list') {
    $sub   = isset($_REQUEST['path']) ? trim($_REQUEST['path']) : '';
    $path  = safePath($root, $sub);
    if ($path === null || !is_dir($path)) {
        jsonOut(['ok' => false, 'error' => 'Invalid path']);
        exit;
    }
    $folders = [];
    $images  = [];
    $exts    = array_map('strtolower', $config['allowed']);
    foreach (scandir($path) as $name) {
        if ($name === '.' || $name === '..') continue;
        $full = $path . '/' . $name;
        if (is_dir($full)) {
            $folders[] = ['name' => $name, 'path' => $sub === '' ? $name : $sub . '/' . $name];
        } elseif (is_file($full)) {
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($ext, $exts, true)) {
                $rel = ltrim(str_replace('\\', '/', substr($full, strlen($root))), '/');
                $images[] = [
                    'name' => $name,
                    'path' => $sub === '' ? $name : $sub . '/' . $name,
                    'rel'  => $rel,
                ];
            }
        }
    }
    jsonOut(['ok' => true, 'folders' => $folders, 'images' => $images]);
    exit;
}

if ($action === 'upload') {
    $sub = isset($_POST['path']) ? trim($_POST['path']) : '';
    $path = safePath($root, $sub);
    if ($path === null) {
        jsonOut(['ok' => false, 'error' => 'Invalid path']);
        exit;
    }
    if (!is_dir($path) && !@mkdir($path, 0755, true)) {
        jsonOut(['ok' => false, 'error' => 'Cannot create directory']);
        exit;
    }
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $err = isset($_FILES['file']['error']) ? $_FILES['file']['error'] : 'no_file';
        jsonOut(['ok' => false, 'error' => 'Upload error: ' . $err]);
        exit;
    }
    $file = $_FILES['file'];
    if ($file['size'] > $max) {
        jsonOut(['ok' => false, 'error' => 'Max size 3 MB']);
        exit;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        jsonOut(['ok' => false, 'error' => 'Extension not allowed']);
        exit;
    }
    if (isset($mimes[$ext])) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $mimes[$ext], true)) {
            jsonOut(['ok' => false, 'error' => 'Invalid file type']);
            exit;
        }
    }
    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
    if ($safeName === '') $safeName = 'file.' . $ext;
    $dest = $path . '/' . $safeName;
    if (file_exists($dest)) {
        $safeName = pathinfo($safeName, PATHINFO_FILENAME) . '_' . time() . '.' . $ext;
        $dest = $path . '/' . $safeName;
    }
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        jsonOut(['ok' => false, 'error' => 'Move failed']);
        exit;
    }
    $rel = ltrim(str_replace('\\', '/', substr($dest, strlen($root))), '/');
    jsonOut(['ok' => true, 'name' => $safeName, 'path' => $sub === '' ? $safeName : $sub . '/' . $safeName, 'rel' => $rel]);
    exit;
}

jsonOut(['ok' => false, 'error' => 'Unknown action']);
