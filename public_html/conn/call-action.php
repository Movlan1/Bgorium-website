<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;  
    
function callActions($kimden, $kime, $konu, $mesaj, $config) {
    require_once 'class.phpmailer.php';
    require_once 'smtp.php';
    require_once 'exception.php';
    
        $mail = new PHPMailer();   
        $mail->IsSMTP(); 
        $mail->From     = $kimden; // Gönderen kısmında yer alacak e-mail adresi  
        $mail->Sender   = $kimden; 
        $mail->FromName = $config["home_title"];  
        $mail->Host     = $config["smtp_host"]; //SMTP server adresi 
        $mail->SMTPAuth = true;
        $mail->Username = $config["smtp_user"];; //SMTP kullanıcı adı  
        $mail->Password = $config["smtp_pass"]; //SMTP şifre 
        $mail->Port = $config["smtp_port"];
        $mail->CharSet = "utf-8";
        $mail->WordWrap = 50;  
        $mail->IsHTML(true); //Mailin HTML formatında hazırlanacağını bildiriyoruz.  
        $mail->Subject  = $konu; // Konu  
        //Mailimizin gövdesi: (HTML ile)  
        $body = $mesaj;
        // HTML okuyamayan mail okuyucularda görünecek düz metin:  
        $textBody = strip_tags($mesaj);
        $mail->Body = $body;  
        $mail->AltBody = $textBody;  
        $mail->AddAddress($kime);
        // Mail gönderilecek adresleri ekliyoruz.  
        return ($mail->Send()) ? true : false;      
        $mail->ClearAddresses();  
        $mail->ClearAttachments();
    }
?>

<?php
    $siteNoticeMessage="";

    if(isset($_POST["submit"])) {

        $link = (isset($_POST["link"])) ? $_POST["link"] : "";
        $device = (isset($_POST["device"])) ? $_POST["device"] : "";
        $ipAddress = (isset($_POST["ipAddress"])) ? $_POST["ipAddress"] : "";
        $nameAndSurname = (isset($_POST["fullName"])) ? $_POST["fullName"] : "";
        $phoneNumber = (isset($_POST["phoneNumber"])) ? $_POST["phoneNumber"] : "";
        $message = (isset($_POST["message"])) ? $_POST["message"] : "";
        $email = (isset($_POST["email"])) ? $_POST["email"] : "";
        
        if($nameAndSurname == "" || $phoneNumber == "" || $email == "" || $message == "") {
            
            $siteNoticeMessage = "<p class='public-error-notice'>Xəta! Bütün sahələr düzgün doldurulmalıdır!</p>";
        } else {
            
            $mesajk = "<b>$nameAndSurname</b> sizə veb saytınızdan mesaj göndərdi:<hr />";
            $mesajk .= "<b>İp adresi:</b> $ipAddress <hr />";
            $mesajk .= "<b>Səhifə:</b> $link <hr />";
            $mesajk .= "<b>Cihaz:</b> $device <hr />";
            $mesajk .= "<b>Ad Soyad:</b> $nameAndSurname <hr />";
            $mesajk .= "<b>Telefon:</b> $phoneNumber <hr />";
            $mesajk .= "<b>Şirkət:</b> $email <hr />";
            $mesajk .= "<b>Mesaj:</b> $message <hr />";
            $mesajk .= "<span style='font-size:10px;color:#bbbbbb;'>Bu mesaj ". date('H:i:s d.m.Y') ." tarixində göndərildi.</span>";
    
            if(callActions($config["smtp_user"], $config["smtp_user"], "progress saytından test mesaj", $mesajk, $config)) {
                
                $siteNoticeMessage =  "<p class='success-message'>
                Müraciət müvəffəqiyyətlə göndərildi.<br> Ən qısa zamanda sizinlə əlaqə saxlayacağıq. <br> Təşəkkür edirik !</p>";
            } else {
                
                $siteNoticeMessage =  "<p class='bad-request'> Göndərilmə uğursuz oldu. </p>";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="az">
   
    <body>
         <?php echo $siteNoticeMessage ?>
        <form method="post" id="sendmail" name="sendmail" action="">
	        <input type="hidden" name="link" value="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>" />
            <input type="hidden" name="device" value="<?php echo $_SERVER['HTTP_USER_AGENT']; ?>" />
            <input type="hidden" name="ipAddress" value="<?php  echo $_SERVER['REMOTE_ADDR'] ;?>" />
                <div class="contact-action-forms">
                <div class="col-12 col-lg-6 col-md-6">
                  <div class="contact-action-wrapper">
                    <input type="text" placeholder="Ad,Soyad">
                  </div>
                </div>
                <div class="col-12 col-lg-6 col-md-6">
                  <div class="contact-action-wrapper">
                    <input type="tel" placeholder="Telefon nömrəniz">
                  </div>
                </div>
                <div class="col-12">
                  <div class="contact-action-wrapper">
                    <input type="email" placeholder="E-mail">
                  </div>
                </div>
                <div class="col-12 col-lg-6 col-md-6">
                  <div class="contact-action-btn">
                    <button>Göndər</button>
                  </div>
                </div>
              </div>
    	</form>
    </body>
</html>