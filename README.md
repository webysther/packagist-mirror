# php composer local mirror

## 快速开始

```sh
# example:
 
#  Persistence data folder, if needed
data_path="${HOME}/packagist-mirror" && mkdir -p $data_path && chown 10000 $data_path

# you server url or ip， build packages.json mirrors url
SERVER_URL='https://packagist.example.com'

# user request server url port
# nginx(or other) proxy the mirror server, enabel ssl and expose port 443
# if user request port not 80 or 443, EXTERNAL_PORT=<port>
EXTERNAL_PORT=443

# if not ssl and proxy ↓
#   EXTERNAL_PORT=8080
#   SERVER_URL='http://packagist.example.com'

docker run -v ${data_path}:/repo/public --name packagist-mirror -p 8080:8080  -e SERVER_URL=${SERVER_URL} -e EXTERNAL_PORT=${EXTERNAL_PORT} -d klzsysy/packagist-mirror

open ${SERVER_URL}:${EXTERNAL_PORT}
```

如果是k8s或者openshift，参考kubernetes目录


## 特点

在原基础上:

- 封装nginx作http服务，默认端口8080
- 更换国内源
- 添加定时运行
- 修改挂载路径
- 修改权限，以便在无root环境运行
- 兼容openshift默认scc权限运行
- **缓存包的zip文件，而不是只有一个index**，服务器下载用户请求的文件url，并在用户第二次请求同一个文件时得到缓存


快速一键部署本地 packagist mirror (*^▽^*)

## 额外变量

- `WEEK_SYNC_TIME` 每周同步的时间 `1-7` 1是周一， 例如 `1 2 3` 为周一到周三，默认每天`all`
- `SYNC_INTERVAL` 同步的间隔, 单位为分钟， 默认`30`，每30分钟同步一次（需要优先满足`WEEK_SYNC_TIME`条件）
- `SERVER_URL` 服务器的有效URL, 生成缓存zip文件时使用，默认`http://localhost`
- `EXTERNAL_PORT` 外部服务端口，即最终用户访问服务器的端口，默认80， 不是容器端口，容器默认端口8080
- `MAIN_MIRROR` ,json索引上游地址，默认国内`packagist.laravel-china.org`（官方源在国内访问失败率很高）
- `UPSTREAM_URL`, 缓存zip文件的上游服务器，默认`https://dl.laravel-china.org`
- `CLEAR_ZIP_CACHE`, 缓存zip清理时间，默认90天，即90天前的文件

## 注意

- 容器运行id `10000`， 在需要持久化时需要注意挂载目录的权限