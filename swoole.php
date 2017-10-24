<?php

/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/10/23
 * Time: 上午11:40
 */

class WebSocketTest
{
    public $server;

    public function __construct()
    {
        $this->server = new swoole_websocket_server("127.0.0.1", 9501);

        $this->server->on('open', function (swoole_websocket_server $server, $request) {
            echo "server: handshake success with fd{$request->fd}\n";
        });

        $this->server->on('message', function (swoole_websocket_server $server, $frame) {
            echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
            foreach ($this->server->connections as $fd) {
                $server->push($fd, $frame->fd. 'said:' . $frame->data);
            }
        });

        $this->server->on('close', function ($ser, $fd) {
            echo "client {$fd} closed\n";
        });

        $this->server->start();
    }
}

new WebSocketTest();