<?php

namespace HappyDemon\SwooleEcho\SocketServer;


class Server
{
    const OPEN = 'Open';
    const CLOSE = 'Close';
    const HANDSHAKE = 'HandShake';
    const MESSAGE = 'Message';
    const REQUEST = 'Request';

    protected $connection;

    public function __construct(\Swoole\WebSocket\Server $connection)
    {
        $this->connection = $connection;
    }

    public function broadcast($channel, $payload)
    {
        
    }

    public function toAll($event, $message, $channel=null)
    {
        foreach ($this->connection->connections as $user)
        {
            $this->connection->push($user, json_encode(compact('event', 'channel', 'message')));
        }
    }

    public function toOthers()
    {

    }

    public function toSelf()
    {

    }

    /**
     * Start the socket server.
     */
    public function start()
    {
        $this->connection->start();
    }

    /**
     * Register a handler for a server event.
     *
     * @param $event
     * @param $callback
     */
    public function register($event, $callback)
    {
        $this->connection->on($event, $callback);
    }
}