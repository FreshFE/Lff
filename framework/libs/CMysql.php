<?php
/**
 *autor: cuity
 *func:  mysql操作类
 *
*/
class CMysql {
   
    var $conn  = null;    //连接ID
    var $RsId  = null;    //资源ID
    var $nCols = null;    //总列数
    var $nRows = null;    //总行数
    
    function __construct($server="127.0.0.1",$user="root",$pswd="",$database=null,$encode="utf8",$timeout=3)
    {  
        $this->Connect($server,$user,$pswd,$database,$encode,$timeout);  
    }
    function Connect($server="127.0.0.1",$user="root",$pswd="",$database=null,$encode="utf8",$timeout=3)
    {
        ini_set("mysql.connect_timeout", $timeout);
        $this->conn = mysql_connect($server,$user,$pswd);
        if($database) mysql_select_db($database,$this->conn);
        mysql_query("Set Names ".$encode);
        return $this->conn?$this->conn:false;
    }
    function PConnect($server="127.0.0.1",$user="root",$pswd="",$database="",$encode="utf8")
    {  $this->conn = mysql_pconnect($server,$user,$pswd);
        if($database) mysql_select_db($database,$this->conn);
        mysql_query("Set Names ".$encode);
        return $this->conn?$this->conn:false;
    }
    function Execute($sql)
    {  return mysql_query($sql);
    }
    function getAll($sql, $pkkey=true, $isint=false, $rstype=MYSQL_ASSOC)    //获取记录集,返回一个数组,是否含字段名
    { $rsArr = array();
        $rsid = mysql_query($sql,$this->conn);    
        if(!$rsid) return false;
        if(mysql_affected_rows() <= 0) return $rsArr;
        $fieldArr = $this->getFields($sql);
        // print_r($fieldArr);
        $this->nCols = mysql_num_fields($rsid);        //总列数
        while($row = mysql_fetch_array($rsid, $rstype)) 
        { foreach($row as $filed=>&$val) {
            if(1==intval($fieldArr[$filed]['zfill']) && $isint) $val = floatval($val);
        }
        $rsArr[] = $row;
        }
        if($pkkey) {
        $rsArr = $this->addPKKey($rsArr, $fieldArr);
        }
        return $rsArr;
    }
    function getRow($sql,$rstype=MYSQL_ASSOC)    //一条记录
    {  
        $rsid = mysql_query($sql,$this->conn);
        if(!$rsid) return false;
        $this->nCols = mysql_num_fields($rsid);   //总列数
        $row = mysql_fetch_array($rsid,$rstype);
        return $row;
    }
    //分页模式
    function getMore($sql,$page,$rPage,$rstype=MYSQL_ASSOC)
    { $rsArr = array();
        $start = $rPage*($page-1);  //开始位置
        
        if(strpos($sql,'limit')<=0) {
        //preg_match("/limit\s+?([0-9]*),{0,1}\s*?([0-9]*)/si", $sql, $arr);
        $sql  = preg_replace("/limit.*/i","",$sql);
        $sql .= " Limit $start,$rPage";
        }
        $rsid = mysql_query($sql,$this->conn);
        if(!$rsid) return false;
        if(mysql_affected_rows() <= 0) return $rsArr;
        $fieldArr = $this->getFields($sql);
        $this->nCols = mysql_num_fields($rsid);        //总列数
        while ($row = mysql_fetch_array($rsid,$rstype)) 
        { foreach($row as $filed=>&$val) {
            if(1 == intval($fieldArr[$filed]['zfill'])) $val = floatval($val);
        }
        $rsArr[] = $row;
        }
        unset($rsid);
        return $rsArr;
    }
    //将pk作为$dataArr的key
    private function addPKKey($dataArr, $fieldArr)
    {
        $pkArr = array();
        foreach($fieldArr as $field=>$arr) {
        if(1 == $arr['pkey'])$pkArr[] = $field;
        }
        if(is_array($pkArr) && count($pkArr)>0) {
        $rstArr = array();
        foreach($dataArr as $row) {
            $pkkeyArr = array();
            foreach($pkArr as $pk) {
            $pkkeyArr[] = $row[$pk];
            }
            $pkkeys = implode('_', $pkkeyArr);
            $rstArr[$pkkeys] = $row;
        }
        return $rstArr;
        }
        return $dataArr;
    }
    function getColumns($sql='')  //获取字段个数√
    { //$rsid = mysql_query($sql);
        //return @mysql_num_fields($rsid);    
        return $this->nCols;
    }
    function getCount($sql)    //获取记录个数---OK√
    {  //$sql = strtolower($sql);    
        $funArr  = array("sum","count","min","max","avg","distinct","group", "union");    //聚合函数    
        foreach($funArr as $fun)
        {   $fun = strpos($sql,$fun);
            if($fun)break;
        }
        $sqlType = preg_match("/[a-z]*\S/si",$sql,$sqlTypeArr);
        $sqlType = strtolower($sqlTypeArr[0]);
        if($sqlType=="select" && !$fun)//(为select型,且没有聚合函数)
        {  
        preg_match("/limit\s+?([0-9]*),{0,1}\s*?([0-9]*)/si", $sql, $arr);
        array_shift($arr);
        //print_r($arr);
        if($arr) {
            $count = $arr[1]?$arr[1]:$arr[0];
            return $count;
        }
        $sqlmax = preg_replace("/limit.*?,.*?[0-9]+/si","",$sql);  //max表示该sql语句查询出的最大行数
        $sqlmax = preg_replace("/trim\(.*?\)/si", "", $sqlmax, 1);
        $sqlmax = preg_replace("/select.*?\sfrom\s/si","Select Count(*) as count From ",$sqlmax,1);
        //echo "$sqlmax <br/>";
        $rsmax = mysql_query($sqlmax,$this->conn);
        if(!$rsmax)return false;
        $row = mysql_fetch_array($rsmax);
        if(!$row)return false;      
        $max = $row['count'];    //查询出该语句最大行数      
        preg_match_all("/limit.*?([0-9]+).*?,.*?([0-9]+)/si",$sql,$cntArr);//找出limit后的两个数字
        $start = @$cntArr[1][0]?$cntArr[1][0]:0;    //查询的开始位置  
        $count = @$cntArr[2][0]?$cntArr[2][0]:$max;  //limit后的查询个数
        $max = ($max>$count)?$count:$max;//如果limit后的个数比最大行要小则采用limit后的那个数
        $nRows = $max-$start;
        unset($sqlmax,$rsmax,$row,$max,$start,$count);
        }else { 
        $this->Execute($sql);
        $nRows = mysql_affected_rows($this->conn);  
        }
        return $nRows;
    }
    function getRows($tblName)
    {  $sql = "select count(*) as cnt from $tblName";
        $rs  = $this->getAll($sql);
        //print_r($rs);
        return $rs[0]['cnt'];
    }
    function getFields($sql='')      //获取字段名(rsArr是一个带字段名的二维数组)√
    { $fieldArr = array();    //字段数组
        if($sql!="")        //如果记录集为空则按照sql语句来查询
        { $pos = strpos($sql,"select");
        if($pos ||$pos===0) {
            $sql  = preg_replace("/limit[\s,0-9]*/si","limit 1",$sql);
            $sufx = substr($sql, -strlen("limit 1"));
            if("limit 1" != $sufx) {
            $sql .= " limit 0";
            }
        }
        $frsid = mysql_query($sql);
        if(!$frsid)return false;
        $f = 0;
        do {
            $meta  = mysql_fetch_field($frsid,$f); 
            //$fLen  = mysql_field_len($frsid,$f); 
            $fName = $meta -> name;
            $fType = $meta -> type;
            $fLen  = $meta -> max_length;
            $fPKey = $meta -> primary_key;
            $fNNull= $meta -> not_null;
            $fNum  = $meta -> numeric;
            $fUniq = $meta -> unique_key;
            $fIdx  = $meta -> multiple_key;
            $zfill = $meta -> zerofill;
    
            
            $tArr = array("name"  => $fName,
                        "type"  => $fType,
                        "len"   => $fLen,
                        "pkey"  => $fPKey,
                        "nnull" => $fNNull,
                        "num"   => $fNum,
                        "uniq"  => $fUniq,
                        "idx"   => $fIdx,
                        "zfill" => $zfill,);
            $fieldArr[$fName] = $tArr;
            $f++;
        }while(@mysql_field_name($frsid,$f));
        unset($frsid);
        }else
        return false;  
        //print_r($fieldArr);
        return $fieldArr;
    }
    function getDesc($tblName)
    { $fieldArr = array();    //字段数组
        $sql = "show columns from $tblName";
        $rs  = $this->getAll($sql);
        for($i=0,$max=count($rs);$i<=$max-1;$i++)
        { $row=$rs[$i];
        $typeall = $row["Type"];
        $type    = preg_replace("/\(.*?\)/i", "", $typeall);
        $len     = trim($typeall,")");
        $len     = preg_replace("/.*?\(/i", "", $len);
        $tArr["name"] = $row["Field"];
        $tArr["type"] = $type;
        $tArr["len"]  = $len;
        $tArr["pkey"] = $row["Key"];
        $tArr["nnull"]= $row["Null"];      
        $tArr["uniq"] = $row["Key"];
        $tArr["pos"]  = $i;  //字段位置
        $fieldArr[] = $tArr;
        }
        //print_r($fieldArr);
        return $fieldArr;
    }
    function getTypes($sql)
    { 
        $typeArr = array();    //字段数组
        $trsid = mysql_query($sql);
        $f = 0;
        do{
            $typeArr[] = mysql_field_type($trsid,$f);
            $f++;
        }while(@mysql_field_name($trsid,$f));
        unset($trsid);
        return $typeArr;
    }
    
    function getAffcRows()    //获取记录个数√
    { return mysql_affected_rows($this->conn);
    }   
    function getError()
    {  return mysql_error();
    }
    function transStart()      //事务启动
    {  return $this->Execute("START TRANSACTION");
    }
    function transCommit()    //事务提交
    {  return $this->Execute("COMMIT");
    }
    function transBack()  //事务回滚
    {  return $this->Execute("ROLLBACK");
    }
    function getServerInfo()
    { return mysql_get_server_info($this->conn);
    }
    function getClientInfo()
    { return mysql_get_client_info();
    }
    function getProtoInfo()
    { return mysql_get_proto_info($this->conn);
    }
    function getQueryInfo()
    { return mysql_info($this->conn);
    }
    function newDB($dbName)
    { return mysql_create_db($dbName,$this->conn);
    }
    function dropDB($dbName)
    { return mysql_drop_db($dbName,$this->conn);
    }
    function close()
    { return mysql_close($this->conn);
    }   
    function test()
    {  return $this->conn;
    }
};
