<?php
/**
 * If you want to change something, copy this file, make config.php and edit with it.
 */

return (object)array(
    'cachedir' => __DIR__ . '/cache/',
    //'cachedir' => '/usr/share/nginx/html/', # nginx
    //'cachedir' => '/usr/local/apache2/htdocs/', # apache
    'packagistUrl' => 'https://packagist.org',
    'lockfile' => __DIR__ . '/cache/.lock',
    'expiredDb' => __DIR__ . '/cache/.expired.db',
    'generateGz' => false,
    'expireMinutes' => 24 * 60,

    // URL for you mirror. example: packagist.jp or packagist.com.br
    'url' => 'http://localhost',

    // This is the directory that stores downloaded packages.json.
    'cachedir' => __DIR__ . '/cache/',
    //'cachedir' => '/usr/share/nginx/html/', # nginx
    //'cachedir' => '/usr/local/apache2/htdocs/', # apache
    
    // This is the URL of the download packagist.org. You can specify another mirror site that already exists.
    'packagistUrl' => 'https://packagist.org',
    
    // Parallel number of parallel downloads. Since it places a load on the origin, please make it a suitable place.
    'maxConnections' => 15,
    
    // Lock execution, if this file exists parallel.php dont execute
    'lockfile' => __DIR__ . '/cache/.lock',
    
    // The old json is recorded by the file update.
    'expiredDb' => __DIR__ . '/cache/.expired.db'
);
