<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no" />
        <title>Packagist Mirror</title>

        <link rel="shortcut icon" href="./favicon.ico" />

        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,300italic,700,700italic" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/mini.css/2.3.7/mini-default.min.css" />
        <link rel="author" href="https://github.com/Webysther/packagist-mirror"/>
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

        <?php if (!empty($googleAnalyticsMainId) || !empty($googleAnalyticsId)) {?>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?=$googleAnalyticsMainId ?: $googleAnalyticsId?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }

            gtag('js', new Date());

            <?php if (!empty($googleAnalyticsMainId)) {?>
            gtag('config', '<?=$googleAnalyticsMainId?>');
            <?php }?>

            <?php if (!empty($googleAnalyticsId)) {?>
            gtag('config', '<?=$googleAnalyticsId?>');
            <?php }?>
        </script>
        <?php }?>

    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-10 col-lg-offset-1">
                    <div class="title">
                        <h1>
                            Packagist Mirror
                            <img src="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.4.3/flags/4x3/<?= $countryCode; ?>.svg"
                                    title="<?= $countryName; ?>"
                                    alt="<?= $countryName; ?>"
                                    class="img-valign"
                                    />
                        </h1>
                        <p>
                            <?= $tz; ?>
                            <br>
                            <span id="lastsynced" ></span>
                            <br>
                            <?php if ($synced > 0) {?>(Synchronized every <?= $synced ?> seconds)<?php }?>
                            <?php if ($synced == 0) {?>(Synchronized continuously)<?php }?>
                        </p>
                    </div>
                    <p>
                        This is PHP package repository Packagist.org mirror site.
                    </p>
                    <p>
                        If you're using PHP Composer, commands like <mark class="default">create-project</mark>, <mark class="default">require</mark>, <mark class="default">update</mark>, <mark class="default">remove</mark> are often used.
                        When those commands are executed, Composer will download information from the packages that are needed also from dependent packages. The number of json files downloaded depends on the complexity of the packages which are going to be used.
                        The further you are from the location of the packagist.org server, the more time is needed to download json files. By using mirror, it will help save the time for downloading because the server location is closer.
                    </p>
                    <p>
                        Please do the following command to change the PHP Composer config to use this site as default Composer repository.
                    </p>
                    <div class="tabs stacked">
                        <input type="radio" name="accordion" id="enable" checked aria-hidden="true">
                        <label for="enable" aria-hidden="true">Enable</label>
                        <div>
                            <p class="bash" >
                                $ <span id="enablingStep"></span>
                                <button class="small tertiary ctclipboard" data-clipboard-target="#enablingStep"><img class="clippy" width="13" src="https://cdnjs.cloudflare.com/ajax/libs/octicons/8.5.0/svg/clippy.svg" alt="Copy to clipboard"> Copy</button>
                            </p>
                        </div>
                        <input type="radio" name="accordion" id="disable"aria-hidden="true">
                        <label for="disable" aria-hidden="true">Disable</label>
                        <div>
                            <p class="bash" >
                                $ <span id="disablingStep"></span>
                                <button class="small tertiary ctclipboard" data-clipboard-target="#disablingStep"><img class="clippy" width="13" src="https://cdnjs.cloudflare.com/ajax/libs/octicons/8.5.0/svg/clippy.svg" alt="Copy to clipboard"> Copy</button>
                            </p>
                        </div>
                    </div>

                    <h2>World Map of all mirrors</h2>
                        <p>
                            All mirrors, the colors represent <a href="network.svg" target="_blank">the topology drawn here</a>.
                        </p>
                        <a href="world_map.svg" target="_blank">
                            <center>
                                <img 
                                src="world_map.svg" 
                                alt="World Map with all mirrors"
                                width="80%" />
                            </center>
                        </a>

                    <h2>Disclaimer</h2>
                    <p>This site offers its services free of charge and only as a mirror site.</p>
                    <p>This site only provides package information / metadata with no distribution file of the packages. All packages metadata files are mirrored from <a href="https://packagist.org/mirrors" target="_blank">packagist.org</a>. We do not modify and/or process the JSON files. If there is something wrong, please disable the setting the Disable command above and try to refer to the original packagist.org.</p>
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.4/clipboard.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment-with-locales.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.26/moment-timezone-with-data-2012-2022.min.js"></script>
        <script>
            // set text of the command
            document.getElementById('enablingStep').innerText = 'composer config -g repos.packagist composer '+ window.location.origin;
            document.getElementById('disablingStep').innerText = 'composer config -g --unset repos.packagist';

            new ClipboardJS('.ctclipboard');

            function fetchHeader(url, wch) {
                try {
                    var req=new XMLHttpRequest();
                    req.open("HEAD", url, true);
                    req.onload = function (e) {
                        var responseHeader = req.getResponseHeader(wch);
                        var actual = moment.tz(responseHeader, '<?=$tz; ?>');
                        var format = 'YYYY-MM-DD HH:mm:ss ZZ';
                        var lastsynced = document.getElementById('lastsynced');
                        lastsynced.innerText = 'Last sync: '+actual.format(format);
                    };
                    req.send(null);
                } catch(er) {}
            }

            if(location.hostname !== ''){
                fetchHeader(location.href,'Last-Modified');
                setInterval(function(){
                    fetchHeader(location.href,'Last-Modified');
                }, (<?=$synced ?>000+1000));
            }
        </script>
    </body>
</html>
