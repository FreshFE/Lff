<?php
/**
 * desc: sql where构造器
 *       注意:1, 此类是一个非常特殊的一个类,因为它是提供链式操作的一系统公共方法，
 *               因此大多数方法的返回值都是需要返回的类对象;
 *           2, 此类是为了解决数据库模型类的公共方法的提炼;
 *           3, 此类中的方法依赖数据库模型类中的性和方法;
 *
 *
*/
class CWhere{

    private $inst    = null;

    function __construct($inst=null)
    {
        $this->inst = $inst;
    }
    function setInst($inst)
    {
        $this->inst = $inst;
    }
    function getInst()
    {
        return $this->inst;
    }

    /*
    *desc: 将字段和值安全化
    *      1, 如果字段不存在则字段值当成字符串处理
    *@record  --- array(一行记录)
    *@rmfield --- bool:是否去除多余字段
    *@quot    --- bool:是否在值的两端加上引号
    *return void
    */
    public function valueSafize(&$record, $xArr=array())
    {
        if(empty($record))return;
        $quot     = isset($xArr['quot'])?isset($xArr['quot']):false;
        $rmfield  = isset($xArr['rmfield'])?isset($xArr['rmfield']):false;

        $typeArr  = $this->inst->typeArr;
        $fieldArr = array_keys($typeArr);
        foreach($record as $f=>&$v) {
            if(!in_array($f, $fieldArr)) {
                if($rmfield){
                    unset($record[$f]);
                }else{
                    if($quot) $v = "'".$v."'";
                }
                continue;
            }
            if(is_array($v)){
                $v = json_encode($v);
                if($quot) $v = "'".$v."'";
                continue;
            }
            $_type = $typeArr[$f]['type'];
            if(in_array($_type, $this->inst->intTypes)){
                $v = intval($v);
                continue;
            }
            if(in_array($_type, $this->inst->floatTypes)){
                if(isset($typeArr[$f]['xArr'])){
                    $xArr = $typeArr[$f]['xArr'];
                    $v = number_format(floatval($v),$xArr['len2'],$xArr['dot'],'');
                    continue;
                }
            }
            $v = addslashes($v);
            if($quot) $v = "'".$v."'";
        }
        return $record;
    }

    public function where($cdts='')
    {
        return $this->wh($cdts);
    }
    public function wh($cdts='')
    {
        $this->inst->cdtions = $cdts;
        return $this->inst;
    }
    /*
    *desc: add bracks for where conditions
    *@pos --- string([start]|end|both)
    *return: this
    */
    public function brack($pos='start')
    {
        if('start' == $pos){
            $this->inst->cdtions = '(' . $this->inst->cdtions;
        }elseif('end'==$pos){
            $this->inst->cdtions = $this->inst->cdtions . ')';
        }else{
            $this->inst->cdtions = '('.$this->inst->cdtions.')';
        }
        return $this->inst;
    }
    public function eq($f, $v, $op='and')
    {
        $rArr = array($f=>$v);
        $this->valueSafize($rArr,array('quot'=>true));
        $v = $rArr[$f];
        $this->inst->cdtions .= " $op $f=$v";
        return $this->inst;
    }
    private function _lt($f, $v, $eq=false, $op='and')
    {
        $rArr = array($f=>$v);
        $this->valueSafize($rArr,array('quot'=>true));
        $v = $rArr[$f];
        $_l = $eq?'=':'';
        $this->inst->cdtions .= " $op $f<$_l$v";
        return $this->inst;
    }
    public function lt($f, $v, $op='and')
    {
        return $this->_lt($f, $v, false, $op);
    }
    public function lte($f, $v, $op='and')
    {
        return $this->_lt($f, $v, true, $op);
    }
    private function _gt($f, $v, $eq=false, $op='and')
    {
        $rArr = array($f=>$v);
        $this->valueSafize($rArr,array('quot'=>true));
        $v = $rArr[$f];
        $_l = $eq?'=':'';
        $this->inst->cdtions .= " $op $f>$_l$v";
        return $this->inst;
    }
    public function gt($f, $v, $op='and')
    {
        return $this->_gt($f, $v, false, $op);
    }
    public function gte($f, $v, $op='and')
    {
        return $this->_gt($f, $v, true, $op);
    }
    public function in($f, $inArr, $op='and')
    {
        $type = $this->inst->typeArr[$f]['type'];
        if(in_array($type, $this->inst->intTypes)){
            foreach($inArr as &$v){
                $v = intval($v);
            }
            $ins = implode(',', $inArr);
        }elseif(in_array($type, $this->inst->floatTypes)){
            foreach($inArr as &$v){
                $xArr = $this->inst->typeArr[$f]['xArr'];
                $v = number_format(floatval($v),$xArr['len2'],$xArr['dot'],'');
            }
            $ins = implode(',', $inArr);
        }else/*(in_array($type, $this->inst->strTypes))*/{
            foreach($inArr as &$v){
                $v = addslashes($v);
            }
            $ins = "'".implode("','", $inArr)."'";
        }
        $this->inst->cdtions .= " $op $f in ($ins)";
        return $this->inst;
    }
    public function between($f, $min, $max, $op='and')
    {
        $min = addslashes($min);
        $max = addslashes($max);
        $this->inst->cdtions .= " $op $f between $min and $max";
        return $this->inst;
    }
    public function like($f, $v, $islike=true, $op='and')
    {
        $v = addslashes($v);
        $notlike = $islike?'':'not';
        $this->inst->cdtions .= " $op $f $notlike like '%$v%'";
        return $this->inst;
    }
    public function null($f, $isnull=true, $op='and')
    {
        $isnull = $isnull?'':'not';
        $this->inst->cdtions .= " $op $f is $isnull null";
        return $this->inst;
    }

    public function order()
    {
    }
    public function group()
    {
    }
    public function having()
    {
    }

    public function show($echo=true)
    {
        if($echo) {
            echo $this->inst->cdtions;
        }
        return $this->inst->cdtions;
    }

    public function getCdtions()
    {
        $cdtions = trim($this->inst->cdtions);
        $cdtions = preg_replace("/^\s*and/i", '', $cdtions);
        $cdtions = preg_replace("/^\s*\(\s*(?:and|or|between|in)\s+/i", '(', $cdtions);
        $this->inst->cdtions = $cdtions;
        return $cdtions;
    }

};