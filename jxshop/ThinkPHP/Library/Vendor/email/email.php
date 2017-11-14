<?php
	require './class.phpmailer.php';
	$mail             = new PHPMailer();
	/*服务器相关信息*/
	$mail->IsSMTP();                        
	$mail->SMTPAuth   = true;               
	$mail->Host       = 'smtp.163.com';   	   
	$mail->Username   = 'phpresources';  		
	$mail->Password   = 'qazwsxedc123';
	/*内容信息*/
	$mail->IsHTML(true);
	$mail->CharSet    ="UTF-8";			
	$mail->From       = 'phpresources@163.com';	 		
	$mail->FromName   ="王尼玛";	
	$mail->Subject    = '邮件发送使用phpmailer'; 
	$mail->MsgHTML('邮件发送使用phpmailer');
   

	$mail->AddAddress('yolo_me@163.com');  
	$mail->AddAttachment("test/test.png"); 
	
	$res=$mail->Send();
	var_dump($res);
?>