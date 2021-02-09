# resumePush
各大招聘平台的附件简历推送

### 支持的平台

Boss直聘、拉勾网

### 准备工作

1. 拉取仓库 `git clone git@github.com:d2gin/resumePush.git`

2. 进入仓库目录，cli执行 `composer install`

### 例子

```
<?php
include dirname(__FILE__) . '/../vendor/autoload.php';

// 本地简历文件
$resume  = '简历.pdf';
$cookies = [
    // 拉勾cookie
    'Lagou'      => 'xxx',
    // 直聘cookie
    'BossZhipin' => 'xxx',
];
// 推送服务
foreach ($cookies as $className => $cookie) {
    $class    = 'resumePush\\' . $className;
    $instance = $class::instance($cookie);
    echo $className . ' start' . PHP_EOL;
    if (!$instance->run($resume)) {
        echo $instance->getError() . PHP_EOL;
    } else echo "推送成功" . PHP_EOL;
}
```

### 推送说明

因为平台附件简历有数量限制，boss为3，拉勾为4。

目前默认推送的方案为：删一传一，也就是到达限制数量就会删除一个更早的附件，以推送最新的附件。

更粗暴的方案：清空传一，也就是每次上传都会清空一遍简历附件，以推送最新的附件。

方案切换：

```php
// 删一传一 默认方案
$instance->run($resume, 1);
// 清空传一 预备方案
$instance->run($resume, 2);
```

