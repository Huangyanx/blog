<?php

class FileController extends Controller
{

    public $view_page='/file/';
    public $mysql;
    function __construct()
    {
       // parent::__construct();
        $this->mysql=$this->loadModel();
    }
    function index(){
        //print_r(get_parent_class());
        //$action=$this->action;
        $this->display("file/index");
        //include_once ROOT_PATH.'/Views/file/'.$_GET['view'].'index.html';
    }
    function preview(){
        /*文件预览*/
        $id=$_GET['id'];
        $where="where id='$id'";
        $file=$this->mysql->select_one('file',$where);
        /*获取导航栏*/
        //$nav=$this->mysql->selects('type');
        $navs=$this->getnav();
        $this->display('previewfile',$file,$this->view_page,array('nav'=>$navs));
    }
    function lists(){
        /*文件预览*/
        $id=$_GET['id'];
        $where="where id='$id'";
        $file=$this->mysql->select_one('file',$where);
        /*获取导航栏*/
        //$nav=$this->mysql->selects('type');
        $navs=$this->getnav();
        $this->display('list',$file,$this->view_page,array('nav'=>$navs));

    }
    function show(){
        /*文件预览*/
        $id=$_GET['id'];
        $where="where id='$id'";
        $file=$this->mysql->select_one('file',$where);
        /*获取导航栏*/
        //$nav=$this->mysql->selects('type');
        $navs=$this->getnav();
        $this->display('show',$file,$this->view_page,array('nav'=>$navs));

    }

    /*查看文件-----
    所有文件都在该页面展示*/
    function viewFile(){
        $this->display('viewFile.php','',$this->view_page);
    }

    /*一级菜单----导航栏----------*/
    function getnav(){
        $pid=0; $table='type';
        $where="where parentid='$pid'";
        $arr=$this->mysql->selects($table,$where);
        $res='';
        foreach ($arr as $val1){
                $res.="<li><a href='index.php?c=back&a=files&kfiled=type&keyword='".$val1['id'].">".$val1['name']."</a>".$this->getSecNav($table,$val1['id'])."</li> ";
        }
        return $res;


    }
    /*二级以上菜单*/
    function getSecNav($table,$id){
        $table='type';
        $where="where parentid='$id'";
        $arr=$this->mysql->selects($table,$where);
        $sec="<ul class='secondMenu'>";
        $scont='';
        foreach ($arr as $val){
            $scont.="<li><a href='index.php?c=back&a=files&kfiled=type&keyword='".$val['id'].">".$val['name']."</a>".$this->getSecNav($table,$val['id'])."</li> ";

        }
        if(empty($scont)){
            return ;
        }else{
            $sec.=$scont."</ul>";
            return $sec;
        }

    }
}
?>