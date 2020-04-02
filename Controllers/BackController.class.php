<?php

class BackController extends Controller
{
    public $view_page='/back/';
    public $mysql;
    public $nav;
    public $bookmark;
    public $manual;
    function __construct()
    {
        //无法调用地址的API，必须通过调用实例的变量获取
        $api= $GLOBALS['controller']->control.'/'. $GLOBALS['controller']->action;
        //echo $api;
        //print_r($_SESSION['accountInfo']);
        if (session_status() != 2) {
            @session_start();
            $this->mysql = $this->loadModel();
            if($api!=='back/doLogin' && $api!=='back/login' && !isset($_SESSION['accountInfo'])){
                $this->redirect('/back/login');
            }
            /* parent::__construct();*/

            $where = " where parentid=16";
            $this->nav = $this->mysql->selects('type', $where);
            $where = " where parentid=21";
            $this->bookmark = $this->mysql->selects('type', $where);
            $where2 = " where parentid=32";
            $this->manual = $this->mysql->selects('type', $where2);
        }

    }
    function login(){
        $this->display('login','',$this->view_page);
    }
    function doLogin(){
        $username=trim($_POST['account']);
        $password= substr(md5(md5(trim($_POST['pwd']))), 3, 20);
        if(empty($username)){
            echo  json_encode(array('status'=>2,'msg'=>'账号不能为空'));
            exit();
        }else if(empty($password)){
            echo  json_encode(array('status'=>3,'msg'=>'密码不能为空'));
            exit();
        }
        $where="where username='$username' and password='$password'";
        $res=$this->mysql->select_one("users",$where);
        //print_r($res);
        if($res){
            $res1=object2array($res);
            @session_start();
            $_SESSION['accountInfo'] = $res1;
            echo  json_encode(['status' => 'ok', 'data' => '登陆成功']);
            exit();
        }else{
            echo  json_encode(array('status'=>3,'msg'=>'账号或者密码不正确'));
            exit();
        }

    }
    function logout(){
        @session_start();
        unset($_SESSION['accountInfo']);
        $this->redirect('/back/login');
    }
    function index(){
        $this->display('index','',$this->view_page);
    }
    function note(){
        $this->display('note','',$this->view_page);
    }
    /*文件*/
    function files(){
        /*分页*/
        $page=empty($_GET['page']) ? 1:$_GET['page'];
        $page_num=10;
        $p_start=($page-1)*$page_num;
        include_once Core."/tree2.php";
        $trees=gettree('type','',$this->mysql,"(16,21)");
        $where="";
        if(!empty($_GET['keyword'])) {
            /*关键字分割-----------1、统一分隔符号为空格*/
            $keyword = str_replace('、', ' ', $_GET['keyword']);//、换为空格
            $keys = explode(' ', $keyword);

            if (empty($_GET['kfiled'])) {
                if (!empty($keys[1])) {
                    $glue = '';
                    $key = '';
                    $klen = count($keys);
                    for ($i = 0; $i < $klen; $i++) {
                        //echo $i;
                        $key .= $glue . " (title like '%$keys[$i]%' or  upfile like '%$keys[$i]%' or remarks like  '%$keys[$i]%')";
                        $glue = " and ";
                        // echo $key;
                    }
                } else {
                    $key = "title like '%$keyword%' or upfile like '%$keyword%' or remarks like  '%$keyword%'";
                }
                $where = " where " . $key;
            } else {
                $filed = $_GET['kfiled'];
                if(!empty($keys[1])){
                    $glue='';
                    $key='';
                    $klen=count($keys);
                    for ($i=0;$i<$klen;$i++) {
                        //echo $i;
                        $key.=$glue." $filed like '%$keys[$i]%'";
                        $glue=" and ";
                        // echo $key;
                    }

                }else{
                    $key=" $filed like '%$keyword%'";
                }
                $where = " where " . $key;
            }
        }

        $where.=" order by posttime DESC";
        $num= $this->mysql->getcount('file',$where);
        $where.=' limit '.$p_start.','.$page_num;
        $file=$this->mysql->selects('file',$where,1);

        $pagelist=pageList($page,$num,$page_num);
        $this->display('files',$file['res'],$this->view_page,array('pagelist'=>$pagelist,'trees'=>$trees));
    }
    function addfile(){
        include_once Core."/tree2.php";
        $trees=gettree('type','',$this->mysql,"(16,21)");
        $this->display('addfile',$trees,$this->view_page);
    }
    function savefile(){
        //include_once (FPATH."/article_fun.php");
        $action=$_POST['action'];
        $isdir=empty($_POST['isdir']) ?  0 : $_POST['isdir'];
        $upfile=$_POST['upfilename'];
        if(!(empty($_FILES['upimg']['name']) && empty($_FILES['upfile_s_up']['name']))){
            include_once FPATH."upfile.php"; //上传文件的处理
            $resupimg=upload("upimg");

            if($isdir){
                $name=$_POST['upfilename'];
                $url="Views/file/dir/".$_POST['upfilename'];
            }else{
                $adrr="Views/file/file/";
                $filetype=array(
                    'image/jpg',
                    'image/jpeg',
                    'image/png',
                    'application/msword',
                    'application/pdf',
                    'text/css',
                    'text/html',
                    'text/javascript',
                    'text/php',
                    'application/x-javascript'
                );
                 $resname=upload("upfile_s_up",$filetype,$adrr);
                if($resname['status']===1){
                    $url=$resname['url'];
                    $upfile=$resname['name'].'.'.$resname['type'];
                }else{
                    echo $resname['msg'];
                    exit();
                }

            }
            if($resupimg['status']==2){
                $upimg='';
            }
            else if($resupimg['status']===1){
                $upimg=$resupimg['url'];

            }else{
                echo $resupimg['msg'];
                exit();
            }
           // header("location:index.php?c=back&a=savefile&id=".$_POST['id']);
        }else{
            $url='';
            $upimg='';
            $upfile='';
        }
        if($action=='add'){
            $upfile=empty($upfile)?$_POST['upfilename']:$upfile;
            $filed='url,content,type,title,remarks,author,upimg,upfile,hits,posttime,is_show,is_dir';
            $content=addslashes($_POST['content']);
            $is_show=empty($_POST['is_show']) ? '':$_POST['is_show'];
            $arr=array($url,$content,$_POST['type'],$_POST['title'],$_POST['remarks'],$_POST['author'],$upimg,$upfile,$_POST['hits'],time(),$is_show,$isdir);
            $res=$this->mysql->add('file',$filed,$arr);
            $page='addfile';

        }else{
            if($isdir){
                $dirname='dir/';
                $upfile=empty($upfile)?$_POST['upfilename']:$upfile;
            }else{
                $dirname='file/';
                $upfile=empty($upfile)?$_POST['upfile_s']:$upfile;
            }
            /*上传时 url为非空，取这时的值，$url有值，否则 前面已赋值，取这时内地值，否则为空*/
            if(empty($url)&&!empty($_POST['upfilename'])){  //$url为空，而添加时已上传过文件，$url的值需重新赋值
                 $url="Views/file/".$dirname.$_POST['upfilename'];
            }
            $content=addslashes($_POST['content']);

            $type=$_POST['type'];
            $title=$_POST['title'];
            $remarks=$_POST['remarks'];
            $author=$_POST['author'];
            if(empty($upimg)&&!empty($_POST['upimgname'])) $upimg=$_POST['upimgname'];
            $hits=$_POST['hits'];
            $is_show=empty($_POST['is_show']) ? '':$_POST['is_show'];
            $arr=array('url'=>$url,'content'=>$content,'type'=>$type,'title'=>$title,'remarks'=>$remarks,'author'=>$author,'upimg'=>$upimg,'upfile'=>$upfile,'hits'=>$hits,'is_show'=>$is_show);
            //$where="url='$url',content='$content',type='$type',title='$title',remarks='$remarks',author='$author',upimg='$upimg',hits='$hits',upfile='$upfile'";
            $res=$this->mysql->edit('file',$arr,$_POST['id']);
            $page='editfile';
        }
        if($res){
           header("location:index.php?c=back&a=files");
        }else{
            header("location:".$_SERVER['HTTP_REFERER']);
        }
    }
    function editfile(){
        include_once Core."/tree2.php";
        $trees=gettree('type','',$this->mysql,"(16,21)");
        $id=$_GET['id'];
        if(empty($id)){
            exit();
        }
        $where="where id='$id'";
        $file=$this->mysql->select_one('file',$where);
        $this->display('editfile',$file,$this->view_page,$trees);
    }
    function delefile(){
        /*删除对应的文件*/
        $upfile=$_GET['upfile'];
        $id=$_GET['id'];
        if(!empty($id)){
            if(!empty($upfile)){
                $names=explode(",",$upfile);
                /*php 用foreach处理出现问题 js数组 用for会出现遍历字符串的情况
                改用方法：字符串传过来，再转数组
                 */
                include FPATH.'/filehandle.php';
                foreach($names as $val){
                    $path=ROOT_PATH.'/Views/file/'.$val;
                    if(file_exists($path)){
                        echo $path;
                        $de=deldir($path);
                        if(!$de){
                            echo json_encode(array('status'=>0,'msg'=>'删除对应文件失败'));
                            exit();
                        }
                    }

                }
            }

            $res=$this->mysql->delect('file','',$id);
            if(empty($res)){
                echo  json_encode(array('status'=>1));
            }else{
                echo json_encode(array('status'=>0));
            }

        }

    }

    /*文章*/
    function article(){
        /*分页*/
        $page=empty($_GET['page']) ? 1:$_GET['page'];
        $page_num=10;
        $p_start=($page-1)*$page_num;
        include_once Core."/tree2.php";
        $trees=gettree('type','',$this->mysql,"(16,21)");
        $where="";
        if(!empty($_GET['keyword'])) {
            /*关键字分割-----------1、统一分隔符号为空格*/
            $keyword = str_replace('、', ' ', $_GET['keyword']);//、换为空格
            $keys = explode(' ', $keyword);

            if (empty($_GET['kfiled'])) {
                if (!empty($keys[1])) {
                    $glue = '';
                    $key = '';
                    $klen = count($keys);
                    for ($i = 0; $i < $klen; $i++) {
                        //echo $i;
                        $key .= $glue . " (title like '%$keys[$i]%' or remarks like  '%$keys[$i]%')";
                        $glue = " and ";
                        // echo $key;
                    }
                } else {
                    $key = "title like '%$keyword%' or remarks like  '%$keyword%'";
                }
                $where = " where " . $key;
            } else {
                $filed = $_GET['kfiled'];
                if(!empty($keys[1])){
                    $glue='';
                    $key='';
                    $klen=count($keys);
                    for ($i=0;$i<$klen;$i++) {
                        //echo $i;
                        $key.=$glue." $filed like '%$keys[$i]%'";
                        $glue=" and ";
                        // echo $key;
                    }

                }else{
                    $key=" $filed like '%$keyword%'";
                }
                $where = " where " . $key;
            }
        }

        $where.=" order by posttime DESC";
        $num= $this->mysql->getcount('article',$where);
        $where.=' limit '.$p_start.','.$page_num;
        $file=$this->mysql->selects('article',$where,1);

        $pagelist=pageList($page,$num,$page_num);
        $this->display('article',$file['res'],$this->view_page,array('pagelist'=>$pagelist,'trees'=>$trees));
    }
    function addarticle(){
        include_once Core."/tree2.php";
        $trees=gettree('type','',$this->mysql,"(16,21)");
        $this->display('addarticle',$trees,$this->view_page);
    }
    function editarticle(){
        include_once Core."/tree2.php";
        $trees=gettree('type','',$this->mysql,"(16,21)");
        $id=$_GET['id'];
        if(empty($id)){
            exit();
        }
        $where="where id='$id'";
        $file=$this->mysql->select_one('article',$where);
        $this->display('editarticle',$file,$this->view_page,$trees);
    }
    function savearticle(){
        include_once (FPATH."/article_fun.php");
        $action=$_POST['action'];
        if(!(empty($_FILES['upimg']['name']))){
            include_once FPATH."upfile.php"; //上传文件的处理
            $resupimg=upload("upimg");
            if($resupimg['status']==2){
                $upimg='';
            }
            else if($resupimg['status']===1){
                $upimg=$resupimg['url'];

            }else{
                echo $resupimg['msg'];
                exit();
            }
        }else{
            $upimg=$_POST['upimgname'];

        }
        //echo $upimg;
        $hand_cont=transform_local_img($_POST['content']);
        $content=addslashes($hand_cont['body']);
       // print_r($hand_cont['img']);
        if(empty($upimg)) $upimg=$hand_cont['img'][0];
        //echo $upimg;
        if($action=='add'){
            if(!empty($_POST['title'])){
                $filed='content,type,title,remarks,author,upimg,hits,posttime,keywords,description';
                $arr=array($content,$_POST['type'],$_POST['title'],$_POST['remarks'],$_POST['author'],$upimg,$_POST['hits'],time(),$_POST['keywords'],$_POST['description']);
              //  exit();
               $res=$this->mysql->add('article',$filed,$arr);
                $page='addarticle';
            }

        }else{
            $id=$_POST['id'];
            if(empty($id)){
                header("location:".$_SERVER['HTTP_REFERER']);
                exit();
            }
            /*获取所有图片*/
            $type=$_POST['type'];
            $title=$_POST['title'];
            $remarks=$_POST['remarks'];
            $author=$_POST['author'];
            if(!empty($upimg)) {
                $upimg=strpos($upimg, "public") === false ? '/public/uploads/images/'.$upimg:$upimg;
            }
            $hits=$_POST['hits'];

            $arr=array('content'=>$content,'type'=>$type,'title'=>$title,'remarks'=>$remarks,'author'=>$author,'upimg'=>$upimg,'hits'=>$hits,'keywords'=>$_POST['keywords'],'description'=>$_POST['description']);
            $res=$this->mysql->edit('article',$arr,$id);
            $page='editarticle';

        }
        if($res){
            header("location:index.php?c=back&a=article");
        }else{
            header("location:".$_SERVER['HTTP_REFERER']);
        }
        include_once (FPATH."usemap.php");
    }
    function delearticle(){
        $id=$_GET['id'];
        if(!empty($id)){
            $res=$this->mysql->delect('article',$_GET['id']);
            if(empty($res)){
                echo  json_encode(array('status'=>1));
            }else{
                echo json_encode(array('status'=>0));
            }

        }

    }
    /*图*/
    function picture(){
        /*分页*/
        $page=empty($_GET['page']) ? 1:$_GET['page'];
        $page_num=10;
        $p_start=($page-1)*$page_num;
        $where1="where parentid=16 or id=16";
        $type=$this->mysql->selects('type',$where1);
        include_once Core."/tree2.php";
        $tree = arr2tree($type);
        $where="";
        if(!empty($_GET['keyword'])) {
            /*关键字分割-----------1、统一分隔符号为空格*/
            $keyword = str_replace('、', ' ', $_GET['keyword']);//、换为空格
            $keys = explode(' ', $keyword);

            if (empty($_GET['kfiled'])) {
                if (!empty($keys[1])) {
                    $glue = '';
                    $key = '';
                    $klen = count($keys);
                    for ($i = 0; $i < $klen; $i++) {
                        //echo $i;
                        $key .= $glue . " title like '%$keys[$i]%' or  name like '%$keys[$i]%' or remark like  '%$keys[$i]%'";
                        $glue = " or ";
                        // echo $key;
                    }
                } else {
                    $key = "title like '%$keyword%' or name like '%$keyword%' or remark like  '%$keyword%'";
                }
                $where = " where " . $key;
            } else {
                $filed = $_GET['kfiled'];
                if(!empty($keys[1])){
                    $glue='';
                    $key='';
                    $klen=count($keys);
                    for ($i=0;$i<$klen;$i++) {
                        //echo $i;
                        $key.=$glue." $filed like '%$keys[$i]%'";
                        $glue=" or ";
                        // echo $key;
                    }

                }else{
                    $key=" $filed like '%$keyword%'";
                }
                $where = " where " . $key;
            }
        }

        $where.=" order by posttime DESC";
        $num= $this->mysql->getcount('picture',$where);
        $where.=' limit '.$p_start.','.$page_num;
        $file=$this->mysql->selects('picture',$where,1);

        $pagelist=pageList($page,$num,$page_num);
        $this->display('picture',$file['res'],$this->view_page,array('tree'=>$tree,'pagelist'=>$pagelist));
    }
    function addpicture(){
        $where1="where parentid=16 or id=16";
        $type=$this->mysql->selects('type',$where1);
        include_once Core."/tree2.php";
        $tree = arr2tree($type);
        $this->display('addpicture',$tree,$this->view_page);
    }
    function editpicture(){
        $where1="where parentid=16 or id=16";
        $type=$this->mysql->selects('type',$where1);
        include_once Core."/tree2.php";
        $tree = arr2tree($type);
        $id=$_GET['id'];
        if(empty($id)){
            exit();
        }
        $where="where id='$id'";
        $file=$this->mysql->select_one('picture',$where);
        $this->display('editpicture',$file,$this->view_page,$tree);
    }
    function savepicture(){
        $action=$_POST['action'];
        include_once FPATH."upfile.php"; //上传文件的处理
        $resupimg=upload("upimg");
        $adrr="Views/picture/images/";
        $isdir=$_POST['isdir'];
        if($action=='add'){
            $filed='url,type,title,remark,upimg,isdir,name,posttime';
            /*上传文件处理  缩略图、图片*/
            if($isdir){
                $name=$_POST['upfilename'];
                $url='';
            }else{
                $resname=upload("upname",'',$adrr);
                if($resname['status']===1){
                    $url=$resname['url'];
                    $name=$resname['name'];
                }else{
                    echo $resname['msg'];
                    exit();
                }
            }
            if($resupimg['status']==2){
                $upimg='';
            }
            else if($resupimg['status']===1){
                $upimg=$resupimg['url'];

            }else{
                echo $resupimg['msg'];
                exit();
            }

            $arr=array($url,$_POST['type'],$_POST['title'],$_POST['remark'],$upimg,$isdir,$name,time());
            $res=$this->mysql->add('picture',$filed,$arr);
            $page='addfile';
        }else{
            if($isdir){
                $name=$_POST['upfilename'];
                $url=$_POST['name'];
            }else{
                $resname=upload("upname",'',$adrr);
                if($resname['status']===1){
                    $url=$resname['url'];
                    $name=$resname['name'];
                }else{
                    echo $resupimg['msg'];
                    exit();
                }
            }
            if($resupimg['status']==2){
                $upimg=$_POST['upimg'];
            }
            else if($resupimg['status']===1){
                $upimg=$resupimg['url'];

            }else{
                echo $resupimg['msg'];
                exit();
            }
            $type=$_POST['type'];
            $title=$_POST['title'];
            $remarks=$_POST['remark'];
            $isdir=$_POST['isdir'];

            $arr=array('url'=>$url,'type'=>$type,'title'=>$title,'remark'=>$remarks,'isdir'=>$isdir,'upimg'=>$upimg,'name'=>$name);
            //$where="url='$url',content='$content',type='$type',title='$title',remarks='$remarks',author='$author',upimg='$upimg',hits='$hits',upfile='$upfile'";
            $res=$this->mysql->edit('picture',$arr,$_POST['id']);
            $page='editpicture';
        }
        if(empty($res)){
            header("location:index.php?c=back&a=picture");
        }else{
            $this->display($page,$this->view_page);
        }
    }

    /*网址收藏*/
    function bookmark(){
        /*分页*/
        $page=empty($_GET['page']) ? 1:$_GET['page'];
        $page_num=10;
        $p_start=($page-1)*$page_num;
        $where1="where parentid=21 or id=21";
        $type=$this->mysql->selects('type',$where1);
        include_once Core."/tree2.php";
        $tree = arr2tree($type);
        $where="";
        if(!empty($_GET['keyword'])) {
            /*关键字分割-----------1、统一分隔符号为空格*/
            $keyword = str_replace('、', ' ', $_GET['keyword']);//、换为空格
            $keys = explode(' ', $keyword);

            if (empty($_GET['kfiled'])) {
                if (!empty($keys[1])) {
                    $glue = '';
                    $key = '';
                    $klen = count($keys);
                    for ($i = 0; $i < $klen; $i++) {
                        //echo $i;
                        $key .= $glue . " (title like '%$keys[$i]%' or remarks like  '%$keys[$i]%')";
                        $glue = " and ";
                        // echo $key;
                    }
                } else {
                    $key = "title like '%$keyword%'or remarks like  '%$keyword%'";
                }
                $where = " where " . $key;
            } else {
                $filed = $_GET['kfiled'];
                if(!empty($keys[1])){
                    $glue='';
                    $key='';
                    $klen=count($keys);
                    for ($i=0;$i<$klen;$i++) {
                        //echo $i;
                        $key.=$glue." $filed like '%$keys[$i]%'";
                        $glue=" and ";
                        // echo $key;
                    }

                }else{
                    $key=" $filed like '%$keyword%'";
                }
                $where = " where " . $key;
            }
        }

        $where.=" order by id DESC";
        $num= $this->mysql->getcount('bookmarking',$where);
        $where.=' limit '.$p_start.','.$page_num;
        $file=$this->mysql->selects('bookmarking',$where,1);

        $pagelist=pageList($page,$num,$page_num);
        $this->display('bookmark',$file['res'],$this->view_page,array('tree'=>$tree,'pagelist'=>$pagelist));
    }
    function addurl(){
        $where1="where parentid=21 or id=21";
        $type=$this->mysql->selects('type',$where1);
        include_once Core."/tree2.php";
        $tree = arr2tree($type);
        $this->display('addurl',$tree,$this->view_page);
    }
    function editurl(){
        $where1="where parentid=21 or id=21";
        $type=$this->mysql->selects('type',$where1);
        include_once Core."/tree2.php";
        $tree = arr2tree($type);
        $id=$_GET['id'];
        if(empty($id)){
            exit();
        }
        $where="where id='$id'";
        $file=$this->mysql->select_one('bookmarking',$where);
        $this->display('editurl',$file,$this->view_page,$tree);
    }
    function saveurl(){
        $action=$_POST['action'];
        if($action=='add'){
            $filed='url,type,title,remarks';
            $url=$_POST['url'];
            $arr=array($url,$_POST['type'],$_POST['title'],$_POST['remarks']);
            $res=$this->mysql->add('bookmarking',$filed,$arr);
            $page='addurl';
        }else{
            $url=$_POST['url'];
            $type=$_POST['type'];
            $title=$_POST['title'];
            $remarks=$_POST['remarks'];
            $arr=array('url'=>$url,'type'=>$type,'title'=>$title,'remarks'=>$remarks);
            $res=$this->mysql->edit('bookmarking',$arr,$_POST['id']);
            $page='editurl';
        }
        if($res){
            header("location:index.php?c=back&a=bookmark");
        }else{
            $this->display($page,$this->view_page);
        }

    }
    function deleurl(){
        $id=$_GET['id'];
        if(!empty($id)){
            $res=$this->mysql->delect('bookmarking',$_GET['id']);
            if(empty($res)){
                echo  json_encode(array('status'=>1));
            }else{
                echo json_encode(array('status'=>0));
            }

        }
    }
    /*分类*/
    function type(){
        $where="";
        if(!empty($_GET['keyword'])){
            $keyword=$_GET['keyword'];
            $where="where name like '%$keyword%'";
        }
        $res=$this->mysql->selects('type',$where,1);
        include_once Core."/tree2.php";
        $tree = arr2tree($res['res']);
        $types=$this->mysql->selects('types');
        //print_r($types);
        $this->display('type',$tree,$this->view_page,array("num"=>$res['num'],'types'=>$types));

    }
    function addtype(){
        if($_GET['action']=='edit'){
            $arr=array('name'=>$_GET['name'],'parentid'=>$_GET['parentid'],'linkurl'=>$_GET['linkurl'],'keywords'=>$_GET['keywords'],'description'=>$_GET['description']);
            $res=$this->mysql->edit('type',$arr,$_GET['id']);
            if(empty($res)){
                echo  json_encode(array('status'=>1,'msg'=>'修改成功'));
            }else{
                echo json_encode(array('status'=>0,'msg'=>'修改失败'));
            }
        }else{
            $name=$_GET['name'];
            //判断分类是否已存在,存在，获取当前sign，否赋当前sign值
            $exist=$this->mysql->select_one('types',"where name='$name'");
            if(!empty($exist['sign'])){
                $sign=$exist['sign'];
            }else{
                $signs=$this->mysql->select_one('types',"order by id DESC");
                //此处返回false
                if(empty($signs['id'])){
                    $sign='1';
                }else{
                    $allSign="123456789abcdefghijklmnopqrstuvwyzABCDEFGHIJKLMNOPQRSTUVWYZ";
                    $pos=mb_strpos($allSign, $signs['sign'], 0, 'UTF-8');
                    $curpos=$pos+1;
                    $sign=mb_substr($allSign, $curpos, 1, 'UTF-8');
                    $fied="name,sign";
                    $arr=array($name,$sign);
                    $res=$this->mysql->add('types',$fied,$arr);
                }
            }
            /*获取父级sign 拼成新的sign*/
            $parentid=$_GET['parentid'];
            if($parentid!=='0'){
                $pss=$this->mysql->select_one('type',"where id='$parentid'");
                $sign=$pss['sign'].','.$sign;
            }
            $fied="name,parentid,addtime,sign,linkurl,keywords,description";
            $arr=array($name,$_GET['parentid'],time(),$sign,$_GET['linkurl'],$_GET['keywords'],$_GET['description']);
            $res=$this->mysql->add('type',$fied,$arr);
            if(empty($res)){
                echo  json_encode(array('status'=>1,'msg'=>'添加成功'));
            }else{
                echo json_encode(array('status'=>0,'msg'=>'添加失败'));
            }
        }

    }
    function deletype(){
        if(!empty($_GET['id'])){
            $res=$this->mysql->delect('type',$_GET['id']);
            if(empty($res)){
                echo  json_encode(array('status'=>1));
            }else{
                echo json_encode(array('status'=>0));
            }
        }


    }
    function edittype(){
        $arr=array('is_open'=>$_GET['is_open']);
        $res=$this->mysql->edit('type',$arr,$_GET['id']);
        if($res['status']==2){
            echo json_encode(array('status'=>2,'msg'=>$res['msg']));
        }else{
            echo json_encode(array('status'=>1,'msg'=>'修改成功！'));
        }
    }
    function gettype(){
        $ids=$_GET['ids'];
        $where="where ";$glue='';
            $len=count($ids);
            for ($i=0;$i<$len;$i++){
                $where.=$glue."parentid=".$ids[$i];
                $glue=' or ';
            }
           // echo $where;
            $res=$this->mysql->selects('type',$where);
            echo  json_encode(array('status'=>1,'data'=>$res));
    }
    function upload(){
        include_once FPATH."updirectory.php";
        updirectory("fname",'name');
    }
    function upimgs(){
        include_once FPATH."updirectory.php";
        updirectory("fname",'name',Vpicture);
    }
    function savetype(){
        $action=$_POST['action'];
        if($action=='add'){
            $filed='name,url,content,type,title,remarks,author,upimg,upfile,hits,posttime';
            $arr=array();
            $res=$this->mysql->edit('file',$filed,$_GET['id']);
        }else{

        }

    }
    /*总分类*/
    function types(){
        $where="";
        if(!empty($_GET['keyword'])){
            $keyword=$_GET['keyword'];
            $where="where name like '%$keyword%'";
        }
        $res=$this->mysql->selects('types',$where,1);
        $this->display('types',$res,$this->view_page,array("num"=>$res['num']));
    }
    function addtypes(){
        if($_GET['action']=='edit'){
            $name=$_GET['name'];
            //判断分类是否已存在
            $exist=$this->mysql->select_one('types',"where name='$name'");
            //echo $exist; 为空时返回结果是1
            if(!empty($exist['id'])){
                echo  json_encode(array('status'=>0,'msg'=>'该分类已存在！'));
                exit();
            }
            $arr=array('name'=>$name);
            $res=$this->mysql->edit('types',$arr,$_GET['id']);
            if(empty($res)){
                echo  json_encode(array('status'=>1,'msg'=>'修改成功'));
            }else{
                echo json_encode(array('status'=>0,'msg'=>'修改失败'));
            }

        }else{
            $name=$_GET['name'];
            //判断分类是否已存在
            $exist=$this->mysql->select_one('types',"where name='$name'");
            //echo $exist; 为空时返回结果是1
            if(!empty($exist['id'])){
                echo  json_encode(array('status'=>0,'msg'=>'该分类已存在！'));
                exit();
            }
          //获取上一条记录的标记 【0123456789abcdefghijklmnopqrstuvwyzABCDEFGHIJKLMNOPQRSTUVWYZ】
            $signs=$this->mysql->select_one('types',"order by id DESC");
            //此处返回false
            if(empty($signs['id'])){
                $sign='1';
            }else{
                $allSign="123456789abcdefghijklmnopqrstuvwyzABCDEFGHIJKLMNOPQRSTUVWYZ";
                $pos=mb_strpos($allSign, $signs['sign'], 0, 'UTF-8');
                $curpos=$pos+1;
                $sign=mb_substr($allSign, $curpos, 1, 'UTF-8');
            }

            $fied="name,sign";
            $arr=array($name,$sign);
            $res=$this->mysql->add('types',$fied,$arr);
            if(empty($res)){
                echo  json_encode(array('status'=>1,'msg'=>'添加成功'));
            }else{
                echo json_encode(array('status'=>0,'msg'=>'添加失败'));
            }
        }

    }
    function deletypes(){
        if(!empty($_GET['id'])){
            $res=$this->mysql->delect('types',$_GET['id']);
            if(empty($res)){
                echo  json_encode(array('status'=>1));
            }else{
                echo json_encode(array('status'=>0));
            }
        }

    }

    /*网站信息配置*/
    function web_info(){
        //设置选项卡项
        $config_tab_arr = array('基本设置','添加变量');
        $res1=$this->mysql->selects("webconfig",'where vargroup=0 ORDER BY id ASC');
        $this->display('web_info',$config_tab_arr,$this->view_page,array($res1));
    }
    function saveweb_info(){
        $config_cache  = Core.'/config.cache.php';
        $gourl="/index.php?c=back&a=web_info";
        if($_POST['action']==='update'){
            foreach($_POST as $k=>$v)
            {
               // echo ($v);
                //统计代码转义
                $v = _RunMagicQuotes($v);
                $arr=array("varvalue"=>$v);
                if($this->mysql->edit('webconfig',$arr,''," WHERE varname='$k'")['status']===2)
                {
                    ShowMsg('更新变量失败，可能有非法字符！',$gourl );
                    exit();
                }
            }
            $this->WriteConfig($config_cache,$gourl);

           ShowMsg('成功保存变量并更新配置文件！', $gourl);
            exit();

            }
        //增加新变量
        if($_POST['action'] === 'add')
        {
            $varname=trim($_POST['varname']);
            $vartype=trim($_POST['vartype']);
            $varvalue=trim($_POST['varvalue']);
            $varinfo=trim($_POST['varinfo']);
            $vargroup=trim($_POST['vargroup']);

            if($varname == '' || preg_match('/[^a-z_]/', $varname))
            {
                ShowMsg('变量名不能为空并必须为[a-z_]组成！', $gourl);
                exit();
            }

            //链接前缀
            $varname = 'cfg_'.$varname;

            if($vartype=='bool' && ($varvalue!='Y' && $varvalue!='N'))
            {
                ShowMsg('布尔变量值必须为\'Y\'或\'N\'！', $gourl);
                exit();
            }
            if($this->mysql->select_one("webconfig","WHERE varname='$varname'"))
            {
                ShowMsg('该变量名称已经存在！', $gourl);
                exit();
            }
            $fied=" varname, varinfo, varvalue, vartype, vargroup";
            $arr=array($varname,$varinfo, $varvalue, $vartype, $vargroup);
            if(!empty($this->mysql->add("webconfig",$fied,$arr)['status']))
            {
                ShowMsg('新增变量失败，可能有非法字符！', $gourl);
                exit();
            }

            $this->WriteConfig($config_cache,$gourl);
            ShowMsg('成功保存变量并更新配置文件！', $gourl);
            exit();

        }

    }
    function  WriteConfig($config_cache,$gourl){
        $res=$this->mysql->selects("webconfig",'ORDER BY id ASC');
        $str = '<?php	'."\r\n\r\n";
        foreach($res as $row) {
            //强制去掉 '
            //强制去掉最后一位 /
            $vartmp = str_replace("'",'',$row['varvalue']);

            if(substr($vartmp, -1) == '\\')
            {
                $vartmp = substr($vartmp,1,-1);
            }

            if($row['vartype'] == 'number')
            {
                if($row['varvalue'] == '')
                {
                    $vartmp = 0;
                }

                $str .= "\${$row['varname']} = ".$vartmp.";\r\n";
            }
            else
            {
                $str .= "\${$row['varname']} = '".$vartmp."';\r\n";
            }
        }
        $str .="\r\n?>";
        if(!Writef($config_cache,$str))
        {
            ShowMsg("变量成功保存，但由于 config.cache.php 无法写入，因此不能更新配置！", $gourl);
            exit();
        }

    }

    /*表*/
    function web_table(){
        if(!empty($_GET['status'])){
            $where =' where status='.$_GET['status'];
        }else{
            $where ='';
        }
        $res=$this->mysql->selects("table",$where);
        $this->display('web_table',$res,$this->view_page);
    }

    function addtable(){
        $this->display('addtable','',$this->view_page);
    }
    function edittable(){
        $id=$_GET['id'];
        if(empty($id)){
            exit();
        }
        if(!empty($_GET['action'])&&$_GET['action']==='delete'){
            $arr=array('status'=>$_GET['status']);
            $res=$this->mysql->edit('table',$arr,$id);
           echo json_encode(array('status'=>1));
        }else{
            $where="where id='$id'";
            $table=$this->mysql->select_one('table',$where);
            $this->display('edittable',$table,$this->view_page);
        }

    }
    function  savetable()
    {
        $name = $_POST['name'];
        $tableName = $_POST['tableName'];
        if (preg_match('/^(\w+)$/', $tableName)) {
            if($_POST['action']=='add'){
                $filed='name,tableName,status';
                $arr=array($name,$tableName, $_POST['status']);
                $res=$this->mysql->add('table',$filed,$arr);
                $msg='添加成功';
            }else if($_POST['action']=='edit' &&!empty($_POST['id'])){
                $arr=array('name'=>$name,'tableName'=>$tableName,'status'=>$_POST['status']);
                $res=$this->mysql->edit('table',$arr,$_POST['id']);
                $msg='修改成功';
            }
            if(empty($res)){
                ShowMsg($msg,'index.php?c=back&a=web_table');
            }else{
                $this->display($_POST['page'],$this->view_page);
            }


        }else{
            ShowMsg('表名只能有字母或者数字','-1');
        }
    }

    /*导航栏的表的处理*/
    function web_urls(){
        $res=$this->mysql->selects("nav");
        $this->display('web_urls',$res,$this->view_page);
    }
    function addurls(){
        include_once Core."/tree2.php";
        $trees=gettree('nav','',$this->mysql);
        $table=$this->mysql->selects('table'," where status='1'");
        /*获取当前排序*/
        $r1 = $this->mysql->select_one("nav","","MAX(orderid) AS orderid");
        $orderid = (empty($r1['orderid']) ? 1 : ($r1['orderid'] + 1));
        $this->display('addurls',$trees,$this->view_page,array('table'=>$table,'orderid'=>$orderid));
    }
    function editurls(){
        $id=$_GET['id'];
        if(empty($id)){
            exit();
        }
        if(!empty($_GET['action'])&&$_GET['action']==='delete'){
            $arr=array('status'=>$_GET['status']);
            $res=$this->mysql->edit('table',$arr,$id);
            echo json_encode(array('status'=>1));
        }else{
            $where="where id='$id'";
            $nav=$this->mysql->select_one('nav',$where);
            include_once Core."/tree2.php";
            $trees=gettree('nav','',$this->mysql);
            $table=$this->mysql->selects('table'," where status='1'");
            $this->display('editurls',$nav,$this->view_page,array('table'=>$table,'parent'=>$trees));
        }

    }
    function  saveurls()
    {
        $name=$_POST['name'];
        $relinkurl=$_POST['relinkurl'];
        $tableType=$_POST['tableType'];
        $parentid=$_POST['parentid'];
        if(!empty($name)&&!empty($relinkurl)){
            if($_POST['action']==='add'){
                //获取所有父级id
                if($parentid == 0)
                {
                    $parentstr = '0,';
                }
                else
                {
                    $r=$this->mysql->select_one('nav',' where id='.$parentid);
                    $parentstr = $r['parentstr'].$parentid.',';
                }

                $filed='name,linkurl,relinkurl,tableType,parentid,parentstr,status,orderid';
                $arr=array($name,$_POST['linkurl'],$relinkurl,$tableType,$parentid,$parentstr, $_POST['status'],$_POST['orderid']);
                $res=$this->mysql->add('nav',$filed,$arr);
                $msg='添加成功';
            }else if($_POST['action']==='edit' && !empty($_POST['id'])){
                if($parentid == 0)
                {
                    $parentstr = '0,';
                }
                else
                {
                    $r=$this->mysql->select_one('nav',' where id='.$parentid);
                    $parentstr = $r['parentstr'].$parentid.',';
                }
                $arr=array('name'=>$name,'linkurl'=>$_POST['linkurl'],'relinkurl'=>$relinkurl,'tableType'=>$tableType,'parentid'=>$parentid,
                    'parentstr'=>$parentstr,'status'=>$_POST['status'],'orderid'=>$_POST['orderid']);
                $res=$this->mysql->edit('nav',$arr,$_POST['id']);
                $msg='修改成功';
            }
            if(empty($res)){
                ShowMsg($msg,'index.php?c=back&a=web_urls');
            }else{
                $this->display($_POST['page'],$this->view_page);
            }

        }


    }

    /*关于*/
    function aboutus(){
        $res=$this->mysql->selects("aboutus");
        $this->display('aboutus',$res,$this->view_page);
    }
    function addaboutus(){
        $this->display('addaboutus','',$this->view_page);
    }
    function editaboutus(){
        $id=$_GET['id'];
        if(empty($id)){
            exit();
        }
        if(!empty($_GET['action'])&&$_GET['action']==='delete'){
            $arr=array('status'=>$_GET['status']);
            $res=$this->mysql->edit('table',$arr,$id);
            echo json_encode(array('status'=>1));
        }else{
            $where="where id='$id'";
            $arr=$this->mysql->select_one('aboutus',$where);
            $this->display('editaboutus',$arr,$this->view_page);
        }

    }
    function  saveaboutus()
    {
        if(!empty($_POST['varname'])){
            $content=addslashes($_POST['content']);
            if($_POST['action']==='add'){
                $filed='varname,varinfo,varvalue,content,addtime';
                $arr=array($_POST['varname'],$_POST['varinfo'],$_POST['varvalue'],$content,time());
                $res=$this->mysql->add('aboutus',$filed,$arr);
                $msg='添加成功';
            }else if($_POST['action']==='edit' && !empty($_POST['id'])){

                $arr=array('varname'=>$_POST['varname'],'varinfo'=>$_POST['varinfo'],'varvalue'=>$_POST['varvalue'],'content'=>$content,'edittime'=>time());
                $res=$this->mysql->edit('addaboutus',$arr,$_POST['id']);
                $msg='修改成功';
            }
            if(empty($res)){
                ShowMsg($msg,'index.php?c=back&a=aboutus');
            }else{
                $this->display($_POST['page'],$this->view_page);
            }

        }


    }
    function addxxx(){
        $this->display('addxxx','',$this->view_page);
    }
    function editxxx(){
        $id=$_GET['id'];
        $res=$this->mysql->select_one("yx_infolist","WHERE id=$id ");
        $this->display('editxxx',$res,$this->view_page);
    }
    function savexxx(){
        $action=$_POST['action'];
        $upimg=empty($_POST['upimgname'])? "":$_POST['upimgname'];
        $headimg=empty($_POST['headimg'])? "":$_POST['headimg'];
        $headimg=serialize($headimg);
        $author=empty($_POST['author'])?"":$_POST['author'];
        $hits=empty($_POST['hits'])?"":$_POST['hits'];
        $description=empty($_POST['description'])?"":$_POST['description'];
        if($action=='add'){
            if(!empty($_POST['title'])){
                $filed='content,title,author,picurl,headimg,hits,posttime,description,classid ,checkinfo';
                //$content=addslashes($_POST['content']);
               $content=($_POST['content']);
                $arr=array($content,$_POST['title'],$author,$upimg,$headimg,$hits,time(),$description,$_POST['classid'],'true');
                $res=$this->mysql->add('yx_infolist',$filed,$arr);

            }
        }else{
            //$content=addslashes($_POST['content']);
            $content=($_POST['content']);
            /*获取所有图片*/
            $title=$_POST['title'];
            $arr=array('content'=>$content,'title'=>$title,'author'=>$author,'picurl'=>$upimg,'headimg'=>$headimg,'hits'=>$hits,'description'=>$description,'classid'=>$_POST['classid']);
            $res=$this->mysql->edit('yx_infolist',$arr,$_POST['id']);
        }
        if($res){
            header("location:index.php?c=back&a=addxxx");
        }else{
            header("location:".$_SERVER['HTTP_REFERER']);
        }

    }

    function sitemap(){
        $this->display('sitemap','',$this->view_page);
    }

}
?>