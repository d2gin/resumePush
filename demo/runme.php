<?php
include dirname(__FILE__) . '/../vendor/autoload.php';

$cookies = [
    'Lagou'       => 'xxx',
    'BossZhiping' => 'xxx',
];
foreach ($cookies as $className => $cookie) {
    $class    = 'resumePush\Lagou';
    $instance = $class::instance($cookie);
    echo $className . ' start' . PHP_EOL;
    if (!$instance->run($this->resume)) {
        echo $instance->getError() . PHP_EOL;
    } else echo "推送成功" . PHP_EOL;
}