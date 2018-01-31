<?php

namespace HappyDemon\SwooleEcho;

use HappyDemon\SwooleEcho\SocketServer\Server as SocketServer;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;


class Swoole
{
    public $server;

    public function init()
    {
        $connection = new Server(config('swoole-echo.server'), config('swoole-echo.port'),
            config('swoole-echo.swoole.mode'));
        $connection->set(config('swoole-echo.swoole.settings'));

        $this->server = new SocketServer($connection);

        $this->server->register(SocketServer::OPEN, [$this, 'open']);
        $this->server->register(SocketServer::MESSAGE, [$this, 'message']);
        $this->server->register(SocketServer::CLOSE, [$this, 'close']);
        $this->server->register(SocketServer::REQUEST, [$this, 'request']);
        $this->server->register(SocketServer::HANDSHAKE, [$this, 'handshake']);

        $this->server->start();

        return $this->server;
    }

    public function open(Server $server, Request $req)
    {
        $this->data = 'welcome, guest-' . $req->fd;
        $this->server->push($req->fd,
            json_encode(['type' => 'login', 'status' => 'success', 'from' => 'guest-' . $req->fd]));
        $this->push('welcome');
    }

    public function onMessage(Server $server, Frame $frame)
    {
        var_dump('[---] Message', $frame, $server);
        $data = json_decode($frame->data);
        switch ($data->action) {
            case 'sendmsg':
                $this->data = $data->msg;
                $this->from = $data->from;
                break;
            default:
                $this->data = 'aha, what do u want?';
                break;
        }
        $this->push();
    }

    public function close($server, $fd)
    {
        $this->data = 'bye..., guest-' . $fd;
        $this->push('welcome');
    }

    public function request(Request $request, Response $response)
    {
        echo 'onRequest';
    }

    public function handshake(Request $request, Response $response)
    {
        \Log::info('handshake request', [$request]);
        $secWebSocketKey = $request->header['sec-websocket-key'];
        $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';

        // Validate key
        if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
            $response->end();

            return false;
        }

        echo $request->header['sec-websocket-key'];

        $key = base64_encode(sha1($request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
            true));

        $headers = [
            'Upgrade'               => 'websocket',
            'Connection'            => 'Upgrade',
            'Sec-WebSocket-Accept'  => $key,
            'Sec-WebSocket-Version' => '13',
        ];

        // WebSocket connection to 'ws://127.0.0.1:9502/'
        // failed: Error during WebSocket handshake:
        // Response must not include 'Sec-WebSocket-Protocol' header if not present in request: websocket
        if (isset($request->header['sec-websocket-protocol'])) {
            $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
        }

        foreach ($headers as $key => $val) {
            $response->header($key, $val);
        }

        $response->status(101);
        $response->end();
        $this->server->push($request->fd,
            json_encode(['type' => 'login', 'status' => 'success', 'from' => 'guest-' . $request->fd]));
        \Log::info('handshake response', [$response]);

        return true;
    }
}