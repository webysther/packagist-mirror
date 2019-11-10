# Contributing

Contributions are **welcome** and will be fully **credited**.

We accept contributions via Pull Requests on [Github](https://github.com/Webysther/packagist-mirror).

Put inside your `~/.*rc` (`~/.bashrc`/`~/.zshrc`/`~/.config/fish/config.fish`):
```bash
alias drun='docker run --user=`id -u $USER`:`id -g` --workdir=/code --rm -v $PWD:/code -v $HOME:/home/YOUR-USERNAME'
# using inside .env PUBLIC_DIR=/public you can using on memory (~1GB)
alias dev='drun -it --net=host --tmpfs /public -v /etc/passwd:/etc/passwd:ro -v /etc/group:/etc/group:ro webysther/composer-debian bash'
# test index.html
# when running (php bin/mirror create --no-clean --no-progress) with .env PUBLIC_DIR=/home/YOUR-USERNAME/public inside alias dev
alias web='drun -v $PWD/nginx.conf:/etc/nginx/nginx.conf:ro -v /home/YOUR-USERNAME/public:/usr/share/nginx/html:ro -p 80:80 -d nginx:alpine'
```

Update your env vars:
```bash
source ~/.*rc
```

Clone project:
```bash
git clone https://github.com/Webysther/packagist-mirror.git
cd packagist-mirror
```

Run env:
```bash
packagist-mirror-env
```

Config and Modify the .env:
```bash
composer install
cp .env.example .env
```

Edit .env:
```bash
PUBLIC_DIR=/home/YOUR-USERNAME/packagist-mirror-public
```

Run local:
```bash
php bin/mirror create -vvv
```

## Pull Requests

- **[PSR-2 Coding Standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)** - The easiest way to apply the conventions is to install [PHP Code Sniffer](http://pear.php.net/package/PHP_CodeSniffer).

- **Add tests!** - Your patch won't be accepted if it doesn't have tests.

- **Document any change in behaviour** - Make sure the README and any other relevant documentation are kept up-to-date.

- **Consider our release cycle** - We try to follow semver. Randomly breaking public APIs is not an option.

- **Create topic branches** - Don't ask us to pull from your master branch.

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please squash them before submitting.


## Running Tests

``` bash
$ vendor/bin/phpunit
```


**Happy coding**!
