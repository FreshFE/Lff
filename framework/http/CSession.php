<?php

class CSession {

    function start($sid=null)
    {
        session_start();
        // session_id();
    }

    function get($key, $default=null)
    {
        if(isset($_SESSION[$key])) return $_SESSION[$key];
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

    function set($key, $val)
    {
        $_SESSION[$key] = $val;
    }

    function sets($kvArr)
    {   
        foreach ($kvArr as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }

    function add($key)
    {
    }

    function clean()
    {
        session_destroy();
    }
};
