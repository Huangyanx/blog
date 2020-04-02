<?php

class PictureController extends Controller
{

    public $view_page='/picture/';
    public $mysql;
    function __construct()
    {
       // parent::__construct();
        $this->mysql=$this->loadModel();

    }
    function index(){
        //print_r(get_parent_class());
        //$action=$this->action;
        $this->display("picture/".$_GET['view'].'/index');
        //include_once ROOT_PATH.'/Views/file/'.$_GET['view'].'index.html';
    }
    function show(){
        /*文件预览*/
        $id=$_GET['id'];
        $where="where id='$id'";
        $file=$this->mysql->select_one('picture',$where);
        /*获取导航栏*/
        //$nav=$this->mysql->selects('type');
        $navs=$this->getnav();
        $this->display('show',$file,$this->view_page,array('nav'=>$navs));
    }
    function lists(){
        /*文件预览*/
        $id=$_GET['id'];
        $where="where id='$id'";
        $file=$this->mysql->select_one('picture',$where);
        /*获取导航栏*/
        //$nav=$this->mysql->selects('type');
        $navs=$this->getnav();
        $this->display('list',$file,$this->view_page,array('nav'=>$navs));
    }
    function muli_img_upload(){
        if($_POST['datas']){
            $imgages=$_POST['datas'];
            $name=$_POST['name'];
            $directory=Vpicture.'/'.$name;
            $dataname=array();
            /*允许的图片类型 ----gif,jpg,jpeg,bmp,png--------*/
            $fietype=array("data:image/gif;base64,","data:image/jpg;base64,","data:image/jpeg;base64,","data:image/bmp;base64,","data:image/png;base64,");
            foreach ($imgages as $val){
                $img = str_replace($fietype,'', $val);
                $img = str_replace(' ','+', $img);
                $img = base64_decode($img);
                /*获取文件类型*/
                $str= substr($val , 0 , 30);
                /*获取分号前的字符串*/
                $arr1=explode(";",$str);
                $arr=explode("/",$arr1[0]);
                $file_name = time().'_'.mt_rand().'.'.$arr[1];
                $dataname[]=$file_name;
                $file = $directory.'/'.$file_name;
                $arr = file_put_contents($file, $img);
                if(!$arr){
                    $dataname="出错";
                }
            }
            echo json_encode(array('status'=>1,'data'=>$dataname));
        }

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