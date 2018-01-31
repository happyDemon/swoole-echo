<?php

namespace HappyDemon\SwooleEcho\SocketServer;


class Socket
{
    public $fd;
    public $channels = [];

    /**
     * Socket constructor.
     *
     * @param $fd
     * @param $channels
     */
    public function __construct($fd, $channels)
    {
        $this->fd = $fd;
        $this->channels = $channels;
    }

}