<?php
include dirname(__FILE__) . '/../vendor/autoload.php';

$cookies = [
    'Lagou'       => 'xxx',
    'BossZhiping' => 'xxx',
];
$resume  = '简历.pdf';
foreach ($cookies as $className => $cookie) {
    $class    = 'resumePush\\' . $className;
    $instance = $class::instance($cookie);
    echo $className . ' start' . PHP_EOL;
    if (!$instance->run($resume)) {
        echo $instance->getError() . PHP_EOL;
    } else echo "推送成功" . PHP_EOL;
}