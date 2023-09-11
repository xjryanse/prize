<?php

namespace xjryanse\prize\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\system\service\SystemConditionService;
use xjryanse\logic\Debug;
use xjryanse\logic\Arrays;
use xjryanse\logic\Arrays2d;
/**
 * 价格规则?
 */
class PrizeRuleService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelQueryTrait;
    use \xjryanse\traits\StaticModelTrait;

    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\prize\\model\\PrizeRule';
    //直接执行后续触发动作
    protected static $directAfter = true;        
    // 20230710：开启方法调用统计
    protected static $callStatics = true;

    public static function extraDetails($ids) {
        return self::commExtraDetails($ids, function($lists){
            // 获取订单流程
            $conditionArr = PrizeConditionService::groupBatchCount('item_key', array_column($lists,'prize_key'));
            foreach($lists as &$item){
                //规则数?
                $item['ruleCounts'] = Arrays::value($conditionArr, $item['prize_key'],0);  
            }
            return $lists;
        });
    }

    /**
     * 20220824:新的计算方法
     * @param type $data
     * start_time
     * 
     * @return type
     */
    public static function getBaoPrizeWithFormula($data = []){
//        $con[] = ['group_cate','=','bao'];
//        // $groups = PrizeGroupService::mainModel()->where($con)->order('level desc')->select();
//        $groups = PrizeGroupService::getEffects($data['start_time'], $con);
//        Debug::debug('$groups',$groups);
//        $resArr = [];
//        foreach($groups as $v){
//            // 20230429：预先判断是否符合当前逻辑分组的条件
//            if(!PrizeConditionService::isConditionKeyMatch( $v['group_key'], $data, true)){
//                continue;
//            }
//            // 价格的数据格式，键值对数组
//            $dataForm   = PrizeRuleService::getPrizeDataArr($v['id'], $data);
//            $formulaId  = PrizeFormulaService::groupIdGetId($v['id']);
//            $resPrize   = PrizeFormulaService::getInstance($formulaId)->calculate($dataForm);
////            dump('====');
////            dump(PrizeFormulaService::getInstance($formulaId)->buildFormula($dataForm));
////            dump($resPrize);
//            //优先级从高到底，匹配到则返回
//            if($resPrize){
//                $resArr['prize']        = $resPrize;
//                $resArr['formula']      = PrizeFormulaService::getInstance($formulaId)->buildFormula($dataForm);
//                $resArr['group_id']     = $v['id'];
//                $resArr['dataForm']     = $dataForm;
//                $resArr['$formulaName']     = PrizeFormulaService::getInstance($formulaId)->fFormulaName();
//                break;
//            }
//        }
//        return $resArr;
        return self::getPrizeWithFormula($data['start_time'], 'bao', $data);
    }
    /**
     * 20230820
     * @param type $time        校验时间
     * @param type $groupCate
     * @param type $data        数据
     * @return type
     */
    public static function getPrizeWithFormula($time , $groupCate, $data){
        $con[] = ['group_cate','=',$groupCate];
        // $groups = PrizeGroupService::mainModel()->where($con)->order('level desc')->select();
        $groups = PrizeGroupService::getEffects($time, $con);
        Debug::debug('$groups',$groups);
        $resArr = [];
        foreach($groups as $v){
            // 20230429：预先判断是否符合当前逻辑分组的条件
            if(!PrizeConditionService::isConditionKeyMatch( $v['group_key'], $data, true)){
                continue;
            }
            // 价格的数据格式，键值对数组
            $dataForm   = PrizeRuleService::getPrizeDataArr($v['id'], $data);
            $formulaId  = PrizeFormulaService::groupIdGetId($v['id']);
            $resPrize   = PrizeFormulaService::getInstance($formulaId)->calculate($dataForm);
//            dump('====');
//            dump(PrizeFormulaService::getInstance($formulaId)->buildFormula($dataForm));
//            dump($resPrize);
            //优先级从高到底，匹配到则返回
            if($resPrize){
                $resArr['prize']        = $resPrize;
                $resArr['formula']      = PrizeFormulaService::getInstance($formulaId)->buildFormula($dataForm);
                $resArr['group_id']     = $v['id'];
                $resArr['dataForm']     = $dataForm;
                $resArr['$formulaName']     = PrizeFormulaService::getInstance($formulaId)->fFormulaName();
                break;
            }
        }
        return $resArr;
    }
    
    
    /**
     * 只取单价，不用公式计算（适用于提取抽成率的情况）
     * 20230820
     * @param type $time        校验时间
     * @param type $groupCate
     * @param type $data        数据
     * @return type
     */
    public static function getPerPrize($time , $groupCate, $data){
        $con[] = ['group_cate','=',$groupCate];
        $groups = PrizeGroupService::getEffects($time, $con);

        $dataForm = [];
        foreach($groups as $v){
            // 20230429：预先判断是否符合当前逻辑分组的条件
            if(!PrizeConditionService::isConditionKeyMatch( $v['group_key'], $data, true)){
                continue;
            }
            // 价格的数据格式，键值对数组
            $dataForm   = self::getPrizeDataArr($v['id'], $data);
        }
        return $dataForm;
    }
    /**
     * 20220822 ；获取包车价格数组
     * 尝试进行大数据量的处理
     */
    public static function getPrizeDataArr($groupId,$data){
        // 提取价格规则
        $cond[] = ['group_id','=',$groupId];
        $lists = self::mainModel()->where($cond)->select();
        $prizeObj = [];

        $matches = [];
        foreach($lists as $vv){
            $isMatch = PrizeConditionService::isConditionKeyMatch( $vv['prize_key'], $data);
            if(!$isMatch){
                continue;
            }

            $matches[] = $vv;
            $thisPrize = ($vv['per_unit'] 
                    ? $data[$vv['per_unit']] * $vv['per_prize'] 
                    : $vv['per_prize']) 
                * $vv['rate'];
                //cal_method示例：round
            $prizeObj[$vv['prize_cate']] = Arrays::value($prizeObj, $vv['prize_cate'], 0)
                    + ($vv['cal_method'] ? $vv['cal_method']($thisPrize) : $thisPrize);
            Debug::debug($vv['prize_describe'],$thisPrize,'prize');            
        }
        //220825:折扣特殊处理，避免出现0
        if(!Arrays::value($prizeObj, 'discount')){
            $prizeObj['discount'] = 1;
        }
        
        $prizeObj['$matches'] = $matches;
        return $prizeObj;
    }
    
    /**
     * 20230820：设定规则
     * @param type $groupKey    字符串，用于提取group_id
     * @param type $ruleInfo    一维数组
     * @param type $rules       二维数组
     */
    public static function setRuleRam($groupKey, $ruleInfo, $rules = []){
        $ruleInfo['group_id'] = PrizeGroupService::keyToId($groupKey);
        $prizeKey   = Arrays::value($ruleInfo, 'prize_key');
        if(!$prizeKey){
            throw new Exception('prize_key必须');
        }

        $id         = self::keyToId($prizeKey);
        if($id){
            $ruleInfo['id'] = $id;
        }
        self::saveGetIdRam($ruleInfo);
        // 保存条件
        PrizeConditionService::saveRulesByKeyRam($prizeKey, $rules);
    }

    public static function keyToId($prizeKey){
        $con[] = ['prize_key', '=', $prizeKey];
        $info = self::staticConFind($con);
        return $info ? Arrays::value($info, 'id') : '';
    }
    
}
