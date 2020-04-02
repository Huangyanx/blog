<?php
/*
 * 文章内容的处理：
 * 远程下载图片等
 *
 * */

/*
 * 远程图片本地化
 * 下载图片，文章的图片地址替换为本地的
 * */
function transform_local_img($content){
     $dosql=new Model();
    $img_array = array();
    preg_match_all("/src=[\"|'|\s]{0,}((http:|https:)\/\/([^>]*)\.(gif|jpg|png|bmp))/isU",$content,$img_array);
    $imgs=array_unique($img_array[1]);
    $imgPath=UPLOADS.'/image';
    $imgUrl=UPLOADS_IMG.'/'.date('Ymd',time());
    $milliSecond=date('YmdHis',time());
    //print_r($imgs);
    if(!is_dir($imgUrl.'/'))
    {
        mkdir($imgUrl,0777);
        //MkdirAll($imgPath,$GLOBALS['cfg_dir_purview']);
    }
    $host='http://'.$_SERVER['SERVER_NAME'];
   $new_img=array();
    foreach($imgs as $key=>$value)
    {
        $value = trim($value);
        if(preg_match("#".$host."#i", $value) || !preg_match("#^(http:|https:)\/\/#i", $value))
        {
            continue;
        }
        $itype =  substr($value, -4, 4);
        if( !preg_match("#\.(gif|jpg|png)#", $itype) ) $itype = ".jpg";

        $rndFileName = $imgUrl.'/'.$milliSecond.'-'.$key.$itype;
        $iurl = '/'.$imgUrl.'/'.$milliSecond.'-'.$key.$itype;
        //下载并保存文件
        $rs = DownImageKeep($value, $value, $rndFileName, '', 0, 30);
        if($rs)
        {
            $new_img[]=$iurl;
            //图片替换
            $content = str_replace($value, $iurl , $content);
           // echo $content;
           // exit();
            $info='';
            $imginfos = GetImageSize($rndFileName, $info);
            $fsize = filesize($rndFileName);
            $filename = $milliSecond.'-'.$key.$itype;
            //保存图片附件信息
            $inquery = "INSERT INTO `#@__uploads`(name,path,size,type,posttime)
            VALUES ('$filename','$iurl','$fsize','$itype','".time()."'); ";
            $field="name,path,size,type,posttime";
            $arr=array($filename,$iurl,$fsize,$itype,time());
            $dosql->add('uploads',$field,$arr);
        }
    }
    return array('body'=>$content,'img'=>$new_img);
}

function DownImageKeep($gurl, $rfurl, $filename, $gcookie="", $JumpCount=0, $maxtime=30)
{
    $urlinfos = GetHostInfo($gurl);
    if(isset($urlinfos['scheme'])){
        $rs=getHttpsImg($gurl,$filename);
    }else{
        $ghost = trim($urlinfos['host']);
        if($ghost=='')
        {
            return FALSE;
        }
        $gquery = $urlinfos['query'];
        if($gcookie=="" && !empty($rfurl) && !isset($urlinfos['scheme']))
        {
            $gcookie = RefurlCookie($rfurl);
        }
        $sessionQuery = "GET $gquery HTTP/1.1\r\n";
        $sessionQuery .= "Host: $ghost\r\n";
        $sessionQuery .= "Referer: $rfurl\r\n";
        $sessionQuery .= "Accept: */*\r\n";
        $sessionQuery .= "User-Agent: Mozilla/4.0 (compatible; MSIE 5.00; Windows 98)\r\n";
        if($gcookie!="" && !preg_match("/[\r\n]/", $gcookie))
        {
            $sessionQuery .= $gcookie."\r\n";
        }
        $sessionQuery .= "Connection: Keep-Alive\r\n\r\n";
        $errno = "";
        $errstr = "";

        $m_fp = fsockopen($ghost, 80, $errno, $errstr,10);


        fwrite($m_fp,$sessionQuery);
        $lnum = 0;

        //获取详细应答头
        $m_httphead = Array();
        $httpstas = explode(" ",fgets($m_fp,256));
        $m_httphead["http-edition"] = trim($httpstas[0]);
        $m_httphead["http-state"] = trim($httpstas[1]);
        while(!feof($m_fp))
        {
            $line = trim(fgets($m_fp,256));
            if($line == "" || $lnum>100)
            {
                break;
            }
            $hkey = "";
            $hvalue = "";
            $v = 0;
            for($i=0; $i<strlen($line); $i++)
            {
                if($v==1)
                {
                    $hvalue .= $line[$i];
                }
                if($line[$i]==":")
                {
                    $v = 1;
                }
                if($v==0)
                {
                    $hkey .= $line[$i];
                }
            }
            $hkey = trim($hkey);
            if($hkey!="")
            {
                $m_httphead[strtolower($hkey)] = trim($hvalue);
            }
        }

        //分析返回记录
        if(preg_match("/^3/", $m_httphead["http-state"]))
        {
            if(isset($m_httphead["location"]) && $JumpCount<3)
            {
                $JumpCount++;
                DownImageKeep($gurl,$rfurl,$filename,$gcookie,$JumpCount);
            }
            else
            {
                return FALSE;
            }
        }
        if(!preg_match("/^2/", $m_httphead["http-state"]))
        {
            return FALSE;
        }
        if(!isset($m_httphead))
        {
            return FALSE;
        }
        $contentLength = $m_httphead['content-length'];

        //保存文件
        $fp = fopen($filename,"w") or die("写入文件：{$filename} 失败！");
        $i=0;
        $okdata = "";
        $starttime = time();
        while(!feof($m_fp))
        {
            $okdata .= fgetc($m_fp);
            $i++;

            //超时结束
            if(time()-$starttime>$maxtime)
            {
                break;
            }

            //到达指定大小结束
            if($i >= $contentLength)
            {
                break;
            }
        }
        if($okdata!="")
        {
            fwrite($fp,$okdata);
        }
        fclose($fp);
        if($okdata=="")
        {
            @unlink($filename);
            fclose($m_fp);
            return FALSE;
        }
        fclose($m_fp);
   }

    return TRUE;
}
/**
 *  获得网址的host和query部份
 *
 * @access    public
 * @param     string  $gurl  调整地址
 * @return    string
 */
function GetHostInfo($gurl)
{
    $gurl = preg_replace("/^http:\/\//i", "", trim($gurl));
    $garr['host'] = preg_replace("/\/(.*)$/i", "", $gurl);
    if($garr['host']==='https:'){
        $arr=parse_url($gurl);
        $garr['scheme']=$arr['scheme'];
        $garr['host']="https://".$arr['host'];
        $garr['host1']=$arr['host'];
        $garr['query'] = $arr['path'];
    }else{
        $garr['query'] = "/".preg_replace("/^([^\/]*)\//i", "", $gurl);
    }

    return $garr;
}

/**
 *  获得某页面返回的Cookie信息
 *
 * @access    public
 * @param     string  $gurl  调整地址
 * @return    string
 */
function RefurlCookie($gurl)
{
    global $gcookie,$lastRfurl;
    $gurl = trim($gurl);
    if(!empty($gcookie) && $lastRfurl==$gurl)
    {
        return $gcookie;
    }
    else
    {
        $lastRfurl=$gurl;
    }
    if(trim($gurl)=='')
    {
        return '';
    }
    $urlinfos = GetHostInfo($gurl);
    //print_r($urlinfos);
    $ghost = $urlinfos['host'];
    $gquery = $urlinfos['query'];
    $sessionQuery = "GET $gquery HTTP/1.1\r\n";
    $sessionQuery .= "Host: $ghost\r\n";
    $sessionQuery .= "Accept: */*\r\n";
    $sessionQuery .= "User-Agent: Mozilla/4.0 (compatible; MSIE 5.00; Windows 98)\r\n";
    $sessionQuery .= "Connection: Close\r\n\r\n";
    $errno = "";
    $errstr = "";
    //echo $ghost;
   // exit();
 /* if(isset($urlinfos['scheme'])){
        $m_fp =  fsockopen($ghost, 443, $errno, $errstr,10) or die($ghost.'<br />');
    }else{*/
        $m_fp = fsockopen($ghost, 80, $errno, $errstr,10) or die($ghost.'<br />');
   // }

    fwrite($m_fp,$sessionQuery);
    $lnum = 0;

    //获取详细应答头
    $gcookie = "";
    while(!feof($m_fp))
    {
        $line = trim(fgets($m_fp,256));
        if($line == "" || $lnum>100)
        {
            break;
        }
        else
        {
            if(preg_match("/^cookie/i", $line))
            {
                $gcookie = $line;
                break;
            }
        }
    }
    fclose($m_fp);
    return $gcookie;
}

function getHttpsImg($url,$filename){
       $return_content = getCont($url);
       //echo $return_content;
       //ob_end_clean ();
        $fp= @fopen($filename,"a");

        fwrite($fp,$return_content); //写入文件
        fclose($fp);
        //unset($return_content);
        return true;
}

function getCont($url,$type=true){
    if($type){
        $ch=curl_init();
        $timeout=20;
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        $code=curl_exec($ch);
        curl_close($ch);
        //echo $code;
    }else{
        ob_start();
        readfile($url);
        $code=ob_get_contents();
        ob_end_clean();
    }

    return $code;
}



?>