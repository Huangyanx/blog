<?php

class ManualController extends Controller
{

    public $view_page='/back/';
    public $mysql;
    function __construct()
    {
        if (session_status() != 2) {
            @session_start();
        }
       // parent::__construct();
        $this->mysql=$this->loadModel();
        $where=" where parentid=16";
        $this->nav=$this->mysql->selects('type',$where);
        $where=" where parentid=21";
        $this->bookmark=$this->mysql->selects('type',$where);
        $where2=" where parentid=32";
        $this->manual=$this->mysql->selects('type',$where2);
    }
    function index(){
        $belong=$_GET['belong'];
        $where=" where belong ='$belong'";
        $arr=$this->mysql->selects('manual',$where);
        //print_r($arr);
        $this->display("manual",$arr,$this->view_page);
        //include_once("Views/back/manual.php");
    }
    function show(){
        /*文件预览*/
        $id=$_POST['id'];
        //$belong=$_GET['belong'];
        $where="where manual_id='$id'";
        $manual=$this->mysql->select_one('manual_content',$where);
        if(!empty($manual['content'])) $manual['content']=stripslashes($manual['content']);
        echo json_encode(array('status'=>1,'data'=>$manual));
    }
    function addManual(){
        /*有添加多个、保存多个的操作*/
        /*当有没有当前ID时，进行添加操作，当全部有ID，进行保存操作*/
        $newname=array();
        $newparent=array();
        $names=$_GET['names'];
        $parentids=$_GET['parentids'];
        $belong=$_GET['belong'];
        $len=count($names);
        if($len>0&& is_array($names)){
            /*通过ID查看该选项是否存在*/
            for($i=0;$i<$len;$i++){
                $ids=$_GET['ids'][$i];
                if(!empty($ids)){
                    $where="where id =$ids " ;
                    $is_in=$this->mysql->select_one('manual',$where);
                    if(!$is_in) {
                        $newname[]=$names[$i];
                        $newparent[]=$parentids[$i];
                    }
                }else{
                    $newname[]=$names[$i];
                    $newparent[]=$parentids[$i];
                }

            }

            if(count($newname)>=1){
                $fied='name,parentid,belong,addtime';
                $posttime=time();
                $len1=count($newname);
                $arr=array();
                for ($i=0;$i<$len1;$i++){
                    $arr[$i]['name']=$newname[$i];
                    $arr[$i]['parentid']=$newparent[$i];
                    $arr[$i]['belong']=$belong;
                    $arr[$i]['addtime']=$posttime;
                }

                $res=$this->mysql->adds('manual',$fied,$arr);
                echo json_encode(array('status'=>1,'添加成功'));
            }else{
                /*所有的ID已存在，进行保存操作*/
                $ids=$_GET['ids'];
                $ids1='';$glue='';$namesfield=' name = CASE id';$parentsfield=' parentid = CASE id';
                for($i=0;$i<$len;$i++){
                    $id=$ids[$i];
                    $name=$names[$i];
                    $parentid=$parentids[$i];
                    $ids1.=$glue.$id;
                    $namesfield.="\n WHEN $id THEN '$name' ";
                    $parentsfield.="\n WHEN $id THEN $parentid ";
                    $glue=',';
                }
                $where=$namesfield."\n END,".$parentsfield." \n END where id in ($ids1)";
                $res=$this->mysql->edit1('manual',$where);
                echo json_encode(array('status'=>2,'msg'=>'修改成功！'));
            }
        }else{
            echo json_encode(array('status'=>0,'msg'=>'请输入内容！'));
        }


    }
    function saveManual(){
        $id=$_POST['id'];
        include_once (FPATH."/article_fun.php");
        $hand_cont=transform_local_img($_POST['content']);
        $content=addslashes($hand_cont['body']);
        $name=$_POST['name'];
        $remark=empty($_POST['remarks']) ? '':$_POST['remarks'];
        if($_POST['action']=='edit'){
            $cur=time();
            $where="title='$name',content='$content',remarks='$remark',posttime=$cur where manual_id='$id'";
            $res=$this->mysql->edit1('manual_content',$where);
            echo json_encode(array('status'=>1,'msg'=>'修改成功！'));

        }else{
            $fied='title,content,manual_id,remarks,posttime';
            $arr=array($name,$content,$id,$remark,time());
            $res=$this->mysql->add('manual_content',$fied,$arr);
            echo json_encode(array('status'=>1));

        }
        include_once (FPATH."usemap.php");
    }
    function deleteManual(){
        //删除操作，只删除没有父级的
        $ids=$_POST['ids'];
        if(empty($ids[0])){
            echo json_encode(array('status'=>111,'msg'=>'请输入要删除的id'));
            exit();
        }
        $glue='';$id='';
        foreach ($ids as $key=>$val){
            //查看 id是否有父级，有父级禁止删除
           $r1=$this->mysql->select_one('manual',"where parentid=$val");
           if(!$r1){
               $id.=$glue.$val;
               $glue=',';
           }
        }
        if(!empty($id)){
            if(stripos($id,',')) $id='('.$id.')';
            //内容删除
            $res1=$this->mysql->delect('manual_content','',$id);
            if($res1){
                $res2=$this->mysql->delect('manual','',$id);
                if($res2){
                    echo json_encode(array('status'=>'ok','msg'=>'删除成功'));
                }else{
                    echo json_encode(array('status'=>122,'msg'=>'删除失败'));
                }

            }else{
                echo json_encode(array('status'=>121,'msg'=>'删除内容失败'));
            }
        }


    }
    function search(){
        $keyword=$_POST['keyword'];
        $belong=$_POST['belong'];
        $p=isset($_POST['sear_p']) ?  $_POST['sear_p']: 1;
        $start=($p-1)*10;

        if(empty($keyword) || empty($belong)){
            echo json_encode(array('status'=>111,'errmsg'=>'请填写完整信息'));
            exit();
        }

        $query="select m.id,m.name from blog_manual as m JOIN blog_manual_content as c where m.id=c.manual_id";
        /*搜索条件*/
        $query.=" and (m.name like '%$keyword%' or c.remark like '%$keyword%')";
        if($belong!=='all'){
            $query.=" and belong='$belong'";
        }
        $query.=" limit $start,10";
       // echo $query;
        $res=$this->mysql->selects('','',1,'',$query);
        if($res){
            echo json_encode(array('status'=>'ok','data'=>$res));
            exit();
        }
    }
}
?>