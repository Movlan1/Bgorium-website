<?php
    function mailSenderForPre($kimden, $kime, $konu, $mesaj) {
        require "class.phpmailer.php"; // PHPMailer file request
        $mail = new PHPMailer();   
        $mail->IsSMTP(); 
        $mail->From     = $kimden; // Gönderen kısmında yer alacak e-mail adresi  
        $mail->Sender   = $kimden; 
        $mail->FromName = "DscPromo";  
        $mail->Host     = "DscPromo.lorem.az"; //SMTP server adresi 
        $mail->SMTPAuth = true;
        $mail->Username = "info@dscpromo.lorem.az"; //SMTP kullanıcı adı  
        $mail->Password = "#^lksV2Z4=+D"; //SMTP şifre 
        $mail->Port = "587";
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

    if(isset($_POST["prem-submit"])) {

        $link = (isset($_POST["link"])) ? $_POST["link"] : "";
        $device = (isset($_POST["device"])) ? $_POST["device"] : "";
        $ipAddress = (isset($_POST["ipAddress"])) ? $_POST["ipAddress"] : "";
        $nameAndSurname = (isset($_POST["prem-fullName"])) ? $_POST["prem-fullName"] : "";
        $selectedProduct = (isset($_POST["prem-product"])) ? $_POST["prem-product"] : "";
        $company = (isset($_POST["prem-company"])) ? $_POST["prem-company"] : "";
        $phoneNumber = (isset($_POST["prem-phoneNumber"])) ? $_POST["prem-phoneNumber"] : "";
        $email = (isset($_POST["prem-email"])) ? $_POST["prem-email"] : "";
        $message = (isset($_POST["prem-message"])) ? $_POST["prem-message"] : "";
        
        if($nameAndSurname == "" || $phoneNumber == "" || $email == "" || $message == "") {
            
            $siteNoticeMessage = "<p class='public-error-notice'>Xəta! Bütün sahələr düzgün doldurulmalıdır!</p>";
        } else {
            
            $mesajk = "<b>$nameAndSurname</b> sizə veb saytınızdan mesaj göndərdi:<hr />";
            $mesajk .= "<b>İp adresi:</b> $ipAddress <hr />";
            $mesajk .= "<b>Səhifə:</b> $link <hr />";
            $mesajk .= "<b>Cihaz:</b> $device <hr />";
            $mesajk .= "<b>Ad Soyad:</b> $nameAndSurname <hr />";
            $mesajk .= "<b>Telefon:</b> $phoneNumber <hr />";
            $mesajk .= "<b>Məhsul:</b> $selectedProduct <hr />";
            $mesajk .= "<b>Şirkət:</b> $company <hr />";
            $mesajk .= "<b>Email:</b> $email <hr />";
            $mesajk .= "<b>Mesaj:</b> $message <hr />";
            $mesajk .= "<span style='font-size:10px;color:#bbbbbb;'>Bu mesaj ". date('H:i:s d.m.Y') ." tarixində göndərildi.</span>";
    
            if(mailSenderForPre("ehmedliehmed0105@gmail.com","ehmedliehmed0105@gmail.com", "dscpromo saytından test mesaj", $mesajk)) {
                
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
    <head>
        <link rel="stylesheet" href="/assets/css/modal_request.css?v=<?php echo time(); ?>" />
    </head>
    <body>
        <form method="post" id="prem-sendmail" name="prem-form" action="">
	        <input type="hidden" name="link" value="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>" />
            <input type="hidden" name="device" value="<?php echo $_SERVER['HTTP_USER_AGENT']; ?>" />
            <input type="hidden" name="ipAddress" value="<?php  echo $_SERVER['REMOTE_ADDR'] ;?>" />
            
            <div class="prem-form_holder">
                <div class="prem-form-head">
                    <p class="prem-form-title"> Qiymət təklifi al </p>
                    <button type="button" data-modal-id="#price-request-modal" class="prem-modal-close-btn btn closeBtn" aria-label="Close"> 
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><script xmlns="" id="eppiocemhmnlbhjplcgkofciiegomcon"></script>
                            <path d="M20 4L4 20" stroke="#222B30" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M4 4L20 20" stroke="#222B30" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                <div class="prem-form-body">
                    <div class="prem-form-body_holder">
                        <?php echo "<p class=\"prem-send-notice\"> $siteNoticeMessage </p>"; ?>
                        <div class="prem-form-inputs">
                            <div class="prem-contact-inputGroup prem-contact-inputGroup1">
                                <label class="prem-fullName-label prem-label" for="prem-fullName"> Ad Soyad: * </label>
                                <input required class="prem-contact-form-input" type="text" name="prem-fullName" id="prem-contact-form-name" />
                            </div>
                            <div class="prem-contact-inputGroup prem-contact-inputGroup5">
                                <label class="prem-product-label prem-label" for="prem-product"> Məhsullar: * </label>
                                <select class="prem-contact-form-input" name="prem-product" id="prem-contact-form-product">
                                    <option value=""></option>
                                    <?php
                                        foreach($cat_info as $key=>$value) {
                                            if($value['id'] >= 15 && $value['id'] <= 59 ) {
                                                echo "
                                                    <option value=\"$value[name]\"> $value[name] </option>
                                                ";
                                            }
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="prem-contact-inputGroup prem-contact-inputGroup6">
                                <label class="prem-label prem-company-label" for="prem-company"> Təmsil etdiyiniz şirkət: * </label>
                                <input required class="prem-contact-form-input" type="text" name="prem-company" id="prem-contact-form-company" />
                            </div>
                            <div class="prem-contact-inputGroup prem-contact-inputGroup2">
                                <label class="prem-label prem-phoneNumber-label" for="prem-phoneNumber"> Telefon: * </label>
                                <input required class="prem-contact-form-input" type="text" name="prem-phoneNumber" id="prem-contact-form-phoneNumber" />
                            </div>
                            <div class="prem-contact-inputGroup prem-contact-inputGroup3">
                                <label class="prem-label prem-email-label" for="prem-email"> E-mail: * </label>
                                <input required class="prem-contact-form-input" type="text" name="prem-email" id="prem-contact-form-email" />
                            </div>
                            <div class="prem-contact-inputGroup prem-contact-inputGroup4">
                                <label class="prem-label prem-email-label" for="prem-email"> Qeydləriniz: * </label>
                                <textarea name="prem-message" id="prem-contact-form-message" rows="6"></textarea>
                            </div>
                        </div>
                        <button name="prem-submit" type="submit" role="button" class="prem-contact-form-submit"> Göndər </button>
                    </div>
                </div>
            </div>
    	</form>
    </body>
</html>