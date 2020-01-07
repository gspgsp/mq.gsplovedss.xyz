<?php

namespace Gjh\app\controllers;

class BaseController
{
    /**
     * @var | 配置文件
     */
    private $config = [];

    /**
     * @var | 客户端参数
     */
    private $params;

    /**
     * @var | 数据库操作对象
     */
    private $db;

    public function __construct($config, $params)
    {
        $this->params = $params;
        $this->db     = $config['db'];
        $this->config = $config['config'];

        var_dump($this->params);
    }
}