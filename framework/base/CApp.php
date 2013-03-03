<?php
/**
 * author: cty@20120324
 *
 * 
 * 
 * 
*/

class CApp extends CRoute {

    public $name        = '';
    public $charset     = 'UTF-8';
    
    private $html       = null;
    private $cacheArr   = null;

    private $modelArr   = array();
    private $dbmodelArr = array();
    
    
    public function __construct($fpcfg)
    {
        parent::__construct($fpcfg);
        Lff::setApp($this);
    
        // parent::__construct();
        /*
        $this->preinit();
    
        $this->initSystemHandlers();
        $this->registerCoreComponents();
    
        $this->configure($config);
        $this->attachBehaviors($this->behaviors);
        $this->preloadComponents();
    
        $this->init(); */
    }
    
    public function run()
    { 
        $this->init();
        $this->appLanuch();
    }
    private function appLanuch()
    {
        // $this->dump($_SERVER);
        $route = $this->getRoute();
        // echo "($route)";
        $this->runRoute($route);
    }
    public function runRoute($route)
    {
        if(false === strpos($route, '/')){
            $route .= '/default';
        }
        $rArr = explode('/', $route);  // route array
        // list($ctrlId, $viewId) = $rArr;
        $len    = count($rArr);
        $viewId = $rArr[$len-1];
        $ctrlId = $rArr[$len-2];
        unset($rArr[$len-1], $rArr[$len-2]);
        list($ctrlFile, $viewFile) = $this->getVCFile($route);
        if(is_file($ctrlFile)) {
            // echo "===$ctrlId, ($viewId)===";
            require_once $ctrlFile;
            $ctrlClass = 'K'.ucfirst($ctrlId);
            // var_dump(class_exists($ctrlClass, false));exit;
            if(class_exists($ctrlClass, false)) {
                $iCtrl = new $ctrlClass;
                $actionName = 'action'.ucfirst($viewId);
                if(method_exists($iCtrl, $actionName)) {
                    $iCtrl->setRoute($route);       //add@20120924
                    $iCtrl->setCfile($ctrlFile);    //add@20120924
                    $iCtrl->setVfile($viewFile);    //add@20120924
                    $iCtrl->$actionName($viewFile);
                }else {
                    $logs = "The view method($viewId) not exists!";
                    // $this->httpError(404, "The view method($viewId) not exists!");
                    $this->fatalError($logs);
                }
            }else {
                $logs = "The controller($ctrlId) file not exists!";
                // $this->httpError(404, "The controller($ctrlId) file not exists!");
                $this->fatalError($logs);
            }
        }else {
            $logs = "The controller($ctrlId) file not exists!";
            // $this->httpError(404, "The controller($ctrlId) file not exists!");
            $this->fatalError($logs);
        }
    }
    public function init()
    {
        header('Content-Type: text/html; charset=UTF-8');
        date_default_timezone_set('Asia/shanghai');

        $session = $this->getSession();
        $session->start();
        Lff::setSession($session);
    }
    public function getId()
    {
        if($this->_id!==null)
        return $this->_id;
        else
        return $this->_id=sprintf('%x',crc32($this->getBasePath().$this->name));
    }
    
    public function getTimeZone()
    {
        return date_default_timezone_get();
    }
    public function setTimeZone($value='Asia/Shanghai')
    {
        date_default_timezone_set($value);
    }
    
    public function getSession()
    {
        if(Lff::$session){
            return Lff::$session;
        }
        return new CSession();
    }
    public function session()
    {
        return $this->getSession();
    }

    /***********************plugin**************************/
    /*
    * desc: get mail instance
    *       phpmailer must exists
    */
    public function mail()
    {
        return new CMail();
    }
    /********************end plugin**************************/

    /**
    * author: cty@20120328
    *   func: load model by modelId
    *@modelId --- string 如果class为MUser那么modelId为user
    *@dirs    --- string dir1/dir2/.../
    *@subApp  --- app name(sub item name)
    */
    public function LoadModel($modelId, $dirs='', $subApp=null)
    {
        $class = 'M'.ucfirst($modelId);
        $id = $dirs.'_'.$class. '_'. $subApp;
        if(isset($this->modelArr[$id]) && is_object($this->modelArr[$id])){
            //防止重复加载以提高效率
            return $this->modelArr[$id];
        }
        $modelId   = trim($modelId, '/');
        // $modelLoc  = Lff::app()->modelLoc;
        if($subApp){
            $modelLoc  = $this->projectLoc . '/'.$subApp .'/model';
        }else{
            $modelLoc  = $this->modelLoc;
        }
        if($dirs){
            $dirs = trim($dirs,'/').'/';
            $modelFile = $modelLoc.'/'.$dirs.$class . '.php';
        }else{
            $modelFile = $modelLoc.'/'.$class . '.php';
        }
        if(!is_file($modelFile)) {
            $this->httpError(500, 'The model class file not exists!');
        }
        require_once($modelFile);
        if(!class_exists($class,false)) {
            $this->httpError(500, 'The model class is not exists!');
        }
        /*
        if(false === strpos($modelId,'/')) {
            if(!class_exists($class,false)) {
                $this->httpError(500, 'The model class is not exists!');
            }
            // $class = $modelId;
        }else {
            $dirArr = explode('/', $modelId);
            $len    = count($dirArr);
            // $class  = $dirArr[$len-1];
            if(!class_exists($class,false)) {
                $this->httpError(500, 'The model class is not exists!');
            }
        }*/
        return $this->modelArr[$id] = new $class;
    }
    public function LoadApiModel($modelId, $dirs='', $subApp=null)
    {
        return $this->LoadModel($modelId, $dirs, 'api');
    }

    //database model
    /**
    * author: cty@20120916
    *   func: load db model by table name and db name
    *@table   --- string table
    *@db      --- string database name
    *@App     --- app name(sub item name)
    *return: db model instance
    */
    public function LoadDbModel($table, $db=null)
    {
        $id = 'dbmodel_'. $table. '_'. $db. '_';
        // print_r($this->dbmodelArr[$id]);
        if(isset($this->dbmodelArr[$id]) && is_object($this->dbmodelArr[$id])){
            //防止重复加载以提高效率
            return $this->dbmodelArr[$id];
        }

        if(null === $db) {
            $db = Lff::App()->dsArr['master'][0]['dbName'];
        }
        /*
        if($subApp){
            $modelLoc  = Lff::$App->projectLoc . '/'.$subApp .'/model';
        }else{
            $modelLoc  = Lff::$App->modelLoc;
        }*/
        $modelLoc  = Lff::$App->daoLoc;
        // $dbmodelLoc  = Lff::app()->modelLoc . '/' . $db;
        $dbmodelLoc  = $modelLoc . '/' . $db;
        if(!is_dir($dbmodelLoc)) {
            if(!mkdir($dbmodelLoc)) return false;
        }
        $class = preg_replace("/[^0-9a-z]/si", ' ', $table);
        $class = preg_replace("/\s{2,}/si", ' ', $class);
        $class = ucwords(strtolower($class));
        $class = 'D'. str_replace(' ', '', $class);
        
        $tableLoc = $dbmodelLoc . '/' . $table . '.php';
        
        $model = new CDbModel($db, $table);
        
        if(is_file($tableLoc)) {
            require_once($tableLoc);
            if(class_exists($class, false)) {
                return $this->dbmodelArr[$id] = new $class;
            }
        }else {
            // $model = new CDbModel($db, $table);
            // return $model;
        }
        
        $template = $model->createTemplate($class);
        if($template) {
            $ok = file_put_contents($tableLoc, $template);
            if($ok){
                require_once($tableLoc);
                if(class_exists($class, false)) {
                    // echo "($tableLoc)------($class)";
                    return $this->dbmodelArr[$id] = new $class;
                }
            }
        }
        return $this->dbmodelArr[$id] = $model;
    }
    //end database model

    function getHtml()
    {
        if(is_resource($this->html)){
            return $this->html;
        }
        return new CHtml();
    }
    function makePage($infoArr)
    { 
        return CHtml::makePage($infoArr);
    }
    /*
    *desc: get cache instance
    *@cid --- str['C','D','F','M','P']
    *retrun cache instance
    */
    function getCache($cid='F', $dir='/tmp')
    {
        $id = md5('cache_'.$cid.'_'.$dir);
        if(isset($this->cacheArr[$id]) && is_resource($this->cacheArr[$id])){
            return $this->cacheArr[$id];
        }
        $class = 'C'.$cid.'Cache';

        $cache = new $class($dir);
        $this->cacheArr[$id] = $cache;
        return $cache;

    }
};
