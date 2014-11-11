<?php
/**
 * 何か変更したい場合は、このファイルをコピーしてconfig.phpを作り、そちらで編集すること。
 */

return (object)array(
    'cachedir' => __DIR__ . '/cache/',
    //'cachedir' => '/usr/share/nginx/html/',
    //'cachedir' => '/usr/local/apache2/htdocs/',
    'packagistUrl' => 'https://packagist.org',
    'olds' => __DIR__ . '/cache/.olds',
    'maxConnections' => 2,
);
