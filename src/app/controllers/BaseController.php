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

    /**
     * 订单信息
     * @var
     */
    protected $order;

    /**
     * 用户ID
     * @var
     */
    protected $user_id;

    /**
     * 格式化时间
     * @var
     */
    protected $time;

    public function __construct($config, $params)
    {
        $this->params = $params;
        $this->db     = $config['db'];
        $this->config = $config['config'];
        $this->init();
    }

    /**
     * 初始化公共数据
     *
     * @return int
     */
    public function init()
    {
        $this->time = date("Y-m-d H:i:s", time());

        if (!empty($this->params) && is_array($this->params)) {
            if ($this->params['branch_type'] == 'order') {
                $this->params['order_id'] = 1563;

                $result                   = $this->db->query(
                    "select id, user_id, package_id from h_orders where id = ".$this->params['order_id']
                );
                if (!$result) {
                    return 0;
                }
                $this->order   = $result->fetch_assoc();
                $this->user_id = $this->order['user_id'];
            }else if ($this->params['branch_type'] == 'vip'){
                $this->params['order_id'] = 912;

                $result                   = $this->db->query(
                    "select id, vip_id, user_id from h_vip_orders where id = ".$this->params['order_id']
                );
                if (!$result) {
                    return 0;
                }
                $this->order   = $result->fetch_assoc();
                $this->user_id = $this->order['user_id'];
            }
            //其它业务逻辑
        }else{
            return 0;
        }
    }
}