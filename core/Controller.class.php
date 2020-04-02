<?php
/**
 * 总控制器
 */
class Controller
{
    public $control;
    public $model;
    public $view;
    public $action;

    function __construct()
    {
       /* $this->model=new Model();*/
       /* $this->view=new View();*/
    }

    /*运行 ，路由的处理*/
    function run(){
        global $conf,$in_control,$in_action,$temporary;
        /*路径的处理
         *在$in_control,$in_action内
         * 或者在views内
         * */

            $_temp=$_SERVER['REQUEST_URI'];
            $_temp1=str_replace(['/index.php'],'',$_temp);
            $_temp2=preg_replace("/\?(.*)$/",'',$_temp1);//清除？后面的参数
            $_temp_url = explode('/',$_temp2);

            if(!empty($_temp_url[1]) && $_temp_url[1]!=='index.php'){
                $c_pstr=array_search($_temp_url[1],$in_control,true);
                if(is_numeric($c_pstr)){
                    $this->control=$_temp_url[1];
                    if(!empty($_temp_url[2]) && in_array($_temp_url[2],$in_action[$c_pstr])){
                        $this->action=$_temp_url[2];
                    }else{
                        if(in_array($_temp_url[2],$temporary)){
                            $this->action="temporary";
                        }else{
                            $this->to404();
                        }
                    }
                } else if(isset($_REQUEST['c']) && empty($this->control)){
                    //echo $_REQUEST['c'];
                    $this->main_parameter($in_control,$in_action,$temporary);
                }else{
                    $this->to404();
                }
            }else if(isset($_REQUEST['c']) && empty($this->control)){
                $this->main_parameter($in_control,$in_action,$temporary);

            }else if(!empty($_temp_url[1]) && $_temp_url[1]==='views'){
                header('location:'.$_temp);
                exit();
            }else if(($_temp==='/'||$_temp==='/index.php') && empty($_REQUEST['c'])){
                $this->control=$conf['default_c'];
                $this->action=$conf['default_a'];
            }else{
                $this->to404();
            }


        $control=ucfirst($this->control.'Controller');
        $control_path=ROOT_PATH.'/Controllers/'.$control.'.class.php';
        if (!is_file($control_path)) {
            $this->to404();
        }
        include_once $control_path;
        $control_class=new $control();
        $action=&$this->action;
        if(method_exists($control_class,$action)){
            $control_class->$action($_temp2);
        }else{
            $this->to404();
        }

    }
    /*视图*/
    public function display($action,$arr='',$arr_page='',$arr_oth=array()){
        global $conf;
        //有些页面需要的变量比较多，由数组换成变量
        if(is_array($arr_oth)){
            foreach ($arr_oth as $key=>$val){
                $$key=$val;
            }
        }
        $name= stripos($action,'.') ? $action: $action.$conf['extension'];
       include_once ROOT_PATH.'/Views/'.$arr_page.$name;
    }
    /**
     * 加载模型
     * @param  string $modelName 模型名称
     * @return class            模型对象
     */
    public function loadModel()
    {
        //$model=ucfirst($modelName.'Model');
        //include_once ROOT_PATH.'/models/'.$model.'.php';
        $mysql=new Model();

        return $mysql;
    }

    /*404跳转*/
    public function to404(){
        //header('location:/Views/back/404.html');
        include_once (Vback.'/404.html');
        exit();
    }

    //重定向
    public function redirect($url){
        header("Location: $url");

    }
    /*参数的处理*/
    function main_parameter ($in_control=array(),$in_action=array(),$temporary=array()){
        $c_pstr=array_search($_REQUEST['c'],$in_control,true);
        if(is_numeric($c_pstr)){
            $this->control=$_REQUEST['c'];
            if (isset($_REQUEST['a']) && in_array($_REQUEST['a'],$in_action[$c_pstr])) {
                $this->action=$_REQUEST['a'];
            }else{
                if(in_array($_REQUEST['a'],$temporary)){
                    $this->action="temporary";
                }else{
                    $this->to404();
                }
                $this->to404();
            }
        }
    }

}
?>