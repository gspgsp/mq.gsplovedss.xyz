<?php

namespace Gjh\Queue\app\controllers;

class OrderExecuteController extends BaseController
{
    /**
     * 订单处理
     * @return int
     */
    public function orderExecute()
    {
        if (!empty($this->params) && is_array($this->params)) {

            return 1;
        }

        return 0;
    }
}
