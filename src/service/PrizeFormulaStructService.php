<?php

namespace xjryanse\prize\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\logic\Debug;
use xjryanse\logic\Arrays;
use xjryanse\logic\Number;
/**
 * 计算公式结构
 */
class PrizeFormulaStructService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;

    use \xjryanse\traits\StaticModelTrait;
    use \xjryanse\traits\TreeTrait;
    
    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\prize\\model\\PrizeFormulaStruct';
    //直接执行后续触发动作
    protected static $directAfter = true;        
    /*
     * 公式创建
     */
    public function calculateByPid($data = []){
        $con[] = ['id','=',$this->uuid];
        $info   = self::staticConFind($con);
        if(!$info){
            // 20231004;增加判断
            return 0;
        }
        //提取全部列表
        //20230427:只提取同一个公式的
        $conF[] = ['formula_id','=',$info['formula_id']];
        $lists  = self::staticConList($conF);
        // Debug::dump('=======');    
        // Debug::dump($data);    
        $res    = self::makeTree($lists,$this->uuid);
        $prize = self::doFormula($res, $data, $info['sub_method']);
        $roundNum = is_null($info['round']) ? 2 : $info['round'];
        // Debug::dump($info);
        // return Number::round($prize, $roundNum);
        // 20240612：恢复TODO
        return round($prize, $roundNum);
    }
    /*
     * 公式格式
     * @param type $treeArr
     * @param type $data        应该是用于计算的数据数组
     * @param type $type
     * @return type
     */
    protected static function doFormula($treeArr, $data = [], $type = 'add'){
        $value  = [];
        foreach($treeArr as $v){
//            Debug::dump('doFormula的$treeArr',$v);
//            Debug::dump('doFormula的$data',$data);
            $value[] = count($v['list']) > 0 
                    ? self::doFormula($v['list'],$data,$v['sub_method']) 
                    : ($v['cate'] == 'var' ? Arrays::value($data,$v['value'],0) : $v['value']);
        }
        
        //Debug::debug('doFormula的$treeArr',$treeArr);            
        // Debug::dump('doFormula的value',$value);
        //TODO 优化？？
        if($type == 'add'){
            return array_sum($value);
        }
        if($type == 'multiply'){
            return array_product($value);
        }
    }

    /*************************************/    
    /**
     * 输出计算公式
     */
    public function buildFormula($data){
        $con[] = ['id','=',$this->uuid];
        $info   = self::staticConFind($con);
        //提取全部列表
        $lists  = self::staticConList();
        $res    = self::makeTree($lists,$this->uuid);
        return self::formulaStr($res, $data, $info['sub_method']);
    }

    protected static function formulaStr($treeArr, $data = [], $type = 'add'){
//        Debug::dump($treeArr);
//        Debug::dump($data);
        $value  = [];
        foreach($treeArr as $v){
            $tStr = Arrays::value($data['descArr'], $v['value']);
            $tDesc = $tStr ? '['.$tStr.']' : '';
            $value[] = count($v['list']) > 0 
                    ? self::formulaStr($v['list'],$data,$v['sub_method']) 
                    : $v['struct_desc'].$tDesc.':'.($v['cate'] == 'var' ? Arrays::value($data,$v['value'],0) : $v['value']);
        }
        //Debug::debug('doFormula的$treeArr',$treeArr);            
        Debug::debug('doFormula的value',$value);
        //TODO 优化？？
        if($type == 'add'){
            $resStr = '('.implode('+',$value).')';
        }
        if($type == 'multiply'){
            $resStr = '('.implode('×',$value).')';
        }
        return $resStr;
    }
    
    
}
