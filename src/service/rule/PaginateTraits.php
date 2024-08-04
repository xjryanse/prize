<?php

namespace xjryanse\prize\service\rule;

use xjryanse\prize\service\PrizeConditionService;
use think\Db;
/**
 * 分页复用列表
 */
trait PaginateTraits{
    
    /**
     * 价格key分组
     */
    public static function paginateForPrizeKeyCondGroupList($con = [], $order = '', $perPage = 10, $having = '', $field = "*", $withSum = false){
        $sql = self::mainModel()->prizeKeyGroupSql();

        $itemKeyGroupSql = PrizeConditionService::mainModel()->itemKeyGroupSql();
        
        $tableFinal = '('.$sql .' as finalA left join '. $itemKeyGroupSql 
                . ' as finalB on finalA.prize_key = finalB.item_key)';

        $res = Db::table($tableFinal)->where($con)->paginate($perPage);
        
        return $res ? $res->toArray() : [];
        
    }
}
