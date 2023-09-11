<?php
namespace xjryanse\prize\logic;

use app\order\service\OrderBaoBusService;
use app\third\gaode\Map as GaodeMap;
use xjryanse\prize\service\PrizeRuleService;
use xjryanse\prize\service\PrizeBaoFixedService;
use xjryanse\system\service\SystemCompanyService;
use app\station\service\StationService;
use app\route\service\RouteBaseService;
use xjryanse\logic\Arrays;

/**
 * 包车报价逻辑
 */
class BaoPrizeLogic
{
    /**
     * 20230411：计算包车价格：
     * 先取固定线路；再取按公里估价
     * @param type $stationsRaw
     * @param type $busTypeId
     * @param type $planStartTime
     * @param type $planFinishTime
     * @param type $subOrderType
     * @param type $miles
     * @return type
     */
    public static function calPrize($stationsRaw, $busTypeId, $planStartTime, $planFinishTime, $subOrderType, $miles){
        // 20230411:固定线路没匹配到，再匹配通用的计价规则
        $tmp = self::calByPrizeRule($stationsRaw, $busTypeId, $planStartTime, $planFinishTime, $subOrderType, $miles);
        
//        //先匹配固定线路
//        $tmp = self::calByPrizeFixed($stationsRaw, $busTypeId, $planStartTime, $planFinishTime, $subOrderType, $miles);
//        if(!$tmp['prizeAll']){
//            // 20230411:固定线路没匹配到，再匹配通用的计价规则
//            $tmp = self::calByPrizeRule($stationsRaw, $busTypeId, $planStartTime, $planFinishTime, $subOrderType, $miles);
//        }
        return $tmp;
    }

    /**
     * 20230411:根据通用计价规则计算
     */
    protected static function calByPrizeRule($stationsRaw, $busTypeId, $planStartTime, $planFinishTime, $subOrderType, $miles){

        $calDataArr = self::getDataForCalculate($stationsRaw, $busTypeId, $planStartTime, $planFinishTime, $subOrderType, $miles);
        
        $prizeData  = PrizeRuleService::getBaoPrizeWithFormula($calDataArr);

        $tmp                = [];
        // 标识
        $tmp['prizeCalCate']    = 'calByPrizeRule';
        //TODO：如果还有其他费用，怎么处理？？？
        // 单车待班费
        $tmp['prizeBase']   = $prizeData['dataForm']['base'] ;
        // 单车公里费
        $tmp['prizeMile']   = $prizeData['dataForm']['mile'];
        //单车补贴费
        $tmp['prizeDriver'] = $prizeData['dataForm']['driver'];
        //单车总价
        $tmp['prizeAll']    = $prizeData['prize'];
        $tmp['formula']     = $prizeData['formula'];
        //20230424
        $tmp['$prizeData']     = $prizeData;
        $tmp['$calDataArr']    = $calDataArr ;
        
        return $tmp;
    }
    /**
     * 20230411:根据固定线路计价规则计算
     */
//    protected static function calByPrizeFixed($stationsRaw, $busTypeId, $planStartTime, $planFinishTime, $subOrderType, $miles){
//        // 20230428：停用
//        return false;
////        $customerId = '';
////        $data = OrderBaoBusService::dataForPrizeCalculateByDataArr($customerId,$planStartTime, $planFinishTime, $subOrderType, $busTypeId, $miles);
////        
////        //取第一点和最后一点;
////        $start  = $stationsRaw[0];
////        $end    = array_reverse($stationsRaw)[0];
////
////        $startArr   = (new GaodeMap())->regeo( $start['longitude'] , $start['latitude'] );
////        $endArr     = (new GaodeMap())->regeo( $end['longitude'] , $end['latitude'] );
////        
////        $data['fromStationArr'] = $startArr['regeocode']['addressComponent'];
////        $data['toStationArr']   = $endArr['regeocode']['addressComponent'];
////        $data['sub_order_type'] = $subOrderType;
////        $data['bus_type_id']    = $busTypeId;
//        // 用于计算价格的基础数据
//        $data = self::getDataForCalculate($stationsRaw, $busTypeId, $planStartTime, $planFinishTime, $subOrderType, $miles);
//        //总价
//        $tmp                = [];
//        // 标识
//        $tmp['prizeCalCate']    = 'calByPrizeFixed';
//        //TODO：如果还有其他费用，怎么处理？？？
//        // 单车待班费
//        $tmp['prizeBase']   = '——' ;
//        // 单车公里费
//        $tmp['prizeMile']   = '——';
//        //单车补贴费
//        $tmp['prizeDriver'] = '——';
//        //单车总价
//        $tmp['prizeAll']    = PrizeBaoFixedService::getPrize($data);
//        $tmp['formula']     = '';
//        // 20230411:价格
//        $tmp['$data']       = $data ;
//        return $tmp;
//    }
    /**
     * 获取用于计算的参数
     */
    protected static function getDataForCalculate($stationsRaw, $busTypeId, $planStartTime, $planFinishTime, $subOrderType, $miles){
        $customerId         = '';
        $calDataArr = OrderBaoBusService::dataForPrizeCalculateByDataArr($customerId,$planStartTime, $planFinishTime, $subOrderType, $busTypeId, $miles);
        // 20230427:途经站点数量
        $calDataArr['stationsCount']  = count($stationsRaw);
        // 【1】拼接公司地点数据
        $sessionCompInfo    = SystemCompanyService::getInstance(session(SESSION_COMPANY_ID))->get();
        // 20230414:增加是否出省逻辑。
        // 20230414:公司所在省
        $calDataArr['companyBaseProvince']  = Arrays::value($sessionCompInfo, 'province');
        // 20230414:公司所在市
        $calDataArr['companyBaseCity']      = Arrays::value($sessionCompInfo, 'city');
        // 20230414:公司所在县
        $calDataArr['companyBaseDistrict']  = Arrays::value($sessionCompInfo, 'district');
        //【2】带参数解析地址数据
        $stationsWithRego = StationService::getStationArrWithRegeo($stationsRaw);
        // $calDataArr['$stationsWithRego']    = $stationsWithRego;
        // 计算出发地匹配的站点id
        $calDataArr['fromStationIds']   = StationService::getMatchStationIdArr($stationsWithRego[0], 'bao');
        $calDataArr['toStationIds']     = StationService::getMatchStationIdArr($stationsWithRego[count($stationsWithRego) - 1], 'bao');
        //【3】计算是否出省；市；县
        $provinces  = Arrays::unsetEmpty(array_unique(array_column($stationsWithRego,'province')));
        $cities     = Arrays::unsetEmpty(array_unique(array_column($stationsWithRego,'city')));
        $districts  = Arrays::unsetEmpty(array_unique(array_column($stationsWithRego,'district')));
//        
//        $calDataArr['$provinces']   = $provinces;
//        $calDataArr['$cities']      = $cities;
//        $calDataArr['$districts']   = $districts;

        // 20230414: 是否出省
        $calDataArr['isOutProvince']    = count($provinces) == 1 && in_array($sessionCompInfo['province'],$provinces) ? 0 :1;
        // 20230414：是否出市
        $calDataArr['isOutCity']        = count($cities) == 1 && in_array($sessionCompInfo['city'], $cities) ? 0 :1;
        // 20230414：是否出县
        $calDataArr['isOutDistrict']    = count($districts) == 1 && in_array($sessionCompInfo['district'], $districts) ? 0 :1;
        // 【4】出发地和目的地的经纬度解析数据
        $calDataArr['fromStationArr'] = $stationsWithRego[0];
        $calDataArr['toStationArr']   = $stationsWithRego[count($stationsWithRego) - 1];
        $calDataArr['sub_order_type'] = $subOrderType;
        $calDataArr['bus_type_id']    = $busTypeId;
        
        // 20230428：拆线路明细表
        // $matchTearRouteList             = RouteBaseService::matchTearRouteList($calDataArr['fromStationIds'], $calDataArr['toStationIds']);
        // $calDataArr['$matchTearRouteList'] = $matchTearRouteList;
        // $calDataArr['matchRouteIds']    = array_column($matchTearRouteList, 'matchRouteId');
        $calDataArr['matchRouteIds']    = RouteBaseService::matchRouteIds($calDataArr['fromStationIds'], $calDataArr['toStationIds']);
        // 路线匹配的价格分组
        $calDataArr['prizeGroupIds']    = RouteBaseService::idsGetPrizeGroupIds($calDataArr['matchRouteIds']);

        return $calDataArr;
    }
}
