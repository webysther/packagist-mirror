<?php
namespace Spindle\HttpClient;

require_once __DIR__ . '/vendor/autoload.php';

$config = [
    //'cachedir' => __DIR__ . '/cache/',
    //'cachedir' => '/usr/share/nginx/html/',
    'cachedir' => '/usr/local/apache2/htdocs/',
];

$providers = downloadProviders($config);
downloadPackages($config, $providers);

/**
 * packages.json & provider-latest$xxx.json downloader
 *
 */
function downloadProviders($config)
{
    $cachedir = $config['cachedir'];

    $packagesCache = $cachedir . 'packages.json';

    $req = new Request('https://packagist.org/packages.json');
    $req->setOptions(array(
        'encoding' => 'gzip,deflate',
        'timeCondition' => CURL_TIMECOND_IFMODSINCE,
    ));
    if (file_exists($packagesCache)) {
        $req->setOption('timeValue', filemtime($packagesCache));
    }

    $res = $req->send();

    if (200 === $res->getStatusCode()) {
        $packages = json_decode($res->getBody());
        foreach (explode(' ', 'notify notify-batch search') as $k) {
            $packages->$k = 'https://packagist.org' . $packages->$k;
        }
        file_put_contents($packagesCache, json_encode($packages));
    } else {
        //throw new \RuntimeException('no changes', $res->getStatusCode());
    }

    if (empty($packages->{'provider-includes'})) {
        throw new \RuntimeException('packages.json schema change?');
    }

    $providers = array();

    foreach ($packages->{'provider-includes'} as $tpl => $version) {
        $fileurl = str_replace('%hash%', $version->sha256, $tpl);
        $cachename = $cachedir . $fileurl;
        $providers[] = $cachename;

        if (file_exists($cachename)) continue;

        $req->setOption('url', 'https://packagist.org/' . $fileurl);
        $res = $req->send();

        echo $res->getStatusCode(), "\t", $fileurl, PHP_EOL;
        if (200 === $res->getStatusCode()) {
            $oldcache = $cachedir . str_replace('%hash%', '*', $tpl);
            foreach (glob($oldcache)  as $old) {
                unlink($old);
            }
            if (!file_exists(dirname($cachename))) {
                mkdir(dirname($cachename), 0777, true);
            }
            file_put_contents($cachename, $res->getBody());
        }
    }

    return $providers;
}

/**
 * composer.json downloader
 *
 */
function downloadPackages($config, $providers)
{
    $cachedir = $config['cachedir'];
    $i = 0;
    $urls = array();

    $req = new Request;
    $req->setOption('encoding', 'gzip,deflate');

    foreach ($providers as $providerjson) {
        $list = json_decode(file_get_contents($providerjson));
        if (!$list || empty($list->providers)) continue;

        $list = $list->providers;

        foreach ($list as $packageName => $provider) {
            $url = "https://packagist.org/p/$packageName\$$provider->sha256.json";
            $cachefile = $cachedir . str_replace('https://packagist.org/', '', $url);
            if (file_exists($cachefile)) continue;

            $req->setOption('url', $url);
            $res = $req->send();

            echo $res->getStatusCode(), "\t", $url, PHP_EOL;

            if (200 === $res->getStatusCode()) {
                $cachefile = $cachedir
                    . str_replace('https://packagist.org/', '', $res->getUrl());
                foreach (glob("$cachedir/p/$packageName*")  as $old) {
                    unlink($old);
                }
                if (!file_exists(dirname($cachefile))) {
                    mkdir(dirname($cachefile), 0777, true);
                }
                file_put_contents($cachefile, $res->getBody());
            }
        }
    }

}
