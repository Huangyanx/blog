<?php
// 将数据按照所属关系封装 见图2
function arr2tree($tree, $rootId = 0) {
    $return = array();
    foreach($tree as $leaf) {
        if($leaf['parentid'] == $rootId) {
            foreach($tree as $subleaf) {
                if($subleaf['parentid'] == $leaf['id']) {
                    $leaf['children'] = arr2tree($tree, $leaf['id']);
                    break;
                }
            }
            $return[] = $leaf;
        }
    }
    return $return;
}
/*-----注意：该数据是从数据库中获取的，必须进行数据库操作-
--$id  父级id（限制了父级id只能为$id）
--$notid 不包含 父级id为$notid的
--*/
function gettree($table,$id='',$db,$notid=''){
    /*获取一级菜单*/
    $pid=0;
    $where="where parentid='$pid'";
    $where=empty($id)? $where:$where." and id='$id'";
    $where=empty($notid)?$where:$where." and id NOT IN ".$notid;
    $arr=$db->selects($table,$where);
    if(count($arr)){
        $i=0;
        $res=array();
        foreach ($arr as $val1){
            $res[$i]=$val1;
            /*二维菜单获取值  ----
            -----要记住当前数组位置进行下一次存储-------*/
            $res[$i]['child']=sontree($table,$val1['id'],$db);
            $i++;
        }
    }else{
        $res='';
    }
    return $res;

}
function sontree($table,$pid,$db){
    $where="where parentid='$pid'";
    $arr=$db->selects($table,$where);
    //print_r($arr);
    if(count($arr)>0){
        $res=array();
        $i=0;
        foreach ($arr as $val1){
            $res[$i]=$val1;
           // print_r($res);
            /*下一维菜单获取值  ----
            -----要记住当前数组位置进行下一次存储-------*/
            $res[$i]['child']=sontree($table,$val1['id'],$db);
            $i++;
        }
    }else{
        $res='';
    }
    return $res;

}



