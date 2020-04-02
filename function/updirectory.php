<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/26
 * Time: 10:41
 */

function updirectory($upfilename,$name='',$dir=''){
    /*php 并不支持中文文件名  建议少用中文名  */
    function createFolder($path) {
        //$path = iconv('UTF-8', 'gb2312', $path);//解决不能名中文名的问题
        if (!file_exists($path)) {
            mkdir($path,0777);
            createFolder(dirname($path));//递归 实现创建多重文件夹
        }
    }
    $dir=empty($dir) ? Vfile : $dir;
    $fn = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);

    if ($fn) {
        //uploaded file name include path
        $fname = $_GET[$upfilename];
        $name=$_GET[$name];
        //$targetfile = iconv('UTF-8', 'gb2312', Vfile.'/'.dirname($fname));//支持中文名
        if ($fname!=''&& $fname!='undefined') {
            //修改文件夹名称
            $names=explode('/',$fname);
            if(!empty($name)&&$names[0]!=$name){
                $fname= str_replace($names[0], $name, $fname);
                $names[0]=$name;
            }else{
                $newname=date("Y-m-d_h_i",time());//名字都用时间戳命名，除了自定义名称 因为多文件，上传时间长导致名称不同  时间到分
                $fname= str_replace($names[0], $newname, $fname);
                $names[0]=$newname;
            }
            $targetfile = iconv('UTF-8', 'gb2312', $dir.'/'.$fname);//支持中文名
        }else{
            $targetfile = iconv('UTF-8', 'gb2312', $dir.'/'.$fn);
            $names=explode('/',$fn);
        }
        $gfname=iconv('UTF-8', 'gb2312',$dir.'/'.$names[0]);
        /*文件已存在，退出上传*/
        /*if(file_exists($gfname)) {
            echo json_encode(array('status'=>3,'msg'=>'该文件已存在，请重新命名！'));
            exit();
        }*/
        createFolder(dirname($targetfile));
        // AJAX call
        if (file_put_contents($targetfile,file_get_contents('php://input'))){
            echo json_encode(array('name'=>$names[0],'status'=>1));
        }else{
            echo json_encode(array('status'=>0));
        }

    }
    else {

        // form submit
        $files = $_FILES['fileselect'];
        print_r($files);
        foreach ($files['error'] as $id => $err) {
            if ($err == UPLOAD_ERR_OK) {
                $fn = $files['name'][$id];
                move_uploaded_file(
                    $files['tmp_name'][$id],
                    Vfile.'/'. $fn
                );
                echo "<p>File $fn uploaded.</p>";
            }
        }

    }
}