<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/vendor/autoload.php';

// Проверяем, была ли отправлена форма
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получаем данные формы
    $name = htmlspecialchars($_POST['fullName']);
    $phone = htmlspecialchars($_POST['phoneNumber']);
    $email = htmlspecialchars($_POST['email']);
    $message_content = htmlspecialchars($_POST['message']);

    // Информация для отправки письма
   /* $to = "ceyhun.rzayeev@gmail.com";
    $subject = "Новая заявка от спецдверистрой.рф@server56.hosting.reg.ru";
    
    $headers = "From: спецдверистрой.рф" . "\r\n" .
               "Reply-To: $email" . "\r\n" .
               "X-Mailer: PHP/" . phpversion();*/
               
$mail = new PHPMailer;
$mail->CharSet = 'UTF-8';

// От кого
$mail->setFrom("admin@laocar.by", 'Заявка с сайта');        

// Кому
$mail->addAddress('laoCarby@gmail.com', ''); // Основной адрес
$mail->addAddress('n65732090@gmail.com', ''); // Дополнительный адрес
 //Тут указываем почту для заявок


// Тема письма 
$mail->Subject = 'Заявка с сайта LAOCAR';

// Настройки SMTP
$mail->isSMTP();
$mail->SMTPAuth = true;
$mail->SMTPDebug = 0;
 
$mail->Host = 'localhost';
$mail->Port = 587;
$mail->Username = 'admin@laocar.by';
$mail->Password = 'rd4SW}o^^Y*S';

$message = "<div>Вы получили сообщение от $name. (СТРАНИЦА - СДАТЬ АВТО)</div>
            <div>Телефон: $phone</div>
            <div>E-mail: $email</div>
            <br>
            <div>Описание автомобиля: $message_content</div>";

// Тело письма
$body = $message;
$mail->msgHTML($body);



if ($mail->send())
{
    echo "Сообщение успешно отправлено.";
} else {
    echo "Ошибка при отправке сообщения. Проверьте настройки сервера.";
}  

exit();

 /*   // Отправляем письмо
    if (mail($to, $subject, $message, $headers)) {
        echo "Сообщение успешно отправлено.";
    } else {
        echo "Ошибка при отправке сообщения. Проверьте настройки сервера.";
    }*/
}
?>
