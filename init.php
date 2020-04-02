<?php
//根目录
defined('ROOT_PATH') or define('ROOT_PATH', str_replace('\\','/',__DIR__));
defined('PUBLIC_s') or define('PUBLIC_s','/public');//静态资源、css、js、img的路径
defined('UPLOADS') or define('UPLOADS',PUBLIC_s.'/uploads');//下载的资源
defined('UPLOADS_IMG') or define('UPLOADS_IMG','public/uploads/image');//下载的资源
defined('a_PUBLIC') or define('a_PUBLIC',ROOT_PATH.'/public/');//绝对路径  静态资源、css、js、img的路径
defined('Core') or define('Core',ROOT_PATH.'/core');//
defined('Vmain') or define('Vmain',ROOT_PATH.'/Views/main');//前端浏览的页面
defined('Vback') or define('Vback',ROOT_PATH.'/Views/back');//后台用户的页面
defined('Vfile') or define('Vfile',ROOT_PATH.'/Views/file');//添加的内容保存的页面
defined('Vpicture') or define('Vpicture',ROOT_PATH.'/Views/picture');//添加的图片保存的页面
defined('FPATH') or define('FPATH',ROOT_PATH.'/function/');//函数目录路径
defined('Pback') or define('Pback',PUBLIC_s.'/back');//后台静态资源
defined('Pmain') or define('Pmain',PUBLIC_s.'/main');//前端静态资源
defined('Pcommon') or define('Pcommon',PUBLIC_s.'/common');//公共静态资源
date_default_timezone_set('PRC');//北京市区为默认时区


//函数内无法使用当前的变量
/*//变量转义
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

//直接应用变量名称替代
foreach(array('_GET','_POST') as $_request)
{
    foreach($$_request as $_k => $_v)
    {
        if(strlen($_k)>0 &&
            preg_match('#^(GLOBALS|_GET|_POST|_SESSION|_COOKIE)#',$_k))
        {
            exit('不允许请求的变量名!');
        }

        ${$_k} = _RunMagicQuotes($_v);
    }
}*/
//引入全局变量  缓存
include_once (Core.'/config.cache.php');

//引入基础函数
include_once (FPATH.'function.php');



?>