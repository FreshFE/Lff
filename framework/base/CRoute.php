<?php
/**
 * athor: cty@20120322
 *  func: requestion handler,eg. url,system variables and so on
 *  desc: loc---address(dir) of location
 *        url---address of web
 * 
*/
abstract class CRoute extends CEle {
    
    private $configArr = array();

    private $route     = null; //route = routedir + routedir + ctrlid + actionid
    private $routedir  = null;
    private $ctrlid    = '';
    private $actionid  = '';

    private $URLMODE   = 2; //url style[1-compility, 2-REST]
    private $subApp    = 'primary';
    
    //address(dir) of web bellow
    public $homeUrl    = '';
    public $itemUrl    = '';
    public $ctrlUrl    = '';
    public $viewUrl    = '';
    public $layoutUrl  = '';
    public $configUrl  = '';
    public $assetsUrl  = '';
    public $imgsUrl    = '';
    public $jsexUrl    = '';
    public $cssxUrl    = '';
    //address(dir) of model bellow
    public $modelLoc   = '';
    public $cacheLoc   = '';
    //address(dir) of location bellow
    public $homeLoc    = '';
    public $appLoc     = '';
    public $primaryLoc = '';
    public $daoLoc     = '';
    public $ctrlLoc    = '';
    public $viewLoc    = '';
    public $layoutLoc  = '';
    public $configLoc  = '';
    public $assetsLoc  = '';
    public $imgsLoc    = '';
    public $jsexLoc    = '';
    public $cssxLoc    = '';
    
    public $tplLoc     = ''; //smarty templates base dir
    
    public $routeKey   = 'path';
    
    public $params     = array();
    
    public $dsArr      = array(); //db server
    
    public function __construct($fpcfg)
    {
        $this->trimConfig($fpcfg);
    }
    private function trimConfig($fpcfg)
    {
        $cfgArr = $this->loadConfig($fpcfg);
        if(!isset($cfgArr['homeUrl']) || !isset($cfgArr['projectLoc'])) {
            exit('The baseUrl or baseLoc don\'t set!');
        }
        $subApp           = isset($cfgArr['subApp'])?$cfgArr['subApp']:'primary';
        $this->subApp     = $subApp;
        $this->URLMODE    = isset($cfgArr['URLMODE'])?$cfgArr['URLMODE']:2;
        
        $this->projectLoc = rtrim($cfgArr['projectLoc'], '/');
        $_PJLOC           = $this->projectLoc;
        $appLoc           = $this->projectLoc . '/'. $subApp;

        $this->homeUrl    = rtrim($cfgArr['homeUrl'], '/');
        $this->homeLoc    = $_PJLOC. '/'. $subApp;
        $this->daoLoc     = $_PJLOC. '/dao';
        $this->appLoc     = $appLoc;
        $this->primaryLoc = $this->projectLoc . '/primary'; //item = project
        $this->ctrlLoc    = $this->appLoc.'/controller';
        $this->viewLoc    = $this->appLoc.'/view';
        $this->layoutLoc  = $this->appLoc.'/layout';
        $this->configLoc  = $this->appLoc.'/config';
        $this->assetsLoc  = $this->appLoc.'/assets';
        $this->imgsLoc    = $this->assetsLoc.'/imgs';
        $this->jsexLoc    = $this->assetsLoc.'/js';
        $this->cssxLoc    = $this->assetsLoc.'/css';
        $this->modelLoc   = $this->appLoc.'/model';
        $this->cacheLoc   = $this->appLoc.'/data/cache';
        $this->tplLoc     = $this->appLoc.'/smarty';
        
        $this->itemUrl    = $this->homeUrl;
        $this->ctrlUrl    = $this->itemUrl.'/controller';
        $this->viewUrl    = $this->itemUrl.'/view';
        $this->layoutUrl  = $this->itemUrl.'/layout';
        $this->configUrl  = $this->itemUrl.'/config';
        $this->assetsUrl  = $this->itemUrl.'/assets';
        $this->imgsUrl    = $this->assetsUrl.'/imgs';
        $this->jsexUrl    = $this->assetsUrl.'/js';
        $this->cssxUrl    = $this->assetsUrl.'/css';
        
        
        isset($cfgArr['params']) && $this->params = $cfgArr['params'];
        isset($cfgArr['dsArr'])  && $this->dsArr  = $cfgArr['dsArr'];
        // $this->trimReq(); // CReq
    }
    private function loadConfig($fpcfg)
    {
        if(is_string($fpcfg)) {
            $cfgArr = require($fpcfg);
        }else{
            $cfgArr = $fpcfg;
        }
        $this->configArr = $cfgArr;
        return $cfgArr;
    }
    public function getConfig($key, $default=null)
    {
        $cfgArr = &$this->configArr;
        // if(null === $key) return $cfgArr; //这样比较危险
        return isset($cfgArr[$key])?$cfgArr[$key]:$default;
    }
    public function getUserConfig($key=null, $default=null)
    {
        $cfgArr = &$this->configArr;
        $userConfig = isset($cfgArr['user'])?$cfgArr['user']:null;
        if(null === $key){
            return $userConfig;
        }
        if($userConfig){
            return isset($userConfig[$key])?$userConfig[$key]:$default;
        }
        return $default;
    }
    /*
    * func: get default routor
    * desc: 1, if subApp is 'primary' then default routor is 'site'
    *       2, if subApp isn't 'primary' then default routor is subApp
    */
    private function getDefaultCtrlName()
    {
        $subApp = $this->subApp;
        return 'primary'==$subApp?'site':$subApp;
    }
    // format request url
    public function trimReq()
    {
        $sArr = $_SERVER;
        $baseUrl  = $this->baseUrl;
        $fullUrl  = 'http://'.$sArr['HTTP_HOST'].$sArr['REQUEST_URI'];
        $assetUrl = $baseUrl .'/'.'assets';
        $imgUrl   = $assetUrl.'/'.'imgs';
        $jsUrl    = $assetUrl.'/'.'js';
        $cssUrl   = $assetUrl.'/'.'cs';
        
        $this->fullUrl  = $fullUrl;
        $this->assetUrl = $assetUrl;
        $this->imgUrl   = $imgUrl;
        $this->jsUrl    = $jsUrl;
        $this->cssUrl   = $cssUrl;
    }
    /**
    * author: cty@20120326
    *   func: create url
    *@route   --- string(controller/action)
    *@paraArr --- string url paramters
    * reutrn: url;
    */
    public function makeUrl($route=null, $paraArr=array())
    {
        $baseUrl = $this->homeUrl;
        $dft_ctrl_name = $this->getDefaultCtrlName();
        if(null === $route){
            $route = $dft_ctrl_name.'/default';
        }else{
            if(false === strpos($route,'/')){
                if(0 == strlen($route)) {
                    $route = $dft_ctrl_name.'/default';
                }else {
                    $route .= '/default';
                }
            }
        }
        unset($paraArr[$this->routeKey]);
        foreach($paraArr as $k=>&$v) {
            if(!is_string($k)) unset($paraArr[$k]);
        }
        $anchor='';
        if(isset($paraArr['#'])) {
            $anchor='#'.$paraArr['#'];
            unset($paraArr['#']);
        }
        $route = trim($route,'/');
        $query = http_build_query($paraArr);
        $query = (strlen($query)>0)?'&'.$query:'';
        if(2 == $this->URLMODE) {
            $query    = trim($query, '&');
            $routeUrl = $baseUrl.'/'.$route.'/?'.$query.$anchor;
        }else {
            $routeUrl = $baseUrl.'/?path='.$route.$query.$anchor;
        }
        $routeUrl = trim($routeUrl, '?');
        $routeUrl = trim($routeUrl, '/');
        // $routeUrl = str_replace('/default', '', $routeUrl);
        return $routeUrl;
    }
    /**
    * author: cty@20120326
    *   func: create url 
    *@route --- format:dir1/dir2/.../ctrlId/viewId
    *           getVCLoc('d1/d2/d3/table/struct')
    *           Array
    *            (
    *                [0] => D:\btweb\lffdemo/controller/d1/d2/d3/ctrlTable.php
    *                [1] => D:\btweb\lffdemo/view/d1/d2/d3/table/struct.php
    *            )
    *@getid --- int 1:controller file, 2:view file, null both
    * reutrn: array(ctrlLoc,viewLoc);
    */
    function getVCFile($route, $getid=null)
    {
        $appLoc  = $this->appLoc;
        $dft_ctrl_name = $this->getDefaultCtrlName();
        $route   = trim($route, '/');
        if(false === strpos($route,'/')){
            if(0 == strlen($route)) {
                $route = $dft_ctrl_name.'/default';
            }else {
                $route .= '/default';
            }
        }
        $segArr = explode('/', $route);
        $len    = count($segArr);
        $viewId = $segArr[$len-1];
        $ctrlId = $segArr[$len-2];
        unset($segArr[$len-1], $segArr[$len-2]);
        $dirList = implode('/', $segArr);
        $dirList = ''==$dirList?'':$dirList.'/';
        $ctrlLoc = $appLoc.'/controller/'.$dirList.'K'.ucfirst($ctrlId).'.php';
        $viewLoc = $appLoc.'/view/'.$dirList.$ctrlId.'/'.$viewId.'.php';
        $locArr  = array($ctrlLoc, $viewLoc);
        if(null === $getid){
            return $locArr;
        }elseif(1 == $getid){
            return $ctrlLoc;
        }elseif(2 == $getid){
            return $viewLoc;
        }
        return $locArr;
    }
    function getRoute()
    {
        if($this->route){
            return $this->route;
        }
        // $this->debug($_SERVER);
        $dft_ctrl_name = $this->getDefaultCtrlName();
        $route = $this->get('path');
        if(empty($route) && isset($_SERVER['REDIRECT_STATUS']) && isset($_SERVER['REQUEST_URI'])) {
            //process REST style urls
            //REST style's urls append to rewrite of APACHE
            $route = $_SERVER['REQUEST_URI'];
            //http://www.smartyhub.com/aaa/bbb/?t=139123456 -- route=/aaa/bbb/?t=139123456
            //so, need remove '?' and '?' after characters.
            $route = preg_replace("/\?.*/si", '', $route);
        }
        $route = trim($route, '/');
        if(empty($route)) {
            $this->route = $dft_ctrl_name.'/default';
            return $this->route;
        }

        //add on 20120927
        // $this->debug($route);
        $ctrlLoc = $this->ctrlLoc;
        
        $rArr = explode('/', $route);
        $dirs = $_dir = $lastDir = '';
        foreach ($rArr as $k=>$dir) {
            $_dir .= $dir.'/';
            if(is_dir($ctrlLoc.'/'.$_dir)){
                unset($rArr[$k]);
                $dirs .= $dir.'/';
                $lastDir = $dir;
                continue;
            }
            break;
        }
        
        $rArr = array_slice($rArr, 0, 2);
        switch (count($rArr)) {
            case 0:
                $defaultController = empty($lastDir)?$dft_ctrl_name:$lastDir;
                $rArr = array($defaultController,'default');  break;
            case 1:
                $rArr[1] = 'default';  break;
        }
        // $this->debug($rArr);
        // $this->debug($dirs);

        $realRoute = implode('/', $rArr);
        // $this->debug($realRoute);
        $route = $dirs . $realRoute;
        $this->routedir = $dirs;
        $this->route    = $route;
        // $this->debug($route);
        //end add on 20120927
        return $route;
    }
    
    function getRouteDir()
    {
        return $this->routedir;
    }
    function getCtrlId()
    {
        // $realRoute = str_replace($this->routedir, '', $this->route);
        $arr = explode('/', $this->route);
        return $arr[count($arr)-2];
    }
    function getActionId()
    {

    }
    function get($key, $default=null)
    {
        if(isset($_GET[$key])) return $_GET[$key];
        return $default;
    }
    function gets($keys, $default=null)
    {
        $keys = trim($keys, ',');
        $keyArr  = explode(',', $keys);
        $dataArr = array();
        foreach($keyArr as $key){
            $dataArr[$key] = $this->get($key, $default);
        }
        return $dataArr;
    }
    function post($key, $default=null)
    {
        if(isset($_POST[$key])) return $_POST[$key];
        return $default;
    }
    function posts($keys, $default=null)
    {
        $keys = trim($keys, ',');
        $keyArr  = explode(',', $keys);
        $dataArr = array();
        foreach($keyArr as $key){
            $dataArr[$key] = $this->post($key, $default);
        }
        return $dataArr;
    }
    
    public function addJScript($jstext)
    {
        $this->jbuffArr[] = $jstext;
    }
    public function addJSFile($jfile)
    {
        $key = md5($jfile);
        $this->jbuffArr[$key] = $jfile;
    }
    public function addCSSFile($cfile)
    {
        $key = md5($cfile);
        $this->$cbuffArr[$key] = $cfile;
    }
    
    public function parseUrl($url)
    {
        $locArr = parse_url($url);
        print_r($locArr);
    }
    public function urlAddPara($key, $val, $url=null)
    {
        if(!is_string($url)) $url = $_SERVER['REQUEST_URI'];
    
        $locArr = parse_url($url);
        // print_r($locArr);
        $anchor = isset($locArr['fragment'])?'#'.$locArr['fragment']:'';
        $bname  = basename($locArr['path']);
        if(isset($locArr['query'])) {
            parse_str($locArr['query'], $pArr);
            $pArr[$key] = $val;
            $paras  = http_build_query($pArr);
            $newurl = "$bname?$paras"; //ÉÏ´Îurl
        }else {
            $newurl = "$bname?$key=$val";
        }
        return $newurl.$anchor;
    }
    
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
    * desc: 为每页面生成一个js文件(实际按控制器)
    *
    */
    public function makePagejs($jsname='', $ctrl=null)
    {
        $jsloc = $this->jsexLoc;
        $dir   = $this->getRouteDir();
        if(null === $ctrl){
            $ctrl  = $this->getCtrlId();
        }

        $file  = $jsloc.'/'.ltrim($dir,'/') . '/' . $ctrl. '/'. $jsname.'.js';
        if(!is_file($file)){
            $suffArr = explode('/', trim(trim($dir.'/').'/'.$ctrl, '/'));
            $prexdir = $jsloc;
            foreach($suffArr as $_d){
                $prexdir = $prexdir.'/'. $_d;
                if(!is_dir($prexdir)) {
                    if(!mkdir($prexdir)) {
                        break;
                    }
                }
            }
        }

        $jsurl = $this->jsexUrl; 
        $jsurl = $jsurl . ltrim($dir,'/') . '/' . $ctrl. '/'. $jsname.'.js';
        return $jsurl;
    }
};