<?php

class CAutoLoad {

    static $debug = true;

    static $IncludedDirs = null; //被设置的include dirs

    static $pathArr = array(
                'framework',
                'framework/libs',
                'framework/libs/db',
                'framework/base',
                'framework/http',
                'framework/curl',
                'framework/cache',
                'framework/web',
                'framework/plugins/*',
            );

    /*
    *  desc: 
    *
    *@paths --- array(...) 绝对路径
    *
    *
    */
    static function setAutoLoad($paths=array())
    {
        $realdir = dirname(__FILE__);
        foreach(self::$pathArr as &$_p){
            $_p = $realdir.'/'.$_p;
        }
        // print_r(self::$pathArr);

        if(is_array($paths) && count($paths)){
            self::$pathArr = array_merge(self::$pathArr, $paths);
        }

        self::importIncludePath(self::$pathArr);

        spl_autoload_register(array('CAutoLoad', 'autoLoadFrameClass'));
        spl_autoload_register(array('CAutoLoad', 'autoLoadSmartyClass'));
    }

    static function importIncludePath($pathArr)
    {
        // if(self::$IncludedDirs)return;
        // $realdir = dirname(__FILE__);
        $PATHSPE = PATH_SEPARATOR; //冒号(:)
        foreach($pathArr as $path) {
            if('*' == substr($path,-1)){
                $path = rtrim($path, '*');
                // $path = realpath($realdir.'/'.$path);
                self::walkdir($path, $subDirs);
                foreach($subDirs as $subpath) {
                    set_include_path(get_include_path().$PATHSPE.$subpath);
                }
            }else{
                // $path = realpath($realdir.'/'.$path);
                set_include_path(get_include_path().$PATHSPE.$path);
            }
        }
        self::$IncludedDirs = get_include_path();
    }

    static function walkdir($dir, &$subDirs=array()) 
    {
        if(!is_dir($dir)) return;
        $subDirs[] = $dir;
        $handler = opendir($dir);
        while(false !== ($filename = readdir($handler)))
        {
            if($filename != '.' && $filename != '..' && $filename != 'System Volume Information') {
                $fullpath = rtrim($dir,'/').'/'.$filename;
                if(is_dir($fullpath)) {
                    self::walkdir($fullpath, $subDirs);
                }
            }
        }
        closedir($handler);
        return;
    }
    /**
     *
     * 根据include_path检查文件是否存在
     * 如果存在返回绝对路径
     * @param string $filename 文件名
     * @return string|false 文件不存在返回false，存在返回绝对路径
     *
     */
    static function fileExists($filename)
    {
        // 检查是不是绝对路径
        if (realpath($filename) == $filename) {
            return $filename;
        }
        //否则，当作相对路径判断处理
        /* 把获取到的include_path中的\替换成/
         * 避免假如路径结尾出有\，导致判断出现错误
        */
        $paths = explode(PATH_SEPARATOR, str_replace('\\', '/', get_include_path()));
        foreach ($paths as $path) {
            if(substr($path, -1) == '/') {
                $fullpath = $path . $filename;
            }else {
                $fullpath = $path . '/' . $filename;
            }
            if(file_exists($fullpath)) {
                return realpath($fullpath);
            }
        }
        return false;
    }

    static function autoLoadFrameClass($class) 
    {
        $class = ucfirst($class);
        $cfile = $class . '.php';

        if(!class_exists($class) && self::fileExists($cfile)){
            require $cfile;
        }
    }
    // static function autoLoad...($class) {} //在此扩展

    
    static function autoLoadSmartyClass($class) 
    {
        $class = strtolower($class);
        $cfile = $class . '.php';

        if(!class_exists($class) && self::fileExists($cfile)){
            require $cfile;
            return;
        }

        //只有最后一个函数才抛以下异常
        if(self::$debug){
            echo '<pre>';
            throw new Exception("Auto Load Error", 1);
            echo '</pre>';
        }
        //end exception
    }

};

