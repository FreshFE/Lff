<?php
/**
 * author: cty@20120301
 *   desc: mysql数据库pdo管理类
 *   call: $db = new CPdb(array(...));
 *         $db->方法名(...);
 *
 *
 *
 *
*/


class CPdb {
    
    private $pdb     = null;
    private $error   = null;
    private $warning = null;
    private $sqls    = array();

    private $iWhere  = null;

    private $dsn     = 'mysql:dbname=test;host=127.0.0.1';
    private $user    = 'root';
    private $pswd    = '123456';

    function __construct($paraArr=array())
    {
        $this->connect($paraArr);
    }

    private function connect($paraArr)
    {
        if(null === $this->pdb) {
            extract($paraArr);
            $dsn  = isset($dsn)?$dsn:$this->dsn;
            $user = isset($user)?$user:$this->user;
            $pswd = isset($pswd)?$pswd:$this->pswd;
            try {
                $this->pdb = new PDO($dsn, $user, $pswd, array(PDO::ATTR_TIMEOUT=>3));
                $this->execute('set names utf8');
            }catch (PDOException $e) {
                $this->error = $e->getMessage();
                $this->pdb = null;
            }
        }
    }
    public function getOne($table, $pArr=array())
    {
        if(null === $this->pdb) return false;
        $pArr['limit'] = 1;
        $sql   = $this->generate_query_sql($table, $pArr);
        $query = $this->pdb->query($sql);
        $this->sqls[] = $sql;
        $row   = $query->fetch(PDO::FETCH_ASSOC);
        return $row;
    }
    /**
    * func: select sql
    * desc: used for select statement primary
    *
    */
    public function getMore($table, $pArr=array())
    {
        if(null === $this->pdb) return false;
        $sql = $this->generate_query_sql($table, $pArr);
        $this->sqls[] = $sql;
        return $this->query($sql);
    }
    public function getAll($table, $pArr=array())
    {
        return $this->getMore($table, $pArr);
    }
    public function query($sql)
    {
        if(null === $this->pdb) return false;
        $query  = $this->pdb->query($sql);
        if($query) {      
            $rstArr = $query->fetchAll(PDO::FETCH_ASSOC);
            return $rstArr;
        }else {
            $errArr = $this->pdb->errorInfo();
            $this->error = $errArr[2];
            return false;
        }
    }
    private function generate_query_sql($table, $pArr)
    {
        $pArr   = empty($pArr)?array():$pArr;
        $page   = isset($pArr['page'])?$pArr['page']:1;
        $limit  = isset($pArr['limit'])?$pArr['limit']:50;
        $fields = isset($pArr['fields'])?$pArr['fields']:'*';
        $where  = isset($pArr['where'])?$pArr['where']:'';
        $order  = isset($pArr['order'])?$pArr['order']:'';
        $group  = isset($pArr['group'])?$pArr['group']:'';
        $having = isset($pArr['having'])?$pArr['having']:'';
        
        $start  = ($page-1)*$limit;
        $where  = empty($where)?'':" where {$where}";
        $order  = empty($order)?'':"order by {$order}";
        $having = empty($having)?'':"having by {$having}";
        $sql    = "select {$fields} from {$table} {$where} {$order} {$group} {$having} limit {$start},{$limit}";
        return $sql;
    }
    public function getCount($sql)
    {
        if(null === $this->pdb) return false;
        $funArr  = array("sum","count","min","max","avg","distinct","group", "union");    //聚合函数    
        foreach($funArr as $fun){
            $fun = strpos($sql,$fun);
            if($fun)break;
        }
        $sqlType = preg_match("/[a-z]*\S/si",$sql,$sqlTypeArr);
        $sqlType = strtolower($sqlTypeArr[0]);
        if($sqlType=="select" && !$fun) {
            //(为select型,且没有聚合函数)
            preg_match("/limit\s+?([0-9]*),{0,1}\s*?([0-9]*)/si", $sql, $arr);
            array_shift($arr);
            $start = $limit = 0;
            if(count($arr) > 0) {
                if(empty($arr[1])) {
                    $limit = $arr[0];
                }else {
                    $start = $arr[0];
                    $limit = $arr[1];
                }
            }
            $sqlmax = preg_replace("/limit.*?,.*?[0-9]+/si","",$sql);  //max表示该sql语句查询出的最大行数
            $sqlmax = preg_replace("/trim\(.*?\)/si", "", $sqlmax, 1);
            $sqlmax = preg_replace("/select.*?\sfrom\s/si","Select Count(*) as count From ",$sqlmax,1);
            $rsmax = $this->getOne($sqlmax);
            if(!$rsmax)return 0;
            $max = $rsmax['count'];    //查询出该语句最大行数      
            // preg_match_all("/limit.*?([0-9]+).*?,.*?([0-9]+)/si",$sql,$cntArr);//找出limit后的两个数字
            $max = $max-$start;
            $nRows = ($max>$limit)?$limit:$max;//如果limit后的个数比最大行要小则采用limit后的那个数
        }else { 
            $query = $this->pdb->query($sql);
            $nRows = $query->rowCount();
        }
        return $nRows;
    }
    /*
    * desc: execut sql
    *@type --- int(-1:delete,0:select,1:insert,2:update)
    *              select目前不存在
    */
    public function execute($sql, $type=0)
    {
        // echo "<hr/>";
        // echo $sql;
        if(null === $this->pdb) return false;
        $rs = $this->pdb->prepare($sql);
        switch ($type) {
            case -1: //delete
            case  1: //insert
                $rs->execute(); break;
            default:
                $rs->execute();
        }
        // var_dump($this->pdb->lastInsertId());
        if(!$rs) {
            $errArr = $this->pdb->errorInfo();
            $this->error = $errArr[2];
            return false;
        }else{
            if(1 == $type && is_resource($rs)){
                $temp = $rs->fetch(PDO::FETCH_ASSOC);
                if($temp){
                    $id = $temp?$temp['last_value']:false;
                    return $id;
                }
            }else{
                return $rs->rowCount();
            }
        }
    }
    private function addLimit($sql, $start=0, $limit=20)
    {
        $sql = strtolower($sql);
        if(false === strpos($sql, 'limit')) {
            $sql  = preg_replace("/limit.*/i", '', $sql);
            $sql .= " Limit {$start},{$limit}";
        }
        return $sql;
    }
    function getDesc($table)
    {
        $table = trim($table);
        $_tarr = explode(' ', $table);
        $prex  = strtolower($_tarr[0]);
        $table = '`'.trim($table,'`').'`';
        if(count($_tarr) > 1 && ('select'==$prex)) {
            //说明传的不是table name而是sql语句
            $row = $this->getOne('explain '.$table);
            $table = $row['table'];
        }

        $sql = "desc $table";
        $initArr = $this->query($sql);
        if(!$initArr) return false;
        $descArr = array();
        foreach($initArr as $row) {
            $trr = array();
            extract($row);
            //fetch length
            if(1 == preg_match("/\(([0-9]+?)\)/", $Type, $arr)) {
                $len = $arr[1];
            }else {
                $len = null;
            }
            $Type = preg_replace("/\([0-9]+?\)/", '', $Type);
            $trr['name'] = $Field;
            $trr['type'] = $Type;
            $trr['lens'] = $len;
            $trr['null'] = 'YES'==$Null?'NULL':'NOT NULL';
            $trr['prik'] = 'PRI'==$Key?'PK':'';
            $trr['unix'] = 'UNI'==$Key?'UNI':'';
            $trr['indx'] = 'MUL'==$Key?'MUL':'';
            $trr['deft'] = $Default;
            $trr['auto'] = $Extra;
            $descArr[$Field] = $trr;
        }
        /*
        [b] => Array
            (
                [name] => b
                [type] => int
                [lens] => 11
                [null] => NOT NULL
                [prik] => 
                [unix] => UNI
                [indx] => 
                [deft] => 0
                [auto] => 
            )
        */
        return $descArr;
    }
    function remove($table, $cdtArr)
    {
        return $this->delete($table, $cdtArr);
    }
    /*
    * desc: delete row
    *
    *@table   --- string table name
    *@cdtions --- str
    */
    function delete($table, $cdtions, $limit=1)
    {
        $table = '`'.trim($table, '`').'`';
        $cdtions = $cdtions?'where '.$cdtions:'';
        $sql = "delete from $table $cdtions limit $limit";
        $this->sqls[] = $sql;
        return $this->execute($sql);
    }
    /*
    * desc: update table
    *
    *@table   --- string table name
    *@valArr  --- array
    *                 field => value, ...)
    *@cdtions --- array(
    *                 field => value, ...)
    *           ||string:'field=value,...'
    */
    public function update($table, $valArr, $cdtions)
    {
        if(empty($valArr)) return false;
        $table = '`'.trim($table, '`').'`';
        //where条件...
        $cdtion = '';
        if(is_array($cdtions) && count($cdtions)>0){
            $_cdtArr = array();
            foreach($cdtions as $f=>$val) {
                // $val = "'".$val."'";
                $val = $this->valCorrectize($val);
                $f   = '`'. trim($f,'`'). '`';
                $_cdtArr[$f] = $val;
            }
            // ini_set('arg_separator.output', ' and ');
            if(!empty($cdtions)){
                $cdtion = http_build_query($_cdtArr);
                $cdtion = str_replace('&', ' and ', $cdtion);
                $cdtion = ' and '. urldecode($cdtion);
            }
        }elseif(is_string($cdtions) && strlen($cdtions)>0){
            $cdtion = ' where '. $cdtions;
        }else {
            $this->warning = 'update condition is null!';
            $cdtion = '';
        }
        //end where条件
        //要更新的值...
        ini_set('arg_separator.output', ',');
        $_valArr = array();
        foreach($valArr as $f=>$val) {
            // $val = "'".$val."'";
            $val = $this->valCorrectize($val);
            $f   = '`'. trim($f,'`'). '`';
            $_valArr[$f] = $val;
        }
        $vallist = urldecode(http_build_query($_valArr));
        $vallist = str_replace('&', ', ', $vallist);
        //end要更新的值

        $sql = "update {$table} set {$vallist} {$cdtion}";
        $this->execute('set names utf8');
        // exit($sql);
        try{
            $this->sqls[] = $sql;
            $ok = $this->execute($sql);
            if(!$ok) 
                throw new Exception();
            else
                return $ok;
        }catch(Exception $e){
            // var_dump($e);
            // $this->error = $e->getMessage();
            $this->setError();
            return false;
        }
    }
    function add($table, $dataArr)
    {
        return $this->insert($table, $dataArr);
    }
    /*
    * func: 插入一条数据
    * desc: $dataArr已经是一个整理过数据(字段不会多)
    *
    */
    function insert($table, $dataArr, $exArr=array())
    {
        $table = '`'.trim($table, '`').'`';
        $fieldlist = '`'. implode('`,`', array_keys($dataArr)) .'`';
        $valArr = array();
        foreach(array_values($dataArr) as $val) {
            $valArr[] = $this->valCorrectize($val);
        }
        $valuelist = implode(',', $valArr);

        $replaced = isset($exArr['replaced'])?$exArr['replaced']:false;
        $ignored  = isset($exArr['ignored'])?$exArr['ignored']:false;
        $optype = $replaced?'replace':'insert';
        $ignore = $ignored?'ignore':'';
        $sql = "{$optype} {$ignore} into {$table}($fieldlist) values($valuelist)";
        $this->sqls[] = $sql;
        $ok = $this->execute($sql,1);
        // return $this->getInsertId();
        // if($ok){
        //     return $this->getInsertId();
        // }
        return $ok; 
    }
    function replace($table, $dataArr)
    {
        return $this->insert($table, $dataArr, array('replaced'=>true));
    }

    /*
    * func: 插入一条数据
    * desc: $dataArr已经是一个整理过数据(字段不会多)
    *@dataArr = [row1,row2,...]
    */
    function inserts($table, $dataArr, $exArr=array())
    {
        $table = '`'.trim($table, '`').'`';
        $fieldlist = '`'. implode('`,`', array_keys($dataArr[0])) .'`';
        
        $valueArr = array();
        foreach($dataArr as $row){
            $valArr = array();
            foreach(array_values($row) as $val) {
                $valArr[] = $this->valCorrectize($val);
            }
            $val = '('.implode(',', $valArr).')';
            $valueArr[] = $val;
        }
        $valuelist = implode(',', $valueArr);

        $replaced = isset($exArr['replaced'])?$exArr['replaced']:false;
        $ignored  = isset($exArr['ignored'])?$exArr['ignored']:false;
        $optype = $replaced?'replace':'insert';
        $ignore = $ignored?'ignore':'';
        $sql = "{$optype} {$ignore} into {$table}($fieldlist) values $valuelist";
        $this->sqls[] = $sql;
        return $this->execute($sql);
    }
    private function getInsertId()
    {
        return $this->pdb->lastInsertId();
    }
    
    /*
    * desc: 正确化字段的值，如：
    *       加引号等,如果使用了mysql函数则不加引号
    */
    public function valCorrectize($val)
    {
        if(is_array($val)) return "'".json_encode($val)."'";
        $isFunction = false;;
        $matchs = preg_match("/[0-9a-z_]{2,}?\(.+\)$/si", $val); //表明这是一个mysql函数
        if($matchs > 0)return $val;
        if(is_array($val)) $val = json_encode($val);
        return "'". addslashes($val). "'";
    }
    /*
    * desc: 字段过滤
    * 
    *@fields  --- string filed list("f1,f2" or "^f1")
    *@descArr --- array  table structure info
    *return: string of field list
    */
    public function ftFields($fields, $descArr)
    {
        $fds = trim($fields);
        $distinct = null;
        if(0 === strpos(strtolower($fds), 'distinct')){
            $fds = str_replace('distinct ', '', $fds);
            $distinct = 'distinct ';
        }
        if(empty($fields) || empty($descArr) || '*'==$fields) {
            return '*';
        }
        
        $fArr     = explode(',', ltrim($fds,'^'));
        $fieldArr = array_keys($descArr);
        if(0 === strpos($fds,'^')){ //过滤模式
            $ignoreArr = $fArr;
            foreach($ignoreArr as $k=>$f){
                if(in_array($f, $fieldArr)) unset($fieldArr[$f]);
            }
            $fds = '`'.implode('`,`', $fieldArr).'`';
        }else {
            foreach($fArr as $k=>&$f){
                $matchs = preg_match("/[0-9a-z_]{2,}?\(.+\)/si", $f); //说明是mysql函数                
                if($matchs > 0)continue;
                if(!in_array($f, $fieldArr)) {
                    unset($fArr[$k]);
                }else{
                    $f = "`$f`";
                }
            }
            if(empty($fArr)){
                $fds = '*';
            }else{
                $fds = implode(',', $fArr);
            }
        }
        return $distinct ? $distinct.$fds : $fds;
    }
    /*
    * desc: 获取上次执行sql语句
    */
    public function getSql()
    {
        return implode(';', $this->sqls);
    }
    //清空sql
    public function cleanSql()
    {
        $this->sqls = array();
    }
    
    public function getError()
    {
        return $this->error;
    }
    private function setError()
    {
        if(null === $this->pdb) return false;
        $arr = $this->pdb->errorInfo();
        $this->error = $arr[2];
    }
    public function getWarning()
    {
        return $this->warning;
    }
};
