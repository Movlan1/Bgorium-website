<?php

//Retrieve form data. 
//GET - user submitted data using AJAX
//POST - in case user does not support javascript, we'll use POST instead
$name = ($_GET['name']) ? $_GET['name'] : $_POST['name'];
$email = ($_GET['email']) ?$_GET['email'] : $_POST['email'];
$website= ($_GET['website']) ?$_GET['website'] : $_POST['website'];
$tarix= ($_GET['tarix']) ?$_GET['tarix'] : $_POST['tarix'];
$comment= ($_GET['comment']) ?$_GET['comment'] : $_POST['comment'];
$razmer= ($_GET['razmer']) ?$_GET['razmer'] : $_POST['razmer'];
$mehsul= ($_GET['mehsul']) ?$_GET['mehsul'] : $_POST['mehsul'];
$haqqinda= ($_GET['haqqinda']) ?$_GET['haqqinda'] : $_POST['haqqinda'];

//flag to indicate which method it uses. If POST set it to 1
if ($_POST) $post=1;

//Simple server side validation for POST data, of course, you should validate the email
if (!$name) $errors[count($errors)] = 'Please enter your name.';
if (!$email) $errors[count($errors)] = 'Please enter your email.'; 
if (!$website) $errors[count($errors)] = 'Please enter your website.'; 
if (!$tarix) $errors[count($errors)] = 'Please enter your tarix.'; 
if (!$comment) $errors[count($errors)] = 'Please enter your comment.'; 
if (!$razmer) $errors[count($errors)] = 'Please enter your razmer.'; 
if (!$mehsul) $errors[count($errors)] = 'Please enter your mehsul.'; 
if (!$haqqinda) $errors[count($errors)] = 'Please enter your haqqinda.'; 

//if the errors array is empty, send the mail
if (!$errors) {

	//recipient
	$to = 'DrBahruz.az <drbehruz@yahoo.com>';	
	//sender
	$from = $name . ' <' . $email . '>';
	
	//subject and the html message
	$subject = 'Randevu: ' . $name;	
	$message = '
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head></head>
	<body>
	<table style="border: 1px solid #FFC107;">
		<tr><td style="border: 1px solid #FFC107;"><b>Tarix:</b> </td><td style="border: 1px solid #FFC107;">' . $tarix . '</td></tr>
		<tr><td style="border: 1px solid #FFC107;"><b>Müştəri:</b> </td><td style="border: 1px solid #FFC107;">' . $name . '</td></tr>
		<tr><td style="border: 1px solid #FFC107;"><b>Telefonu:</b> </td><td style="border: 1px solid #FFC107;">' . $email . '</td></tr>
		<tr><td style="border: 1px solid #FFC107;"><b>Xidmət:</b> </td><td style="border: 1px solid #FFC107;">' . $website. '</td></tr>
		<tr><td style="border: 1px solid #FFC107;"><b>Mesajı:</b> </td><td style="border: 1px solid #FFC107;">' . $comment. '</td></tr>
		<tr><td style="border: 1px solid #FFC107;"><b>User info:</b> </td><td style="border: 1px solid #FFC107;">' . $haqqinda. '</td></tr>
	</table>
	</body>
	</html>';

	//send the mail
	$result = sendmail($to, $subject, $message, $from);
	
	//if POST was used, display the message straight away
	if ($_POST) {
		if ($result) echo 'Təşəkkürlər, məktubunuzu aldıq.';
		else echo 'Yenidən cəhd edin';
		
	//else if GET was used, return the boolean value so that 
	//ajax script can react accordingly
	//1 means success, 0 means failed
	} else {
		echo $result;	
	}

//if the errors array has values
} else {
	//display the errors message
	for ($i=0; $i<count($errors); $i++) echo $errors[$i] . '<br/>';
	echo '<a href="form.php">GeriyÉ™</a>';
	exit;
}


//Simple mail function with HTML header
function sendmail($to, $subject, $message, $from) {
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=utf-8" . "\r\n";
	$headers .= 'From: ' . $from . "\r\n";
	
	$result = mail($to,$subject,$message,$headers);
	
	if ($result) return 1;
	else return 0;
}

?>