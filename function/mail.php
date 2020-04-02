<?php

# 防止文件直接被访问
/*if( !defined( 'ERROR_MAIL' ) ){
    header("HTTP/1.0 404 Not Found");
    exit;
}*/

var_dump(ERROR_MAIL);exit;
//错误邮件
if( ERROR_MAIL ){
    headers_sent() ?  '' : header('content-type:text/html;charset=utf-8');
    //程序的错误处理方法
    /**
     * 程序错误时候会提示的错误信息
     * @todo 需要单独记录日志或者是发送报错邮件
     */
    function shutdown_func(){
        $log_content = "rn请求接口" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '发生错误';
        $error_info = error_get_last();
        //需要记录的错误级别
        $error_level = array(
            1,
            2,
            4,
//            8,
            16,
            32,
            64,
            128,
            256,
            512
        );

        //记录致命性的错误
        if( in_array(  $error_info['type'] , $error_level )  ) {
            $error_info = print_r( $error_info , true );
        }else{
            return '';
        }
        $err_obj = new ErrorMail();
        $err_obj->doWriteLog( $error_info );
    }

    #注册错误函数
    register_shutdown_function( 'shutdown_func' );

    # 自定义错误
    function userError( $errno, $errstr, $errfile, $errline ) {

        $log_content = "rn请求接口" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '发生错误';

        $not_write_log = array( 2 , 8 , 1024 , 2048 , 4096, 8191 );
        if( in_array( $errno , $not_write_log ) ){
            return '';
        }
        $log =  "rn" . '----------------------------------------------------'."rn Error: rn 错误编码：[$errno] rn 错误信息：$errstr";
        $log .=" Error on line $errline in $errfile";
        $log .="rn ---------------------------------------------------- rn";
        $err_obj = new ErrorMail( './log/');
        $err_obj -> doWriteLog( $log_content . $log );
    }
    set_error_handler('userError');
}
# 邮件的配置
$conf = array(

    //日志文件大小
    'log_size' => 10000000 ,

    //发生邮件频率
    'send_time' => 300 ,

    //邮件发送人
    'EMAIL_CONF' => array(
        //发送邮件的用户
        'send_user' => '***@****.com' ,
        'send_psd' => '*****' ,
        'send_smtp' => 'smtp.****.com',

        //接收邮件的Email
        'receive' => array(
            'zhangsan@163.com' ,
        ),

        'mail_title' => '[ANDROID_API_ERROR]API发生异常'
    )
);
/**
 * Class ErrorMail
 * 错误处理以及邮件发送类
 */
class ErrorMail{

    //错误文件的大小 [字节]
    public $error_log_size = '';

    //发生邮件的频率
    public $error_mail_send_time = '' ;

    //文件句柄
    public $handle = '';

    //日志文件
    public $Log_file_path = '';

    /**
     * 构造方法
     * ErrorMail constructor.
     */
    public function __construct( $file_path = ''){


        if( !empty( $file_path ) ){
            $this -> Log_file_path = str_replace( '\' , '/' ,  $file_path );
        }else{
            $this -> Log_file_path = str_replace( '\' , '/' , dirname( __FILE__ ) . '/accessLog/errorLog/api_Error.log' );
        }
 
        if( !is_file( $this -> Log_file_path ) ){
 
            $dir =  substr( $this -> Log_file_path , 0 , strrpos( $this -> Log_file_path , '/' ) );
            //递归创建文件夹
            if( !is_dir( $dir ) ){
                mkdir( $dir , 0777 , true );
            }
 
            //生成文件夹
            if( !is_file( $this -> Log_file_path ) ){
                $this -> handle  = fopen( $this -> Log_file_path , 'a+' );
            }
 
        }
 
        //文件句柄
        if( empty( $this -> handle ) ){
 
            $this -> handle  = fopen( $this -> Log_file_path , 'a+' );
        }
 
    }
 
    /**
     * 记录错误日志
     */
    public function doWriteLog( $log = '' ){
 
        $error_time = '[' . sprintf( '%s' , date('Y-m-d H:i:s') ) . ']';
 
        $error_info = $log;
 
 
        if( !empty( $log ) ){
            $error_info = $log;
        }else{
            $error_info = print_r( $error_info ,true ) ;
        }
 
        $error_msg = $error_info ;
 
        $log =  $error_time . $error_msg;
 
        fwrite( $this -> handle , $log );
 
        $this -> sendApiErrorMail();
    }
 
    /**
     * 判断是否需要发送邮件
     */
    public function sendApiErrorMail(){
 
        #声明全局变量
        global $conf;
 
        //文件创建时间超过五分钟，并且不为空 或者是 文件超过10M 就发送错误邮件
        $file_u_time = filectime( $this -> Log_file_path );
 
        //获取文件大小
        $file_size_byte = filesize( $this -> Log_file_path );
 
        if( ( time() -  $file_u_time >= $conf['send_time'] && $file_size_byte > 0 ) ||  $file_size_byte >= $conf['log_size']){
 
            //发送邮件
 
            $log_content = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
 
            $log_time = $_SERVER['REQUEST_TIME'];
 
            $send_result = $this -> __send_mail__( $conf['EMAIL_CONF']['receive'] ,  '' , $log_content . '接口发生问题，请求时间：' . $log_time . '请及时排查问题' );
 
            //删除错误日志文件
            if( $send_result['mark'] == true ){
 
                fclose( $this -> handle );
                $this -> handle  = '';
                //删除日志文件
                unlink( $this -> Log_file_path );
            }
 
        }
 
 
    }
 
 
    /**
     * 发送邮件
     */
    public function __send_mail__( $email_addr_arr , $reply_to , $mail_content ){
 
        #声明全局变量
        global $conf;
 
 
        //include_once  __DIR__ . '/ThinkPHP/Library/Vendor/Mail/class.phpmailer.php';
 
        //实例化邮件发送类
        $mail = new PHPMailer();
 
        // 使用SMTP方式发送
        $mail->IsSMTP();
 
        // 设置邮件的字符编码
        $mail->CharSet='UTF-8';
 
        // 企业邮局域名
        $mail->Host = $conf['EMAIL_CONF']['send_smtp'];
 
        // 启用SMTP验证功能
        $mail->SMTPAuth = true;
 
        //
        //邮件发送人的用户名(请填写完整的email地址)
        $mail->Username = $conf['EMAIL_CONF']['send_user'];
 
        // 邮件发送人的密码
        $mail->Password = $conf['EMAIL_CONF']['send_psd'];
 
        //邮件发送者email地址
        $mail->From = $conf['EMAIL_CONF']['send_user'];
 
        //发送邮件人的标题
        $mail->FromName = $conf['EMAIL_CONF']['send_user'];
 
        //收件人地址，可以替换成任何想要接收邮件的email信箱,格式是AddAddress("收件人email","收件人姓名")
        foreach( $email_addr_arr as $k => $v){
            $mail->AddAddress("$v", substr(  $v, 0 , strpos($v ,'@')));
        }
        if( !empty( $reply_to ) ){
            foreach( $reply_to as $kk => $vv){
                //收件人地址，可以替换成任何想要接收邮件的email信箱,格式是AddCC("收件人email","收件人姓名")
                $mail->AddCC("$vv", substr(  $vv, 0 , strpos($vv ,'@')));
            }
        }
 
        //回复的地址
        $mail->AddReplyTo(  $conf['EMAIL_CONF']['send_user'] , "" );
 
        //$mail->AddAttachment("./mail.rar"); // 添加附件
 
        // set email format to HTML //是否使用HTML格式
        $mail->IsHTML(true);
 
        //邮件标题
        $mail->Subject = $conf['EMAIL_CONF']['mail_title'];
 
        //邮件内容
        $mail->Body =  "

" . $mail_content . '
';
 
        //附加信息，可以省略
        $mail->AltBody = '';
 
        // 添加附件,并指定名称
        $mail->AddAttachment( $this -> Log_file_path ,'api_error.log');
 
        //设置邮件中的图片
        //$mail->AddEmbeddedImage("shuai.jpg", "my-attach", "shuai.jpg");
 
        if( !$mail->Send() ){
            $mail_return_arr['mark'] = false ;
            $str  =  "邮件发送失败.

";
            $str .= "错误原因: " . $mail->ErrorInfo;
            $mail_return_arr['info'] = $str ;
        }else{
            $mail_return_arr['mark'] = true ;
            $str =  "邮件发送成功";
            $mail_return_arr['info'] = $str ;
        }
        return $mail_return_arr;
 
    }
 
    /**
     * 析构函数
     */
    public function __destruct(  ){
 
        if( is_resource( $this -> handle ) ){
            //关闭文件句柄
            fclose( $this -> handle );
        }
    }
 
 
}


