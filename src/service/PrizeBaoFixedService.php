<?php

namespace xjryanse\prize\service;

use xjryanse\system\interfaces\MainModelInterface;
use app\station\service\StationService;
use xjryanse\logic\Arrays2d;
use xjryanse\logic\Debug;
//use app\third\tencent\Map as TencentMap;
//use app\third\gaode\Map as GaodeMap;

/**
 * 20230410：包车固定价格
 */
class PrizeBaoFixedService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;

    use \xjryanse\traits\StaticModelTrait;

    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\prize\\model\\PrizeBaoFixed';
    //直接执行后续触发动作
    protected static $directAfter = true;        

    public static function extraDetails($ids) {
        return self::commExtraDetails($ids, function($lists){
            foreach($lists as &$item){
                
            }
            return $lists;
        });
    }
    /**
     * 获取报价
     */
    public static function getPrize($data = []){
        $fromStationData    = $data['fromStationArr'];
        $toStationData      = $data['toStationArr'];
        // 优先级
        $con[]              = ['status','=',1];
        $rules              = self::staticConList($con,'','sort');
        //Debug::dump($rules);
        $stations           = StationService::staticConList();
        $stationsArr        = Arrays2d::fieldSetKey($stations, 'id') ;
        foreach($rules as $v){
//            Debug::dump('判断条件');
//            Debug::dump($v);
            if(!in_array($data['sub_order_type'],explode(',',$v['sub_order_type']))){
                continue;
            }            
            //出发地不匹配，下一个
            if(!StationService::isStationMatch($stationsArr[$v['from_station_id']], $fromStationData)){
                continue;
            }
            //目的地不匹配，下一个
            if(!StationService::isStationMatch($stationsArr[$v['to_station_id']], $toStationData)){
                continue;
            }
            //车型不匹配，下一个
            if($v['bus_type_id'] != $data['bus_type_id']){
                continue;
            }
            //【】
            // 20230412:加上每日待班费
            $dailyPrize = $data['days'] > 1 ? ($data['days'] - 1) * $v['daily_prize'] : 0 ;
            // 全部匹配，则返回价格 (单日费用 + 待班费)
//            Debug::dump($v);
            return $v['fixed_prize'] + $dailyPrize;
        }
        return 0;
    }

}
