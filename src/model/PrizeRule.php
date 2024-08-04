<?php
namespace xjryanse\prize\model;

/**
 *
 */
class PrizeRule extends Base
{
    
    /**
     * 价格key 聚合
     */
    public static function prizeKeyGroupSql($con = []){
        $fields = [];
        $fields[] = 'group_id';
        $fields[] = 'prize_key';
        $fields[] = 'count( 1 ) AS ruleCount';
        // 受影响因素
        $fields[] = 'group_concat(distinct prize_cate) as prizeCate';

        $sql = self::where($con)->field(implode(',',$fields))->group('group_id,prize_key')->buildSql();

        return $sql;
    }
}