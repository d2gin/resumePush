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