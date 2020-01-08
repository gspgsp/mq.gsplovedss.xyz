<?php

namespace Gjh\Queue;

class Server
{
    private $port = 9501;
    private $host = '0.0.0.0';//这个host务必写成0.0.0.0，否则会连接拒绝

    private $server;
    private $config;

    /**
     * @var \mysqli
     */
    private $db;
    private $redis;

    public function __construct()
    {
        $this->server = new \swoole_server($this->host, $this->port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
        $this->server->set(
            [
                'worker_num'      => 2,
                'task_worker_num' => 4,
                'task_ipc_mode'   => 1,
                'dispatch_mode'   => 2,
                'open_eof_check'  => true,
                'open_eof_split'  => true,
                'package_eof'     => PHP_EOL,
                'daemonize'       => false,
                'log_file'        => '/var/log/gjh.log',
            ]
        );

        $this->server->on('start', [$this, 'onStart']);
        $this->server->on('workerStart', [$this, 'onWorkerStart']);
        $this->server->on('connect', [$this, 'onConnect']);
        $this->server->on('receive', [$this, 'onReceive']);
        $this->server->on('task', [$this, 'onTask']);
        $this->server->on('finish', [$this, 'onFinish']);
        $this->server->on('close', [$this, 'onClose']);
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function start()
    {
        $this->server->start();
    }

    public function onStart($server)
    {
        echo "Server listening on {$server->host}:{$server->port}\n";
    }

    /**
     * @param $server
     * @param $worker_id
     */
    public function onWorkerStart($server, $worker_id)
    {
        //判断当前是Worker进程还是Task进程
        if ($server->taskworker) {
            $this->db = new \mysqli(
                $this->config['mysql']['host'],
                $this->config['mysql']['username'],
                $this->config['mysql']['password'],
                $this->config['mysql']['databasename'],
                $this->config['mysql']['port']
            );

            echo $this->db->connect_errno ? "当前worker: {$worker_id}连接失败\n" : "当前worker: {$worker_id}连接成功\n";
        }
    }

    public function onConnect($server, $fd, $from_id)
    {
        echo "i'm connect\n";
    }

    public function onReceive($server, $fd, $from_id, $data)
    {
        $this->server->task($data);
    }

    public function onTask($server, $task_id, $from_id, $data)
    {
        $result = 0;
        $data   = @json_decode($data, true);

        if (!empty($data) && is_array($data)) {
            $class  = 'Gjh\Queue\app\controllers\\'.$data['class'].'Controller';
            $params = $data['params'];
            $method = $data['method'];
            $config = [
                'db'     => $this->db,
                'config' => $this->config,
            ];

            if (class_exists($class) && method_exists($class, $method)) {
                $instance = new $class($config, $params);
                $result   = $instance->$method();
            }
        }

        return $result;
    }

    public function onFinish($server, $task_id, $data)
    {
        echo "$task_id - $data\n";
    }

    public function onClose($server, $fd, $reactorId)
    {
        echo "i'm close\n";
    }
}
