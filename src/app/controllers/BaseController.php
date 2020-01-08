<?php

namespace Gjh\Queue\app\controllers;

class BaseController
{
    /**
     * @var | 配置文件
     */
    protected $config = [];

    /**
     * @var | 客户端参数
     */
    protected $params;

    /**
     * @var \mysqli
     */
    protected $db;

    public function __construct($config, $params)
    {
        $this->params = $params;
        $this->db     = $config['db'];
        $this->config = $config['config'];
    }
}