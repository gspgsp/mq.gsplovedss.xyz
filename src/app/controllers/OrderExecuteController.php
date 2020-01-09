<?php

/**
 * 订单处理服务
 */

namespace Gjh\Queue\app\controllers;

class OrderExecuteController extends BaseController
{
    protected $order;
    protected $time;
    protected $user_id;

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
                    "select id, user_id, package_id from h_orders where id = ".$this->params['order_id']
                );
                if (!$result) {
                    return 0;
                }
                $this->order = $result->fetch_assoc();
                $this->user_id = $this->db->query("select user_id from  h_orders where id = {$this->order['id']}")->fetch_row()[0];

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

    /**
     * 添加用户课程
     * @return int
     */
    private function _setUserCourse()
    {
        if ($result = $this->db->query(
            "select id, course_id from h_order_items where order_id = ".$this->order['id']
        )) {
            while ($row = $result->fetch_assoc()) {
                $type = $this->db->query("select type from h_edu_courses where id = ".$row['course_id'])->fetch_assoc();
                //先不考虑 训练营的课程
                $this->db->query(
                    "insert into h_user_course (`type`, `user_id`, `course_id`, `order_id`, `order_item_id`, `created_at`, `updated_at`) value('{$type['type']}', {$this->order['user_id']}, {$row['course_id']}, {$this->order['id']}, {$row['id']}, '{$this->time}', '{$this->time}')"
                );
            }

            return 1;
        }

        return 0;
    }

    /**
     * 更新课程购买数
     *
     * @return int
     */
    private function _setCourseBuyNum()
    {
        if ($this->order['package_id']) {
            if ($this->db->query(
                "update h_edu_packages set buy_num = buy_num + 1 where id = {$this->order['package_id']}"
            )) {
                $this->db->query(
                    "update h_edu_courses set buy_num = buy_num + 1 where id in(select course_id from h_edu_package_course where package_id = {$this->order['package_id']})"
                );

                return 1;
            }

            return 0;
        } else {
            $this->db->query(
                "update h_edu_courses set buy_num = buy_num + 1 where id in(select course_id from h_order_items where order_id = {$this->order['id']})"
            );

            return 1;
        }
    }

    /**
     * 如果当前课程有设置优惠券 那么者会给用户发一张优惠券
     */
    private function _setUserCoupon()
    {
        if ($res = $this->db->query(
            "select coupon_id from h_edu_courses where id in(select course_id from h_order_items where order_id = {$this->order['id']})"
        )) {
            while ($row = $res->fetch_assoc()) {
                if ($row['coupon_id']) {
                    $this->_grantUserCoupon($this->order['id'], $row['coupon_id']);
                }
            }
        }

        if ($this->order['package_id']) {
            if ($res = $this->db->query(
                "select coupon_id from h_edu_packages where id = {$this->order['package_id']}"
            )) {
                while ($row = $res->fetch_assoc()) {
                    if ($row['coupon_id']) {
                        $this->_grantUserCoupon($this->order['id'], $row['coupon_id']);
                    }
                }
            }
        }

        return 1;
    }

    /**
     * 设置当前用户为付费用户
     * @return int
     */
    private function _setUserPay()
    {
        $this->db->query(
            "UPDATE h_users
SET `level`= CASE
   WHEN `level`='not' THEN 'pay'
   ELSE `level`
END where id = (select user_id from h_orders where id = {$this->order['id']})"
        );

        return 1;
    }

    /**
     * 給用戶发放优惠券，需要用到swoole定时器：https://wiki.swoole.com/wiki/page/480.html，单位毫秒
     *
     * @param $order_id
     * @param $coupon_id
     */
    private function _grantUserCoupon($order_id, $coupon_id)
    {
        $count = 0;

        $coupon_res = $this->db->query("select * from h_coupons where id = {$coupon_id} and if(effective_day > 0, timestampdiff(DAY, created_at, now()) <= effective_day, unix_timestamp(not_after) > unix_timestamp(now())) and enabled = 1");
        $res = $this->db->query("select count(*) as user_coupon_count as  from h_user_coupon where coupon_id = {$coupon_id} and user_id = {$this->user_id}");

        if ($res){
            $count = $res->fetch_assoc()['user_coupon_count'];
        }



        if ($coupon_res){
            while ($coupon = $coupon_res->fetch_assoc()){
                if ($coupon['limit_number'] > $count && $coupon['total'] > $coupon['used_number']){

                    //TODO::逻辑处理
                    swoole_timer_after(
                        6000,
                        function () use ($order_id, $coupon_id) {
                            echo "swoole order id is:".$order_id."coupon is:".$coupon_id."\n";
                        }
                    );
                }
            }
        }
    }
}
