<?php

namespace xjryanse\prize\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\logic\Debug;
use xjryanse\logic\Arrays;
use xjryanse\logic\Number;
use xjryanse\prize\service\PrizeConditionService;

/**
 * 价格规则?
 */
class PrizeFixedService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;

    use \xjryanse\traits\StaticModelTrait;

    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\prize\\model\\PrizeFixed';
    //直接执行后续触发动作
    protected static $directAfter = true;        
    // 20230710：开启方法调用统计
    protected static $callStatics = true;
    
}
