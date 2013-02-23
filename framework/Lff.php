<?php
/**
 * autor: cty@20120325
 *  desc: global class Lff
 *
 *
*/
class Lff {
    
    static $App     = null;
    static $session = null;
    
    public static function makeApp($fpcfg)
    {
        return new CApp($fpcfg);
    }
    
    public static function setApp($App)
    {
        if(null===self::$App){
            self::$App = $App;
            return true;
        }else{
            exit('can not create app!');
        }
        return false;
    }
    public static function getApp()
    {
        return self::app();
    }
    public static function App()
    {
        return self::$App;
    }
    
    public static function setSession($session)
    {
        if(null===self::$session){
            self::$session = $session;
            return true;
        }
        return false;
    }
    public static function getSession()
    {
        if(null===self::$session){
            self::$session = $session;
            return true;
        }
        return false;
    }
};

