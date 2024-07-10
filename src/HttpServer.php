<?php

namespace Paulo\FileProcessorServer;

use Router;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Worker;


class HttpServer
{
    protected Worker $worker;

    protected Router $router;
    public function __construct(string $host, string $port = '80', array $options = [])
    {
        $this->worker = new Worker(sprintf("%s://%s:%s", isset($options['ssl']) ? 'https' : 'http', $host, $port), $options);
        $this->router = new Router();
        $this->setSettings();
    }

    protected function setSettings()
    {
        $this->worker->onMessage = function (TcpConnection $connection, Request $data) {
            echo "URI: " . $data->uri() . PHP_EOL;
            $closure = $this->router->resolve($data);

            if ($closure instanceof \Closure) {
                $closure($connection, $data);
            } else {
                $response = new Response(404, ['Content-Type' => 'application/json'], json_encode(['error' => 'route not founded']));
                $connection->send($response);
            }
        };

        // Emitted when connection closed
        $this->worker->onClose = function ($connection) {
            echo "Connection closed\n";
        };
    }

    public function start()
    {
        Worker::runAll();
    }
}