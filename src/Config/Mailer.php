<?php

namespace Sys\Config;

class Mailer extends \Sys\Object
{
    public $Host = "smtp.yandex.ru";
    public $SMTPAuth = true;
    public $Username = "invicta2017";
    public $Password = "invicta";
    public $Port = "465";
    public $SMTPSecure = "ssl";
    public $Charset = "utf-8";
    public $From = "invicta2017@yandex.ru";
    public $FromName = "Invicta L.T.D.";

    /**
     * Отправка почты
     * 
     * @param type $from Почтовый адресс отправителя
     * @param type $from_name Имя отправителя
     * @param type $to Почтовый адресс получателя
     * @param type $to_name Имя получателя
     * @param type $subject Название письма
     * @param type $body Содержимое письма
     * @param type $attachment Вложенные файлы
     * @param type $attachment_name Названия вложенных файлов
     * @return string Если успешно то пустая строка в противном случае текст ошибки
     */
    public function Send($to, $to_name, $body, $subject="", $from=null, $from_name=null, $attachment = null, $attachment_name = null) {
        
        $mail = new \PHPMailer\PHPMailer();

        //$mail->SMTPDebug = 3;                               // Enable verbose debug output
        
        $mail->isSMTP(); // Set mailer to use SMTP
        $mail->Host = $this->Host; // Specify main and backup SMTP servers
        $mail->SMTPAuth = $this->SMTPAuth; // Enable SMTP authentication
        $mail->Username = $this->Username; // SMTP username
        $mail->Password = $this->Password; // SMTP password
        $mail->SMTPSecure = $this->SMTPSecure; // Enable TLS encryption, `ssl` also accepted
        $mail->Port = $this->Port; // TCP port to connect to
        $mail->CharSet = $this->Charset;
        $mail->From = (isset($from)?$from:$this->From);
        $mail->FromName = (isset($from_name)?$from_name:$this->FromName);

        if ($to_name == null) {
            $mail->addAddress($to);
        } else {
            $mail->addAddress($to, $to_name);
        }
        
        if ($attachment != null) {
            if(is_array($attachment)){
                foreach ($attachment as $key => $value) {
                    $mail->addAttachment($value);
                }
            } else if($attachment_name != null) $mail->addAttachment($attachment, $attachment_name);
            else $mail->addAttachment($attachment);
        }

        $mail->isHTML(true);

        $mail->Subject = $subject;
        $mail->Body = $body;

        if (!$mail->send()) {
            return 'Warning: '.$mail->ErrorInfo;
        } else {
            return '';
        }
    }

}