<?php
/*列出所有文件*/
function myscandir($dirname,&$arr_files) {
    $arr = array();
    if(Is_dir($dirname)){
        $dir=opendir($dirname);
    }else{
        return ;
    }

    while($filename=readdir($dir)){
        $file=$dirname."/".$filename;
        if($filename!="." && $filename!=".."){
            if(is_dir($file)){
                myscandir($file,$arr_files); //递归完成
            }else{
                $arr_files[] = $file;
            }
        }
    }
    closedir($dir);
    return $arr_files;
}
/*针对性列出*/
/*-----列出所有的HTML、php 和文件夹---------------*/
function get_php_html($dirname,&$arr_files){
    $arr = array();
    if(Is_dir($dirname)){
        $dir=opendir($dirname);
    }else{
        return $dirname;
    }

    while($filename=readdir($dir)){
        $file=$dirname."/".$filename;

        if($filename!="." && $filename!=".."){
            if(is_dir($file)){//文件夹 返回文件名称和大小
                $num=sizeof(scandir($file));
                $num=($num>2)?$num-2:0;//文件数量
                $size=dirsize($file);
                $arr_files[$filename]['name']=$filename;
                $arr_files[$filename]['num']=$num;
                $arr_files[$filename]['size']=$size;
                //myscandir($file,$arr_files); //递归完成
            }else{
                $arr_files[] = $file;

            }
        }
    }
    closedir($dir);
    return $arr_files;
}


  /*  $arr_files = array();
    myscandir('/www/web/test',$arr_files);
    print_r($arr_files);*/
//求目录大小
function toSize( $size){
    $dw="Bytes";
    if( $size >  pow(2, 30)){
        $size= round( $size/ pow(2, 30), 2);
        $dw="GB";
    } else  if( $size >  pow(2, 20)){
        $size= round( $size/ pow(2, 20), 2);
        $dw="MB";
    } else  if( $size >  pow(2, 10)){
        $size= round( $size/ pow(2, 10), 2);
        $dw="KB";
    } else{
        $dw="bytes";
    }
    return  $size. $dw;
}

function dirsize( $dirname) {
    $dirsize=0;

    $dir= opendir( $dirname);

    while( $filename= readdir( $dir)){
        $file= $dirname."/". $filename;
        if( $filename!="." &&  $filename!=".."){
            if( is_dir( $file)){
                $dirsize+=dirsize( $file);  // 递归完成
            } else{
                $dirsize+= filesize( $file);
            }
        }
    }
    closedir( $dir);

    return  $dirsize;

}
