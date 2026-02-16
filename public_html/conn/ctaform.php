<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/vendor/autoload.php';

// Проверяем, была ли отправлена форма
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получаем данные формы
    $name = htmlspecialchars($_POST['name']);
    $phone = htmlspecialchars($_POST['phone']);
    $message = htmlspecialchars($_POST['message']);
    $email = htmlspecialchars($_POST['email']);
   
$mail = new PHPMailer;
$mail->CharSet = 'UTF-8';

// От кого
$mail->setFrom("info@danilamaster60.ru", 'Заявка с сайта');        

// Кому
$mail->addAddress('info@danilamaster60.ru', ''); // Основной адрес
$mail->addAddress('prodoski@mail.ru', ''); // Дополнительный адрес
 //Тут указываем почту для заявок


// Тема письма 
$mail->Subject = 'Заявка с сайта Данила мастер';

// Настройки SMTP
$mail->isSMTP();
$mail->SMTPAuth = true;
$mail->SMTPDebug = 0;
 
$mail->Host = 'mail.hosting.reg.ru';
$mail->Port = 587;
$mail->Username = 'info@danilamaster60.ru';
$mail->Password = 'lN4nI4lF0dnH5vV1';

$message = "<div>Вы получили сообщение от $name. (СТРАНИЦА - Данила мастер)</div>
            <div>Ваше имя: $name</div>
            <div>Номер телефона: $phone</div>
            <div>Почта: $email</div>
            <div>Сообщение: $message</div>";

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

}
?>
