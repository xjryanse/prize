<?php

namespace xjryanse\prize\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\logic\Arrays;
/**
 * 价格计算公式
 */
class PrizeFormulaService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelQueryTrait;
    use \xjryanse\traits\StaticModelTrait;

    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\prize\\model\\PrizeFormula';
    //直接执行后续触发动作
    protected static $directAfter = true;        

    public static function extraDetails($ids) {
        return self::commExtraDetails($ids, function($lists) use($ids){
            $structArr = PrizeFormulaStructService::groupBatchCount('formula_id', $ids);
            foreach ($lists as &$v) {
                //0824公式结构数
                $v['structCounts']    = Arrays::value($structArr, $v['id'],0);
            }
            return $lists;
        });
    }
    /**
     * 计算公式值
     */
    public function calculate($data){
        $rootId = $this->structRootId();
        $res    = PrizeFormulaStructService::getInstance($rootId)->calculateByPid($data);
        return $res;
    }
    /**
     * 输出公式
     * @param type $data
     * @return type
     * @throws Exception
     */
    public function buildFormula($data){
        $rootId = $this->structRootId();
        $res    = PrizeFormulaStructService::getInstance($rootId)->buildFormula($data);
        return $res;
    }
    
    protected function structRootId(){
        $con[] = ['formula_id','=',$this->uuid];
        $con[] = ['pid','=',''];
        $rootInfo = PrizeFormulaStructService::staticConFind($con);
        return $rootInfo ? $rootInfo['id'] : '';
    }
    /*
     * 20220824：分组id取得公式id
     */
    public static function groupIdGetId( $groupId ){
        $con[] = ['group_id','=',$groupId];
        $info = self::staticConFind($con);
        return $info ? $info['id'] : '';
    }
    /**
     * 公式名称
     * @return type
     */
    public function fFormulaName() {
        return $this->getFFieldValue(__FUNCTION__);
    }

}
