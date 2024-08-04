<?php
namespace xjryanse\prize\model;

/**
 *
 */
class PrizeCondition extends Base
{
    
    /**
     * item key 聚合
     */
    public static function itemKeyGroupSql($con = []){
        $fields = [];
        $fields[] = 'item_key';
        // 条件描述
        $fields[] = 'group_concat(distinct item_describe order by judge_field) as condDesc';
        // 条件数量
        $fields[] = 'count( 1 ) AS condCount';

        $sql = self::where($con)->field(implode(',',$fields))->group('item_key')->buildSql();

        return $sql;
    }
}