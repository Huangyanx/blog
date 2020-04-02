<?php

class BookmarkController extends Controller
{

    public $view_page='/back/';
    public $mysql;
    function __construct()
    {
       // parent::__construct();
        $this->mysql=$this->loadModel();
        $where=" where parentid=16";
        $this->nav=$this->mysql->selects('type',$where);
        $where=" where parentid=21";
        $this->bookmark=$this->mysql->selects('type',$where);
    }
    function index(){
        $belong=$_GET['belong'];
        $where=" where belong ='$belong'";
        $arr=$this->mysql->selects('bookmarking',$where);
        //print_r($arr);
        $this->display("bookmark",$arr,$this->view_page);
        //include_once("Views/back/manual.php");
    }
    function show(){
        /*文件预览*/
        $id=$_GET['id'];
        //$belong=$_GET['belong'];
        $where="where manual_id='$id'";
        $manual=$this->mysql->select_one('manual_content',$where);
        if(!empty($manual['content'])) $manual['content']=stripslashes($manual['content']);
        echo json_encode(array('status'=>1,'data'=>$manual));
    }
    function addManual(){
        $fied='name,parentid,belong,addtime';
        $names=$_GET['names'];
        $parentids=$_GET['parentids'];
        $belong=$_GET['belong'];
        $posttime=time();
        $len=count($names);
        $arr=array();
        for ($i=0;$i<$len;$i++){
            $arr[$i]['name']=$names[$i];
            $arr[$i]['parentid']=$parentids[$i];
            $arr[$i]['belong']=$belong;
            $arr[$i]['addtime']=$posttime;
        }
       $res=$this->mysql->adds('manual',$fied,$arr);
        echo json_encode(array('status'=>1));
    }
    function saveManual(){
        $id=$_GET['id'];
        $content=addslashes($_GET['content']);
        $name=$_GET['name'];
        if($_GET['action']=='edit'){
            $where="content='$content' where manual_id='$id'";
            $res=$this->mysql->edit1('manual_content',$where);
            echo json_encode(array('status'=>1,'msg'=>'修改成功！'));

        }else{
            $fied='name,content,manual_id';
            $arr=array($name,$content,$id);
            $res=$this->mysql->add('manual_content',$fied,$arr);
            echo json_encode(array('status'=>1));

        }
    }
}
?>