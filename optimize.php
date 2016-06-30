<?php

const BASEPATH = 'cache';
const OPTPATH = 'optimized';

$packagesjson = json_decode(file_get_contents(BASEPATH . '/packages.json'));

/*
if (file_exists('optimize.db')) {
    unlink('optimize.db');
}

$pdo = new PDO('sqlite3:optimize.db', null, null, [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
$pdo->beginTransaction();
$pdo->exec(
    'CREATE TABLE IF NOT EXISTS packages ('
    .'provider TEXT,'
    .'providerhash TEXT,'
    .')'
);
$pdo->exec(
    'CREATE TABLE IF NOT EXISTS provider ('
    .'provider TEXT,'
    .'package TEXT,'
    .'packagehash TEXT,'
    .')'
);
$pdo->exec(
    'CREATE INDEX IF NOT EXISTS idx_provider'
    .' ON provider (provider)'
);
*/

$muda = 0;
foreach ($packagesjson->{'provider-includes'} as $providerpath => $providerinfo) {
    $providerjson = json_decode(file_get_contents(BASEPATH . '/' . str_replace('%hash%', $providerinfo->sha256, $providerpath)));

    foreach ($providerjson->providers as $packagename => $packageinfo) {
        $packagejson = json_decode(file_get_contents(BASEPATH . "/p/$packagename\${$packageinfo->sha256}.json"));

        foreach ($packagejson->packages as $versionname => $info) {
            if ($versionname !== $packagename) {
                echo "むだな $versionname が $packagename の中に含まれています\n";
                $muda += strlen(json_encode($info));
            }
        }
    }
}

echo "全部で $muda byte 無駄です\n";
