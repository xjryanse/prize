<?php

namespace xjryanse\prize\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\logic\Debug;
use xjryanse\logic\Arrays;
/**
 * 计算公式结构
 */
class PrizeFormulaStructService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
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
        //提取全部列表
        //20230427:只提取同一个公式的
        $conF[] = ['formula_id','=',$info['formula_id']];
        $lists  = self::staticConList($conF);
        Debug::debug('$lists',$lists);        
        $res    = self::makeTree($lists,$this->uuid);
        $prize = self::doFormula($res, $data, $info['sub_method']);
        return round($prize);
    }
    /*
     * 公式格式
     */
    protected static function doFormula($treeArr, $data = [], $type = 'add'){
        $value  = [];
        foreach($treeArr as $v){
            $value[] = count($v['list']) > 0 
                    ? self::doFormula($v['list'],$data,$v['sub_method']) 
                    : ($v['cate'] == 'var' ? Arrays::value($data,$v['value'],0) : $v['value']);
        }
        
        //Debug::debug('doFormula的$treeArr',$treeArr);            
        Debug::debug('doFormula的value',$value);
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
        $value  = [];
        foreach($treeArr as $v){
            $value[] = count($v['list']) > 0 
                    ? self::formulaStr($v['list'],$data,$v['sub_method']) 
                    : $v['struct_desc'].':'.($v['cate'] == 'var' ? Arrays::value($data,$v['value'],0) : $v['value']);
        }
        //Debug::debug('doFormula的$treeArr',$treeArr);            
        Debug::debug('doFormula的value',$value);
        //TODO 优化？？
        if($type == 'add'){
            return '('.implode('+',$value).')';
        }
        if($type == 'multiply'){
            return '('.implode('×',$value).')';
        }
    }
    
    
}
