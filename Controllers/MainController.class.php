<?php

class MainController extends Controller
{

    public $view_page='main/';
    public $mysql;
    public $nav;
    public $footer_nav;
    public $cfg_introduce;
    public $article_num;
    public $picture_num;
    public $bookmarking_num;
    public $templates_num;
    public $category;
    public $category_num;
    public $comment_nums;
    public $recent_comment;
    public $last_news;
    function __construct()
    {
        global $cfg_introduce;
        $this->cfg_introduce=$cfg_introduce;//本站说明
        // parent::__construct();
        $this->mysql=$this->loadModel();
        $this->nav=$this->getnav();//头部
        $this->footer_nav=$this->getnav(true);//头部
        $this->ch=$this->getTKD();
        /*左侧栏目*/
        $this->article_num=$this->mysql->getcount("article");
        $this->picture_num=$this->mysql->getcount("picture");
        $this->bookmarking_num=$this->mysql->getcount("bookmarking");
        $this->templates_num=$this->mysql->getcount("file"," where url != ''");
        $this->category_num=$this->mysql->getcount("type");
        /*带链接的分类*/
        $this->category=$this->mysql->selects("type"," where linkurl !=''");
        /*评论总数*/
        $this->comment_nums=$this->mysql->getcount("comment");

        /*最新评论*/
        $where=" order by posttime DESC limit 0 , 5";
        $this->recent_comment=$this->mysql->selects('comment', $where);
        /*获取对应的头像地址  ---------文章标题  */
        foreach ($this->recent_comment as $key=>$val){
            $r1=$this->mysql->select_one("article"," where id=".$val['tid'],"title");
            $this->recent_comment[$key]['article_title']=$r1['title'];
        }
        //最新更新
        $query="select * from ( 
                    (select id,title,posttime,tableid,remarks as belong from blog_article as a where checkinfo='true' order by posttime DESC limit 0,1) UNION
                    (select id,name as title,addtime as posttime,tableid,belong from blog_manual as m order by addtime DESC limit 0,1)  UNION    
                    (select id,title,posttime ,tableid,url as belong from blog_file as f order by posttime DESC limit 0,1) UNION 
                    (SELECT id , title,posttime,tableid,type as belong FROM blog_reprint as r where status=1  order by posttime DESC limit 0,1)
             ) a
              ORDER BY posttime DESC limit 0,1
             ";
        $this->last_news=$this->mysql->select_one('','','',$query);
    }
    function index(){
        $oth_arr=array();
        $oth_arr['title']='个人博客首页--'.$GLOBALS['cfg_seotitle'];
        $oth_arr['keywords']=$GLOBALS['cfg_keyword'];
        $oth_arr['description']=$GLOBALS['cfg_description'];
        /*文章*/
        $article=$this->mysql->selects('article',' order by posttime DESC limit 0,5');
        /*文章评论数*/
        foreach ($article as $key=>$val){
            $r=$this->mysql->getcount("comment"," where tid=".$val['id']);
            $article[$key]['commentnum']=$r;
        }
        $oth_arr['article']=$article;
        /*获取文章类型*/
        foreach ($oth_arr['article'] as $key=>$value){
            $r1=$this->mysql->select_one("type"," where id=".$value['type'],'name');
            $oth_arr['article'][$key]['typename']=$r1['name'];
        }
        /*推荐文章*/
        $hotarticle=$this->mysql->selects('article',' order by hits DESC limit 0,10','','title,id,hits');
        $oth_arr['hotarticle']=$hotarticle;
        /*文件   --------模板*/
        $file=$this->mysql->selects('file'," where url !='' AND is_show!=1 order by posttime DESC limit 0,6",'','title,url,upimg');
        $oth_arr['file']=$file;
        /*手册*/
        $manual_list=$this->mysql->selects('type',' where parentid=32 limit 0,40');
        $oth_arr['manual_list']=$manual_list;
        /*收藏*/
        $where1="where parentid=21 or id=21";
        $website_list=$this->mysql->selects('type',$where1);
        $oth_arr['website_list']=$website_list;
        /*图片   --------*/
        $picture=$this->mysql->selects('picture'," order by posttime DESC limit 0,6",'','title,url,upimg,isdir');
        $oth_arr['picture']=$picture;
        $this->display('index','',$this->view_page,$oth_arr);
    }
    function article(){
        preg_match_all('/page-(\d+)$/', $_SERVER['QUERY_STRING'], $strResult, PREG_PATTERN_ORDER);
        if(!empty($_GET['page'])){
            $page=$_GET['page'];
        }
        else if(!empty($strResult[1][0])){
            $page=$strResult[1][0];
        }else{
            $page=1;
        }
        $page_num=10;
        $p_start=($page-1)*$page_num;
        $oth_arr=array();
        $oth_arr['title']='文章列表'.$GLOBALS['cfg_seotitle'];
        $oth_arr['keywords']="热门文章;值得一看的文章";
        $oth_arr['description']="收藏每一篇有意义的文章，每一篇引人深思、有触感的文章……";
        /*文章*/
        $where=" where checkinfo='true' order by posttime DESC";
        $num= $this->mysql->getcount('article',$where);
        $where.=' limit '.$p_start.','.$page_num;
        $article=$this->mysql->selects('article', $where);
        $oth_arr['article']=$article;
        /*获取文章类型  ------评论数*/
        foreach ($oth_arr['article'] as $key=>$value){
            $r1=$this->mysql->select_one("type"," where id=".$value['type'],'name');
            $oth_arr['article'][$key]['typename']=$r1['name'];
            $r2=$this->mysql->getcount("comment"," where tid=".$value['id']);
            $oth_arr['article'][$key]['commentnum']=$r2;
        }

        /*热门文章*/
        $hotarticle=$this->mysql->selects('article',' order by hits DESC limit 0,10','','title,id,hits');
        $oth_arr['hotarticle']=$hotarticle;
        /*文件   --------模板*/
        $file=$this->mysql->selects('file'," where url !='' AND is_show!=1 order by posttime DESC limit 0,6",'','title,url,upimg');
        $oth_arr['file']=$file;
        /*图片   --------*/
        $picture=$this->mysql->selects('picture'," order by posttime DESC limit 0,6",'','title,url,upimg,isdir');
        $oth_arr['picture']=$picture;
        $pagelist=pageList($page,$num,$page_num,true);
        $oth_arr['pagelist']=$pagelist;
        $this->display('article','',$this->view_page,$oth_arr);
    }
    function articleshow(){
        if(!empty($_GET['id'])){
            $id=$_GET['id'];
        } else{
            preg_match_all('/id-(\d+)$/', $_SERVER['QUERY_STRING'], $strResult, PREG_PATTERN_ORDER);
            if(!empty($strResult[1][0])){
                $id=$strResult[1][0];
            }else{
                $id=1;
            }
        }
        $arr=$this->mysql->select_one('article'," where id='$id' and checkinfo='true'");
        if(empty($arr)) {
            $arr = array('title' => '暂无内容', 'content' => '暂无内容','author'=>'','posttime'=>0,'hits'=>'','thumbs_up'=>'','step'=>'');
            $r2 = 0;
            $recommentarticle=array();
        }else{
            $r2 = $this->mysql->getcount("comment", " where tid=" . $arr['id']);
            $recommentarticle=$this->mysql->selects('article',' where type='.$arr['type'].' and id !='.$id.' order by hits DESC limit 0,5','','title,id');
        }
        $arr['commentnum']=$r2;
        $oth_arr=array();
        $oth_arr['title']=$arr['title']."-文章详情-".$GLOBALS['cfg_seotitle'];
        $oth_arr['keywords']=empty($arr['keywords']) ? $GLOBALS['cfg_keyword']:$arr['keywords'];
        $oth_arr['description']=empty($arr['description']) ? cut_len($arr['content']):$arr['description'];
        /*热门文章*/
        $hotarticle=$this->mysql->selects('article',' order by thumbs_up DESC,hits DESC limit 0,10','','title,id,hits');
        $oth_arr['hotarticle']=$hotarticle;
        /*推荐文章*/
        $oth_arr['recommentarticle']=$recommentarticle;
        $this->display('articleshow',$arr,$this->view_page,$oth_arr);

        /*浏览次数的增加*/
        if(!empty($arr['id'])){
            $hitsnext=$arr['hits']+1;
            $arr1=array('hits'=>$hitsnext);
            $this->mysql->edit('article',$arr1,$id);
        }

    }

    function search(){
        if(!empty($_GET['keyword'])){
            $keyword=$_GET['keyword'];
        }
        else{
            $keyword='';
        }
        preg_match_all('/page-(\d+)$/', $_SERVER['QUERY_STRING'], $strResult, PREG_PATTERN_ORDER);
        if(!empty($_GET['page'])){
            $page=$_GET['page'];
        }
        else if(!empty($strResult[1][0])){
            $page=$strResult[1][0];
        }else{
            $page=1;
        }
        $page_num=10;
        $p_start=($page-1)*$page_num;
        $oth_arr=array();
        $oth_arr['title']=$keyword.'-关键字搜索'.$GLOBALS['cfg_seotitle'];
        $oth_arr['keywords']="个人博客;关键字搜索";
        $oth_arr['description']="搜索快速查找资源……";
        $oth_arr['keyword']=$keyword;
        /*文章*/
        $range=empty($_GET['range']) ? 0:$_GET['range'];
        $query="SELECT * FROM";
        $field="id, title, remarks,tableid,hits,posttime,url as belong";
        $where=" WHERE (title LIKE '%$keyword%'OR remarks LIKE '%$keyword%' ) ORDER BY posttime DESC ";
        /*多表查询并合并*/
        switch ($range){
            case 0:
                $query1=" ( SELECT  id, title, remarks,tableid,hits,posttime,type as belong FROM blog_article UNION 
                         ( SELECT m.id , title, remarks,c.tableid,hits,posttime,m.belong FROM blog_manual_content as c  left outer join blog_manual as m on c.manual_id=m.id ) UNION
                         SELECT   ".$field."  FROM blog_file  UNION 
                         SELECT  ".$field."  FROM blog_bookmarking  UNION 
                         SELECT id , title, description as remarks,tableid,hits,posttime,url as belong FROM blog_reprint
                     ) d 
              ";
                break;
            case 1:
                $table="blog_article";
                $query= "SELECT id, title, remarks,tableid,hits,posttime,type as belong FROM ".$table;
                break;
            case 2:
                $table="blog_bookmarking";
                $query= "SELECT ".$field." FROM ".$table;
                break;
            case 3:
                $table="blog_file";
                $query= "SELECT ".$field." FROM ".$table;
                break;
            case 5:
                $table="blog_manual_content";
                $query= "SELECT m.id , title, remarks,c.tableid,hits,posttime,m.belong FROM blog_manual_content as c  left outer join blog_manual as m on c.manual_id=m.id ";
                break;
            case 7:
                $table="blog_reprint";
                $query= "SELECT id , title, description as remarks,tableid,hits,posttime,url as belong FROM blog_reprint";
                $where=" WHERE (title LIKE '%$keyword%'OR description LIKE '%$keyword%' ) ORDER BY posttime DESC ";
                break;
            default :
                $table="blog_article";
                $query= "SELECT id, title, remarks,tableid,hits,posttime,type as belong FROM ".$table;
                break;
        }
        $count_sql= empty($query1) ? "select count(id) from ".$table.$where:"select  count(id) from ".$query1.$where;
        $num= $this->mysql->getcount('article','',$count_sql);
        $query=empty($query1) ?  $query .$where :  $query.$query1.$where;
        $query.=' limit '.$p_start.','.$page_num;
        $article=$this->mysql->selects('', '','','',$query);
        $oth_arr['article']=$article;

        /*热门文章*/
        $hotarticle=$this->mysql->selects('article',' order by hits DESC limit 0,10','','title,id,hits');
        $oth_arr['hotarticle']=$hotarticle;
        /*文件   --------模板*/
        $file=$this->mysql->selects('file'," where url !='' AND is_show!=1 order by posttime DESC limit 0,6",'','title,url,upimg');
        $oth_arr['file']=$file;
        /*图片   --------*/
        $picture=$this->mysql->selects('picture'," order by posttime DESC limit 0,6",'','title,url,upimg,isdir');
        $oth_arr['picture']=$picture;
        $pagelist=pageList($page,$num,$page_num,true);
        $oth_arr['pagelist']=$pagelist;
        $this->display('search','',$this->view_page,$oth_arr);

        /*
         SELECT * FROM (
	SELECT id, title, remarks FROM blog_article UNION
	SELECT id, title, remarks FROM blog_manual_content UNION
	SELECT id, title, remarks FROM blog_file UNION
	SELECT id, title, remarks FROM blog_bookmarking
) d
WHERE (d.title LIKE '%滚动%'OR d.remarks LIKE '%滚动%' ) ORDER BY id DESC
        */
    }

    function website(){
        $oth_arr=array();
        $oth_arr=$this->getTKD("/main/website");
        $type=array();
        $website_all=array();
        if(!empty($_GET['keyword'])){
            $keyword=$_GET['keyword'];
            $type[]=array('id'=>1,'name'=>'搜索结果');
            $where=" where title like '%$keyword%'or remarks like  '%$keyword%'";
            $website_all[]=$this->mysql->selects('bookmarking',$where);

        }else{
            $where1="where parentid=21 or id=21";
            $type=$this->mysql->selects('type',$where1);
            /*二维数组---对应类型的网址*/
            foreach ($type as $val){
                $type_one=$val['id'];
                $where2="where type='$type_one'";
                $website_one=$this->mysql->selects('bookmarking',$where2);
                $website_all[]=$website_one;
            }
        }
        $oth_arr['type']=$type;
        $oth_arr['website_all']=$website_all;

        $this->display('website','',$this->view_page,$oth_arr);
    }

    function templates(){
        preg_match_all('/page-(\d+)$/', $_SERVER['QUERY_STRING'], $strResult, PREG_PATTERN_ORDER);
        if(!empty($strResult[1][0])){
            $page=$strResult[1][0];
        }else{
            $page=1;
        }
        $page_num=12;
        $p_start=($page-1)*$page_num;
        $oth_arr=array();
        $oth_arr['title']='模板'.$GLOBALS['cfg_seotitle'];
        $oth_arr['keywords']=$GLOBALS['cfg_keyword'];
        $oth_arr['description']=$GLOBALS['cfg_description'];

        $where="  where url !='' AND is_show!=1 order by posttime DESC ";
        $num= $this->mysql->getcount('file',$where);
        $where.=' limit '.$p_start.','.$page_num;
        /*文件   --------模板*/
        $file=$this->mysql->selects('file',$where,'','title,url,upimg');
        $oth_arr['file']=$file;
        $pagelist=pageList($page,$num,$page_num,true);
        $oth_arr['pagelist']=$pagelist;
        /*热门文章*/
        $hotarticle=$this->mysql->selects('article',' order by hits DESC limit 0,10','','title,id,hits');
        $oth_arr['hotarticle']=$hotarticle;
        $this->display('templates','',$this->view_page,$oth_arr);
    }

    function manual(){
        $oth_arr=array();
        $oth_arr=$this->getTKD("/main/manual");
        $manual_list=$this->mysql->selects('type',' where parentid=32 limit 0,40');
        $oth_arr['manual_list']=$manual_list;
        if(empty($_GET['belong'])){
            $oth_arr['title']='手册列表';
            /*热门文章*/
            $hotarticle=$this->mysql->selects('article',' order by hits DESC limit 0,10','','title,id,hits');
            $oth_arr['hotarticle']=$hotarticle;

            $this->display('manual','',$this->view_page,$oth_arr);
        }else{
            /*获取手册*/
            $belong=$_GET['belong'];
            $mid=isset($_GET['mid']) ? $_GET['mid']:'';
            $belong1= strtolower($belong);
            $oth_arr['title1']=$belong.'手册';
            $oth_arr['belong']=$belong;
            $oth_arr['mid']=$mid;
            $where=" where belong ='$belong' or  belong ='$belong1'";
            $tree=$this->mysql->selects('manual',$where);
           // include_once Core."/tree2.php";
           // $tree = arr2tree($arr);
            /*默认第一个数据*/
            if(empty($mid)){
                $mid=empty($tree[0]['id'])?'':$tree[0]['id'];
            }
            /*详情*/
            if(!empty($mid)){
                $oth_arr['manual_content']=$this->mysql->select_one("manual_content","where manual_id='$mid'");
                $oth_arr['title']=$oth_arr['title1'].'--'.$oth_arr['manual_content']['title'];
                $oth_arr['keywords']=$oth_arr['keywords'].$oth_arr['manual_content']['title'];
                $oth_arr['description']=cut_len($oth_arr['manual_content']['content']);
            }else{
                $oth_arr['manual_content']='';
            }

            $this->display('manual_item',$tree,$this->view_page,$oth_arr);

            //浏览次数
            if(!empty($oth_arr['manual_content'])){
                $arr1=array('hits'=>$oth_arr['manual_content']['hits']+1);
                $this->mysql->edit("manual_content",$arr1,'',"where manual_id='$mid'");
            }
        }

    }
    function aboutus(){
        $arr=array();
        $oth_arr=array();
        if(!empty($_GET['id'])){
            $id=$_GET['id'];
            $arr=$this->mysql->select_one('aboutus'," where id='$id'");
            $oth_arr['title']=$arr['varinfo']."|".$GLOBALS['cfg_seotitle'];
            $oth_arr['keywords']="关于;".$arr['varinfo'];
            $oth_arr['description']=cut_len($arr['content']);

            $arr['varinfo']=empty($arr['varinfo']) ? "暂无内容":$arr['varinfo'];
            $arr['content']=empty($arr['content']) ? "暂无内容":stripslashes($arr['content']);

        }
        $this->display('aboutus',$arr,$this->view_page,$oth_arr);

    }

    function temporary($_temp=''){
        $arr=$this->mysql->select_one('nav'," where relinkurl like '%$_temp%'",'name');
        $arr['name']=empty($arr['name'])? '暂无内容':$arr['name'];
        $oth_arr=array();
        $oth_arr['title']=$arr['name'].'|'.$GLOBALS['cfg_seotitle'];
        $oth_arr['keywords']=$arr['name'].';'.$GLOBALS['cfg_keyword'];
        $this->display('temporary',$arr,$this->view_page,$oth_arr);

    }

    /*点赞 踩的处理*/
    function spot(){
        $action=$_POST['action'];
        if($action==='changespot'){
            $table=$_POST['table'];
            $id=$_POST['id'];
            $field=$_POST['field'];
            $value=$_POST['value'];
            if(!empty($table) && !empty($id)&&!empty($field)&& !is_int($value)){
                $arr=array($field=>$value);
                $res=$this->mysql->edit($table,$arr,$id);
                if(!empty($res['status']) && $res['status']){
                    $status=2;
                }else{
                    $status=1;
                }
                echo json_encode(array("status"=>$status));
            }else{
                echo json_encode(array("status"=>3));
            }
        }else if($action==='getheadimg'){
            $where=" where type=1";
            $res=$this->mysql->selects('imgs',$where);
            echo json_encode(array("status"=>2,'data'=>$res));
        }else if($action==='headimg_url'){
            /*获取当前图片地址*/
            $where=" where id=".$_POST['id'];
            $res=$this->mysql->select_one('imgs',$where);
            echo json_encode(array("status"=>2,'data'=>$res));
        }
    }

    /*热门资讯*/
    function news(){
        $ctype=empty($_GET['ct']) ? '':$_GET['ct'];
        $page=empty($_GET['page']) ? 1:$_GET['page'];

        $page_num=10;
        $p_start=($page-1)*$page_num;
        $oth_arr=array();
        $oth_arr['title']='热门资讯'.$GLOBALS['cfg_seotitle'];
        $oth_arr['keywords']="热门资讯;技术干货";
        $oth_arr['description']="收藏最新，最热门资讯，了解最新技术……";
        $oth_arr['ctype']=$ctype;
        /*文章*/
        if(!empty($ctype)){
            $where=" where status=1 and type ='$ctype' order by posttime DESC";
        }else{
            $where=" where status=1 order by posttime DESC";
        }

        $num= $this->mysql->getcount('reprint',$where);
        $where.=' limit '.$p_start.','.$page_num;
        $article=$this->mysql->selects('reprint', $where);
        $oth_arr['article']=$article;
        /*获取文章类型  ------评论数*/
        foreach ($oth_arr['article'] as $key=>$value){
            $r1=$this->mysql->select_one("type"," where id=".$value['type'],'name');
            $oth_arr['article'][$key]['typename']=$r1['name'];
        }
        /*资讯类型*/
        include_once Core."/tree2.php";
        $oth_arr['ctypes']=gettree('type','',$this->mysql,'',47 );

        /*热门文章*/
        $hotarticle=$this->mysql->selects('article',' order by hits DESC limit 0,10','','title,id,hits');
        $oth_arr['hotarticle']=$hotarticle;
        /*文件   --------模板*/
        $file=$this->mysql->selects('file'," where url !='' AND is_show!=1 order by posttime DESC limit 0,6",'','title,url,upimg');
        $oth_arr['file']=$file;
        /*图片   --------*/
        $picture=$this->mysql->selects('picture'," order by posttime DESC limit 0,6",'','title,url,upimg,isdir');
        $oth_arr['picture']=$picture;
        $pagelist=pageList($page,$num,$page_num,false);
        $oth_arr['pagelist']=$pagelist;
        $this->display('news','',$this->view_page,$oth_arr);
    }

    //评论
    function comment(){
        global $cfg_webname,$cfg_weburl;
        $action=$_POST['action'];

        if($action==='add'){
            $email=$_POST['email'];
            $nickname=$_POST['nickname'];
            if(empty($_POST['content'])){
                echo json_encode(array("status"=>3,'msg'=>"请填写评论内容"));
                exit();
            }
            $_POST['content']=strip_tags($_POST['content']);
            $filed='nickname,uerid,content,headimg,tableid,tid,replayid,ip,weburl,posttime,hassend';
            $headimg=$_POST['headimg'];

            if(empty($headimg)){//头像为空，随机获取头像
                $len=20;
                $num=rand(0,$len);
                $head_imgs=select_one("imgs"," limit ".$num." ,1 ","headimg");
                $headimg=$head_imgs['headimg'];
            }
            if(!empty($_POST['userid'])){
                $uerid=$_POST['userid'];
            }else{
                //同一个邮箱看做一个账号
                $res1=$this->mysql->select_one("users"," where email='".$email."'","id,nickname,headimg");
                if($res1){

                    $uerid=$res1['id'];
                    $headimg=empty($_POST['head_imgs']) ? $res1['headimg'] : $headimg;
                    if($nickname!==$res1['nickname']){//修改昵称
                        $arr2=array('nickname'=>$nickname);
                        $res=$this->mysql->edit('users',$arr2,$uerid);
                    }


                }else{

                    /*将用户记录在表*/
                    $filed1="nickname,registered_time,status,user_type,email,headimg";
                    $arr1=array($_POST['nickname'],time(),1,3,$email,$headimg);
                    $res2=$this->mysql->add("users",$filed1,$arr1);
                    if($res2){
                        $uerid= $this->mysql->lastInsertId();
                    }


                }

            }

            $curtime=time();
            $result=array();
            $result['userid']=$uerid;
            $result['headimg']=$headimg;
            $result['nickname']=$_POST['nickname'];
            $result['content']=$_POST['content'];
            $result['posttime']=$curtime;
            $result['thumbs_up']='';
            $result['step']='';

            $arr=array($_POST['nickname'],$uerid,$_POST['content'],$headimg,$_POST['table'],$_POST['tid'],$_POST['replayid'],GetIP(),$_POST['weburl'],$curtime,'false');

            $this->mysql->add("comment",$filed,$arr);
            /*回答的评论也给记上*/
            /*取消在父级评论也给记上的做法*/
           $lastid= $this->mysql->lastInsertId();
            $result['id']=$lastid;
            /*
            if(!empty($_POST['replayid'])){
                $r2=$this->mysql->select_one("comment"," where id='".$_POST['replayid']."'","childid");
                if(!empty($r2['childid'])){
                   $replayid= $r2['childid'].",".$lastid;
                }else{
                    $replayid=$lastid;
                }

                $arr3=array('childid'=> $replayid);
                $res3=$this->mysql->edit('comment',$arr3,$_POST['replayid']);
            }*/

            echo json_encode(array("status"=>2,'data'=>$result));
            /*测试一下 ajax 可执行多长时间*/
            /*经测试  等所有执行完才响应，所以发送邮件分开操作……*/

        }else if($action==='list'){
            $page=empty($_POST['page']) ? 1:$_POST['page'];
            $pagelen=10;
            $rid=empty($_POST['rid']) ? 0:$_POST['rid'];
            $where=" where tid=".$_POST['tid']." and  replayid=$rid and tableid=".$_POST['tableid'];
            $p_start=($page-1)*$pagelen;

            $pagenum=$this->mysql->getcount("comment",$where);
            $pages=ceil($pagenum/$pagelen);

            if($page<=$pages){
                $where.=" order by posttime DESC limit ".$p_start.", ".$pagelen;
                $res=$this->mysql->selects('comment', $where);
                /*回复数目*/
                foreach ($res as $key=>$val){
                   $comment_nums=$this->mysql->getcount("comment"," where replayid=".$val['id']);
                   $res[$key]['comment_nums']=$comment_nums;
                }

                echo json_encode(array("status"=>2,"data"=>$res,'pagelist'=>array($pages,$pagenum)));

            }



        }else if($action==='sendmail'){
               @session_start();
               $_SESSION['random_comment']='';
               require_once FPATH."sendMail.php";
               /*接收方（分两种）： 1、管理员  2、评论者加管理员
               */
               $comment_id=$_POST['comment_id'];
               $curarticle=empty($_POST['curarticle'])? '':$_POST['curarticle'];

               /*根据评论id获取评论内容和用户id*/
               $res=$this->mysql->select_one("comment","where id=$comment_id");

               $replayid=$res['replayid'];

               if(empty($replayid)){

                   /*管理员接收*/
                   $subject=$cfg_webname."-【".$curarticle."】有新留言";
                   $body='<p>评论内容为：</p>';
                   $body.='<p style="margin: 15px 0;padding: 20px; border-radius: 5px;background-color: #f7f7f7">'.$res['content'].'</p>';
                   $body.='<p>期待您的回复！</p>';
                   $body.='<p style="text-align: right;" >原文：<a style="text-decoration:none; color:#2ebef3" href="'.$cfg_weburl.'/main/articleshow?id-'.$res['tid'].'" rel="noopener" target="_blank"> 《'.$curarticle.'》 </a></p>';
                   smtp_mail("1579379019@qq.com",$subject,$body);
                   echo json_encode(array("status"=>1,'msg'=>'发送成功！'));
               }else{

                   $res1=$this->mysql->select_one("comment","where id=$replayid");

                   $subject=$cfg_webname."-【".$curarticle."】有新回复";
                   $body="<p>".$res1['nickname']."评论：</p>";
                   $body.='<p style="margin: 15px 0;padding: 20px; border-radius: 5px;background-color: #f7f7f7">'.$res1['content'].'</p>';
                   $body.='<p>'.$res['nickname'].'回复'.$res1['nickname'].'：</p>';
                   $body.='<p style="margin: 15px 0;padding: 20px; border-radius: 5px;background-color: #f7f7f7">'.$res['content'].'</p><br/>';
                   $body.='<p style="text-align: right;" >原文：<a style="text-decoration:none; color:#2ebef3" href="'.$cfg_weburl.'/main/articleshow?id-'.$res['tid'].'" rel="noopener" target="_blank"> 《'.$curarticle.'》 </a></p>';
                   smtp_mail("1579379019@qq.com",$subject,$body);
                   /*获取 用户邮箱*/
                   $uerid=$res1['uerid'];
                   $res3=$this->mysql->select_one("users","where id=$uerid",'email');
                   if(!empty($res3['email'])){
                       $subject1="您在[".$cfg_webname."]-".$curarticle." 评论有新回复";
                       $body1='<p>'.$res1['nickname'].'，您好！</p>';
                       $body1.='<div class="graytext nowrap qm_dispname">您在【'.$cfg_webname.'】网站评论有新回复啦</div>';
                       $body1.='<div style="margin-left:50px;"><p>您的评论：</p>';
                       $body1.='<p style="margin: 15px 0;padding: 20px; border-radius: 5px;background-color: #f7f7f7">'.$res1['content'].'</p>';
                       $body1.='<p>'.$res['nickname'].'回复您：</p>';
                       $body1.='<p style="margin: 15px 0;padding: 20px; border-radius: 5px;background-color: #f7f7f7">'.$res['content'].'</p><br/>';
                       $body1.='<p style="text-align: right;" >原文：<a style="text-decoration:none; color:#2ebef3" href="'.$cfg_weburl.'/main/articleshow?id-'.$res['tid'].'" rel="noopener" target="_blank"> 《'.$curarticle.'》 </a></p>';
                       $body1.='<p style="padding-bottom: 10px; border-bottom: 1px dashed #ccc;">欢迎再次光临 <a style="text-decoration:none; color:#2ebef3" href="'.$cfg_weburl.'" rel="noopener" target="_blank">'.$cfg_webname.'</a></p>';
                       $body1.='<p style="margin-bottom: 10px;">(此邮件由系统发出,无需回复.)</p></div>';
                       smtp_mail($res3['email'],$subject1,$body1);

                   }
                   echo json_encode(array("status"=>1,'msg'=>'发送成功！'));


               }

        }

    }
    /* 标题、关键字、描述*/
    function getTKD($url=''){
        $oth_arr=array();
        if(!empty($url)){
            $where=" where linkurl='$url'";
            $res1=$this->mysql->select_one('type',$where);
            if(!empty($res1[0])){
                $oth_arr['title']=$res1['name'].'-'.$GLOBALS['cfg_seotitle'];
                $oth_arr['keywords']=$res1['keywords'];
                $oth_arr['description']=$res1['description'];
            }else{
                $oth_arr['title']='';
                $oth_arr['keywords']='';
                $oth_arr['description']='';
            }
            return $oth_arr;
        }
        return false;
    }

    /*一级菜单----导航栏----------*/
    function getnav($only_one_level=false){
        $pid=0; $table='nav';
        $where="where parentid='$pid' and status=1 order by orderid,id";
        $arr=$this->mysql->selects($table,$where);
        $res='';
        foreach ($arr as $val1){
            if($GLOBALS['cfg_isreurl']==='Y'&&!empty(trim($val1['relinkurl']))){
                //$link=$GLOBALS['cfg_weburl'].$val1['relinkurl'];
                $link=$val1['relinkurl'];
            }else if(!empty($val1['linkurl'])){
                $link=$val1['linkurl'];
            }else{
                $link="javascript:;";
            }

                $res.="<li><a href='".$link."'>".$val1['name']."</a>";
                if(!$only_one_level) {
                    $res.=$this->getSecNav($table, $val1['id']);
                }
            $res.="</li> ";


        }
        return $res;


    }
    /*二级以上菜单*/
    function getSecNav($table,$id){
        $where="where parentid='$id'and status=1";
        $arr=$this->mysql->selects($table,$where);
        $sec="<ul class='secondMenu'>";
        $scont='';
        foreach ($arr as $val){
            if($GLOBALS['cfg_isreurl']==='Y'){
                $link=$link=$GLOBALS['cfg_weburl'].$val['relinkurl'];
            }else if(!empty($val['linkurl'])){
                $link=$link=$GLOBALS['cfg_weburl'].$val['linkurl'];
            }else{
                $link="javascript:;";
            }
            $scont.="<li><a href='".$link."'>".$val['name']."</a>".$this->getSecNav($table,$val['id'])."</li> ";

        }
        if(empty($scont)){
            return ;
        }else{
            $sec.=$scont."</ul>";
            return $sec;
        }

    }

    function printnum($i=0){
        return $i+1;
    }
}
?>