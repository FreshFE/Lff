<?php

class CHtml {

    /** 
       * $infoArr  = array("page"=>$page, "rPage"=>$rPage, "totle"=>$totle, "url"=>url);
    */
    static function makePage($infoArr)
    { 
        $htmlpage = "";
        extract($infoArr);
        $url    = isset($url)?$url:'?1';
        $urlInfo= parse_url($url);
        //print_r($urlInfo);
        $params = $urlInfo['query'];
        $arr1   = explode("?", $url);
        $baseurl= isset($arr1[0])?$arr1[0]:'';
        parse_str($params, $paraList);

        unset($paraList['page']);
        $oldquery = "";
        $formquery = "";
        foreach($paraList as $key=>$val) {
        //重新组装url 参数
        $oldquery .= "$key=$val&";
        $formquery.= "<input type='hidden' name='$key' value='$val' />";
        }
        $oldquery = trim($oldquery, "&");

        $pages  = ceil($total/$rPage);
        $page   = ($page<=$pages)?$page:$pages;
        $pPage  = ($page-1<1)?1:$page-1;
        $nPage  = ($page+1>$pages)?$pages:$page+1;
        $str_curr  = "第{$page}/{$pages}页";
        //$str_pages = "共{$pages}页"; 
        $str_rows = "共{$total}行"; 
        $link_first= "<a href='$baseurl?$oldquery&page=1'>首页</a>";
        $link_first= ($page>1)?$link_first:"<label style='color:#888;display:inline;'>首页</label>";
        $link_prev = "<a href='$baseurl?$oldquery&page=$pPage'>上一页</a>";
        $link_prev = ($page>1)?$link_prev:"<label style='color:#888;display:inline;'>上一页</label>";
        $link_next = "<a href='$baseurl?$oldquery&page=$nPage'>下一页</a>";
        $link_next = ($page<$pages)?$link_next:"<label style='color:#888;display:inline;'>下一页</label>";
        $link_end  = "<a href='$baseurl?$oldquery&page=$pages'>末页</a>";
        $link_end  = ($page<$pages)?$link_end:"<label style='color:#888;display:inline;'>末页</label>";
        $maxlen = strlen($pages);
        $jump_page = "
        <form action='$baseurl?$oldquery' style='display:inline' method='get' >
          转到<input name='page' id='page' value='$nPage' maxlength='$maxlen' style='width:0.6cm;font-size:12px; text-align:center; border:0 solid #369;border-bottom:1px solid #369;outline:none;background:rgba(0,0,0,0) ' onkeyup=\"trimValue(event,this,'[^0-9\\\.]')\" />
          <input type='submit' value='页 确定' hidefocus style='outline:none;cursor:pointer; border:0 solid #369; padding:0; margin-bottom:-3px; background:rgba(0,0,0,0) ' >
          $formquery
        </form>
        ";
        $htmlpage  = "$str_rows $str_curr $link_first|$link_prev|$link_next|$link_end $jump_page";
        return $htmlpage;
    }
}
?>