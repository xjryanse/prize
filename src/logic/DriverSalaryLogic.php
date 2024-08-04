<?php
namespace xjryanse\prize\logic;

use xjryanse\prize\service\PrizeRuleService;
use xjryanse\logic\Arrays;
use xjryanse\logic\Debug;
use xjryanse\logic\Cachex;
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
        // Debug::dump($data);
        $cacheKey = __METHOD__.md5(json_encode($data));
        return Cachex::funcGet($cacheKey, function() use ($data){
            $time       = Arrays::value($data, 'start_time');
            $groupCate  = 'driverSalaryYing';
            return PrizeRuleService::getPrizeWithFormula($time, $groupCate, $data);
        },true,1);
    }
    /**
     * 计算司机抽点
     */
    public static function calRate($data){
        $time = Arrays::value($data, 'start_time');
        $groupCate = 'driverSalaryRate';
        //TODO：合理吗？
        $res = PrizeRuleService::getPerPrize($time, $groupCate, $data);
        
        return $res && isset($res['rate']) ? $res['rate'] : 0;
    }

}
