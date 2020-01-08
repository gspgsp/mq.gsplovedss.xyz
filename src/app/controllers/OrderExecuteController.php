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
                $this->order = $result->fetch_row();
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
        $result = $this->db->query("select id, course_id from h_order_items where order_id = ".$this->order[0]);
        if ($rows = $result->fetch_all()) {
            foreach ($rows as $k => $val) {
                $type = $this->db->query("select type from h_edu_courses where id = ".$val[1]);
                //先不考虑 训练营的课程
                $this->db->query(
                    "insert into h_user_course('type', 'user_id', 'course_id', 'order_id', 'order_item_id', 'created_at', 'updated_at') values({$type}, {$this->order[1]}, {$val[1]}, {$this->order[0]}, {$val[0]}, {$this->time}, {$this->time})"
                );
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
