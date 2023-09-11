<?php
namespace xjryanse\prize\logic;

use xjryanse\prize\service\PrizeRuleService;
use xjryanse\logic\Arrays;
/**
 * 司机薪水逻辑
 */
class DriverSalaryLogic
{
    /**
     * 计算营运额
     * （不是原始营运额，算工资用）
     * @param type $data    salary_order_bao_bus_driver表的一条记录
     * @return int
     */
    public static function calYing($data){
        $distPrize = Arrays::value($data, 'distribute_prize');
        
        $time       = Arrays::value($data, 'start_time');
        $groupCate  = 'driverSalaryYing';
        $res        = PrizeRuleService::getPrizeWithFormula($time, $groupCate, $data);
        dump($res);
        return $distPrize * 0.95;
//        $time = Arrays::value($data, 'start_time');
//        $groupCate = 'driverSalaryRate';
//        $res = PrizeRuleService::getPrizeWithFormula($time, $groupCate, $data);
//
//        return $res['prize'];
    }
    /**
     * 计算司机抽点
     */
    public static function calRate($data){
        $time = Arrays::value($data, 'start_time');
        $groupCate = 'driverSalaryRate';
        //TODO：合理吗？
        $res = PrizeRuleService::getPerPrize($time, $groupCate, $data);
        return $res['rate'];
    }

}
