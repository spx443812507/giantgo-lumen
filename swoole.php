<?php

/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/10/23
 * Time: 上午11:40
 */
class Swoole
{
    public $server;

    public $conn;

    public $listener = [];

    public $service;

    public $user = 'root';

    public $password = 'sino@123';

    public $host = '127.0.0.1';

    public $databse = 'giantgo';

    public function __construct()
    {
        $this->server = new swoole_websocket_server("127.0.0.1", 9501);

        $this->conn = new PDO("mysql:host=$this->host;dbname=$this->databse", $this->user, $this->password, array(PDO::ATTR_PERSISTENT => true));

        $this->setupServer();
    }

    private function isWebSocketClient($fd)
    {
        $connectionInfo = $this->server->connection_info($fd);
        return !empty($connectionInfo) && $connectionInfo['websocket_status'] === 3;
    }

    private function setupServer()
    {
        $this->server->on('open', function (swoole_websocket_server $server, $request) {
            echo "server: handshake success with fd{$request->fd}\n";
        });

        $this->server->on('message', function (swoole_websocket_server $server, $frame) {
            echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";

            $sql = <<<MySQL
INSERT INTO listeners(fd, command, params, created_at) SELECT :fd, :command, :params, :created_at
FROM dual WHERE not exists (select * from listeners where fd = :fd AND command = :command);
MySQL;

            $data = json_decode($frame->data);

            $executor = $this->conn->prepare($sql);

            $executor->execute([
                ':fd' => $frame->fd,
                ':command' => $data->command,
                ':params' => json_encode($data->params),
                ':created_at' => date('Y-m-d H:m:s'),
                ':updated_at' => date('Y-m-d H:m:s')
            ]);

            $executor->closeCursor();
        });

        $this->server->on('request', function ($request, $response) {
            $command = $request->post['command'];
            $data = $request->post['data'];

            $sql = 'select * from listeners where command = :command';
            $executor = $this->conn->prepare($sql);

            $executor->execute([':command' => $command]);

            $rows = $executor->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                if ($this->isWebSocketClient($row['fd'])) {
                    $this->server->push($row['fd'], json_encode([
                        'command' => $command,
                        'params' => $row['params'],
                        'data' => $data
                    ]));
                }
            }
        });

        $this->server->on('close', function ($ser, $fd) {
            echo "client {$fd} closed\n";
            if ($this->isWebSocketClient($fd)) {
                $sql = 'delete from listeners where fd = :fd';
                $executor = $this->conn->prepare($sql);

                $executor->execute(['fd' => $fd]);

                $executor->closeCursor();
            }
        });

        $this->server->start();
    }
}

new Swoole();