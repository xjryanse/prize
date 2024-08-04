<?php

namespace xjryanse\prize\service\rule;

/**
 * 分页复用列表
 */
trait FieldTraits{
    public function fId() {
        return $this->getFFieldValue(__FUNCTION__);
    }
    
    public function fPerPrize() {
        return $this->getFFieldValue(__FUNCTION__);
    }

}
