<?php
$conf = array(
    'host' =>'127.0.0.1' ,
    'db_driver' =>'mysql' ,
    'db_name' =>'pblog' ,
    'user' =>'root' ,
    'db_pwd' =>'root' ,
    'db_tablepre'=>'blog_',
    'default_c'=>'main',
    'default_a'=>'index',
    'extension'=>'.html'//静态文件默认扩展名
);
/*controller*/
$in_control=array('article','back','bookmark','datas','file','index','main','manual','picture');
/*action*/
$in_action=array(
    0=>array("preview"),
    1=>array('index','login','doLogin','logout','files','addfile','editfile','savefile','delefile','article','addarticle','editarticle','savearticle','delearticle','bookmark',
        'picture','type','types','addtype','web_info',"saveweb_info",'addurl','editurl','saveurl','web_table','addtable','edittable','savetable',
        'web_urls','addurls','editurls','saveurls','aboutus','addaboutus','editaboutus','saveaboutus','addxxx',"editxxx",'savexxx','sitemap'),
    2=>array(),
    3=>array('index'),
    4=>array("preview",'show','lists'),
    5=>array(),
    6=>array('index','templates','article','articleshow','website','manual','aboutus','temporary','spot','comment','search'),
    7=>array('index','show','saveManual','addManual','search','deleteManual'),
    8=>array('lists','show'),

);
/*暂时页面*/
$temporary=array("question","interview");
/*发邮件邮箱*/
$send_emails=array("h2913327703@163.com","h478578@163.com","z8843545@163.com");
$send_emails_pass=array("ckww123ab","cdkkk345ab","dgfkgkf454");

?>
