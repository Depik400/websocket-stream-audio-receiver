<?php

namespace Paulo\FileProcessorServer\Controllers;

use Paulo\FileProcessorServer\Attributes\Controller;
use Paulo\FileProcessorServer\Attributes\Route;
use Paulo\FileProcessorServer\Enum\FileServiceEventType;
use Paulo\FileProcessorServer\Events\FileServiceEvent;
use Paulo\FileProcessorServer\Services\FileServerService;
use Paulo\FileProcessorServer\ValueObjects\FileSaverContext;
use Workerman\Connection\TcpConnection;
use Workerman\Lib\Timer;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Protocols\Websocket;

#[Controller]
class FileSaverController
{
    public function __construct()
    {

    }

    private function isValidJson(string $json): bool
    {
        if (function_exists('json_validate')) {
            return json_validate($json);
        }
        json_decode($json);
        return json_last_error() === JSON_ERROR_NONE;
    }
    #[Route('GET', 'file-saver')]
    public function index(TcpConnection $connection, Request $request)
    {
        $connection->onWebSocketConnect = function ($connection) {
            $connection->context->handshakeStep = 2;
            echo 'websocket is connected' . PHP_EOL;
        };
        $connection->websocketPingInterval = 30;
        $connection->protocol = Websocket::class;
        Websocket::input($request->rawBuffer(), $connection);
        $service = new FileServerService(new FileSaverContext(FileServiceEventType::Init));
        $connection->pinger = Timer::add(30, function () use ($connection) {
            $connection->send(json_encode(['type' => 'ping']));
        });
        $connection->onClose = function () use ($connection) {
            Timer::del($connection->pinger);
        };
        $connection->onMessage = function (TcpConnection $connection, $data) use ($service) {
            try {
                if ($service->fileSaverContext->state === FileServiceEventType::Data && !$this->isValidJson($data)) {
                    $result = $service->processEvent(new FileServiceEvent(FileServiceEventType::Data, $data));
                    if ($result) {
                        $connection->send(json_encode($result->toArray()));
                    }
                    return;
                }
                $data = json_decode($data, true, JSON_THROW_ON_ERROR);
                if (!isset($data['event'])) {
                    $items = implode(',', array_map(fn(FileServiceEventType $enum) => $enum->value, FileServiceEventType::cases()));
                    throw new \RuntimeException("incorrect body. must be json with 'event' ($items) key");
                }
                $event = FileServiceEventType::tryFrom($data['event']);
                $data = $data['body'] ?? null;
                $result = $service->processEvent(new FileServiceEvent($event, $data));
                if ($result) {
                    $connection->send(json_encode($result->toArray()));
                }
            } catch (\Throwable $err) {
                $connection->send(json_encode([
                    'status' => 'error',
                    'issue' => $err->getMessage(),
                    'trace' => $err->getTraceAsString(),
                ]));
            }
        };
    }
}