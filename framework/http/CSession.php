<?php

class CSession {

    function start($sid=null)
    {
        if(!isset($_SESSION))session_start();
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

    function pushMessage($msg)
    {
        $_SESSION['___sessionMessage'] = $msg;
    }
    function flushMessage()
    {
        if(isset($_SESSION['___sessionMessage'])){
            $msg = $_SESSION['___sessionMessage'];
            unset($_SESSION['___sessionMessage']);
            return $msg;
        }
        return null;
    }
};
