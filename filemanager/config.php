<?php
/**
 * DC Filemanager – konfiguracja (w module dc_pfeaturedbox).
 * Katalog obrazów: sklep /img/dc_filemenager (gdy w PrestaShop), inaczej w module.
 */
$rootDir = (defined('_PS_ROOT_DIR_') ? _PS_ROOT_DIR_ . '/img/dc_filemenager' : __DIR__ . '/img/dc_filemanager');
return [
    'root_dir'  => $rootDir,
    'max_size'  => 3 * 1024 * 1024, // 3 MB
    'allowed'   => ['jpg', 'jpeg', 'webp', 'png', 'svg'],
    'mimes'     => [
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'webp' => ['image/webp'],
        'png'  => ['image/png'],
        'svg'  => ['image/svg+xml', 'image/svg'],
    ],
];
