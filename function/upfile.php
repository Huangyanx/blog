<?php
    function upload($upfile,$uptypes='',$destination_folder=''){
        //上传文件类型列表
        if(count($uptypes)<2){
            $uptypes=array(
                'image/jpg',
                'image/jpeg',
                'image/png',
                'image/bmp',
            );
        }

        $max_file_size=2000000;     //上传文件大小限制, 单位BYTE
        $destination_folder=empty($destination_folder)? 'public/uploads/image/':$destination_folder; //上传文件路径
        if (!is_uploaded_file($_FILES[$upfile]["tmp_name"]))
            //是否存在文件
        {
            echo "图片不存在!";
           return array('status'=>2,'msg'=>'图片不存在');
        }
        else {
            $file = $_FILES[$upfile];
            if($max_file_size < $file["size"])
                //检查文件大小
            {
                echo "文件太大!";
                return array('status'=>3,'msg'=>'文件太大');
            }
            else if(!in_array($file["type"],$uptypes))
                //检查文件类型
            {
                echo "文件类型不符!".$file["type"];
                return array('status'=>4,'msg'=>"文件类型不符!".$file["type"]);
            }
            else {
                if(!file_exists($destination_folder))
                    mkdir($destination_folder); //创建文件包
                $filename=$file["tmp_name"];
                $image_size = getimagesize($filename);
                $pinfo=pathinfo($file["name"]);
                $ftype=$pinfo['extension'];
                $name=time();
                $destination = $destination_folder.$name.".".$ftype;
                session_start();
                $_SESSION["destination"]=$destination;
                if (file_exists($destination) && $overwrite != true)
                {
                    echo "同名文件已经存在了";
                    return array('status'=>5,'msg'=>"同名文件已经存在了");
                }

                else if(!move_uploaded_file ($filename, $destination))
                {
                    echo "移动文件出错";
                    return array('status'=>6,'msg'=>"移动文件出错");
                }
                else {
                    return array('status'=>1,'msg'=>"上传成功",'url'=>$destination,'name'=>$name,'type'=>$ftype);
                }

            }
        }

    }




?>