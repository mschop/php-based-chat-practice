<?php


namespace App;


use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Promise;
use Amp\Success;
use Amp\Websocket\Client;
use Amp\Websocket\Message;
use Amp\Websocket\Server\ClientHandler;
use Amp\Websocket\Server\Gateway;
use Psr\Log\LoggerInterface;
use function Amp\call;

class WebsocketClientHandler implements ClientHandler
{
    function __construct(private LoggerInterface $logger)
    {
    }

    private const ALLOWED_ORIGINS = [
        'http://localhost:1337',
        'http://127.0.0.1:1337',
        'http://[::1]:1337'
    ];

    public function handleHandshake(Gateway $gateway, Request $request, Response $response): Promise
    {
        if (!\in_array($request->getHeader('origin'), self::ALLOWED_ORIGINS, true)) {
            return $gateway->getErrorHandler()->handleError(403);
        }

        return new Success($response);
    }

    public function handleClient(Gateway $gateway, Client $client, Request $request, Response $response): Promise
    {
        return call(function () use ($gateway, $client): \Generator {
            while ($message = yield $client->receive()) {
                \assert($message instanceof Message);
                $this->logger->info('broadcasting message');
                $gateway->broadcast(yield $message->read());
            }
        });
    }
}