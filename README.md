# packagist local mirror

## 快速开始

```sh
# rpm data folder
data_path="${HOME}/data/packagist-mirror"

docker run -v ${data_path}:/repo/public --name packagist-mirror -p 8080:8080  -d klzsysy/packagist-mirror
# view repo index and client repo file
open ${SERVER_NAME}:8080
```

## 变更

在原基础上:

- 封装nginx作http服务，默认端口8080
- 更换国内源
- 添加定时运行
- 修改挂载路径
- 修改权限，以便在无root环境运行
- 兼容openshift无特权运行

快速一键部署本地 packagist mirror (*^▽^*)

## 新增变量

- `WEEK_SYNC_TIME` 每周同步的时间 `1-7` 1是周一， 例如 `1 2 3` 为周一到周三，默认每天`all`
- `SYNC_INTERVAL_DAY` 每天同步的时间, 单位为分钟， 默认`360`，每6小时同步一次（需要优先满足`WEEK_SYNC_TIME`条件）
- `HTTP_PORT` 默认8080
