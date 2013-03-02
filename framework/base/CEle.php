<?php
/**
 * athor: cty@20120322
 *  func: basest class
 *  desc: CEle --- class elements
 *        renderFile
 * 
*/
abstract class CEle {
    public $jbuffArr = array();  //javascript buffer
    public $cbuffArr = array();  //css buffer
    
    
    function renderFile($file, $paraArr=array())
    {
        if(!is_file($file)) return '';
        ob_clean();
        ob_start();
        ob_implicit_flush(false); //don't flush
        if(is_array($paraArr) && count($paraArr)>0) {
            extract($paraArr, EXTR_PREFIX_SAME, 'rend');
        }
        require($file);
        return ob_get_clean();
    }
    
    public function httpError($code=500, $errmsg='')
    {
        header("HTTP/1.0 $code");
        exit($errmsg);
    }
    public function writeLog($logs, $mod="a", $dir='/tmp')
    {
        $fpfile = $dir.'/Lff.'.date("Ymd").'.log';
        $fp = fopen($fpfile, $mod);
        fputs($fp, $logs."\n");
        fclose($fp);
    }
    public function fatalError($error, $code=500)
    {
        $this->writeLog($error);
        if($code){
            $this->httpError($code);
        }
        exit(0);
    }
    
    public function getMethods($obj)
    {
        if(is_resource($obj)) {
            $mArr = get_class_methods($obj);
            $this->debug($mArr);
        }
    }

    //对象转数组
    function object2array($obj){
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        foreach ($_arr as $key => $val){
            $val = (is_array($val) || is_object($val)) ? $this->object2array($val) : $val;
            $arr[$key] = $val;
        }
        return $arr;
    }
    //数组转对象
    function array2object($arr){
        return new ArrayObject($arr);
    }

    //将html中的相对路径转换为绝对路径
    public function relative2absolute($html, $domain) {
        preg_match('/(http|https|ftp):\/\//', $domain, $protocol); 
        $server_url = preg_replace("/(http|https|ftp|news):\/\//", "", $domain); 
        $server_url = preg_replace("/\/.*/", "", $server_url); 
        if ($server_url == '') { 
            return $html; 
        } 
        if (isset($protocol[0])) { 
            $new_html = preg_replace('/href="\//', 'href="'.$protocol[0].$server_url.'/', $html); 
            $new_html = preg_replace('/src="\//', 'src="'.$protocol[0].$server_url.'/', $new_html); 
        }else { 
            $new_html = $html; 
        }
        return $new_html;
    }
    
    public function debug($val, $exit=false)
    {
        echo '<pre>';
        print_r($val);
        echo '</pre>';
        $exit && exit(1);
    }
    public function dump($val, $exit=false)
    {
        $this->debug($val, $exit);
    }
};
