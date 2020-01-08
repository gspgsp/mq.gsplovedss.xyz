<?php

/**
 * 订单处理服务
 */

namespace Gjh\Queue\app\controllers;

class OrderExecuteController extends BaseController
{
    protected $order;
    protected $time;

    /**
     * 订单处理
     * @return int
     */
    public function orderExecute()
    {
        $this->time = date("Y-m-d H:i:s", time());
        if (!empty($this->params) && is_array($this->params)) {
            if ($this->params['branch_type'] == 'order') {

                //当为订单时
                $this->params['order_id'] = 1563;
                $result                   = $this->db->query(
                    "select id, user_id from h_orders where id = ".$this->params['order_id']
                );
                if (!$result) {
                    return 0;
                }
                $this->order = $result->fetch_assoc();

                //开启事务
                $this->db->begin_transaction();
                try {
                    $this->_setUserCourse();
                    $this->_setCourseBuyNum();
                    $this->_setUserCoupon();
                    $this->_setUserPay();

                    $this->db->commit();

                    return 1;
                } catch (\Exception $exception) {
                    $this->db->rollback();

                    return $exception->getMessage();
                }
            }
        }

        return 0;
    }

    private function _setUserCourse()
    {
        if ($result = $this->db->query(
            "select id, course_id from h_order_items where order_id = ".$this->order['id']
        )) {
            while ($row = $result->fetch_assoc()) {
                $type = $this->db->query("select type from h_edu_courses where id = ".$row['course_id'])->fetch_assoc();
                //先不考虑 训练营的课程
                echo $type['type']."\n";
                var_dump($this->order);
                var_dump($row);
                echo $this->time."\n";

                $this->db->query("insert into h_user_course ('type', 'user_id', 'course_id', 'order_id', 'order_item_id', 'created_at', 'updated_at') value('{$type['type']}', {$this->order['user_id']}, {$row['course_id']}, {$this->order['id']}, {$row['id']}, {$this->time}, {$this->time})");
            }

            return 1;
        }

        return 0;
    }

    private function _setCourseBuyNum()
    {

    }

    private function _setUserCoupon()
    {

    }

    private function _setUserPay()
    {

    }
}
