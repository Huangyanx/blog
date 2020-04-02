<?php

class DatasController extends Controller
{

    public $view_page='/datas/';
    public $mysql;
    function __construct()
    {
       // parent::__construct();
        $this->mysql=$this->loadModel();
    }
    function index(){
        $navs=$this->getnav();
        $table=$this->mysql->getFields('file');
        $this->display("index",$table,$this->view_page,array('nav'=>$navs));

    }
    function getFields(){
        $table=$_GET['table'];
        $res=$this->mysql->getFields($table);
        if($res){
            echo json_encode(array('status'=>1,'fields'=>$res));
        }else{
            echo json_encode(array('status'=>0));
        }

    }

    /*将一张表分为两张表*/
    function one_table_to_two(){
        $oldtable=$_GET['oldtable'];
        $newtable=$_GET['newtable'];
        $newstable='blog_'.$newtable;
        $tips=$_GET['tips'];
        $name=$_GET['name'];
        $field_type=$_GET['field_type'];
        $length=$_GET['length'];
        $len=count($name);

        $link=$this->mysql->getLink();

        if(!$this->mysql->isTable($newstable)){
            /*第一步：新建表*/
            $sql = "CREATE TABLE $newstable (
        id INT(255) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
       ";
            $fied='';//第二步的$fied
            for ($i=0;$i<$len;$i++){
              $lens=empty($length[$i]) ? '':"(".$length[$i].")";
                 $length[$i];
                 if($i==$len-1) {
                     $fied.=$name[$i];
                     $sql.=$name[$i].' '.$field_type[$i].$lens." COMMENT '".$tips[$i]."' \n";
                 }else{
                     $sql.=$name[$i].' '.$field_type[$i].$lens." COMMENT '".$tips[$i]."' ,\n";
                     $fied.=$name[$i].', ';
                 }

            }
            $sql.=" )";
            $count = $link->exec($sql);
            if($count>=0){
                $msg="数据表 ".$newstable." 创建成功";
                /*第二步：获取旧表对应的数据*/
                $where=" where url='' AND is_show=''";
                $datas=$this->mysql->selects($oldtable,$where);
                $msg.="获取旧表".$oldtable."数据成功";
                /*第三步：新表插入新的数据*/
                $len1=count($datas);
                $arr=array();
                for ($i=0;$i<$len1;$i++){
                    for ($j=0;$j<$len;$j++){
                        $fiedname=$name[$j];
                        if($fiedname=='content') {
                            $arr[$i][$fiedname]=addslashes($datas[$i][$fiedname]);}
                        else{
                            $arr[$i][$fiedname]=$datas[$i][$fiedname];
                        }

                    }
                }
                $this->mysql->adds($newtable,$fied,$arr);
                echo json_encode(array('stutas'=>2,'msg'=>$msg));
            }
        }else{
            echo json_encode(array('stutas'=>2,'msg'=>'该数据表已经存在'));
        }

    }

    /*表中某一字段赋予另一表的某字段的值*/
    function assign_field_value(){
        $table=$_GET['table'];
        $condition=$_GET['condition1'];
        $tables=$_GET['tables'];
        $name=$_GET['name'];
        $curname=$_GET['curname'];
        $len=count($name);
        /*第一步：获取表 字段的值*/
        /*新的表去重*/
        $newstable=array_unique($tables);

        /*获取所有数据*/
        $getdata=array();
        foreach ($newstable as $val){
            $getdata[]=$this->mysql->selects($val);
        }
        /*条件中 找出所有带new的字段 where cur.type=new.id
        */
        /*空格分开  =号分开  ----$arr1为当前表的字段   $arr2为新表的字段--------*/
        $arr1 = array();$arr2 = array();
        for ($k=0;$k<$len;$k++){
            if(!empty($condition[$k])) {
                $arr3 = explode(' ', $condition[$k]);
                foreach ($arr3 as $val) {
                    if (stripos($val, '=')) {
                        $arr5 = explode('=', $val);
                    }
                }
                /*将带有cur new的字段获取*/
                foreach ($arr5 as $val) {
                    $arr4 = explode('.', $val);
                    if (is_int(stripos($val, 'cur'))) {
                        $arr1[$k][]=$arr4[1];

                    } else if (is_int(stripos($val, 'new'))) {
                        $arr2[$k][]=$arr4[1];
                    }
                }

            }else{
                    $arr1[$k]='id';
            }


        }
        print_r($arr1);
        print_r($arr2);
    exit();


            /*修改表中对应的值改为*/
        $fied='';$glue='';
        for ($i=0;$i<$len;$i++){
            $names=$curname[$i];
            $old_name=$curname[$i];
            $fied.=$glue.$curname[$i];
            $namefied= $names.'= CASE type';
            foreach ($getdata[$i] as $val){
                $id=$val['id'];
                $old_val=$val[$old_name];
                $arr[$i][$names]=$val[$old_name];
                $namefied.="\n WHEN  $id THEN '$old_val' ";
            }
            $namefied.="\n END,";
        }
      echo $namefied;


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