<?php

namespace xjryanse\prize\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\logic\Arrays;
/**
 * 价格分组
 */
class PrizeGroupService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelQueryTrait;
    use \xjryanse\traits\StaticModelTrait;

    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\prize\\model\\PrizeGroup';
    //
    protected static $directAfter = true;        
    /**
     * 20220822:根据传入时间，获取有效的价格分组
     */
    public static function getEffects($time, $con = []){
        $con[] = ['start_time','<=',$time];
        $con[] = ['end_time','>=',$time];
        $con[] = ['company_id','=',session(SESSION_COMPANY_ID)];
        $groups = PrizeGroupService::mainModel()->where($con)->order('level desc')->select();
        return $groups;
    }
    
    /**
     * 
     * @param type $ids
     * @return type
     */
    public static function extraDetails($ids) {
        return self::commExtraDetails($ids, function($lists) use($ids){
            $ruleArr        = PrizeRuleService::groupBatchCount('group_id', $ids);
            $formulaArr     = PrizeFormulaService::groupBatchCount('group_id', $ids);
            $conditionArr   = PrizeConditionService::groupBatchCount('item_key', array_column($lists,'group_key'));

            foreach ($lists as &$v) {
                //规则状态:0-未生效;1-生效中;2-已过期
                $v['groupState']        = self::groupState($v['start_time'], $v['end_time']);
                //规则数
                $v['ruleCounts']        = Arrays::value($ruleArr, $v['id'],0);
                // 条件数
                $v['conditionCount']    = Arrays::value($conditionArr, $v['group_key'],0);
                //0824公式数
                $v['formulaCounts']     = Arrays::value($formulaArr, $v['id'],0);
            }
            return $lists;
        });
    }
    /**
     * 分组状态
     */
    protected static function groupState($startTime,$endTime){
        //开始时间未到
        if($startTime > date('Y-m-d H:i:s') ){
            //未生效
            return 0;
        }
        //开始时间已过，结束时间未到
        if($startTime < date('Y-m-d H:i:s') && $endTime > date('Y-m-d H:i:s')){
            //生效中
            return 1;
        }
        //结束时间已过
        if($endTime < date('Y-m-d H:i:s') ){
            //已过期
            return 2;
        }
        //出错
        return 9999;
    }
    
    public static function keyToId($groupKey){
        $con[] = ['group_key', '=', $groupKey];
        $info = self::staticConFind($con);
        return Arrays::value($info, 'id');
    }
    
    
    public function fCustomerId() {
        return $this->getFFieldValue(__FUNCTION__);
    }
}
