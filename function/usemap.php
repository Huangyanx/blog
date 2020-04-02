<?php
    date_default_timezone_set('PRC');//北京市区为默认时区
    require_once ("sitemap.class.php");
    $site = new Sitemap();
    $dir = dirname(__FILE__);
    require_once ($dir ."/../core/config.php");
    require_once ($dir."/../core/config.cache.php");
    require_once ($dir."/../core/Model.class.php");
    global $cfg_weburl;
    $dosql=new Model();//在函数内为空
    /*首页*/
    $site->AddItem($cfg_weburl,1,'weekly');
    /*---根据分类获取对应链接----*/
    $res=$dosql->selects("type"," where linkurl !=''",'','id,linkurl');
    foreach ($res as $val){
        $site->AddItem($cfg_weburl.$val['linkurl'],0.8,'weekly');
    }
    /*文章列表*/
    $link=get_list_page('/main/article','article',10,'',$dosql);
    foreach ($link as $val){
        $site->AddItem($cfg_weburl.$val,0.7,'weekly');
    }

    /*文章详情*/
    $show_link=get_detail_link('/main/articleshow','article','',$dosql);
    foreach ($show_link as $val){
        $site->AddItem($cfg_weburl.$val,0.7,'weekly');
    }
    /*手册 详情*/
    $manual=$dosql->selects("type"," where parentid =32",'','linkurl,name');
    foreach ($manual as $val){
        $name=$val['name'];
        $manual_item=$dosql->selects("manual"," where belong ='$name'",'','id');
        foreach ($manual_item as $val1){
            $site->AddItem($cfg_weburl.$val['linkurl'].'&amp;mid='.$val1['id'],0.6,'weekly');
        }
    }
    $site->SaveToFile('sitemap.xml');
    $site->SaveUrls('sitemap.txt');

/*获取列表页
 * 链接规律---名称-cid-page.html
   获取途径
    名称：手动
    cid：手动
    page：根据分页数和cid
$filename  --路径前缀
$cid 栏目的classid
$page_l 每页数目  all为不分页
$infotype 栏目类型  1 单页  2 列表  3 图片
*/

    function get_list_page($url,$table,$page_l=10,$where='',$dosql1){
        /*获取总数*/
        $newnum=$dosql1->getcount($table,$where);
            $link=array();
            if (!empty($newnum)) {
                $page = ceil($newnum / $page_l);
                for ($i = 1; $i <= $page; $i++) {
                    $link[] = $url .'?page-'.$i;
                }
            }
            return $link;
    }

    /*获取详情页链接
    *名称-cid-id-page.html
    *$filename  --路径前缀
    * $cid 栏目的classid
    *$infotype 栏目类型  1 单页  2 列表  3 图片
     *
    */
    function get_detail_link($url,$table,$where='',$dosql1){
        /*获取总数*/
        $ids=$dosql1->selects($table,$where,''," id ");
        $link=array();
            if (!empty($ids)) {
                foreach ($ids as $key=>$val){
                    $link[] =$url .'?id-'.$val['id'];
                }
            }
            return $link;
    }

?>