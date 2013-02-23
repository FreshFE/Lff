<?php
/**
 * author: cty@20120328
 *   func: the basest model class
 *   desc: user model class must extend this class
 *
 *
*/

abstract class CModel {
    
    private   $serverid = 0;    //当前选中是哪号主机
    private   $pdbArr   = null; //记录连接id --- array('id'=>pdb对象,...)
    protected $dbName   = null;
    
    protected function getDb($dbName=null, $type='master')
    {
        $this->dbName = $dbName;
        $dbArr = $this->getServer($type, $realtype); //realtype(虽然传的是slave但实际并没有配置slave,这时type实际为master)
        if(empty($dbArr)) return false;
        $cid   = md5($realtype . json_encode($dbArr));
        // print_r($dbArr);

        if(empty($this->pdbArr[$cid])) { //如果两次连接信息一样则不用再次连接数据库
            $this->pdbArr[$cid] = new CPdb($dbArr);
            return $this->pdbArr[$cid];
        }
        return $this->pdbArr[$cid];
    }
    protected function getServer($type='master', &$realtype=null)
    {
        if(!in_array($type, array('master', 'slave'))) return false;
        $dsArr = Lff::$App->dsArr;
        //如果slave不存在则取master
        if(isset($dsArr[$type]) && !empty($dsArr[$type])) {
            $realtype = $type;
            $_dsArr = $dsArr[$type];
        }else{
            $realtype = 'master';
            $_dsArr = $dsArr['master'];
        }
        if('master' == $type) {
            $sid = 0;
        }else {
            //随机选择slave服务器
            $len = count($_dsArr);
            $sid = rand(0, $len-1);
        }
        $this->serverid = $sid;
        $dbInfo = $_dsArr[$sid]; // array
        $dbName = null===$this->dbName?$dbInfo['dbName']:$this->dbName;
        $dsn    = 'mysql:dbname='.$dbName.';host='.$dbInfo['host'];
        $dbInfo['dsn'] = $dsn;
        return $dbInfo;
    }

    public function getServerId()
    {
        return $this->$serverid;
    }



    protected $iWhere = null;
    function __call($method, $args)
    {
        if(null === $this->iWhere){
            $iWhere = new CWhere($this);
        }else{
            $iWhere = $this->iWhere;
        }
        if(method_exists($iWhere, $method)) {
            $argc = count($args);
            switch ($argc) {
                case 0:
                    return $iWhere->$method();  break;
                case 1:
                    return $iWhere->$method($args[0]);  break;
                case 2:
                    return $iWhere->$method($args[0],$args[1]);  break;
                case 3:
                    return $iWhere->$method($args[0],$args[1],$args[2]);  break;
                case 4:
                    return $iWhere->$method($args[0],$args[1],$args[2],$args[3]);  break;
                case 5:
                    return $iWhere->$method($args[0],$args[1],$args[2],$args[3],$args[4]);  break;
                default:
                    return $this;
            }
        }else {
            return $this;
        }
    }

};
