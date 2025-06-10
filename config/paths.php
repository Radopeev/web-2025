<?php

$root = dirname(__DIR__) . DIRECTORY_SEPARATOR;

return [
    'root' => $root, // Project root directory

    // Filesystem (absolute or relative to project root)
    'upload_configs_dir' => $root . 'public/uploads/configs/',
    'upload_sources_dir' => $root . 'public/uploads/sources/',
    'upload_profile_pictures_dir' => $root . 'public/uploads/profile_pictures/',

    // HTTP (for use in links, downloads, img src, etc.)
    'url_configs' => '/public/uploads/configs/',
    'url_sources' => '/public/uploads/sources/',
    'url_profile_pictures' => '/public/uploads/profile_pictures/',
];