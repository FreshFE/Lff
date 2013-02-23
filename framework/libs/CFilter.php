<?php
/**
* author: cty@20121012
*   desc: 过滤字符串处理类
*
* 
*/
class CFilter {

    /*
    * desc: 将中文过滤掉
    *       $str = "abc123_测试abc"; ---> $str = "abc123_abc"
    *@str --- 应是一个标量
    *return: string
    */
    static function ftoutChinese($str)   //[a-z0-9_]
    {
        if(is_scalar($str)){
            $str = preg_replace("[\x80-\xff]", '', $str);
        }
        return $str;
    }
    /*
    * desc: 将非中文过滤掉
    *       $str = "abc123_测试abc"; ---> $str = "abc123_abc"
    *@str --- 应是一个标量
    *return: string
    */
    static function ftChinese($str)   //[a-z0-9_]
    {
        if(is_scalar($str)){
            $str = preg_replace("[^\x80-\xff]", '', $str);
        }
        return $str;
    }
    
    /*
    * desc: 将可以组成变量的字符过滤掉
    *       $str = "abc123_测试abc"; ---> $str = "测试"
    *@str --- 应是一个标量
    *return: string
    */
    static function ftoutVaraible($str)   //[a-z0-9_]
    {
        if(is_scalar($str)){
            $str = preg_replace("[a-z0-9_]", '', $str);
        }
        return $str;
    }
    /*
    * desc: 将数字过滤掉
    *       $str = "abc123"; ---> $str = "abc"
    *@str --- 应是一个标量
    *return: string
    */
    static function ftoutNum($str)        //[0-9]
    {
        if(is_scalar($str)){
            $str = preg_replace("[0-9]", '', $str);
        }
        return $str;
    }
    /*
    * desc: 将字母过滤掉
    *       $str = "abc123"; ---> $str = "123"
    *@str --- 应是一个标量
    *return: string
    */
    static function ftoutChar($str)       //[a-z]
    {
        if(is_scalar($str)){
            $str = preg_replace("[a-z]", '', $str);
        }
        return $str;
    }
    /*
    * desc: 将非可以组成变量的字符过滤掉
    *       $str = "abc123_测试abc"; ---> $str = "abc123_abc"
    *@str --- 应是一个标量
    *return: string
    */
    static function ftVariable($str)      //[^a-z0-9_]
    {
        if(is_scalar($str)){
            $str = preg_replace("[^a-z0-9_]", '', $str);
        }
        return $str;
    }
    /*
    * desc: 将非数字过滤掉
    *       $str = "abc123"; ---> $str = "123"
    *@str --- 应是一个标量
    *return: string
    */
    static function ftNum($str)           //[^0-9]
    {
        if(is_scalar($str)){
            $str = preg_replace("[^0-9]", '', $str);
        }
        return $str;
    }
    /*
    * desc: 将非字母过滤掉
    *       $str = "abc123"; ---> $str = "abc"
    *@str --- 应是一个标量
    *return: string
    */
    static function ftChar($str)          //[^a-z]
    {
        if(is_scalar($str)){
            $str = preg_replace("[^a-z]", '', $str);
        }
        return $str;
    }
    /*
    * desc: 自定义正则表达式的过滤
    *       $str = "abc123"; ---> $str = "abc"
    *@patt --- string
    *@str  --- 应是一个标量
    *return: string
    */
    static function ftPatt($patt, $str)          //patt
    {
        if(is_scalar($patt)){
            $str = preg_replace("$patt", '', $str);
        }
        return $str;
    }
};
