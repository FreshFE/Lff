<?php
/**
 * author: cty@20120916
 *   func: mysql数据库模型相关方法
 *   desc: 
 *
 *
*/

class CDbModel extends CModel {
    
    private $db    = null;
    private $table = null;
    private $error = null;
    
    function __construct($dbName, $table)
    {
        $this->db = $dbName;
        $this->table  = $table;
    }
    
    function __call($method, $args)
    {
        if(method_exists($this, 'getDb')) {
            $pdb = $this->getDb($this->db);
        }else {
            return null;
        }
        if(!empty($method)) {
            switch($method) {
                case 'add':
                case 'insert':
                    $rst = $pdb->insert($this->table, $args[0]);
                    break;
                case 'get':
                case 'getOne':
                    $rst = $pdb->getOne($this->table, $args[0]);
                    break;
                case 'gets':
                case 'getMore':
                case 'getList':
                    $rst = $pdb->getMore($this->table, $args[0]);
                    break;
                case 'update':
                    // Lff::$App->dump($args[1]);
                    $rst = $pdb->update($this->table, $args[0], $args[1]);
                    break;
                default:
                    if(method_exists($pdb, $method)) {
                        $rst = $pdb->$method($this->table);
                    }
            }
            if(!isset($rst)) {
                $this->error = $pdb->getError();
                return null;
            }
            return $rst;
        }else {
            $this->error = 'The method being called dose not exists!';
        }
    }
    
    /*
    * desc: 为表格创建模板
    *
    */
    public function createTemplate($class)
    {
        $db    = $this->db;
        $table = $this->table;
        if(method_exists($this, 'getDb')) {
            $pdb = $this->getDb($this->db);
        }else {
            return false;
        }
        $descArr   = $pdb->getDesc($table);
        if(empty($descArr)){
            $this->error = $pdb->getError();
            return false;
        }
        foreach($descArr as $f=>&$dArr){
            $_type = $dArr['type'];
            preg_match("/(.*?)\(([0-9]+),([0-9]+)\)/i", $_type, $pArr);
            if(4 == count($pArr)){
                //在浮点型数据中提取整数部分和小数部分的长度
                $dArr['type'] = $pArr[1];
                $dot = intval($pArr[3])>0?'.':'';
                $dArr['xArr'] = array('len1'=>$pArr[2], 'len2'=>$pArr[3], 'dot'=>$dot);
            }
        }
        // print_r($descArr);
        $fields = var_export($descArr, true);
        $fields = preg_replace("/\=\>\s*[\n\r]{0,2}/si", '=> ', $fields);
        $fields = str_replace("\n", "\n                        ", $fields);
        // echo $fields;
        
        $template  = "<?php\n";
        $template .= "/**\n";
        $template .= " * desc: {$table} model\n";
        $template .= "*/\n\n\n";
        
        $template .= "class {$class} extends CModel {\n";
        $template .= "    private \$db         = '{$db}';\n";
        $template .= "    private \$table      = '{$table}';\n";
        $template .= "    public  \$cdtions    = '';//must be public\n";
        $template .= "    public  \$strTypes   = array('char','varchar','tinytext','text','mediumtext','longtext','binary','varbinary','tinyblob','mediumblob','longblob','enum','set');\n";
        $template .= "    public  \$intTypes   = array('int','tinyint','smallint','mediumint','bigint','bit','boolean','serial');\n";
        $template .= "    public  \$floatTypes = array('decimal','float','double','real');\n";
        $template .= "    public  \$typeArr    = {$fields};\n\n";


        // $template .= "    /*\n";
        // $template .= "    *desc: 将字段和值安全化\n";
        // $template .= "    *@record --- array(一行记录)\n";
        // $template .= "    *return void\n";
        // $template .= "    */\n";
        // $template .= "    private function valueSafize(&\$record)\n";
        // $template .= "    {\n";
        // $template .= "        if(empty(\$record))return;\n";
        // $template .= "        \$typeArr  = \$this->typeArr;\n";
        // $template .= "        \$fieldArr = array_keys(\$typeArr);\n";
        // $template .= "        foreach(\$record as \$f=>&\$v) {\n";
        // $template .= "            if(!in_array(\$f, \$fieldArr)) {\n";
        // $template .= "                unset(\$record[\$f]);\n";
        // $template .= "                continue;\n";
        // $template .= "            }\n";
        // $template .= "            if(is_array(\$v)){\n";
        // $template .= "                \$v = json_encode(\$v);\n";
        // $template .= "                continue;\n";
        // $template .= "            }\n";
        // $template .= "            \$_type = \$typeArr[\$f]['type'];\n";
        // $template .= "            if(in_array(\$_type, \$this->intTypes)){\n";
        // $template .= "                \$v = intval(\$v);\n";
        // $template .= "                continue;\n";
        // $template .= "            }\n";
        // $template .= "            if(in_array(\$_type, \$this->floatTypes)){\n";
        // $template .= "                if(isset(\$typeArr[\$f]['xArr'])){\n";
        // $template .= "                    \$xArr = \$typeArr[\$f]['xArr'];\n";
        // $template .= "                    \$v = number_format(floatval(\$v),\$xArr['len2'],\$xArr['dot'],'');\n";
        // $template .= "                    continue;\n";
        // $template .= "                }\n";
        // $template .= "            }\n";
        // $template .= "        }\n";
        // $template .= "    }\n";

        /*
        $template .= "    function __call(\$method, \$args)\n";
        $template .= "    {\n";
        $template .= "        \$iWhere = new CWhere(\$this);\n";
        $template .= "        if(method_exists(\$iWhere, \$method)) {\n";
        $template .= "            \$argc = count(\$args);\n";
        $template .= "            switch (\$argc) {\n";
        $template .= "                case 0:\n";
        $template .= "                    return \$iWhere->\$method();  break;\n";
        $template .= "                case 1:\n";
        $template .= "                    return \$iWhere->\$method(\$args[0]);  break;\n";
        $template .= "                case 2:\n";
        $template .= "                    return \$iWhere->\$method(\$args[0],\$args[1]);  break;\n";
        $template .= "                case 3:\n";
        $template .= "                    return \$iWhere->\$method(\$args[0],\$args[1],\$args[2]);  break;\n";
        $template .= "                case 4:\n";
        $template .= "                    return \$iWhere->\$method(\$args[0],\$args[1],\$args[2],\$args[3]);  break;\n";
        $template .= "                case 5:\n";
        $template .= "                    return \$iWhere->\$method(\$args[0],\$args[1],\$args[2],\$args[3],\$args[4]);  break;\n";
        $template .= "                default:\n";
        $template .= "                    return \$this;\n";
        $template .= "            }\n";
        $template .= "        }else {\n";
        $template .= "            return \$this;\n";
        $template .= "        }\n";
        $template .= "    }\n";
        */     
        
        $template .= "    //添加一条数据\n";
        $template .= "    public function add(\$dataArr, \$exArr=array())\n";
        $template .= "    {\n";
        $template .= "        \$pdb = \$this->getDb(\$this->db);\n";
        $template .= "        if(!\$pdb) return false;\n";
        $template .= "        if(empty(\$dataArr) || !is_array(\$dataArr)) return false;\n";
        $template .= "        \$dataArr = \$this->valueSafize(\$dataArr, array('rmfield'=>true));\n";
        $template .= "        return \$pdb->insert(\$this->table, \$dataArr, \$exArr);\n";
        $template .= "    }\n\n";

        $template .= "    /**\n";
        $template .= "     * 添加多条数据\n";
        $template .= "     *@dataArr = [array1,array2,...]\n";
        $template .= "     *\n";
        $template .= "     *\n";
        $template .= "    */\n";
        $template .= "    public function adds(\$dataArr, \$exArr=array())\n";
        $template .= "    {\n";
        $template .= "        \$pdb = \$this->getDb(\$this->db);\n";
        $template .= "        if(!\$pdb) return false;\n";
        $template .= "        if(empty(\$dataArr) || !is_array(\$dataArr)) return false;\n";
        $template .= "        \$typeArr  = \$this->typeArr;\n";
        $template .= "        \$fieldArr = array_keys(\$this->typeArr);\n";
        $template .= "        foreach(\$dataArr as &\$row){\n";
        $template .= "            \$row = \$this->valueSafize(\$row, array('rmfield'=>true));\n";
        $template .= "        }\n";
        $template .= "        return \$pdb->inserts(\$this->table, \$dataArr, \$exArr);\n";
        $template .= "    }\n\n";
        
        //delete
        $template .= "    //删除数据\n";
        $template .= "    function remove()\n";
        $template .= "    {\n";
        $template .= "        \$pdb = \$this->getDb(\$this->db);\n";
        $template .= "        if(!\$pdb) return false;\n";
        $template .= "        return \$pdb->delete(\$this->table, \$this->cdtions);\n";
        $template .= "    }\n\n";
        
        //query one
        $template .= "    //查询一条数据\n";
        $template .= "    function getOne(\$pArr=null)\n";
        $template .= "    {\n";
        $template .= "        \$pdb = \$this->getDb(\$this->db);\n";
        $template .= "        if(!\$pdb) return false;\n";
        $template .= "        if(is_array(\$pArr) && count(\$pArr)>0){\n";
        $template .= "            if(isset(\$pArr['fields'])){\n";
        $template .= "                \$pArr['fields'] = \$pdb->ftFields(\$pArr['fields'], \$this->typeArr);\n";
        $template .= "            }\n";
        $template .= "        }\n";
        $template .= "        return \$pdb->getOne(\$this->table, \$pArr);\n";
        $template .= "    }\n\n";

        //query one
        $template .= "    //同getOne\n";
        $template .= "    function get(\$pArr=null)\n";
        $template .= "    {\n";
        $template .= "        return \$this->getOne(\$pArr);\n";
        $template .= "    }\n\n";

        //query list
        $template .= "    //同getList\n";
        $template .= "    function gets(\$pArr=null)\n";
        $template .= "    {\n";
        $template .= "        return \$this->getList(\$pArr);\n";
        $template .= "    }\n\n";
        
        //query list
        $template .= "    /* \n";
        $template .= "    * desc: 查询多条数据\n";
        $template .= "    *@pArr --- array(\n";
        $template .= "    *        'page'    -- int[1]\n";
        $template .= "    *        'limit'   -- int[50]\n";
        $template .= "    *        'fields'  -- str['*']\n";
        $template .= "    *        'where'   -- str['']\n";
        $template .= "    *        'order'   -- str['']\n";
        $template .= "    *        'group'   -- str['']\n";
        $template .= "    *        'having'  -- str['']\n";
        $template .= "    *    )\n";
        $template .= "    */\n";
        $template .= "    function getList(\$pArr=null)\n";
        $template .= "    {\n";
        $template .= "        \$pdb = \$this->getDb(\$this->db, 'slave');\n";
        $template .= "        if(!\$pdb) return false;\n";
        $template .= "        if(is_array(\$pArr) && count(\$pArr)>0){\n";
        $template .= "            if(isset(\$pArr['fields'])){\n";
        $template .= "                \$pArr['fields'] = \$pdb->ftFields(\$pArr['fields'], \$this->typeArr);\n";
        $template .= "            }\n";
        $template .= "        }\n";
        $template .= "        if(!isset(\$pArr['where'])) \$pArr['where'] = \$this->getCdtions();\n";
        $template .= "        return \$pdb->getMore(\$this->table, \$pArr);\n";
        $template .= "    }\n\n";

        
        //update
        $template .= "    //更新数据\n";
        $template .= "    function sets(\$valArr, \$cdtions=null)\n";
        $template .= "    {\n";
        $template .= "        \$pdb = \$this->getDb(\$this->db);\n";
        $template .= "        if(!\$pdb) return false;\n";
        $template .= "        if(empty(\$valArr) || !is_array(\$valArr)) return false;\n";
                          
        $template .= "        \$fieldArr = array_keys(\$this->typeArr);\n";
        $template .= "        \$dataArr  = \$this->valueSafize(\$valArr);\n";

        $template .= "        if(is_array(\$cdtions) && count(\$cdtions)>0){\n";
        $template .= "            foreach(\$cdtions as \$f=>&\$v) {\n";
        $template .= "                if(!in_array(\$f, \$fieldArr)) {\n";
        $template .= "                    unset(\$cdtions[\$f]);\n";
        $template .= "                }\n";
        $template .= "            }\n";
        $template .= "        }\n";
        $template .= "        \$cdtions = \$cdtions?\$cdtions:\$this->getCdtions();\n";
        $template .= "        return \$pdb->update(\$this->table, \$valArr, \$cdtions);\n";
        $template .= "    }\n";



        $template .= "    //获取错误信息\n";
        $template .= "    function getError()\n";
        $template .= "    {\n";
        $template .= "        return \$this->getDb(\$this->db)->getError();\n";
        $template .= "    }\n";

        $template .= "    //获取上次执行sql\n";
        $template .= "    function getSql()\n";
        $template .= "    {\n";
        $template .= "        return \$this->getDb(\$this->db)->getSql();\n";
        $template .= "    }\n\n";

        $template .= "    //清空之前执行sql\n";
        $template .= "    function cleanSql()\n";
        $template .= "    {\n";
        $template .= "        \$this->getDb(\$this->db)->cleanSql();\n";
        $template .= "    }\n\n";
        
        $template .= "};";
        $template .= "";
        
        
        return $template;
    }
    
    public function getError()
    {
        return $this->error;
    }
};

