<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no" />
    <title>Composer / Packagist 中国镜像</title>
    <meta name=description content="Composer / Packagist 中国镜像同步源来自 packagist.org, 加速 Composer 安装、更新软件包，让国内开发者使用更加方便、快捷。">
    <meta name=keywords content=Composer,Packagist,Composer镜像,镜像,composer.json,Laravel,PHP包管理,PHP>

    <link rel="shortcut icon" href="/favicon.ico" />

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,300italic,700,700italic" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/mini.css/2.3.7/mini-default.min.css" />
    <style>
        body { font-family: 'Roboto', 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif; }
        .title { text-align: center}

        @media screen and (min-width: 768px) {
            h1 { font-size: 300% }
            h1 > img { width: 10%; }
        }
        @media screen and (max-width: 768px) {
            h1 { font-size: 200% }
            h1 > img { width: 61px; }
        }
        .bash {
            overflow: auto;
            border-radius: 0 .125rem .125rem 0;
            background: #e6e6e6;
            padding: .75rem;
            margin: .5rem;
            border-left: .25rem solid #1565c0;
            font-family: monospace, monospace;
        }
        .bash > span { font-family: monospace, monospace; }
        mark.default { background: rgba(220,220,220,0.75); color: #212121; }
        .img-valign {
            vertical-align: middle;
            width:50px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-10 col-lg-offset-1">
            <div class="title">
                <h1>
                    Composer / Packagist 中国镜像
                    <img src="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.1.0/flags/4x3/<?= $countryCode; ?>.svg"
                         title="<?= $countryName; ?>"
                         alt="<?= $countryName; ?>"
                         class="img-valign"
                    />
                </h1>
                <p>
                    <mark id="lastsynced" class="tertiary"></mark><br>
                    (每 <?= $synced ?> 秒同步一次)
                </p>
            </div>
            <p>
                PHP 包仓库 Packagist.org 中国区镜像站点.
            </p>
            <p>
                如果你经常使用 <b>Composer</b> 命令，如：<mark class="default">create-project</mark>，<mark class="default">require</mark>，<mark class="default">update</mark>，<mark class="default">remove</mark>。
                当这些命令执行时，Composer 会通过依赖下载对应包信息。 下载 JSON 文件数量取决于您使用的软件包复杂程度。<br>
                中国互联网大环境由于众所周知的原因，连接 packagist.org 速度很慢，甚至无法连接。通过使用镜像可以加快下载速度，节省没必要的等待时间。<br>
            </p>
            <p>
                请执行以下命令将 Composer 默认仓库设置为本站
            </p>
            <div class="tabs stacked">

                <input type="radio" name="accordion" id="enable" checked aria-hidden="true">
                <label for="enable" aria-hidden="true">Composer 全局配置</label>
                <div>
                    <p class="bash" >
                        $ <span id="enablingStep"></span>
                        <button class="small tertiary ctclipboard" data-clipboard-target="#enablingStep"><img class="clippy" width="13" src="https://cdnjs.cloudflare.com/ajax/libs/octicons/4.4.0/svg/clippy.svg" alt="Copy to clipboard"> 复制</button>
                    </p>
                </div>

                <input type="radio" name="accordion" id="jsonStep" aria-hidden="true">
                <label for="jsonStep" aria-hidden="true">项目 composer.json 配置</label>
                <div>
                    <p class="bash" >
                        $ <span id="enablingJsonStep"></span>
                        <button class="small tertiary ctclipboard" data-clipboard-target="#enablingJsonStep"><img class="clippy" width="13" src="https://cdnjs.cloudflare.com/ajax/libs/octicons/4.4.0/svg/clippy.svg" alt="Copy to clipboard"> 复制</button>
                    </p>
                </div>

                <input type="radio" name="accordion" id="disable" aria-hidden="true">
                <label for="disable" aria-hidden="true">关闭全局配置</label>
                <div>
                    <p class="bash" >
                        $ <span id="disablingStep"></span>
                        <button class="small tertiary ctclipboard" data-clipboard-target="#disablingStep"><img class="clippy" width="13" src="https://cdnjs.cloudflare.com/ajax/libs/octicons/4.4.0/svg/clippy.svg" alt="Copy to clipboard"> 复制</button>
                    </p>
                </div>

            </div>
            <h2>声明</h2>
            <p>该网站仅作为免费镜像站点提供服务。</p>
            <p>该站点仅提供包信息、元数据。 所有包、元数据均镜像自 <a href="https://packagist.org" target="_blank">Packagist.org</a>。我们<mark class="secondary">绝不会</mark>更新、处理 JSON 文件。 如果发生错误或者你感觉不爽，随时使用以上命令禁用，尝试从源站 packagist.org 读取。</p>
            <h2>镜像架构</h2>
            <p>
                <img src="/architecture.png" alt="架构图">
            </p>
            <h2>赞助</h2>
            <p>
                <div class="col-sm-2 col-md-2 col-lg-2">
                    <div class="sponsor">
                        <a href="https://www.anchnet.com/?from=https://php.cnpkg.org" target="_blank">
                            <img src="https://www.anchnet.com/assets/libs/v1/image/logo/anchnet/logo-full-gray.svg" alt="安畅网络" width="120px">
                        </a>
                    </div>
                </div>
            </p>
        </div>
    </div>
</div>
<footer class="row">
    <div class="col-sm-12 col-md-12 col-lg-10 col-lg-offset-1">
        <p>
            <b>Packagist Mirror</b> was built from <?= $countryName ?> by
            <a href="<?= $maintainerProfile ?>" target="_blank"><?= $maintainerMirror ?></a>.
        </p>
        <p>
            It is licensed under the <a href="<?= $maintainerRepo ?>/blob/master/LICENSE" target="_blank"><?= $maintainerLicense ?></a>.
            You can view the project's source code on <a href="<?= $maintainerRepo ?>" target="_blank">GitHub</a>.
        </p>
    </div>
</footer>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.0/clipboard.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment-with-locales.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.17/moment-timezone-with-data-2012-2022.min.js"></script>
<script>
    // set text of the command
    document.getElementById('enablingStep').innerText = 'composer config -g repos.packagist composer '+ window.location.origin;
    document.getElementById('enablingJsonStep').innerText = 'composer config repo.packagist composer '+ window.location.origin;
    document.getElementById('disablingStep').innerText = 'composer config -g --unset repos.packagist';

    new ClipboardJS('.ctclipboard');

    function fetchHeader(url, wch) {
        try {
            var req=new XMLHttpRequest();
            req.open("HEAD", url, true);
            req.onload = function (e) {
                var responseHeader = req.getResponseHeader(wch);
                var actual = moment.tz(responseHeader, '<?=$tz; ?>');
                var format = 'YYYY/MM/D HH:mm:ss ZZ';
                var lastsynced = document.getElementById('lastsynced');
                lastsynced.innerText = '最后同步时间: '+actual.format(format);
            };
            req.send(null);
        } catch(er) {}
    }

    fetchHeader(location.href,'Last-Modified');
    setInterval(function(){
        fetchHeader(location.href,'Last-Modified');
    }, (<?=$synced ?>000));
</script>
<script>
    var _hmt = _hmt || [];
    (function() {
        var hm = document.createElement("script");
        hm.src = "https://hm.baidu.com/hm.js?4c3eaae1842c4d1a4723c636cd3420d9";
        var s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(hm, s);
    })();
</script>

</body>
</html>