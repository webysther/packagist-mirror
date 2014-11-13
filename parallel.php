<?php
namespace Spindle\HttpClient;

require_once __DIR__ . '/vendor/autoload.php';

if (file_exists(__DIR__ . '/config.php')) {
    $config = require __DIR__ . '/config.php';
} else {
    $config = require __DIR__ . '/config.default.php';
}

if (file_exists($config->olds)) {
    throw new \RuntimeException("$config->olds exists");
}

$globals = new \stdClass;
$globals->q = new \SplQueue;
for ($i=0; $i<$config->maxConnections; ++$i) {
    $req = new Request;
    $req->setOption('encoding', 'gzip');
    $globals->q->enqueue($req);
}

$globals->mh = new Multi;

do {
    $globals->retry = false;
    $providers = downloadProviders($config, $globals);
    downloadPackages($config, $globals, $providers);
} while ($globals->retry);

flushFiles($config);
exit;

/**
 * packages.json & provider-latest$xxx.json downloader
 *
 */
function downloadProviders($config, $globals)
{
    $cachedir = $config->cachedir;

    $packagesCache = $cachedir . 'packages.json';

    $req = new Request($config->packagistUrl . '/packages.json');
    $req->setOptions(array(
        'encoding' => 'gzip',
        'timeCondition' => \CURL_TIMECOND_IFMODSINCE,
    ));
    if (file_exists($packagesCache)) {
        $req->setOption('timeValue', filemtime($packagesCache));
    }

    $res = $req->send();

    if (200 === $res->getStatusCode()) {
        $packages = json_decode($res->getBody());
        foreach (explode(' ', 'notify notify-batch search') as $k) {
            if (0 === strpos($packages->$k, '/')) {
                $packages->$k = 'https://packagist.org' . $packages->$k;
            }
        }
        file_put_contents($packagesCache . '.new', json_encode($packages));
    } else {
        //throw new \RuntimeException('no changes', $res->getStatusCode());
        $packages = json_decode(file_get_contents($packagesCache));
    }

    if (empty($packages->{'provider-includes'})) {
        throw new \RuntimeException('packages.json schema change?');
    }

    $providers = array();
    $olds = new \SplFileObject($config->olds, 'a');

    foreach ($packages->{'provider-includes'} as $tpl => $version) {
        $fileurl = str_replace('%hash%', $version->sha256, $tpl);
        $cachename = $cachedir . $fileurl;
        $providers[] = $cachename;

        if (file_exists($cachename)) continue;

        $req->setOption('url', $config->packagistUrl . '/' . $fileurl);
        $res = $req->send();

        echo $res->getStatusCode(), "\t", $res->getUrl(), PHP_EOL;
        if (200 === $res->getStatusCode()) {
            $oldcache = $cachedir . str_replace('%hash%', '*', $tpl);
            foreach (glob($oldcache)  as $old) {
                //unlink($old);
                $olds->fwrite($old . \PHP_EOL);
            }
            if (!file_exists(dirname($cachename))) {
                mkdir(dirname($cachename), 0777, true);
            }
            file_put_contents($cachename, $res->getBody());
        } else {
            $globals->retry = true;
        }
    }

    return $providers;
}

/**
 * composer.json downloader
 *
 */
function downloadPackages($config, $globals, $providers)
{
    $cachedir = $config->cachedir;
    $i = 0;
    $urls = array();

    $olds = new \SplFileObject($config->olds, 'a');

    foreach ($providers as $providerjson) {
        $list = json_decode(file_get_contents($providerjson));
        if (!$list || empty($list->providers)) continue;

        $list = $list->providers;

        foreach ($list as $packageName => $provider) {
            $url = "$config->packagistUrl/p/$packageName\$$provider->sha256.json";
            $cachefile = $cachedir . str_replace("$config->packagistUrl/", '', $url);
            if (file_exists($cachefile)) continue;

            $req = $globals->q->dequeue();
            $req->packageName = $packageName;
            $req->setOption('url', $url);
            $globals->mh->attach($req);
            $globals->mh->start(); //non block

            if (count($globals->q)) continue;

            /** @type Request[] $requests */
            do {
                $requests = $globals->mh->getFinishedResponses(); //block
            } while (0 === count($requests));

            foreach ($requests as $req) {
                $res = $req->getResponse();
                $globals->q->enqueue($req);

                echo $res->getStatusCode(), "\t", $res->getUrl(), PHP_EOL;
                if (200 === $res->getStatusCode()) {
                    $cachefile = $cachedir
                        . str_replace("$config->packagistUrl/", '', $res->getUrl());
                    foreach (glob("{$cachedir}p/$req->packageName\$*")  as $old) {
                        //unlink($old);
                        $olds->fwrite($old . \PHP_EOL);
                    }
                    if (!file_exists(dirname($cachefile))) {
                        mkdir(dirname($cachefile), 0777, true);
                    }
                    file_put_contents($cachefile, $res->getBody());
                } else {
                    $globals->retry = true;
                }
            }
        }
    }


    if (0 === count($globals->mh)) return;
    //残りの端数をダウンロード
    $globals->mh->waitResponse();
    foreach ($globals->mh as $req) {
        $res = $req->getResponse();

        echo $res->getStatusCode(), "\t", $res->getUrl(), PHP_EOL;

        if (200 === $res->getStatusCode()) {
            $cachefile = $cachedir
                . str_replace("$config->packagistUrl/", '', $res->getUrl());
            foreach (glob("{$cachedir}p/$req->packageName\$*")  as $old) {
                //unlink($old);
                $olds->fwrite($old . \PHP_EOL);
            }
            if (!file_exists(dirname($cachefile))) {
                mkdir(dirname($cachefile), 0777, true);
            }
            file_put_contents($cachefile, $res->getBody());
        } else {
            $globals->retry = true;
        }
    }

}

function flushFiles($config)
{
    rename(
        $config->cachedir . 'packages.json.new',
        $config->cachedir . 'packages.json'
    );

    echo 'finished! flushing...', PHP_EOL;

//    sleep(10); //何秒あれば十分なのか？

    $olds = new \SplFileObject($config->olds, 'r');

    foreach ($olds as $oldfile) {
        $oldfile = rtrim($oldfile);
        if (file_exists($oldfile)) unlink($oldfile);
    }

    unset($olds);
    unlink($config->olds);
}
