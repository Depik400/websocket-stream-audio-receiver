<?php
use Paulo\FileProcessorServer\HttpServer;
use Paulo\FileProcessorServer\Lib\Env;
use Paulo\FileProcessorServer\Services\RouterCacheFactory;
use Workerman\Events\Swoole;
use Workerman\Worker;

require __DIR__ . '/vendor/autoload.php';

if (defined('SWOOLE_VERSION')) {
    Worker::$eventLoopClass = Swoole::class;
}
try {
    Env::instance();
} catch (\Throwable $err) {
    echo $err->getMessage() . PHP_EOL;
    return;
}


$server = new HttpServer(Env::instance()->get('HOST'), Env::instance()->get('PORT'));

$server->start();