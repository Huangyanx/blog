<?php
/**
 *
 */
class Model
{
    private $pdo;
    public $db_tablepre;
    function __construct()
    {
        global $conf;
        $db_driver=$conf['db_driver'];
        $host=$conf['host'];
        $db_name=$conf['db_name'];
        $this->db_tablepre=$conf['db_tablepre'];
        try{
            $this->pdo= new PDO("$db_driver:host=$host;dbname=$db_name;charset=utf8",$conf['user'],$conf['db_pwd']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        }catch(PDOException $e){
            echo  $e->getMessage();
        }
    }

    function select_one($table,$where,$field='',$sql='',$acctype=PDO::FETCH_ASSOC){
        if(empty($sql)) {
            if (!stripos($table, $this->db_tablepre)) $table = $this->db_tablepre . $table;
            $field = empty($field) ? '*' : $field;
            $sql = "select " . $field . " from $table $where";
        }
        //echo $sql;
        $res=$this->pdo->prepare($sql);
        $res->execute();
        if ($this->pdo->errorCode()!=00000) {
            echo $this->pdo->errorInfo()['2'];
            return false;
        }else{
            return $news=$res->fetch($acctype);
        }

    }
    function selects($table,$where='',$total='',$field='',$sql=''){
        if(empty($sql)){
            if(!stripos($table,$this->db_tablepre)) $table =$this->db_tablepre.$table;
            $field=empty($field) ? '*' : $field;
            $sql="select $field from $table $where";
        }
       // echo $sql.'<br>';
        $res=$this->pdo->prepare($sql);
        $res->execute();
        if ($this->pdo->errorCode()!=00000) {
          //  echo $this->pdo->errorInfo()['2'];
        }else{
            if($total==1){//总记录数
                return array("res"=>$res->fetchAll(),"num"=>$res->rowCount());
            }else{
                return $res->fetchAll();
            }
        }
    }
    function delect($table,$where='',$id=''){
        if(!stripos($table,$this->db_tablepre)) $table =$this->db_tablepre.$table;
        if(!empty($id)){
            $where .=stripos($id,',') ? " id in $id":" id= $id";
        }
        if(empty($where)) return false;
        $sql="delete from $table where ".$where;
        $res=$this->pdo->prepare($sql);
        $res->execute();
        if ($this->pdo->errorCode() != 00000) {
           // echo $this->pdo->errorInfo()['2'];
            return false;
        }else{
            return true;
        }
    }
    function add($table,$fied,$arr){
        if(!stripos($table,$this->db_tablepre)) $table =$this->db_tablepre.$table;
        $num=substr_count($fied,',')+1;
        $values='';$glue='';
        for ($i=0;$i<$num;$i++){
            $values.=$glue."?";
            $glue=',';
        }
        $squery="insert into $table ($fied) values ($values)";

        $sql=$this->pdo->prepare($squery);
       for ($i=0;$i< count($arr);$i++) {
            $sql->bindValue(($i+1),$arr[$i]);
        }
        $sql->execute();
        if ($this->pdo->errorCode()!=00000) {
             //echo $this->pdo->errorInfo()['2'];
            return false;
        }else{
            return true ;
        }
    }
    function edit($table,$arr,$id='',$where=''){
        if(!stripos($table,$this->db_tablepre)) $table =$this->db_tablepre.$table;
        $sql="update $table set ";
        if(!empty($where) && !empty($id)){
            $where .=" and id =$id";
        }else if(!empty($id)){
            $where .=" where id =$id";
        }else if(!empty($where)){

        }else{
            $where="";
        }
            $dou="";
            foreach ($arr as $key => $value) {
                $sql.=$dou.$key."='".$value."'";
                $dou=",";
            }
        $sql.=$where;
       // echo $sql;
        $this->pdo->exec($sql);
        if ($this->pdo->errorCode()!=00000) {
          //  return array('status'=>2,'msg'=>$this->pdo->errorInfo()['2']);
            return false;
        }else{
            return true;
        }
    }
    /*批量修改*/
    function edit1($table,$where=''){
        if(!stripos($table,$this->db_tablepre)) $table =$this->db_tablepre.$table;
        $sql="update $table set ";
        if(!empty($where)){
            $sql.=$where;
        }
        $this->pdo->exec($sql);
        if ($this->pdo->errorCode()!=00000) {
            return $this->pdo->errorInfo()['2'];
        }else{
            return;
        }
    }
    function getcount($table,$where='',$sql=''){
        if(empty($sql)){
            if(!stripos($table,$this->db_tablepre)) $table =$this->db_tablepre.$table;
            $sql = "select count(id) from ".$table.$where;
        }
       // echo $sql;
        $result = $this->pdo->query($sql);//提交sql
        $rowsNumber = $result->fetchColumn();//取回结果集中的一个字段
       return $rowsNumber;
    }
    /*获取表字段*/
    function getFields($table){
        if(!stripos($table,'_')) $table =$this->db_tablepre.$table;
        if(!$this->isTable($table)){
            return false;
        }
        $stmt = $this->pdo->prepare('DESC '.$table);
        $stmt->execute();
        if ($this->pdo->errorCode()!=00000) {
             echo $this->pdo->errorInfo()['2'];
        }else{
            return $news=$stmt->fetchAll(PDO::FETCH_COLUMN);
        }
    }

    /*检测是否某数据表*/
    function isTable($table){
        if(!stripos($table,$this->db_tablepre)) $table =$this->db_tablepre.$table;
        $sql = $this->pdo->query("show tables")->fetchAll(PDO::FETCH_GROUP);
        if ( in_array($table, array_keys($sql)) ) {
           return true;
        } else {
            return false;
        }
    }
    /*插入多条记录
    $table,$fied,$arr 表、字段、值（二维数组）
    */

    function getLink(){
        return $this->pdo;
    }
    function adds($table,$fied,$arr){
        /*$arr 为二维数组*/
        $table=$this->db_tablepre.$table;
       //print_r($arr);
        /*将$values 拼接成（值，值），（值）的形式*/

        /*大文件超了*/
        //$sql1='set global max_allowed_packet=268435456';
        //$this->pdo->query($sql1);
        $values='';$outglue="(";
        foreach ($arr as $vals){
            $values.=$outglue;$glue='';
            foreach ($vals as $val=>$key){
                $values.=$glue."'".$vals[$val]."'";
                $glue=',';
            }
            $values.=")";
            $outglue=',(';
        }
        $querys="insert into $table ($fied) values $values";
        $sql=$this->pdo->prepare($querys);
        $sql->execute();
        if ($this->pdo->errorCode()!=00000) {
            return $this->pdo->errorInfo()['2'];
        }else{
            return ;
        }
    }
    /*上一次插入的最后id*/
    function lastInsertId(){
        return $this->pdo->lastInsertId();
    }
}
?>