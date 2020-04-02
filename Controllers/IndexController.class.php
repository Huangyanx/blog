<?php

class IndexController extends Controller
{

    function __construct()
    {
        parent::__construct();
    }
    function index(){
        //print_r(get_parent_class());
        //$action=$this->action;
        $this->display('index');
    }
}
?>