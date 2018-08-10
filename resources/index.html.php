<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no" />
        <title>Packagist Mirror中国镜像</title>

        <link rel="shortcut icon" href="/favicon.ico" />

        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,300italic,700,700italic" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/mini.css/2.3.7/mini-default.min.css" />
        <style>
            body { font-family: 'Roboto', 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif; }
            .title { text-align: center}

            @media screen and (min-width: 768px) {
                h1 { font-size: 500% }
                h1 > img { width: 10%; }
            }
            @media screen and (max-width: 768px) {
                h1 { font-size: 300% }
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
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-10 col-lg-offset-1">
                    <div class="title">
                        <h1>
                            Packagist Mirror
                            <img src="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.1.0/flags/4x3/<?= $countryCode; ?>.svg"
                                    title="<?= $countryName; ?>"
                                    alt="<?= $countryName; ?>"
                                    class="img-valign"
                                    />
                        </h1>
                        <p><span id="lastsynced" ></span><br>(每 <?= $synced ?> 秒钟同步一次)</p>
                    </div>
                    <p>
                        这是PHP包仓库Packagist.org镜像站点。
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
                        <label for="enable" aria-hidden="true">全局配置Composer </label>
                        <div>
                            <p class="bash" >
                                $ <span id="enablingStep"></span>
                                <button class="small tertiary ctclipboard" data-clipboard-target="#enablingStep"><img class="clippy" width="13" src="https://cdnjs.cloudflare.com/ajax/libs/octicons/4.4.0/svg/clippy.svg" alt="Copy to clipboard"> Copy</button>
                            </p>
                        </div>

                        <input type="radio" name="accordion" id="OneStep" aria-hidden="true">
                        <label for="OneStep" aria-hidden="true">单个项目配置Composer </label>
                        <div>
                            <p class="bash" >
                                $ <span id="enablingOneStep"></span>
                                <button class="small tertiary ctclipboard" data-clipboard-target="#enablingOneStep"><img class="clippy" width="13" src="https://cdnjs.cloudflare.com/ajax/libs/octicons/4.4.0/svg/clippy.svg" alt="Copy to clipboard"> Copy</button>
                            </p>
                        </div>

                        <input type="radio" name="accordion" id="disable"aria-hidden="true">
                        <label for="disable" aria-hidden="true">关闭全局配置</label>
                        <div>
                            <p class="bash" >
                                $ <span id="disablingStep"></span>
                                <button class="small tertiary ctclipboard" data-clipboard-target="#disablingStep"><img class="clippy" width="13" src="https://cdnjs.cloudflare.com/ajax/libs/octicons/4.4.0/svg/clippy.svg" alt="Copy to clipboard"> Copy</button>
                            </p>
                        </div>
                    </div>

                    <h2>声明</h2>
                    <p>该网站仅作为免费镜像站点提供服务。</p>
                    <p>该站点仅提供包信息、元数据。 所有包、元数据均镜像自 Packagist.org。我们绝不会更新、处理 JSON 文件。 如果发生错误或者你感觉不爽，随时使用以上命令禁用，尝试从源站 packagist.org 读取。</p>
                </div>
            </div>
        </div>
        <footer class="row">
            <div class="col-sm-12 col-md-12 col-lg-10 col-lg-offset-1">
                <p>
                    <a href="<?= $maintainerProfile ?>" target="_blank"><?= $maintainerMirror ?></a>在<?= $countryName ?>搭建了<b>Packagist Mirror</b>
                </p>
                <p>
                    该项目由 <a href="<?= $maintainerRepo ?>/blob/master/LICENSE" target="_blank"><?= $maintainerLicense ?></a>协议授权。
                    你可以访问 <a href="<?= $maintainerRepo ?>" target="_blank">GitHub</a>，查看源代码.
                </p>
            </div>
        </footer>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.0/clipboard.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment-with-locales.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.17/moment-timezone-with-data-2012-2022.min.js"></script>
        <script>
            // set text of the command
            document.getElementById('enablingStep').innerText = 'composer config -g repos.packagist composer '+ window.location.origin;
            document.getElementById('enablingOneStep').innerText = 'composer config repos.packagist composer '+ window.location.origin;
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
                        lastsynced.innerText = 'Last sync: '+actual.format(format);
                    };
                    req.send(null);
                } catch(er) {}
            }

            fetchHeader(location.href,'Last-Modified');
            setInterval(function(){
                fetchHeader(location.href,'Last-Modified');
            }, (<?=$synced ?>000));
        </script>
    </body>
</html>
