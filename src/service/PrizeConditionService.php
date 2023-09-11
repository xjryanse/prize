<?php

namespace xjryanse\prize\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\system\service\SystemConditionService;
use xjryanse\logic\Arrays;
use xjryanse\logic\Debug;
/**
 * 
 */
class PrizeConditionService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelQueryTrait;

    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\prize\\model\\PrizeCondition';
    //鐩存帴鎵ц鍚庣画瑙﹀彂鍔ㄤ綔
    protected static $directAfter = true;
    // 20230710：开启方法调用统计
    protected static $callStatics = true;
    
    /**
     * 
     * @param type $key
     * @param type $param
     * @param type $noCondPass  没有条件是否通过
     * @return boolean
     */
    public static function isConditionKeyMatch($key,$param = [], $noCondPass = false){
        $con[] = ['item_key','=',$key];
        $lists = self::mainModel()->where($con)->select();
        // 20230429:没有条件默认过
        if(!count($lists) && $noCondPass){
            return true;
        }

        $res = [];        
        foreach($lists as $v){
            Debug::debug('isConditionKeyMatch的$v',$v);
            $value      = Arrays::value($param,$v['judge_field']);
            $judgeSign  = $v['judge_sign'];
            $judgeValue = $v['judge_value'];
            Debug::debug('isConditionKeyMatch的数据',$value.' '.$judgeSign.' '.$judgeValue);
            //结果
            if($judgeSign == 'in'){
                //是否在数组中
                $res[$v['group_id']][] = in_array($value,explode(',',$judgeValue));
            } else if($judgeSign == 'has'){
                // 20230427：场景:一个具体站点匹配了多个模板站点（例：厦门，厦门机场），判断匹配结果是否包含指定站点（例：是否包含厦门机场）
                $res[$v['group_id']][] = in_array($judgeValue, $value);
            } else if($judgeSign == 'intersect'){
                // 20230428：有交集，$judgeValue为数组
                $res[$v['group_id']][] = array_intersect($value, explode(',',$judgeValue)) ? true : false;
            }else {
                $evalStr = 'return \'' . $value. '\' ' . $judgeSign . ' \'' . $judgeValue . '\';';
                $res[$v['group_id']][] = eval($evalStr);
            }
        }
//        dump('------');
//        dump($key);
//        dump($res);
//        dump($param);
        foreach ($res as $value) {
            //某一组全为true（没有false）,说明条件达成，
            if (!in_array(false, $value)) {
                return true;
            }
        }
        return false;
    }
    /**
     * 按价格key保存价格规则
     * @param type $prizeKey
     * @param type $rules
     */
    public static function saveRulesByKeyRam($prizeKey, $rules){
        // 先删
        $con[] = ['item_key','=',$prizeKey];
        self::where($con)->delete();
        // 再写
        foreach($rules as &$v){
            $v['item_key'] = $prizeKey;
        }
        return self::saveAllRam($rules);
    }
}
