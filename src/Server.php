<?php

namespace Gjh\Queue;

class Server
{
    private $port = 9501;
    private $host = '127.0.0.1';

    private $server;

    public function __construct()
    {
        $this->server = \swoole_server($this->host, $this->port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
        $this->server->set(
            [
                'worker_num'      => 1,
                'task_worker_num' => 2,
                'task_ipc_mode'   => 1,
                'dispatch_mode'   => 3,
                'open_eof_check'  => true,
                'open_eof_split'  => true,
                'package_eof'     => PHP_EOL,
                'daemonize'       => false,
                'log_file'=>'/var/log/gjh.log'
            ]
        );

        $this->server->on('start', [$this, 'onStart']);
    }

    public function start()
    {
        $this->server->start();
    }

    public function onStart($server)
    {
        echo "Server listening on {$server->host}:{$server->port}\n";
    }
}
