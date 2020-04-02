<?php

function cut_len($str,$len=100){
    $str = preg_replace('/<img.*?src=[\"|\'](.+?)[\"|\'].*?>/', '[图片]', $str,PREG_PATTERN_ORDER);
    $subject = strip_tags($str);//去除html标签
    $pattern = '/\s/';//去除空白
    $content = preg_replace($pattern, '', $subject);
    $seodata= mb_substr($content, 0, $len,"utf-8");//截取80个汉字
    /*因为中英文的长度不一致，在英文下加一个空格*/
   // $seodata = preg_replace('/(\w)(\w)/', '${1} ${2} ', $seodata);
    return $seodata;
}
//写入文件内容
if(!function_exists('Writef'))
{
    function Writef($file,$str,$mode='w')
    {
        if(file_exists($file) && is_writable($file))
        {
            $fp = fopen($file, $mode);
            flock($fp, 3);
            fwrite($fp, $str);
            fclose($fp);

            return TRUE;
        }
        else if(!file_exists($file))
        {
            $fp = fopen($file, $mode);
            flock($fp, 3);
            fwrite($fp, $str);
            fclose($fp);
        }
        else
        {
            return FALSE;
        }
    }
}

//变量转义
function _RunMagicQuotes(&$svar)
{
    if(!get_magic_quotes_gpc())
    {
        if( is_array($svar) )
        {
            foreach($svar as $_k => $_v) $svar[$_k] = _RunMagicQuotes($_v);
        }
        else
        {
            if( strlen($svar)>0 && preg_match('#^(cfg_|GLOBALS|_GET|_POST|_COOKIE|_SESSION)#',$svar) )
            {
                exit('Request var not allow!');
            }
            $svar = addslashes($svar);
        }
    }
    return $svar;
}

//显示信息
if(!function_exists('ShowMsg'))
{
    function ShowMsg($msg='', $gourl='-1')
    {
        if($gourl == '-1')
            echo '<script>alert("'.$msg.'");history.go(-1);</script>';

        else if($gourl == '0')
            echo '<script>alert("'.$msg.'");location.reload();</script>';

        else
            echo '<script>alert("'.$msg.'");location.href="'.$gourl.'";</script>';
    }
}

/*获取文章图片*/
function get_img($content='',$img=''){
    global $cfg_weburl;
   if(!empty($img)){
       $img=strpos($img, "http") === false ? $cfg_weburl.'/'.$img:$img;
       return $img;
   }else if(!empty($content)){
       preg_match_all('/<img .*?src=[\"|\'](.+?)[\"|\'].*?>/', $content, $strResult, PREG_PATTERN_ORDER);
       if(!empty($strResult[1][0])){
           $img=strpos($strResult[1][0], "http") === false ? $cfg_weburl.$strResult[1][0]:$strResult[1][0];
       }else{
           $img=Pcommon.'/images/nopicture.png';
       }
   }else{
       $img=Pcommon.'/images/nopicture.png';

   }
    return $img;

}

//获取IP
if(!function_exists('GetIP'))
{
    function GetIP()
    {
        static $ip = NULL;
        if($ip !== NULL) return $ip;

        if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip  = trim($arr[0]);
        }
        else if(isset($_SERVER['HTTP_CLIENT_IP']))
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        else if(isset($_SERVER['REMOTE_ADDR']))
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        //IP地址合法验证
        $ip = (false !== ip2long($ip)) ? $ip : '0.0.0.0';
        return $ip;
    }
}
function object2array($object) {
    if (is_object($object)) {
        foreach ($object as $key => $value) {
            $array[$key] = $value;
        }
    }
    else {
        $array = $object;
    }
    return $array;
}


/*分页*/
function pageList($page,$num,$page_num=10,$isstatic=false){
    $pages=ceil($num/$page_num);
    //echo $num.'<br>';
    //echo $pages;
    $key= isset($_REQUEST['keyword']) ? $_REQUEST['keyword']: '';
    //获取除page参数外的其他参数
    $query_str = explode('&',$_SERVER['QUERY_STRING']);

    if($query_str[0] != '')
    {
        $query_strs = '';

        foreach($query_str as $k)
        {
            $query_str_arr = explode('=', $k);

            if(strstr($query_str_arr[0],'page') == '')
            {
                $query_str_arr[0] = isset($query_str_arr[0]) ? $query_str_arr[0] : '';
                $query_str_arr[1] = isset($query_str_arr[1]) ? $query_str_arr[1] : '';

                //伪静态设置
                if($isstatic && empty($key))
                {
                    $query_strs .=  '-'.$query_str_arr[1];
                }
                else
                {
                    $query_strs .= $query_str_arr[0].'='.$query_str_arr[1].'&';
                }
            }
        }

        $url = ($isstatic && empty($key)) ? '?'.$query_strs:'?'.$query_strs;
    }
    else
    {
        $url = '?';
    }

    $pageset= ($isstatic && empty($key)) ? "page-":"page=";
    if($pages==1){
        $p_pre='javascript:;';
        $p_next='javascript:;';
    }else if($page==1){
        $p_pre='javascript:;';
        $p_next=$url.$pageset.($page+1);
    } else if($page==$pages){
        $p_pre=$url.$pageset.($page-1);
        $p_next='javascript:;';
    } else{
        $p_pre=$url.$pageset.($page-1);
        $p_next=$url.$pageset.($page+1);
    }

    switch ($pages){
        case 0:
            $p_list=array('');
            break;
        case 1 :
            $p_list=array(1);
            break;
        case 2 :
            $p_list=[1,2];
            break;
        case 3 :
            $p_list=[1,2,3];
            break;
        case 4 :
            $p_list=[1,2,3,4];
            break;
        default:
            if($page<3){//无法判断值是否等于1,1为true，
                $p1=$page;
            }else if($page==$pages||$page==($pages-1)){
                $p1=($page-3);
            }else{
                $p1=($page-1);
            }
            $p2= $p1+1;
            $p3=$p2+1;
            $p4=$p3+1;
            $p_list=[$p1,$p2,$p3,$p4];
            break;
    }
    $first_page=$pages>=1?$url.$pageset."1":"javascript:;";
    $last_page=$pages>=1?$url.$pageset.$pages:"javascript:;";

    $pagelist=array('num'=>$num,'pages'=>$pages,'page'=>$page,'p_pre'=>$p_pre,'p_next'=>$p_next,'first_page'=>$first_page,'last_page'=>$last_page,'url'=>$url,'p_list'=>$p_list);
    return $pagelist;

}


?>