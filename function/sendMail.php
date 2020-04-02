<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/19
 * Time: 10:50
 */

/*发邮件*/

if(!function_exists('smtp_mail')) {
    require_once FPATH."phpmailer/class.phpmailer.php";
    function smtp_mail($sendto_email, $subject, $body, $att = array())
    {
        // global $dsql;
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->Host = "smtp.163.com";
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->Username = "h2913327703@163.com";   //发送邮件账号
        $mail->Password = "ckww123ab";     //发信邮件密码
        $mail->FromName = "雄-个人博客";   //发件人姓名
        $mail->SMTPAuth = true;
        $mail->From = $mail->Username;
        $mail->CharSet = "utf-8";
        $mail->Encoding = "base64";
        if (is_array($sendto_email)) {
            foreach ($sendto_email as $addressv) {
                $mail->AddAddress($addressv);
            }
        } else {
            $mail->AddAddress($sendto_email);
        }
        $mail->IsHTML(true);
        $mail->Subject = $subject;
        $mail->Body = "<div style='max-width: 800px;margin: 0 auto;'>".$body."</div>";
        if (!$mail->Send()) {
            echo "邮件错误信息: " . $mail->ErrorInfo;
        }/*else{
        $query="update `#@__diyform1` set hassend='true' WHERE id=$id";
        $dsql->ExecuteNoneQuery($query);
    }*/
    }
}
