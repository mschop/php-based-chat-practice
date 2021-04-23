<?php


namespace App;


use Amp\Http\Server\HttpServer;
use Amp\Http\Server\StaticContent\DocumentRoot;
use Amp\Socket\Server;
use Amp\Http\Server\Router;
use Amp\Websocket\Server\Websocket;
use Psr\Log\LoggerInterface;

class LoopHandler
{
    function __construct(private Websocket $websocket, private LoggerInterface $logger)
    {
    }

    function __invoke()
    {
        $sockets = [
            Server::listen('127.0.0.1:1337'),
            Server::listen('[::1]:1337'),
        ];

        $router = new Router;
        $router->addRoute('GET', '/broadcast', $this->websocket);
        $router->setFallback(new DocumentRoot(__DIR__ . '/../public'));

        $server = new HttpServer($sockets, $router, $this->logger);

        return $server->start();
    }
}