# Packagist 镜像
原始代码查看[fork来源](https://github.com/Webysther/packagist-mirror)，本仓库变动如下
1. 处理了数据压缩，json直接展示明文
2. 类型变更为项目，可以使用composer命令create-project直接创建

## 安装

引用composer

``` bash
$ composer create-project jinfeijie/packagist-mirror-cn --remove-vcs
```

安装依赖

```bash
$ composer install --no-progress --no-ansi --no-dev --optimize-autoloader
```

配置环境

```bash
cp .env.example .env
```

使用命令来创建和更新镜像

```bash
$ php bin/mirror create --no-progress
```

## 要求

此版本支持以下版本的PHP。

* PHP >=7.1

## 测试

``` bash
$ vendor/bin/phpunit
```