<?php
/**
 * athor: cty@20120322
 *  func: base control
 *  desc: 
 * 
 * 
*/
abstract class CCtrl extends CEle {

    protected $layout = 'layframe';
    private $route    = null; //route of current request
    private $cfile    = null; //controller file path
    private $vfile    = null; //view file path
    private $tpldir   = null; //smarty template dir
    
    private $smarty   = null;

    /*
    * desc: the bellow three methods call by Lff(CApp class)
    *
    *
    */
    public function setRoute($route)
    {
        $this->route = $route;
    }
    public function setCfile($cfile)
    {
        $this->cfile = $cfile;
    }
    public function setVfile($vfile)
    {
        $this->vfile = $vfile;
    }
    
    public function render($viewId=null, $dataArr=array())
    {
        if(null === $viewId){
            $vfile = $this->vfile;
        }else{
            $vfile = $viewId;
            if(!is_file($viewId)) {
                // $this->httpError(404, 'The view file is not exists!');
                $vfile = dirname($this->vfile).'/'. $viewId . '.php';
            }
        }
        // echo $this->vfile;
        // exit($vfile);
        if(!is_file($vfile)) {
            $this->httpError(404, 'The view file is not exists!');
        }
        //1, get view file contents
        $viewContent = $this->renderFile($vfile, $dataArr);
        //2, get layout file contents
        $layFile = Lff::$App->layoutLoc . '/'. $this->layout . '.php';
        $layContent  = $this->renderFile($layFile, array('content'=>$viewContent));
        //3, output contents
        // ob_clean();
        // echo 'fffffffffffffffff';
        list($jscript, $jsfiles) = $this->getJS();
        // var_dump($jscript, $jsfiles);
        // print_r($jscript);
        if(strlen($jscript) > 0){
            $layContent = str_replace('</head>', $jscript.'</head>', $layContent);
        }
        if(strlen($jsfiles) > 0){
            $layContent = str_replace('</body>', $jsfiles.'</body>', $layContent);
        }
        echo $layContent;
    }
    function renderView($viewId, $paraArr=array(), $return=false)
    {
        // ob_clean();
        // ob_start();
        $viewFile = $this->getViewFile($viewId);
        $content = $this->renderFile($viewFile, $paraArr);
        if(!$return) exit($content);
        return $content;
    }
    function renderLayout($layout, $paraArr=array(), $return=true)
    {
        $layFile = $this->getLayFile($layout);
        $content = $this->renderFile($layFile, $paraArr);
        if(!$return) echo $content;
        return $content;
    }
    /**
    * author: cty@20120326
    *@viewId --- string(dir1/index)
    *return: /../../dir1/index.php(absolute path) 
    */
    function getViewFile($viewId)
    {
        if(is_file($viewId)) return $viewId;
        $viewLoc  = Lff::app()->viewLoc;
        $viewId   = trim($viewId, '/');
        $viewFile = $viewLoc.'/'.$viewId.'.php';
        return $viewLoc;
    }
    function getLayFile()
    {
        $layLoc  = Lff::app()->layLoc;
        $layFile = $layLoc.'/'.$this->layout . '.php';
        return $layFile;
    }
    /**
    * author: cty@20120331
    *   func: get js from jbuffArr
    */
    function getJS()
    {
        $jbuffArr = Lff::$App->jbuffArr;
        $jscript = $jsfiles = '';
        foreach($jbuffArr as $key => $js) {
            if(is_string($key)) {
                $jsfiles .= '<script type="text/javascript" src="'.$js.'" ></script>'."\n";
            }else {
                $jscript .= $js."\n";
            }
        }
        if(strlen($jscript) > 0) {
            $jscript = "<script type='text/javascript' >\n".'$(document).ready(function(){'."\n".$jscript."\n".'});'."\n</script>\n";
        }
        return array($jscript, $jsfiles);
    }
    /**
    * author: cty@20120328
    *   func: load model by modelId
    *@modelId --- string dir1/dir2/.../modelClass
    *@subApp  --- app name(sub item name)
    */
    /*
    public function LoadModel($modelId, $subApp=null)
    {
        $modelId   = trim($modelId, '/');
        // $modelLoc  = Lff::app()->modelLoc;
        if($subApp){
            $modelLoc  = Lff::$App->projectLoc . '/'.$subApp .'/model';
        }else{
            $modelLoc  = Lff::$App->modelLoc;
        }
        $modelFile = $modelLoc.'/'.$modelId . '.php';
        if(!is_file($modelFile)) {
            $this->httpError(500, 'The model class file not exists!');
        }
        require_once($modelFile);
        
        if(false === strpos($modelId,'/')) {
            if(!class_exists($modelId,false)) {
                $this->httpError(500, 'The model class is not exists!');
            }
            $class = $modelId;
        }else {
            $dirArr = explode('/', $modelId);
            $len    = count($dirArr);
            $class  = $dirArr[$len-1];
            if(!class_exists($class,false)) {
                $this->httpError(500, 'The model class is not exists!');
            }
        }
        return new $class;
    }*/
    
    function __call($method, $args)
    {
        // $this->dump($args);
        $app = Lff::$App;
        if(method_exists($app, $method)) {
            $argc = count($args);
            switch ($argc) {
                case 1:
                    return $app->$method($args[0]);  break;
                case 2:
                    return $app->$method($args[0],$args[1]);  break;
                case 3:
                    return $app->$method($args[0],$args[1],$args[2]);  break;
                case 4:
                    return $app->$method($args[0],$args[1],$args[2],$args[3]);  break;
                case 5:
                    return $app->$method($args[0],$args[1],$args[2],$args[3],$args[4]);  break;
                case 6:
                    return $app->$method($args[0],$args[1],$args[2],$args[3],$args[4],$args[5]);  break;
                case 7:
                    return $app->$method($args[0],$args[1],$args[2],$args[3],$args[4],$args[5],$args[6]);  break;
                case 8:
                    return $app->$method($args[0],$args[1],$args[2],$args[3],$args[4],$args[5],$args[6],$args[7]);  break;
                default:
                    return $app->$method();
            }
        }else {
            return null;
        }
    }

    /*********************************smarty******************************/
    public function LoadSmarty()
    {
        if(null === $this->smarty) {
            $tpl_base_dir = Lff::$App->tplLoc;
            $this->smarty = new CSmarty($tpl_base_dir);
            $this->smarty->assign('HOME',           Lff::$App->homeUrl);
            $this->smarty->assign('ROOT',           Lff::$App->homeLoc);
            $this->smarty->assign('BOOT',           Lff::$App->homeLoc);
            $this->smarty->assign('CSS_URL',        Lff::$App->cssxUrl);
            $this->smarty->assign('IMG_URL',        Lff::$App->imgsUrl);
            $this->smarty->assign('JS_URL',         Lff::$App->jsexUrl);
            $this->smarty->assign('TPL_LOC',        Lff::$App->homeLoc.'/smarty');
            $this->smarty->assign('TPL_TEMP_LOC',   Lff::$App->homeLoc.'/smarty/templates');
            $this->smarty->assign('TPL_LAYOUT_LOC', Lff::$App->homeLoc.'/smarty/templates/layout');
            $this->smarty->assign('TPL_CACHE_LOC',  Lff::$App->homeLoc.'/smarty/cache');
            $this->smarty->assign('TPL_CFG_LOC',    Lff::$App->homeLoc.'/smarty/congigs');
        }
        return $this->smarty;
    }
    public function assign($key, $value=null, $nocache=false)
    {
        $smarty = $this->LoadSmarty();
        return $smarty->assign($key, $value, $nocache);
    }
    public function assigns($vArr, $nocache=false)
    {
        $smarty = $this->LoadSmarty();
        foreach ($vArr as $key => $value) {
            $smarty->assign($key, $value, $nocache);
        }
        return true;
    }
    public function display($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        $smarty   = $this->LoadSmarty();
        /*
        according:
        /home/sites/events.atjiehun.com/public/smarty
        /home/sites/events.atjiehun.com/public/view/demo/demo.php
        to:
        demo/demo.tpl
        */
        //echo $template;
        $viewDir  = dirname(Lff::$App->tplLoc).'/view/';
        $suff     = str_replace($viewDir, '', $this->vfile);
        $basename = basename($template);
        $basename = $basename?$basename:'default';
        $template = dirname($suff) .'/'. $basename.'.html';
        $smarty->display($template, $cache_id, $compile_id, $parent);
        exit;
    }
    /*****************************end smarty******************************/

};
